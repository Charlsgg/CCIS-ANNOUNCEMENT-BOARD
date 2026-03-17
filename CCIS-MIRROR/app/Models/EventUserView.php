<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class EventUserView extends Model
{
    // Point the model to your SQL view
    protected $table = 'view_event_user_filters';
    
    // Set the primary key so Laravel knows how to identify unique rows
    protected $primaryKey = 'event_id';
    
    // Views are typically read-only, so disable timestamps
    public $timestamps = false;

    /**
     * Scope a query to filter by the event title.
     */
    public function scopeFilterByTitle(Builder $query, string $title): Builder
    {
        return $query->where('title', 'LIKE', '%' . $title . '%');
    }

    /**
     * Scope a query to filter by the user's name.
     */
    public function scopeFilterByUserName(Builder $query, string $userName): Builder
    {
        return $query->where('user_name', 'LIKE', '%' . $userName . '%');
    }
}