<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserList extends Model
{
    protected $fillable = ['name', 'share_code', 'is_public'];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    public static function boot()
    {
        parent::boot();
        
        static::creating(function ($userList) {
            if (empty($userList->share_code)) {
                $userList->share_code = self::generateUniqueShareCode();
            }
        });
    }

    /**
     * Generate a unique 8-character share code
     * Uses A-Z, a-z, 2-9 (excludes confusing characters like 0, O, l, I, 1)
     */
    private static function generateUniqueShareCode(): string
    {
        do {
            $code = '';
            $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
            for ($i = 0; $i < 8; $i++) {
                $code .= $chars[random_int(0, strlen($chars) - 1)];
            }
        } while (self::where('share_code', $code)->exists());
        
        return $code;
    }

    /**
     * Generate a new share code for existing lists
     */
    public function generateNewShareCode(): string
    {
        $this->share_code = self::generateUniqueShareCode();
        $this->save();
        return $this->share_code;
    }

    /**
     * Check if this list is shareable (has public sharing enabled)
     */
    public function isShareable(): bool
    {
        return $this->is_public && !empty($this->share_code);
    }

    /**
     * Get the shareable URL for this list
     */
    public function getShareUrlAttribute(): string
    {
        if (empty($this->share_code)) {
            return '';
        }
        
        return route('lists.shared', $this->share_code);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function listItems()
    {
        return $this->hasMany(ListItem::class);
    }
}
