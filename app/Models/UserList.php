<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\ListCopy;

class UserList extends Model
{
    protected $fillable = [
        'name', 
        'share_code', 
        'is_public',
        'marketplace_enabled',
        'marketplace_title',
        'marketplace_description',
        'marketplace_category',
        'view_count',
        'copy_count',
        'published_at'
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'marketplace_enabled' => 'boolean',
        'published_at' => 'datetime',
        'view_count' => 'integer',
        'copy_count' => 'integer',
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

    // Marketplace relationships
    public function copies()
    {
        return $this->hasMany(ListCopy::class, 'original_list_id');
    }

    public function originalCopy()
    {
        return $this->hasOne(ListCopy::class, 'copied_list_id');
    }

    // Marketplace methods
    public function isInMarketplace(): bool
    {
        return $this->marketplace_enabled && !empty($this->marketplace_title);
    }

    public function publishToMarketplace(string $title, ?string $description = null, ?string $category = null): bool
    {
        $this->update([
            'marketplace_enabled' => true,
            'marketplace_title' => $title,
            'marketplace_description' => $description,
            'marketplace_category' => $category,
            'published_at' => now(),
        ]);

        return true;
    }

    public function unpublishFromMarketplace(): bool
    {
        $this->update([
            'marketplace_enabled' => false,
            'published_at' => null,
        ]);

        return true;
    }

    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    public function incrementCopyCount(): void
    {
        $this->increment('copy_count');
    }

    public function copyForUser(User $user, ?string $customName = null): UserList
    {
        // Create the new list
        $newList = $user->userLists()->create([
            'name' => $customName ?? $this->marketplace_title ?? $this->name,
        ]);

        // Copy all list items (without inventory levels)
        $itemsToCopy = $this->listItems()->with('pluCode')->get();
        
        foreach ($itemsToCopy as $item) {
            $newList->listItems()->create([
                'plu_code_id' => $item->plu_code_id,
                'organic' => $item->organic,
                'inventory_level' => 0, // Start with zero inventory
            ]);
        }

        // Track the copy relationship
        ListCopy::create([
            'original_list_id' => $this->id,
            'copied_list_id' => $newList->id,
            'user_id' => $user->id,
        ]);

        // Increment copy count
        $this->incrementCopyCount();

        return $newList;
    }

    public function copyForUserWithInventory(User $user, ?string $customName = null): UserList
    {
        // Create the new list with a smart default name
        $listName = $customName ?? $this->name;
        
        // Check for duplicate names and append (Copy) if needed
        $existingCount = $user->userLists()->where('name', 'like', $listName . '%')->count();
        if ($existingCount > 0) {
            $listName = $listName . ' (Copy)';
            $copyCount = $user->userLists()->where('name', 'like', $listName . '%')->count();
            if ($copyCount > 0) {
                $listName = $listName . ' ' . ($copyCount + 1);
            }
        }
        
        $newList = $user->userLists()->create([
            'name' => $listName,
        ]);

        // Copy ALL items WITH inventory levels (including items with 0 inventory)
        $itemsToCopy = $this->listItems()
            ->with('pluCode')
            ->get();
        
        foreach ($itemsToCopy as $item) {
            $newList->listItems()->create([
                'plu_code_id' => $item->plu_code_id,
                'organic' => $item->organic,
                'inventory_level' => $item->inventory_level, // Preserve inventory!
            ]);
        }

        // Optional: Track shared list copies with source type
        // For now, we'll use the same tracking but could extend later
        ListCopy::create([
            'original_list_id' => $this->id,
            'copied_list_id' => $newList->id,
            'user_id' => $user->id,
        ]);

        return $newList;
    }

    // Marketplace scopes
    public function scopeMarketplace($query)
    {
        return $query->where('marketplace_enabled', true)
                     ->whereNotNull('marketplace_title')
                     ->whereNotNull('published_at');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('marketplace_category', $category);
    }

    // Marketplace categories
    public static function getMarketplaceCategories(): array
    {
        return [
            'meal-planning' => 'Meal Planning',
            'seasonal' => 'Seasonal',
            'organic' => 'Organic Focus',
            'budget' => 'Budget Friendly',
            'healthy' => 'Healthy Eating',
            'family' => 'Family Meals',
            'quick-meals' => 'Quick Meals',
            'special-diet' => 'Special Diet',
            'entertaining' => 'Entertaining',
            'grocery-retail' => 'Grocery Retail',
            'other' => 'Other',
        ];
    }
}
