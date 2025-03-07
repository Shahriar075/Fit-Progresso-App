<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
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
        'role_id',
        'active'
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

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function isAdmin() : bool
    {
        return $this->role()->where('name', 'admin')->exists();
    }
    public function exercises()
    {
        return $this->hasMany(Exercise::class);
    }

    public function workoutTemplates()
    {
        return $this->hasMany(WorkoutTemplate::class, 'created_by');
    }

    public function workoutLogs()
    {
        return $this->hasMany(WorkoutLog::class);
    }

    public function sets()
    {
        return $this->hasMany(WorkoutLogSet::class);
    }
    public function getJwtIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function checkActive()
    {
        if (!$this->active) {
            throw new \Exception('Your account is inactive, Please contact administrator');
        }
    }
}
