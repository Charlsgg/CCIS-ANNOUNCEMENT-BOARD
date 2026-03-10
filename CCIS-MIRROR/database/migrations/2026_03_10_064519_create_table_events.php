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
        Schema::create('table_events', function (Blueprint $table) {
            $table->integer('event_id')->unique();
            $table->integer('author_id');
            $table->integer('board_id');
            $table->string('title');
            $table->text('content');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            
            $table->primary('event_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_events');
    }
};
