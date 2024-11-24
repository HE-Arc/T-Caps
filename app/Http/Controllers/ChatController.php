<?php

namespace App\Http\Controllers;
use App\Models\Message;
use App\Models\Friendship;
use App\Models\Chat;


use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index()
    {
        $discussions = auth()->user()->chats()
            ->with([
                'messages' => function ($query) {
                    $query->latest()->limit(1);
                }
            ])
            ->latest()
            ->get();

        // Récupérer les amis acceptés
        $friends = Friendship::where(function ($query) {
            $query->where('user_id', auth()->id())
                ->orWhere('friend_id', auth()->id());
            })
            ->where('status', 'accepted')
            ->with('user', 'friend')
            ->get()
            ->map(function ($friendship) {
                return $friendship->user_id === auth()->id() ? $friendship->friend : $friendship->user;
            });

        return view('dashboard', [
            'discussions' => $discussions,
            'selectedDiscussion' => $discussions->first(),
            'friends' => $friends
        ]);
    }

    public function getMessages($chatId)
    {
        $messages = Message::where('chat_id', $chatId)
            ->orderBy('created_at', 'asc')
            ->with('user')
            ->get();

        return response()->json(['messages' => $messages]);
    }

    public function storeMessage(Request $request, $chatId)
    {
        $request->validate([
            'message' => 'required|string',
        ]);
        $message = new Message();
        $message->user_id = auth()->id();
        $message->chat_id = $chatId;
        $message->message = $request->message;
        $message->save();

        return response()->json(['message' => $message]);
    }

    public function storeChat(Request $request)
    {
        // Valider les champs du formulaire
        $request->validate([
            'chat_name' => 'required|string|max:255',
            'friends' => 'required|array|min:1', // Minimum 1 ami sélectionné
            'friends.*' => 'exists:users,id', // Vérifie si les amis existent dans la table users
        ]);

        // Créer la discussion dans la table 'chats'
        $chat = new Chat();  // Assurez-vous d'importer App\Models\Chat en haut
        $chat->name = $request->chat_name;
        $chat->save();

        // Associer l'utilisateur actuel à la discussion
        $chat->users()->attach(auth()->id());

        // Associer les amis sélectionnés à la discussion
        $chat->users()->attach($request->friends);

        // Rediriger avec un message de succès
        return redirect()->route('dashboard')->with('success', 'Discussion créée avec succès !');
    }

}
