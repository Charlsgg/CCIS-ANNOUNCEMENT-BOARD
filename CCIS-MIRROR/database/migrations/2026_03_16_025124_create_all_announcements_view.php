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
        DB::statement("DROP VIEW IF EXISTS all_announcements_view");

        DB::statement("
            CREATE VIEW all_announcements_view AS
            SELECT 
                a.announcement_id,
                a.board_id,
                a.author_id,
                u.name AS author_name,
                u.user_type AS author_type,
                up.profile_picture AS author_avatar, 
                a.title,
                a.content,
                a.topic,
                a.likes_count,
                a.created_at,
                a.created_at AS announcement_date,
                aa.attachment_id,
                aa.file_path,
                aa.file_type
            FROM table_announcement a
            JOIN table_users u 
                ON a.author_id = u.user_id
            LEFT JOIN user_profiles up
                ON u.user_id = up.user_id
            LEFT JOIN table_announcement_attachment aa 
                ON a.announcement_id = aa.announcement_id 
                AND aa.deleted_at IS NULL
            WHERE a.deleted_at IS NULL 
              AND u.deleted_at IS NULL
        ");
    }

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS all_announcements_view");
    }
};