<?php

namespace App\Models;

use App\Models\Box;
use App\Models\Roles;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Partner extends Model
{
    use HasApiTokens;
    use HasFactory;
    protected $guarded = [];

    public $timestamps = true;
    protected $fillable = ['name', 'description', 'email', 'phone', 'password', 'image', 'category', 'openingtime', 'closingtime'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->attributes['role_id'] = Roles::findOrCreate(['type' => 'partner'])->id;
    }

    public function scopePartners($query)
    {
        $partner_role = Roles::where('type', 'partner')->first();
        return $query->where('role_id', $partner_role->id);
    }

    public function Roles()
    {
        // return $this->hasMany(Roles::class);
        return  $this->hasOne(Roles::class, 'id', 'role_id');
    }

    public function likes()
    {
        return $this->hasMany(LikePartner::class);
    }
    public function boxs()
    {
        return $this->hasMany(Box::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }
}
