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
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            // The email of the user requesting the reset
            $table->string('email')->primary();
            
            // The actual secure token
            $table->string('token');
            
            // When the token was created (Laravel uses this to expire old links)
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
    }
};
