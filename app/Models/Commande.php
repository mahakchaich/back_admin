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
    protected $fillable = ['date_cmd', 'heure_cmd', 'user_id', 'statut'];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class);
    }

    public function commandePaniers()
    {
        return $this->hasMany(CommandePanier::class);
    }
}
