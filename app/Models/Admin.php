<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Admin extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $guard = 'admin';

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'password' => 'hashed',
    ];

    protected function email(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value !== null ? strtolower(trim($value)) : null,
        );
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value !== null ? ucwords(strtolower($value)) : null,
            set: fn (?string $value) => $value !== null ? strtolower(trim($value)) : null,
        );
    }

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return ['role' => 'admin'];
    }
}