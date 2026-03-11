<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
{
    Schema::create('table_board', function (Blueprint $table) {
        // Changed to id() to match the BigInteger expected by Announcement
        $table->id('board_id'); 
        $table->string('board_name')->nullable();
        
        $table->timestamps(); 
        $table->softDeletes(); 
    });
}

    public function down(): void
    {
        Schema::dropIfExists('table_board');
    }
};