<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Event extends Model
{
    use SoftDeletes;
    
    protected $table = 'table_events';
    protected $primaryKey = 'event_id';
    public $incrementing = true; 

    protected $fillable = [
        'event_type', 'author_id', 'board_id', 'title', 'content', 'start_time', 'end_time',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id', 'user_id');
    }

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class, 'board_id', 'board_id');
    }
}