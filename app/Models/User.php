<?php

namespace App\Models;

use App\Models\Command;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    public $timestamps = false;
    protected $fillable = ['name', 'email', 'phone', 'password'];


    public function commands()
    {
        return $this->hasMany(Command::class);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];


    public function scopeUtilisateurs($query)
    {
        return $query->where('is_admin', 0);
    }

    public function scopeAdmins($query)
    {
        return $query->where('is_admin', 1);
    }


    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function verificationCode()
    {
        return $this->hasMany(verification_code::class);
    }
}