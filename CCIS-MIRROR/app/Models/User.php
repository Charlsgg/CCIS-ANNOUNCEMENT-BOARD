<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    protected $table = 'table_users';
    protected $primaryKey = 'user_id';
    public $incrementing = true;

    public $timestamps = true;


    protected $fillable = [
        'name',
        'email',
        'user_type',
        'password', 
    ];

    public function announcements()
    {
        return $this->hasMany(Announcement::class, 'author_id', 'user_id');
    }
    public function events()
    {
        return $this->hasMany(Event::class, 'author_id', 'user_id');
    }
    public function profile()
    {
        return $this->hasOne(UserProfile::class, 'user_id', 'user_id');
    }
}