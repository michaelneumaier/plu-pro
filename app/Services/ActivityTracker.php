<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityTracker
{
    /**
     * Log a user activity
     */
    public function log(string $action, ?Model $subject = null, array $metadata = [], ?string $description = null, ?User $user = null): UserActivity
    {
        $user = $user ?? Auth::user();

        if (! $user) {
            throw new \Exception('User must be authenticated to log activity');
        }

        // Generate description if not provided
        if (! $description) {
            $description = $this->generateDescription($action, $subject, $metadata);
        }

        return UserActivity::create([
            'user_id' => $user->id,
            'action' => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id,
            'metadata' => $metadata,
            'description' => $description,
        ]);
    }

    /**
     * Get recent activities for a user
     */
    public function getRecentActivity(?User $user = null, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        $user = $user ?? Auth::user();

        return UserActivity::forUser($user->id)
            ->recent($limit)
            ->with('subject')
            ->get();
    }

    /**
     * Generate a human-readable description for an activity
     */
    private function generateDescription(string $action, ?Model $subject = null, array $metadata = []): string
    {
        $subjectName = $subject?->name ?? 'Unknown';
        $listName = $metadata['list_name'] ?? 'a list';
        $sourceListName = $metadata['source_list_name'] ?? 'a list';
        $itemCount = $metadata['item_count'] ?? 1;

        return match ($action) {
            'created_list' => "Created list \"{$subjectName}\"",
            'updated_list' => "Updated list \"{$subjectName}\"",
            'deleted_list' => "Deleted list \"{$listName}\"",
            'published_list' => "Published \"{$subjectName}\" to marketplace",
            'unpublished_list' => "Unpublished \"{$subjectName}\" from marketplace",
            'shared_list' => "Made list \"{$subjectName}\" public",
            'unshared_list' => "Made list \"{$subjectName}\" private",
            'added_item' => "Added {$itemCount} item(s) to \"{$listName}\"",
            'removed_item' => "Removed {$itemCount} item(s) from \"{$listName}\"",
            'copied_list' => "Copied \"{$sourceListName}\" from marketplace",
            'cleared_list' => "Cleared all items from \"{$subjectName}\"",
            'updated_inventory' => "Updated inventory for {$itemCount} item(s)",
            default => ucfirst(str_replace('_', ' ', $action)),
        };
    }

    /**
     * Activity action constants
     */
    public const ACTION_CREATED_LIST = 'created_list';

    public const ACTION_UPDATED_LIST = 'updated_list';

    public const ACTION_DELETED_LIST = 'deleted_list';

    public const ACTION_PUBLISHED_LIST = 'published_list';

    public const ACTION_UNPUBLISHED_LIST = 'unpublished_list';

    public const ACTION_SHARED_LIST = 'shared_list';

    public const ACTION_UNSHARED_LIST = 'unshared_list';

    public const ACTION_ADDED_ITEM = 'added_item';

    public const ACTION_REMOVED_ITEM = 'removed_item';

    public const ACTION_COPIED_LIST = 'copied_list';

    public const ACTION_CLEARED_LIST = 'cleared_list';

    public const ACTION_UPDATED_INVENTORY = 'updated_inventory';
}
