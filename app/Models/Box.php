<?php

namespace App\Models;


use Carbon\Carbon;
use App\Models\Like;
use App\Models\Command;
use App\Models\Partner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Box extends Model
{
    use HasFactory;
    protected $table = 'boxs';
    protected $guarded = [];

    public $timestamps = true;
    protected $fillable = ['title', 'description', 'oldprice', 'newprice', 'startdate', 'enddate', 'quantity', 'remaining_quantity', 'image', 'category', 'status', 'partner_id'];
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

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
