<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  
   public function up(): void
{
    Schema::create('table_events', function (Blueprint $table) {

            $table->id('event_id'); 

            $table->unsignedBigInteger('author_id');
            $table->unsignedBigInteger('board_id');

            $table->string('title');
            $table->text('content');
            $table->string('event_type');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            
            $table->timestamps(); 
            $table->softDeletes(); 
            
            $table->foreign('author_id')
                ->references('user_id')
                ->on('table_users')
                ->onDelete('cascade');

            $table->foreign('board_id')
                ->references('board_id')
                ->on('table_board')
                ->onDelete('cascade');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('table_events');
    }
};
