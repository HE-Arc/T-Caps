<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use \App\Models\User;
use \App\Models\Friendship;
use Illuminate\Support\Facades\Auth;

class FriendshipsController extends Controller
{
    //Show every user friends
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
        return view('friendships.index', ['friends' => $friends]);
    }




    //show one friend profile
    function friends()
    {

    }

    function addFriend(Request $request)
    {

    }

    function removeFriend(Request $request)
    {

    }

    function declineFriend(Request $request)
    {

    }

    function acceptFriend(Request $request)
    {

    }
}
