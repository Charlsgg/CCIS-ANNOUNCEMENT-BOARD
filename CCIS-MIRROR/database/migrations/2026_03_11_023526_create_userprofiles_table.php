<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
 
    public function up(): void
{
    Schema::create('user_profiles', function (Blueprint $table) {
        $table->string('profile_picture')->nullable();
        $table->text('bio')->nullable();
        //
        $table->timestamps();
        $table->softDeletes();
        //
        $table->foreignId('user_id')->primary()
        ->constrained('table_users', 'user_id')
        ->onDelete('cascade');
    });
}

  public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};