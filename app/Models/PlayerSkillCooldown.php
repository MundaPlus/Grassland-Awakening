<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerSkillCooldown extends Model
{
    protected $fillable = [
        'player_id',
        'skill_id',
        'available_at'
    ];

    protected $casts = [
        'available_at' => 'datetime'
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }

    /**
     * Check if the cooldown has expired
     */
    public function isExpired(): bool
    {
        return $this->available_at->isPast();
    }

    /**
     * Get remaining time in seconds
     */
    public function getRemainingSeconds(): int
    {
        return max(0, $this->available_at->diffInSeconds(now()));
    }

    /**
     * Clean up expired cooldowns
     */
    public static function cleanupExpired(): int
    {
        return self::where('available_at', '<', now())->delete();
    }
}