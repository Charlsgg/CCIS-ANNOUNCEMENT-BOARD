<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite workaround: Drop the view first if it exists
        DB::statement("DROP VIEW IF EXISTS view_event_user_filters");

        // Use standard CREATE VIEW instead of CREATE OR REPLACE
        DB::statement("
            CREATE VIEW view_event_user_filters AS
            SELECT 
                e.event_id,
                e.user_id,
                e.board_id,
                e.title,
                u.name AS user_name,
                e.content,
                e.venue,
                e.start_time,
                e.end_time,
                e.created_at AS event_created_at
            FROM table_events e
            INNER JOIN table_users u ON e.user_id = u.user_id
            WHERE e.deleted_at IS NULL 
              AND u.deleted_at IS NULL
        ");
    }

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS view_event_user_filters");
    }
};