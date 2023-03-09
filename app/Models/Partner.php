<?php

namespace App\Models;

use App\Models\Box;
use App\Models\Roles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Partner extends Model
{
    use HasFactory;
    protected $guarded = [];

    public $timestamps = false;
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


    public function boxs()
    {
        return $this->hasMany(Box::class);
    }
}
