<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    use SoftDeletes;

    // Point Eloquent to your custom primary key
    protected $primaryKey = 'user_id';

    // Tell Eloquent this isn't an auto-incrementing integer (since it comes from the users table)
    public $incrementing = false;

    // Define which fields can be mass-assigned
    protected $fillable = [
        'profile_picture',
        'bio',
    ];

    /**
     * Get the user that owns this profile.
     */
    public function user(): BelongsTo
    {
        // Assuming your User model is App\Models\User and its primary key is 'user_id'
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}