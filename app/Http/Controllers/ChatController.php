<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index()
    {
        $discussions = auth()->user()->chats()
            ->with(['messages' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->latest()
            ->get();

        return view('dashboard', [
            'discussions' => $discussions,
            'selectedDiscussion' => $discussions->first(),
        ]);
    }
}
