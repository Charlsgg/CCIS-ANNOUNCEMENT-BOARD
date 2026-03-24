<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Prunable; 
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    use HasFactory, SoftDeletes, Prunable; 
    protected $table = 'table_events';
    protected $primaryKey = 'event_id';



    public function prunable()
    {
        return static::onlyTrashed()->where('created_at', '<', now()->subDays(60));
    }

    protected $fillable = [
        'user_id', 'board_id', 'title', 'content', 'venue', 'start_time', 'end_time',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class, 'user_id', 'user_id'); }
    public function board(): BelongsTo { return $this->belongsTo(Board::class, 'board_id', 'board_id'); }
}