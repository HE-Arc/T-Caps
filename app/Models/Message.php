<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'messages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'message',
        'chat_id',
        'user_id',
    ];

    /**
     * Get the chat that the message belongs to.
     *
     * This method defines the relationship between the Message model and the Chat model.
     * A message belongs to a chat, identified by the 'chat_id'.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function chats()
    {
        return $this->belongsTo(Chat::class);
    }

    /**
     * Get the user that sent the message.
     *
     * This method defines the relationship between the Message model and the User model.
     * A message belongs to a user, identified by the 'user_id'.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
