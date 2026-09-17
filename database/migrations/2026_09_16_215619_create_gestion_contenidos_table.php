<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gestion_contenidos', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('database_name');
            $table->string('driver')->default('mysql');
            $table->string('host')->default('127.0.0.1');
            $table->unsignedInteger('port')->default(3306);
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gestion_contenidos');
    }
};
