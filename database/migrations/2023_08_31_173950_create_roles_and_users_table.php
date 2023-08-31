<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('mobile');
            $table->string('password');
            $table->string('auth_token');
            $table->unsignedBigInteger('role_id');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('roles')->references('id')->on('roles');
        });

        // Create a Admin Role
        DB::table('roles')->insert(['name' => 'admin', 'description' => 'A SuperUser']);

        // Assign the admin role to the first user
        DB::table('users')->where('id', 1)->update(['role' => '1']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
        Schema::dropIfExists('users');
    }
};
