<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
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

    protected $fillable = [
        'name',
        'email',
        'password',
        'score_weights',
        'role',
        'scans_this_month',
        'scans_limit',
        'trial_ends_at',
        'provider',
        'provider_id',
        'avatar',
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
            'password'          => 'hashed',
            'score_weights'     => 'array',
            'role'              => 'string',
        ];
    }

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
}
