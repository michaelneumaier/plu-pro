<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListCopy extends Model
{
    protected $fillable = [
        'original_list_id',
        'copied_list_id',
        'user_id',
    ];

    /**
     * Get the original list that was copied
     */
    public function originalList(): BelongsTo
    {
        return $this->belongsTo(UserList::class, 'original_list_id');
    }

    /**
     * Get the copied list
     */
    public function copiedList(): BelongsTo
    {
        return $this->belongsTo(UserList::class, 'copied_list_id');
    }

    /**
     * Get the user who copied the list
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if a user has already copied a specific list
     */
    public static function userHasCopied(int $userId, int $originalListId): bool
    {
        return self::where('user_id', $userId)
            ->where('original_list_id', $originalListId)
            ->exists();
    }
}
