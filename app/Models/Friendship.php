<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Friendship extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'friendships';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'friend_id',
        'status',
    ];

    /**
     * Get the user that owns the friendship.
     *
     * This defines the relationship between the Friendship model and the User model.
     * A friendship belongs to a user, identified by the 'user_id'.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the friend associated with the friendship.
     *
     * This defines the relationship between the Friendship model and the User model.
     * A friendship belongs to a friend, identified by the 'friend_id'.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function friend(): BelongsTo
    {
        return $this->belongsTo(User::class, 'friend_id');
    }

    /**
     * Determine if the friendship is accepted.
     *
     * This method checks if the friendship status is 'accepted'.
     * It returns true if the status is 'accepted', otherwise false.
     *
     * @return bool
     */
    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    /**
     * Determine if the friendship is pending.
     *
     * This method checks if the friendship status is 'pending'.
     * It returns true if the status is 'pending', otherwise false.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Determine if the friendship is blocked.
     *
     * This method checks if the friendship status is 'blocked'.
     * It returns true if the status is 'blocked', otherwise false.
     *
     * @return bool
     */
    public function isBlocked(): bool
    {
        return $this->status === 'blocked';
    }
}
