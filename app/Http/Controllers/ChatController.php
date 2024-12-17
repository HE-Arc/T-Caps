<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Friendship;
use App\Models\Chat;
use Illuminate\Support\Facades\DB;


use Illuminate\Http\Request;

/**
 * Controller for the chat system (discussions, messages, capsules, etc.).
 */
class ChatController extends Controller
{
    /** 
     * Show the dashboard with the list of discussions and pass the friends list to the view.
     */
    public function index()
    {
        // Get the list of discussions with the last message and the members
        $discussions = auth()->user()->chats()
            ->with(['messages' => function ($query) {
                $query->latest()->limit(1);
            }, 'members'])
            ->latest()
            ->get()
            ->map(function ($discussion) {
                // We check if the discussion is a private chat
                if ($discussion->members->count() == 2) {
                    // And set the profile picture of the other member as the discussion picture
                    $otherMember = $discussion->members->first()->id == auth()->id()
                        ? $discussion->members->skip(1)->first()
                        : $discussion->members->first();

                    $discussion->discussionPicture = $otherMember->image
                        ? asset('storage/' . $otherMember->image)
                        : asset('source/assets/avatar/avatar.png');
                } else {
                    // We use a default group picture if the discussion is a group chat
                    $discussion->discussionPicture = asset('source/assets/images/group.png');
                }

                return $discussion;
            });

        // Get the list of friends (accepted friendships)
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

    /**
     * Return the messages of a discussion (with the closed capsules and opened capsules)
     * 
     * @param int $chatId The ID of the discussion
     * @return \Illuminate\Http\JsonResponse A JSON response containing the array of messages.
     */
    public function getMessages($chatId)
    {
        // We get all the messages (including the closed capsules) of the discussion
        $messages = Message::where('chat_id', $chatId)
            ->orderBy('created_at', 'asc')
            ->with('user')
            ->get();

        // We get the opened capsules of the discussion
        $opened_capsule = Message::where('chat_id', $chatId)
            ->where('created_at', '<', DB::raw('opening_date'))
            ->where('opening_date', '<', now('Europe/Paris'))
            ->orderBy('created_at', 'asc')
            ->with('user')
            ->get();

        // For all the opened capsules, we modify the ID to avoid conflicts with the other messages
        // and we change the creation date to the opening date as the messages will be sorted by creation date
        // We do that to display the opened capsules at the right place in the chat
        $opened_capsule->each(function ($message) {
            $message->id += 10000000;
            $message->created_at = $message->opening_date;
        });

        // For all messages that have a creation date less than the opening_date, modify the message
        // If the creation date is less than the opening date, it means that the message is a capsule,
        // so we change the message to a locked message and we change the media_url to a default image
        // to avoid sending the message and media of the capsule to the client before the opening date
        $messages->each(function ($message) {
            if ($message->created_at < $message->opening_date) {
                $prettyDate = \Carbon\Carbon::parse($message->opening_date);
                $prettyDate = $prettyDate->format('d/m/Y H:i');
                $message->message = "🔒 Ce message va être ouvert le {$prettyDate}";
                $message->media_url = "logo.png";
            }
        });

        // To avoid error in the format of the returned JSON
        $opened_capsule = $opened_capsule->toArray();
        $messages = $messages->toArray();

        $messages = array_merge($messages, $opened_capsule);

        // We sort the messages by creation date
        usort($messages, function ($a, $b) {
            return $a['created_at'] <=> $b['created_at'];
        });

        return response()->json(['messages' => $messages]);
    }

    /**
     * Store a new message sent by the user in a specific chat.
     * 
     * @param \Illuminate\Http\Request $request The request containing the message content.
     * @param int $chatId The ID of the chat.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the message (or an error).
     */
    public function storeMessage(Request $request, $chatId)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $chat = Chat::with('members')->find($chatId);

        if (!$chat || !$chat->members->contains(auth()->id())) {
            return response()->json(['error' => 'Chat not found or unauthorized.'], 404);
        }

        // Logic to check if the user is blocked by the other member or if the other member is blocked
        if ($chat->members->count() == 2) {
            $otherMember = $chat->members->firstWhere('id', '!=', auth()->id());

            $friendship = Friendship::where(function ($query) use ($otherMember) {
                $query->where('user_id', auth()->id())
                    ->where('friend_id', $otherMember->id);
            })->orWhere(function ($query) use ($otherMember) {
                $query->where('user_id', $otherMember->id)
                    ->where('friend_id', auth()->id());
            })->first();

            if ($friendship && $friendship->isBlocked()) {
                return response()->json(['error' => 'You are blocked by this user or you blocked this user.']);
            }
        }

        // Create a new message and save it in the database
        $message = new Message();
        $message->user_id = auth()->id();
        $message->chat_id = $chatId;
        $message->message = $request->message;
        $message->save();

        return response()->json(['message' => $message]);
    }

