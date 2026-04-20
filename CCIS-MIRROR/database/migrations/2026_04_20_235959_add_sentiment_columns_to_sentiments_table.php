<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sentiments', function (Blueprint $table) {
            if (!Schema::hasColumn('sentiments', 'sentiment')) {
                $table->string('sentiment');
            }

            if (!Schema::hasColumn('sentiments', 'ip_address')) {
                $table->string('ip_address')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sentiments', function (Blueprint $table) {
            if (Schema::hasColumn('sentiments', 'sentiment')) {
                $table->dropColumn('sentiment');
            }

            if (Schema::hasColumn('sentiments', 'ip_address')) {
                $table->dropColumn('ip_address');
            }
        });
    }
};
