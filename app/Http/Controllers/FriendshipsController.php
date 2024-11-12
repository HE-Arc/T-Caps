<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Friendship;
use Illuminate\Support\Facades\Auth;

class FriendshipsController extends Controller
{
    public function index()
    {
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|exists:users,name',
        ]);

        $friend = User::where('name', $validated['name'])->first();

        if ($friend->id === Auth::id()) {
            return back()->with('error', 'You cannot add yourself.');
        }

        $friendshipExists = Friendship::where(function ($query) use ($friend) {
            $query->where('user_id', auth()->id())
                ->where('friend_id', $friend->id);
        })->orWhere(function ($query) use ($friend) {
            $query->where('user_id', $friend->id)
                ->where('friend_id', auth()->id());
        })->exists();

        if ($friendshipExists) {
            return back()->with('error', 'You are already friends, blocked or a request is pending.');
        }

        Friendship::create([
            'user_id' => auth()->id(),
            'friend_id' => $friend->id,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Friend request sent !');
    }

    public function accept(Request $request, $friendship_id)
    {
        $friendship = Friendship::find($friendship_id);

        if ($friendship && $friendship->status === 'pending') {
            // Met à jour le statut directement
            $friendship->update(['status' => 'accepted']);
            return back()->with('success', 'Friend request accepted!');
        }

        return back()->with('error', 'Could not accept the friend request.');
    }

    public function decline(Request $request, $friendship_id)
    {
        $friendship = Friendship::find($friendship_id);

        if ($friendship) {
            $friendship->delete();
            return back()->with('success', 'Friend request declined!');
        }

        return back()->with('error', 'Could not decline the friend request.');
    }

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
            return back()->with('success', 'Friend removed successfully!');
        }

        return back()->with('error', 'Could not find the friend to remove.');
    }

    public function block($friend)
    {
        $friend = User::findOrFail($friend);

        if ($friend->id === Auth::id()) {
            return back()->with('error', 'You cannot block yourself.');
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
                return back()->with('error', 'This user is already blocked.');
            }
            $friendship->update(['status' => 'blocked']);
            return back()->with('success', 'User blocked successfully!');
        } else {
            // Create a new friendship with 'blocked' status if no existing relation is found
            Friendship::create([
                'user_id' => auth()->id(),
                'friend_id' => $friend->id,
                'status' => 'blocked',
            ]);
            return back()->with('success', 'User blocked successfully!');
        }
    }
}
