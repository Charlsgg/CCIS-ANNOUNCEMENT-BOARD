<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    // 1. Override the default table name
    protected $table = 'table_events';

    // 2. Override the default primary key
    protected $primaryKey = 'event_id';

    // 3. Define the fillable attributes for mass assignment
    protected $fillable = [
        'user_id',
        'board_id',
        'title',
        'content',
        'venue',
        'start_time',
        'end_time',
    ];

    // 4. Automatically cast date/time columns to Carbon instances
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    /**
     * Get the user that created the event.
     */
    public function user(): BelongsTo
    {
        // Parameter order: (Model, foreign_key, owner_key)
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get the board that this event belongs to.
     */
    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class, 'board_id', 'board_id');
    }
}