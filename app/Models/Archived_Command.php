<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Archived_Command extends Model
{
    use HasFactory;

    // protected $table = 'box_command';

    protected $guarded = [];
    

    public $timestamps = true;
    protected $fillable = [
        'id',
        'user_id',
        'price',
        'status'
    ];

    
}
