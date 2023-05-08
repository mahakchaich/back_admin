<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Roles extends Model
{
    use HasFactory;


    protected $guarded = [];

    public $timestamps = true;
    protected $fillable = [
        'type',
        'id'
    ];

    protected $hidden = [];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public static function findOrCreate(array $attributes)
    {
        $model = self::where($attributes)->first();
        return $model ?: self::create($attributes);
    }
}
