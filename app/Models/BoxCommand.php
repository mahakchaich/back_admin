<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoxCommand extends Model
{
    use HasFactory;

    protected $table = 'box_command';
    protected $fillable = ['box_id', 'command_id', 'quantity'];
}
