<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
class Board extends Model
{
    use SoftDeletes;
    protected $table = 'table_board';
    protected $primaryKey = 'board_id';
    public $incrementing = true; 
    
    public $timestamps = true; 

    protected $fillable = [
        'board_name',
    ];

    public function announcements()
    {
        return $this->hasMany(Announcement::class, 'board_id', 'board_id');
    }
}