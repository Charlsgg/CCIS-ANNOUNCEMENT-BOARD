<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("DROP VIEW IF EXISTS view_event_filters");

        DB::statement("
            CREATE VIEW view_event_filters AS
            SELECT 
                event_id,
                user_id,
                board_id,
                title,
                content,
                Venue,
                start_time,
                end_time,
                created_at,
                -- UPDATED FOR POSTGRESQL: Use EXTRACT instead of strftime
                CAST(EXTRACT(MONTH FROM start_time) AS INTEGER) AS event_month,
                CAST(EXTRACT(YEAR FROM start_time) AS INTEGER) AS event_year
            FROM table_events
            WHERE deleted_at IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS view_event_filters");
    }
};