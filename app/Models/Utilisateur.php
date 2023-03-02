<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Utilisateur extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['name', 'email', 'password', 'numero_tel', 'adresse'];

    public function commandes()
    {
        return $this->hasMany(Commande::class);
    }
    public function verificationCode()
    {
        return $this->hasMany(verification_code::class);
    }
}
