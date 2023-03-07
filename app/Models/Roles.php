<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Roles extends Model
{
    use HasFactory;


    protected $guarded = [];

    public $timestamps = false;
    protected $fillable = [
        'type',
        'id'
    ];

    protected $hidden = [];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
