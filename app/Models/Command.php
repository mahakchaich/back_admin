<?php

namespace App\Models;

use App\Models\User;
use App\Models\Panier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Command extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $fillable = [
        'user_id',
        'price',
        'status'
    ];



    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paniers()
    {
        return $this->belongsToMany(Panier::class)->withPivot('quantity');
    }
}
