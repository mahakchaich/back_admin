<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $guarded = [];

    public $timestamps = true;
    protected $fillable = ['lat', 'lng', 'position', 'partner_id'];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }
}
