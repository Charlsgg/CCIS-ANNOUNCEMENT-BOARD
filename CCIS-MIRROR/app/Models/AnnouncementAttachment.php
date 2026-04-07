<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AnnouncementAttachment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'table_announcement_attachment';
    protected $primaryKey = 'attachment_id';

    protected $fillable = [
        'announcement_id',
        'file_path',
        'file_type',
    ];

    // Tell Laravel to automatically append this custom attribute to JSON arrays
    protected $appends = ['url'];

    /**
     * Accessor to generate the full S3 URL on the fly.
     * Accessible in your frontend as attachment.url
     */
    public function getUrlAttribute()
    {
        if (!$this->file_path) {
            return null;
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('s3');

        return $disk->url($this->file_path);
    }

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class, 'announcement_id', 'announcement_id');
    }
}
