<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;
    // Note: Billable (Cashier/Stripe) intentionally removed — payment replaced by waitlist model.

    public const DEFAULT_WEIGHTS = [
        'http_ok'   => 30,
        'ssl'       => 20,
        'mobile'    => 20,
        'contact'   => 10,
        'impressum' => 10,
        'favicon'   => 10,
    ];

    public const PROFILES = [
        'webdesign' => ['http_ok' => 20, 'ssl' => 20, 'mobile' => 25, 'contact' => 10, 'impressum' => 5,  'favicon' => 20],
        'seo'       => ['http_ok' => 15, 'ssl' => 15, 'mobile' => 30, 'contact' => 15, 'impressum' => 15, 'favicon' => 10],
        'lokal'     => ['http_ok' => 20, 'ssl' => 10, 'mobile' => 20, 'contact' => 25, 'impressum' => 15, 'favicon' => 10],
    ];

    /** Maximum number of free-tier slots available at launch. */
    public const FREE_TIER_LIMIT = 25;

    protected $fillable = [
        'name',
        'email',
        'password',
        'score_weights',
        'role',
        'scans_this_month',
        'scans_limit',
        'provider',
        'provider_id',
        'avatar',
        // Waitlist fields
        'waitlist_status',
        'is_free_tier',
        'approved_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'approved_at'       => 'datetime',
            'password'          => 'hashed',
            'score_weights'     => 'array',
            'role'              => 'string',
            'is_free_tier'      => 'boolean',
        ];
    }

    // ────────────────────────────────────────────────────────────────────────────
    //  Waitlist helpers
    // ────────────────────────────────────────────────────────────────────────────

    /**
     * Whether this user has been approved (waitlist or free tier).
     */
    public function isApproved(): bool
    {
        return $this->waitlist_status === 'approved';
    }

    /**
     * Whether the user is still waiting for approval.
     */
    public function isWaiting(): bool
    {
        return $this->waitlist_status === 'waiting';
    }

    /**
     * Whether the user has been rejected.
     */
    public function isRejected(): bool
    {
        return $this->waitlist_status === 'rejected';
    }

    /**
     * How many users have already been approved (used to determine free-tier eligibility).
     */
    public static function approvedCount(): int
    {
        return self::where('waitlist_status', 'approved')->count();
    }

    // ────────────────────────────────────────────────────────────────────────────
    //  Score / Scan helpers
    // ────────────────────────────────────────────────────────────────────────────

    public function getScoreWeights(): array
    {
        return $this->score_weights ?? self::DEFAULT_WEIGHTS;
    }

    public function leads()
    {
        return $this->hasMany(\App\Models\Lead::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isAgent(): bool
    {
        return $this->role === 'agent';
    }

    /**
     * Returns the effective scan limit for a user.
     * Approved free-tier users: unlimited (null).
     * Approved paid-tier users: scans_limit (0 = unlimited).
     * Unapproved / waiting users: 0 (blocked).
     */
    public static function effectiveLimit(self $user): ?int
    {
        // Admins always have unlimited access regardless of waitlist status.
        if ($user->isAdmin()) {
            return null;
        }

        if (!$user->isApproved()) {
            return 0;
        }

        if ($user->is_free_tier) {
            return null; // unlimited for first 25
        }

        return $user->scans_limit === 0 ? null : $user->scans_limit;
    }
}
