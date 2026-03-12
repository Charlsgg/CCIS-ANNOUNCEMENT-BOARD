<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use function Laravel\Prompts\table;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('table_notification', function (Blueprint $table) {
            $table->id('notification_id')->primary();
            $table->unsignedBigInteger('reciepient_id');
            $table->unsignedBigInteger('announcement_id')->nullable();
            $table->unsignedBigInteger('event_id')->nullable();
            $table->string('message');
            $table->boolean('is_read')->default(false); 
            //
            $table->timestamps();
            $table->softDeletes();
            //
            $table->foreign('reciepient_id')
                ->references('user_id')
                ->on('table_users')
                ->onDelete('cascade');
            $table->foreign('announcement_id')
                ->references('announcement_id')
                ->on('table_announcement')
                ->onDelete('cascade');
            $table->foreign('event_id')
                ->references('event_id')
                ->on('table_events')
                ->onDelete('cascade');  
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_notification');
    }
};
