<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('table_users', function (Blueprint $table) {
            $table->id('user_id'); 
            $table->string('name');
            $table->string('email')->unique(); 
            $table->string('password'); 
            $table->string('user_type')->default('customer');
            
            $table->timestamps(); 
            $table->softDeletes(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('table_users');
    }
};