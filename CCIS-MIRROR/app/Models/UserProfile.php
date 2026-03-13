<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserProfile extends Model
{
    use SoftDeletes;

    protected $table = 'user_profiles';

    protected $primaryKey = 'user_id';

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'profile_picture',
        'bio',
    ];

    /**
     * Relationship back to the User
     */
    public function user(): BelongsTo
    {
     
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}