    /**
     * Store a new capsule sent by the user in a specific chat.
     * 
     * @param \Illuminate\Http\Request $request The request containing the message content and the media file.
     * @param int $chatId The ID of the chat.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the capsule (or an error).
     */
    public function storeCapsule(Request $request, $chatId)
    {
        // We authorize file up to 1GB and only the following formats
        $request->validate([
            'file' => 'required|file|mimes:jpeg,png,jpg,gif,mp3,mp4,mov|max:1048576',
            'message' => 'required|string'
        ]);

        // Check if the file is valid
        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $media = $request->file('file');

            // Generate a unique name for the file and move it to the media folder
            $mediaName = time() . '_' . $media->getClientOriginalName();
            $media->move(public_path('source/media'), $mediaName);

            // Create a new message and save it in the database (with the media URL)
            $message = new Message();
            $message->user_id = auth()->id();
            $message->chat_id = $chatId;
            $message->message = $request->message;
            $message->media_url = $mediaName;

            // Setting the opening date to now (to open it instantly) if the user didn't set one
            $message->opening_date = $request->filled('date_time') ? $request->date_time : now("Europe/Paris");

            $message->save();

            return response()->json(['message' => $message]);
        }
        return response()->json(['error' => 'The file was not sent or is invalid.'], 400);
    }

    /**
     * Store a new chat with the name and the friends selected by the user.
     * 
     * @param \Illuminate\Http\Request $request The request containing the chat name and the friends.
     * @return \Illuminate\Http\RedirectResponse A redirect response to the dashboard with a success message.
     */
    public function storeChat(Request $request)
    {
        $request->validate([
            'chat_name' => 'required|string|max:255',
            'friends' => 'required|array|min:1', // Minimum 1 friend selected
            'friends.*' => 'exists:users,id', // Checking if the friends exist in the database
        ]);

        // Create a new chat and save it in the database
        $chat = new Chat();
        $chat->name = $request->chat_name;
        $chat->save();

        // Link the user who created the chat to the discussion
        $chat->users()->attach(auth()->id());

        // Link the friends selected by the user to the discussion
        $chat->users()->attach($request->friends);

        return redirect()->route('dashboard')->with('success', 'Discussion créée avec succès !');
    }

    /**
     * Leave a chat (remove the user from the chat).
     * 
     * @param int $chatId The ID of the chat.
     * @return \Illuminate\Http\JsonResponse A JSON response with a message.
     */
    public function leaveChat($chatId)
    {
        $chat = Chat::find($chatId);

        if (!$chat) {
            return response()->json(['error' => 'Chat not found.'], 404);
        }

        $chat->users()->detach(auth()->id());

        return response()->json(['message' => 'You left the chat.']);
    }

    /**
     * Delete a message from a discussion.
     * 
     * @param int $discussionId The ID of the discussion.
     * @param int $messageId The ID of the message.
     * @return \Illuminate\Http\JsonResponse A JSON response with a message (or an error).
     */
    public function deleteMessage($discussionId, $messageId)
    {   
        $message = Message::where('id', $messageId)->where('chat_id', $discussionId)->first();

        if (!$message) {
            return response()->json(['error' => 'Message not found or does not belong to this discussion.'], 404);
        }

        if ($message->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }
        //Check if the message has a corresponding capsule
        if (Message::find($message->id + 10000000))
        {   
            //If the message has a corresponding capsule, we delete the capsule and the message
            $capsule = Message::where('id', $messageId + 10000000)->where('chat_id', $discussionId)->first();
            if ($message->media_url) {
                $mediaPath = public_path('source/assets/media/' . $message->media_url);
                if (file_exists($mediaPath)) {
                    unlink($mediaPath); 
                }
            }
            $capsule->delete();
            $message->delete();
        }
        //If the message doesn't have a corresponding capsule, we delete the message
        //We also check if the message has a media file and delete it if it exists
        else{
            if ($message->media_url) {
                $mediaPath = public_path("/source/media/" . $message->media_url);
                if (file_exists($mediaPath)) {
                    unlink($mediaPath); 
                }
            }
            $message->delete();
        }

        return response()->json(['message' => 'Message deleted successfully.']);
    }
}
