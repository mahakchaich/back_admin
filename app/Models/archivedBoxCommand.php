<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class archivedBoxCommand extends Model
{
    use HasFactory;

    protected $table = 'archived_box_commands';
    protected $guarded = [];

    public $timestamps = true;
    protected $fillable = [
        'id',
        'box_id',
        'command_id',
        'quantity'
    ];

    public function box()
    {
        return $this->belongsTo(archived_boxs::class);
    }
    public function command()
    {
        return $this->belongsTo(Archived_Command::class);
    }
}
