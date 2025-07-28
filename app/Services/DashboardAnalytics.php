<?php

namespace App\Services;

use App\Models\ListItem;
use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardAnalytics
{
    /**
     * Get comprehensive user statistics
     */
    public function getUserStats(?User $user = null): array
    {
        $user = $user ?? Auth::user();

        return Cache::remember("user_stats_{$user->id}", 300, function () use ($user) {
            $totalLists = $user->userLists()->count();
            $totalItems = ListItem::whereIn('user_list_id', $user->userLists()->pluck('id'))->count();
            $publishedLists = $user->userLists()->where('marketplace_enabled', true)->count();
            $publicLists = $user->userLists()->where('is_public', true)->count();

            // Calculate total inventory
            $totalInventory = ListItem::whereIn('user_list_id', $user->userLists()->pluck('id'))
                ->sum('inventory_level');

            return [
                'total_lists' => $totalLists,
                'total_items' => $totalItems,
                'published_lists' => $publishedLists,
                'public_lists' => $publicLists,
                'total_inventory' => $totalInventory,
            ];
        });
    }

    /**
     * Get recent lists for dashboard
     */
    public function getRecentLists(?User $user = null, int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        $user = $user ?? Auth::user();

        return $user->userLists()
            ->with('listItems')
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get PLU usage insights
     */
    public function getPLUInsights(?User $user = null): array
    {
        $user = $user ?? Auth::user();

        return Cache::remember("plu_insights_{$user->id}", 600, function () use ($user) {
            $userListIds = $user->userLists()->pluck('id');

            // Most used PLU codes
            $mostUsedPLUs = ListItem::whereIn('user_list_id', $userListIds)
                ->select('plu_code_id', 'organic', DB::raw('COUNT(*) as usage_count'))
                ->with('pluCode')
                ->groupBy('plu_code_id', 'organic')
                ->orderBy('usage_count', 'desc')
                ->limit(5)
                ->get();

            // Category breakdown
            $categoryBreakdown = ListItem::whereIn('user_list_id', $userListIds)
                ->join('plu_codes', 'list_items.plu_code_id', '=', 'plu_codes.id')
                ->select('plu_codes.commodity', DB::raw('COUNT(*) as count'))
                ->groupBy('plu_codes.commodity')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get();

            // Organic ratio
            $totalItems = ListItem::whereIn('user_list_id', $userListIds)->count();
            $organicItems = ListItem::whereIn('user_list_id', $userListIds)->where('organic', true)->count();
            $organicRatio = $totalItems > 0 ? round(($organicItems / $totalItems) * 100, 1) : 0;

            // Weekly activity
            $weeklyActivity = $this->getWeeklyActivity($user);

            return [
                'most_used_plus' => $mostUsedPLUs,
                'category_breakdown' => $categoryBreakdown,
                'organic_ratio' => $organicRatio,
                'total_items' => $totalItems,
                'organic_items' => $organicItems,
                'weekly_activity' => $weeklyActivity,
            ];
        });
    }

    /**
     * Get marketplace insights for published lists
     */
    public function getMarketplaceInsights(?User $user = null): array
    {
        $user = $user ?? Auth::user();

        return Cache::remember("marketplace_insights_{$user->id}", 900, function () use ($user) {
            $publishedLists = $user->userLists()
                ->where('marketplace_enabled', true)
                ->orderBy('view_count', 'desc')
                ->get();

            $totalViews = $publishedLists->sum('view_count');
            $totalCopies = $publishedLists->sum('copy_count');

            return [
                'published_lists' => $publishedLists,
                'total_views' => $totalViews,
                'total_copies' => $totalCopies,
                'top_performing' => $publishedLists->take(3),
            ];
        });
    }

    /**
     * Get sharing activity insights
     */
    public function getSharingInsights(?User $user = null): array
    {
        $user = $user ?? Auth::user();

        $publicLists = $user->userLists()->where('is_public', true)->get();
        $totalPublicViews = $publicLists->sum('view_count');

        // Count copied lists from marketplace (approximation based on activity)
        $copiedListsCount = UserActivity::forUser($user->id)
            ->where('action', ActivityTracker::ACTION_COPIED_LIST)
            ->where('created_at', '>=', now()->subMonth())
            ->count();

        return [
            'public_lists' => $publicLists->count(),
            'total_public_views' => $totalPublicViews,
            'copied_this_month' => $copiedListsCount,
        ];
    }

    /**
     * Get weekly activity summary
     */
    private function getWeeklyActivity(User $user): array
    {
        $weekStart = now()->startOfWeek();

        $activities = UserActivity::forUser($user->id)
            ->where('created_at', '>=', $weekStart)
            ->get();

        $itemsAdded = $activities->where('action', ActivityTracker::ACTION_ADDED_ITEM)
            ->sum(function ($activity) {
                return $activity->metadata['item_count'] ?? 1;
            });

        $listsUpdated = $activities->whereIn('action', [
            ActivityTracker::ACTION_UPDATED_LIST,
            ActivityTracker::ACTION_ADDED_ITEM,
            ActivityTracker::ACTION_REMOVED_ITEM,
        ])->unique('subject_id')->count();

        return [
            'items_added' => $itemsAdded,
            'lists_updated' => $listsUpdated,
            'total_activities' => $activities->count(),
        ];
    }

    /**
     * Clear user analytics cache
     */
    public function clearUserCache(?User $user = null): void
    {
        $user = $user ?? Auth::user();

        Cache::forget("user_stats_{$user->id}");
        Cache::forget("plu_insights_{$user->id}");
        Cache::forget("marketplace_insights_{$user->id}");
    }
}
