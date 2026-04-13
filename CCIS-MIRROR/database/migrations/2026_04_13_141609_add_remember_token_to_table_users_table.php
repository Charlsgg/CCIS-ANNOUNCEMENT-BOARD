<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('table_users', function (Blueprint $table) {
            // This helper automatically creates a VARCHAR(100) nullable 'remember_token' column
            $table->rememberToken(); 
        });
    }

    public function down()
    {
        Schema::table('table_users', function (Blueprint $table) {
            $table->dropColumn('remember_token');
        });
    }
};