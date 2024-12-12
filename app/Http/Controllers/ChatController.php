<?php

namespace App\Http\Controllers;
use App\Models\Message;

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

        return view('dashboard', [
            'discussions' => $discussions,
            'selectedDiscussion' => $discussions->first(),
        ]);
    }

    public function getMessages($chatId)
    {
        $messages = Message::where('chat_id', $chatId)
            ->orderBy('created_at', 'asc')
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

    public function storeCapsule(Request $request, $chatId)
    {
        // Validation
        $request->validate([
            'file' => 'required|file|mimes:jpeg,png,jpg,gif,mp3,mp4|max:2048',
            'message' => 'required|string',
        ]);

        // Vérification de la présence du fichier
        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $media = $request->file('file');
            $mediaName = time() . '_' . $media->getClientOriginalName();
            
            // Déplacer le fichier vers le répertoire public/source/media
            $media->move(public_path('source/media'), $mediaName);

            // Créer un nouveau message et l'enregistrer dans la base de données
            $message = new Message();
            $message->user_id = auth()->id();
            $message->chat_id = $chatId;
            $message->message = $request->message;
            $message->media_url = $mediaName;

            $message->save();

            return response()->json(['message' => $message]);
        }

        return response()->json(['error' => 'Le fichier n\'a pas été envoyé ou est invalide.'], 400);
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
