<?php

namespace App\Models;

use App\Models\Box;
use App\Models\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BoxCommand extends Model
{
    use HasFactory;

    protected $table = 'box_command';
    protected $fillable = ['box_id', 'command_id', 'quantity'];

    public function box()
    {
        return $this->belongsTo(Box::class);
    }

    public function command()
    {
        return $this->belongsTo(Command::class);
    }
}
