<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('table_announcement', function (Blueprint $table) {

            $table->id('announcement_id'); 
            $table->unsignedBigInteger('board_id');
            $table->unsignedBigInteger('author_id');
            $table->string('title'); 
            $table->longText('content'); 
            $table->string('topic');
            $table->integer('likes_count')->default(0);
            //
            //
            $table->foreign('author_id')
                ->references('user_id')
                ->on('table_users')
                ->onDelete('cascade'); 

            $table->foreign('board_id')
                ->references('board_id')
                ->on('table_board')
                ->onDelete('cascade');
                
            $table->timestamps(); 
            $table->softDeletes(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('table_announcement');
    }
};