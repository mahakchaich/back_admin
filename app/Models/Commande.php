<?php

namespace App\Models;

use App\Models\Panier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Commande extends Model
{
    use HasFactory;
    protected $guarded = [];

    public $timestamps = false;
    protected $fillable = [
        'date_cmd',
        'heure_cmd',
        'user_id',
        'email',
        'statut'
    ];

    public function commandePaniers()
    {
        return $this->hasMany(CommandePanier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->with('email');
    }
}
