<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Friendship extends Model
{
    use HasFactory;

    // Spécifie le nom de la table associée à ce modèle
    protected $table = 'friendships';

    // Les attributs qui peuvent être assignés en masse
    protected $fillable = [
        'user_id',
        'friend_id',
        'status',
    ];

    /**
     * Obtenir l'utilisateur qui possède cette amitié.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id'); // Relation avec le modèle User
    }

    /**
     * Obtenir l'ami associé à cette amitié.
     */
    public function friend(): BelongsTo
    {
        return $this->belongsTo(User::class, 'friend_id'); // Relation avec le modèle User
    }

    /**
     * Vérifier si l'amitié est acceptée.
     */
    public function isAccepted(): bool
    {
        return $this->status === 'accepted'; // Retourne true si le statut est 'accepted'
    }

    /**
     * Vérifier si l'amitié est en attente.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending'; // Retourne true si le statut est 'pending'
    }

    /**
     * Vérifier si l'amitié est bloquée.
     */
    public function isBlocked(): bool
    {
        return $this->status === 'blocked'; // Retourne true si le statut est 'blocked'
    }
}
