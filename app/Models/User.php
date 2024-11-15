<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Support\Str;
use Laravel\Fortify\Features;
use App\Models\Concerns\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Concerns\HasProfilePhoto;
use Illuminate\Notifications\Notifiable;
use App\Models\Concerns\RecordsUserActions;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasRoles;
    use HasTeams;
    use Notifiable;
    use RecordsUserActions;
    use SoftDeletes;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['is_super_admin'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'is_super_admin',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    // protected $with = ['roles'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['profile_photo_url', 'two_factor_enabled', 'is_admin'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
        ];
    }

    public static function booted()
    {
        static::creating(function ($user) {
            if (blank($user->password)) {
                $user->password = Hash::make(Str::random(10));
            }
        });
    }

    public function twoFactorEnabled(): Attribute
    {
        return Attribute::get(fn () => $this->hasEnabledTwoFactorAuthentication());
    }

    public function isAdmin(): Attribute
    {
        return Attribute::get(fn () => (bool) $this->is_super_admin);
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        if (Features::enabled(Features::emailVerification())) {
            $this->notify(new VerifyEmail);
        }
    }

    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_super_admin;
    }

    public function info()
    {
        $teamId = session()->get('current_team_id');
        $team = $this->currentTeam;

        return cache()->remember($this->getCacheKey("info:{$teamId}:{$team?->updated_at}"), 5 * 60, function () {
            return [
                'user' => $this->withoutRelations(),
                'current_team' => $this->currentTeam,
                'all_teams' => $this->allTeams(),
                'permissions' => $this->getAllPermissions(),
                'features' => [
                    // will merge more as the come
                    'fortify' => [
                        'features' => config('fortify.features', []),
                        'options' => config('fortify-options', []),
                    ],
                ],
            ];
        });
    }
}
