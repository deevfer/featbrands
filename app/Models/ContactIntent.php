<?php

namespace App\Models;

use Database\Factories\ContactIntentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A contact form the visitor started: saved while they type and marked as submitted when they send it.
 */
#[Fillable(['intent_key', 'first_name', 'last_name', 'email', 'country_code', 'phone', 'company', 'job_title', 'budget', 'referral_source', 'message', 'locale', 'market', 'marketing_consent', 'tracking', 'last_activity_at', 'submitted_at', 'contact_submission_id', 'abandoned_notified_at'])]
class ContactIntent extends Model
{
    /** @use HasFactory<ContactIntentFactory> */
    use HasFactory;

    /** Minutes without activity before an unsent form counts as abandoned. */
    public const ABANDONED_AFTER_MINUTES = 30;

    /**
     * Cookies read only when the visitor allowed marketing cookies, keyed by the name stored here.
     *
     * @var array<string, string>
     */
    public const TRACKING_COOKIES = [
        'fbp' => '_fbp',
        'fbc' => '_fbc',
        'ga' => '_ga',
        'gcl_aw' => '_gcl_aw',
        'li_fat_id' => 'li_fat_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'marketing_consent' => 'boolean',
            'tracking' => 'array',
            'last_activity_at' => 'datetime',
            'submitted_at' => 'datetime',
            'abandoned_notified_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<ContactSubmission, $this>
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(ContactSubmission::class, 'contact_submission_id');
    }

    /**
     * Unsent forms whose last change is at least 30 minutes old and were not reported yet.
     *
     * @param  Builder<ContactIntent>  $query
     */
    public function scopeAbandoned(Builder $query): void
    {
        $query->whereNull('submitted_at')
            ->whereNull('abandoned_notified_at')
            ->where('last_activity_at', '<=', now()->subMinutes(self::ABANDONED_AFTER_MINUTES));
    }

    public function fullName(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function fullPhone(): string
    {
        return trim($this->country_code.' '.$this->phone);
    }
}
