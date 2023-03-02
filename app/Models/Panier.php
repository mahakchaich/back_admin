<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Panier extends Model
{
    use HasFactory;
    protected $guarded = [];

    public $timestamps = false;
    protected $fillable = ['title', 'description', 'ancien_prix', 'nouveau_prix', 'date_dispo', 'quantite', 'image', 'categorie'];

    protected $casts = [
        'date_dispo' => 'datetime:d/m/y',
    ];
}
