<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Schedule extends Model
{
    use SoftDeletes;

    // Mapping to your specific table ID
    protected $table = 'table_events';
    
    // Telling Laravel the custom PK name
    protected $primaryKey = 'event_id';

    // If these IDs are not auto-incrementing integers
    public $incrementing = false;

    // Mapping your custom "created_at" names if different from Laravel defaults
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';

    protected $fillable = [
        'event_id', 'author_id', 'board_id', 'title', 
        'content', 'start_time', 'end_time'
    ];

    /**
     * Relationship to the Author (User)
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id', 'user_id');
    }

    /**
     * Relationship to the Board
     */
    public function board()
    {
        return $this->belongsTo(Board::class, 'board_id', 'board_id');
    }
}