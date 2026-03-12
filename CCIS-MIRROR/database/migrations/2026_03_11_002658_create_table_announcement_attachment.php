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
        Schema::create('table_announcement_attachment', function (Blueprint $table) {
            $table->integer('attachment_id')->primary();
            $table->integer('announcement_id');
            $table->string('file_path');
            $table->string('file_type');
            //
            $table->timestamps(); 
            $table->softDeletes(); 
            //
            $table->foreign('announcement_id')
                ->references('announcement_id')
                ->on('table_announcement')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_announcement_attachment');
    }
};
