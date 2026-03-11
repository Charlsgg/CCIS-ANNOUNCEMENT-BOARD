<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement_Attachement extends Model
{
    use SoftDeletes;
    protected $table = 'table_announcement_attachment';
    protected $primaryKey = 'attachment_id';
    public $incrementing = false;
    public $timestamps = true;
    protected $fillable = [
        'attachment_id',
        'announcement_id',
        'file_path',
        'file_type',
    ];
}
