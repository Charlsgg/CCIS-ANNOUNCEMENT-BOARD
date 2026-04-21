<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Prunable; // <--- ADDED
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    use SoftDeletes, Prunable;

    protected $table = 'table_announcement';
    protected $primaryKey = 'announcement_id';
    protected $with = ['author.profile']; 


    public function prunable()
    {
        return static::onlyTrashed()->where('created_at', '<', now()->subDays(60));
    }
    protected $fillable = ['board_id', 'author_id', 'title', 'content', 'topic', 'likes_count'];

    public function attachments(): HasMany { return $this->hasMany(AnnouncementAttachment::class, 'announcement_id', 'announcement_id')->orderBy('id', 'asc'); }
    public function author(): BelongsTo { return $this->belongsTo(User::class, 'author_id', 'user_id'); }
    public function board(): BelongsTo { return $this->belongsTo(Board::class, 'board_id', 'board_id'); }
}