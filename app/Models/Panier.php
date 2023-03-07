<?php

namespace App\Models;


use Carbon\Carbon;
use App\Models\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Panier extends Model
{
    use HasFactory;
    protected $guarded = [];

    public $timestamps = false;
    protected $fillable = ['title', 'description', 'ancien_prix', 'nouveau_prix', 'date_debut', 'date_fin', 'quantity', 'remaining_quantity', 'image', 'categorie', 'status'];
    public function commands()
    {
        return $this->belongsToMany(Command::class);
    }

    public function substruct($qtn, $column)
    {
        $this->$column -= $qtn;
        $this->save();
    }
}
