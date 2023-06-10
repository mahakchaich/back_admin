<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;
    protected $fillable = ['rating', 'comment', 'partner_id','user_id','command_id'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function command()
    {
        return $this->belongsTo(Command::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
