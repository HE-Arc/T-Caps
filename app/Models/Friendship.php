<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Friendship extends Model
{
    use HasFactory;

    protected $table = 'friendships';
    protected $fillable = [
        'user_id',
        'friend_id',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id'); // Relation avec le modèle User
    }
    public function friend(): BelongsTo
    {
        return $this->belongsTo(User::class, 'friend_id'); // Relation avec le modèle User
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted'; // Retourne true si le statut est 'accepted'
    }

    public function isPending(): bool
    {
        return $this->status === 'pending'; // Retourne true si le statut est 'pending'
    }

    public function isBlocked(): bool
    {
        return $this->status === 'blocked'; // Retourne true si le statut est 'blocked'
    }
}
