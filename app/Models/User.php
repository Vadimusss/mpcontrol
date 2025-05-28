<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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
            'password' => 'hashed',
        ];
    }

    public function ownShops(): HasMany
    {
        return $this->hasMany(Shop::class)->chaperone();
    }

    public function ownWorkSpaces(): HasMany
    {
        return $this->hasMany(WorkSpace::class);
    }

    public function ownGoodLists(): HasMany
    {
        return $this->hasMany(GoodList::class);
    }

    public function ownApiKeys(): HasMany
    {
        return $this->hasMany(ApiKey::class);
    }

    public function availableShops(): BelongsToMany
    {
        return $this->belongsToMany(Shop::class);
    }

    public function viewStates(): HasMany
    {
        return $this->hasMany(ViewState::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }
}
