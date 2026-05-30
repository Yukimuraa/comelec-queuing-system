<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class QueueToken extends Model
{
    protected $fillable = ['token_number', 'status', 'called_at', 'served_at'];

    protected $casts = [
        'called_at' => 'datetime',
        'served_at' => 'datetime',
    ];

    /**
     * Scope to only include tokens created today.
     * Note: We return the raw query here because the admin manually
     * resets/clears the database every day. This avoids timezone discrepancies.
     */
    public function scopeToday($query)
    {
        return $query;
    }

    /**
     * Scope to only include pending tokens (First In First Out order).
     */
    public function scopePending($query)
    {
        return $query->today()->where('status', 'pending')->orderBy('created_at', 'asc');
    }

    /**
     * Scope: priority pending tokens (e.g. P-042).
     */
    public function scopePriorityPending($query)
    {
        return $query->today()->where('status', 'pending')
            ->where('token_number', 'like', 'P-%')
            ->orderBy('created_at', 'asc');
    }

    /**
     * Scope: regular pending tokens (no priority prefix).
     */
    public function scopeRegularPending($query)
    {
        return $query->today()->where('status', 'pending')
            ->where('token_number', 'not like', 'P-%')
            ->orderBy('created_at', 'asc');
    }

    /**
     * Pending tokens in call order: all priority (FIFO) first, then regular (FIFO).
     */
    public function scopePendingCallOrder($query)
    {
        return $query->today()->where('status', 'pending')
            ->orderByRaw("CASE WHEN token_number LIKE 'P-%' THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'asc');
    }

    /**
     * Next token to call for general FIFO (priority lane first).
     */
    public static function nextToCall(): ?self
    {
        return static::priorityPending()->first()
            ?? static::regularPending()->first();
    }

    /**
     * Determine if this token is a priority lane token.
     */
    public function getIsPriorityAttribute(): bool
    {
        return str_starts_with($this->token_number, 'P');
    }

    /**
     * Scope to only include tokens currently being served.
     */
    public function scopeServing($query)
    {
        return $query->today()->where('status', 'serving')->orderBy('called_at', 'desc');
    }

    /**
     * Scope to only include served tokens.
     */
    public function scopeServed($query)
    {
        return $query->today()->where('status', 'served')->orderBy('served_at', 'desc');
    }

    /**
     * Scope to only include skipped tokens.
     */
    public function scopeSkipped($query)
    {
        return $query->today()->where('status', 'skipped')->orderBy('updated_at', 'desc');
    }
}
