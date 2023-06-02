<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class archived_boxs extends Model
{
    use HasFactory;

    protected $table = 'archived_boxs';
    protected $guarded = [];


    public $timestamps = true;
    protected $fillable = [
        'id',
        'title',
        'description',
        'oldprice',
        'newprice',
        'startdate',
        'enddate',
        'quantity',
        'remaining_quantity',
        'image',
        'category',
        'status',
        'partner_id'
    ];


    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
