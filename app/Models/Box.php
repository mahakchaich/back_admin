<?php

namespace App\Models;


use Carbon\Carbon;
use App\Models\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Box extends Model
{
    use HasFactory;
    protected $table = 'boxs';
    protected $guarded = [];

    public $timestamps = false;
    protected $fillable = ['title', 'description', 'oldprice', 'newprice', 'startdate', 'enddate', 'quantity', 'remaining_quantity', 'image', 'category', 'status'];
    public function commands()
    {
        return $this->belongsToMany(Command::class);
    }

    public function substruct($qtn, $column)
    {
        $this->$column -= $qtn;
        $this->save();
    }

    public function boxsCommand()
    {
        return $this->hasMany(BoxCommand::class);
    }
}
