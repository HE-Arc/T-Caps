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

        $pendingRequestsFromFriends = Friendship::where('friend_id', Auth::id())
            ->where('status', 'pending')
            ->with('user', 'friend')
            ->get();

        $pendingRequestsFromUser = Friendship::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->with(['user', 'friend'])
            ->get();

        $friends = $friendsFromFriends->merge($friendsFromUser);
        $pendingRequests = $pendingRequestsFromFriends->merge($pendingRequestsFromUser);

        return view('friendships.index', [
            'friends' => $friends,
            'pendingRequests' => $pendingRequests,
        ]);
    }

    public function create()
    {
        return view('friendships.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|exists:users,name',
        ]);

        $friend = User::where('name', $validated['name'])->first();

        if ($friend->id === Auth::id()) {
            return redirect()->route('friends.index')->with('error', 'You cannot add yourself.');
        }

        $friendshipExists = Friendship::where(function ($query) use ($friend) {
            $query->where('user_id', auth()->id())
                ->where('friend_id', $friend->id);
        })->orWhere(function ($query) use ($friend) {
            $query->where('user_id', $friend->id)
                ->where('friend_id', auth()->id());
        })->exists();

        if ($friendshipExists) {
            return redirect()->route('friends.index')->with('error', 'You are already friends or a request is pending.');
        }

        Friendship::create([
            'user_id' => auth()->id(),
            'friend_id' => $friend->id,
            'status' => 'pending',
        ]);

        return redirect()->route('friends.index')->with('success', 'Friend request sent!');
    }

    public function accept(Request $request, $friendship_id)
    {
        $friendship = Friendship::find($friendship_id);

        if ($friendship && $friendship->status === 'pending') {
            // Met à jour le statut directement
            $friendship->update(['status' => 'accepted']);
            return redirect()->route('friends.index')->with('success', 'Friend request accepted!');
        }

        return redirect()->route('friends.index')->with('error', 'Could not accept the friend request.');
    }

    public function decline(Request $request, $friendship_id)
    {
        $friendship = Friendship::find($friendship_id);

        if ($friendship) {
            $friendship->delete();
            return redirect()->route('friends.index')->with('success', 'Friend request declined!');
        }

        return redirect()->route('friends.index')->with('error', 'Could not decline the friend request.');
    }

    public function removeFriend(Request $request)
    {
    }

    public function declineFriend(Request $request)
    {
    }

    public function acceptFriend(Request $request)
    {
    }
}
