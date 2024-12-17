<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Friendship;
use Illuminate\Support\Facades\Auth;

class FriendshipsController extends Controller
{
    /**
     * Display a listing of the friendships of the user.
     */
    public function index()
    {

        // Auth id can be the user_id or the friend_id in the db so we need to check both
        $friendsFromUser = Friendship::where('user_id', Auth::id())
            ->where('status', 'accepted')
            ->with(['user', 'friend'])
            ->get();

        $friendsFromFriends = Friendship::where('friend_id', Auth::id())
            ->where('status', 'accepted')
            ->with(['user', 'friend'])
            ->get();

        $friends = $friendsFromFriends->merge($friendsFromUser);

        return view('friendships.index', [
            'friends' => $friends,
        ]);
    }

    /**
     * Show the form for creating a new friendship.
     */
    public function create()
    {
        return view('friendships.create');
    }

    public function pending()
    {
        $pendingRequestsFromFriends = Friendship::where('friend_id', Auth::id())
            ->where('status', 'pending')
            ->with('user', 'friend')
            ->get();

        $pendingRequestsFromUser = Friendship::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->with(['user', 'friend'])
            ->get();

        $pendingRequests = $pendingRequestsFromFriends->merge($pendingRequestsFromUser);

        return view('friendships.pending', data: [
            'pendingRequests' => $pendingRequests,
        ]);
    }

    /**
     * Store a newly created friendship in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|exists:users,name',
        ]);

        $friend = User::where('name', $validated['name'])->first();

        if ($friend->id === Auth::id()) {
            return back()->with('error', 'Vous ne pouvez pas vous ajouter vous même');
        }

        $friendshipExists = Friendship::where(function ($query) use ($friend) {
            $query->where('user_id', auth()->id())
                ->where('friend_id', $friend->id);
        })->orWhere(function ($query) use ($friend) {
            $query->where('user_id', $friend->id)
                ->where('friend_id', auth()->id());
        })->exists();

        if ($friendshipExists) {
            return back()->with('error', 'Vous êtes déjà amis, bloqué ou une demande est en attente.');
        }

        Friendship::create([
            'user_id' => auth()->id(),
            'friend_id' => $friend->id,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Demande d\'ami envoyée !');
    }

    /**
     * Accept a friendship request.
     *
     * @param  int  $friendship_id
     * @return \Illuminate\Http\Response
     */
    public function accept($friendship_id)
    {
        $friendship = Friendship::find($friendship_id);

        if ($friendship && $friendship->status === 'pending') {
            // Met à jour le statut directement
            $friendship->update(['status' => 'accepted']);
            return back()->with('success', 'Demande d\'ami acceptée!');
        }

        return back()->with('error', 'La demande d\'ami n\'a pas pu être acceptée');
    }

    /**
     * Decline a friendship request.
     *
     * @param  int  $friendship_id
     * @return \Illuminate\Http\Response
     */
    public function decline($friendship_id)
    {
        $friendship = Friendship::find($friendship_id);

        if ($friendship) {
            $friendship->delete();
            return back()->with('success', 'Demande d\'ami refusée');
        }

        return back()->with('error', 'La demande d\'ami n\'a pas pu être refusée');
    }

    /**
     * Remove the specified friendship from storage.
     *
     * @param  int  $friend
     * @return \Illuminate\Http\Response
     */
    public function destroy($friend)
    {
        $friendship = Friendship::where(function ($query) use ($friend) {
            $query->where('user_id', Auth::id())
                ->where('friend_id', $friend);
        })->orWhere(function ($query) use ($friend) {
            $query->where('user_id', $friend)
                ->where('friend_id', Auth::id());
        })->first();

        if ($friendship) {
            $friendship->delete();
            return back()->with('success', 'Succès de la suppression de l\'ami');
        }

        return back()->with('error', 'Impossible de supprimer l\'ami');
    }

    /**
     * Block a user.
     *
     * @param  int  $friend
     * @return \Illuminate\Http\Response
     */
    public function block($friend)
    {
        $friend = User::findOrFail($friend);

        if ($friend->id === Auth::id()) {
            return back()->with('error', 'Impossible de vous bloquer vous même');
        }

        $friendship = Friendship::where(function ($query) use ($friend) {
            $query->where('user_id', auth()->id())
                ->where('friend_id', $friend->id);
        })->orWhere(function ($query) use ($friend) {
            $query->where('user_id', $friend->id)
                ->where('friend_id', auth()->id());
        })->first();

        if ($friendship) {
            if ($friendship->status === 'blocked') {
                return back()->with('error', 'Cet utilisateur est déjà bloqué');
            }
            $friendship->update(['status' => 'blocked']);
            return back()->with('success', 'Utilisateur bloqué');
        } else {
            // Create a new friendship with 'blocked' status if no existing relation is found
            Friendship::create([
                'user_id' => auth()->id(),
                'friend_id' => $friend->id,
                'status' => 'blocked',
            ]);
            return back()->with('success', 'Utilisateur bloqué');
        }
    }
}
