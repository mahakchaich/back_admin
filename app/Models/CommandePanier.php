<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommandePanier extends Model
{
    use HasFactory;

    public function Commande()
    {
        return $this->belongsTo(Commande::class);
    }

    public function panier()
    {
        return $this->belongsTo(Panier::class);
    }
}
