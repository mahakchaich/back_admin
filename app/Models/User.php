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
    protected $fillable = ['name', 'email', 'phone', 'password', 'status', 'role_id'];


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


    public function scopeUsers($query)
    {
        $role = Roles::where("type", "user")->first();
        return $query->where('role_id', $role->id);
    }

    public function scopeAdmins($query)
    {
        $role = Roles::where("type", "admin")->first();
        return $query->where('role_id', $role->id);
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

    public function Roles()
    {
        // return $this->hasMany(Roles::class);
        return  $this->hasOne(Roles::class, 'id', 'role_id');
    }
}
