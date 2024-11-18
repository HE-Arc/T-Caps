<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $table = 'messages';

    protected $fillable = [
        'message',
        'chat_id',
        'user_id',
    ];

    /**
     * Chat where message was sent
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function chats()
    {
        return $this->belongsTo(Chat::class);
    }

    /**
     * User who sent the message
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function users()
    {
        return $this->belongsTo(User::class);
    }
}
