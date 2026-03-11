<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Announcement extends Model
{
    use SoftDeletes;
    protected $table = 'table_announcement';
    protected $primaryKey = 'announcement_id';
    
    // Set to true because the database is now handling the ID generation
    public $incrementing = true; 
    
    protected $fillable = [
        'title', 
        'board_id',
        'author_id',
        'content',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id', 'user_id');
    }

    public function board()
    {
        return $this->belongsTo(Board::class, 'board_id', 'board_id');
    }
    public function attachments()
    {
        return $this->hasMany(Announcement_Attachement::class, 'announcement_id', 'announcement_id');
    }
}