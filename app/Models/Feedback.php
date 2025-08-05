<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'priority',
        'status',
        'subject',
        'message',
        'admin_response',
        'assigned_admin_id',
        'metadata',
        'responded_at',
        'resolved_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'responded_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_admin_id');
    }

    // Scopes
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }

    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeResolved(Builder $query): Builder
    {
        return $query->where('status', 'resolved');
    }

    public function scopeClosed(Builder $query): Builder
    {
        return $query->where('status', 'closed');
    }

    public function scopeByPriority(Builder $query, string $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    // Accessors & Mutators
    public function getTypeDisplayAttribute(): string
    {
        return match ($this->type) {
            'bug' => 'Bug Report',
            'feature' => 'Feature Request',
            'improvement' => 'Improvement',
            'general' => 'General Feedback',
            default => ucfirst($this->type),
        };
    }

    public function getPriorityDisplayAttribute(): string
    {
        return ucfirst($this->priority);
    }

    public function getStatusDisplayAttribute(): string
    {
        return match ($this->status) {
            'open' => 'Open',
            'in_progress' => 'In Progress',
            'resolved' => 'Resolved',
            'closed' => 'Closed',
            default => ucfirst($this->status),
        };
    }

    // Helper methods
    public function markAsResolved(?string $adminResponse = null): void
    {
        $this->update([
            'status' => 'resolved',
            'admin_response' => $adminResponse ?: $this->admin_response,
            'resolved_at' => now(),
            'responded_at' => $adminResponse ? now() : $this->responded_at,
        ]);
    }

    public function assignTo(User $admin): void
    {
        $this->update([
            'assigned_admin_id' => $admin->id,
            'status' => $this->status === 'open' ? 'in_progress' : $this->status,
        ]);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isResolved(): bool
    {
        return in_array($this->status, ['resolved', 'closed']);
    }
}
