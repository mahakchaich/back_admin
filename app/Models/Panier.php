<?php

namespace App\Models;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Panier extends Model
{
    use HasFactory;
    protected $guarded = [];

    public $timestamps = false;
    protected $fillable = ['title', 'description', 'ancien_prix', 'nouveau_prix', 'date_debut', 'date_fin', 'quantite initial', 'quantite restante', 'image', 'categorie', 'statut'];

    public function commandePaniers()
    {
        return $this->hasMany(CommandePanier::class)->with('commande');
    }
}
