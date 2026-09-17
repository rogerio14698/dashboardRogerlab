<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gestion_contenidos', function (Blueprint $table): void {
            $table->string('upload_path')->nullable()->after('database_name');
        });
    }

    public function down(): void
    {
        Schema::table('gestion_contenidos', function (Blueprint $table): void {
            $table->dropColumn('upload_path');
        });
    }
};
