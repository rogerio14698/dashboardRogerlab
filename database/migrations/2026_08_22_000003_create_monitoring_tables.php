<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_metrics', function (Blueprint $table): void {
            $table->id(); $table->json('snapshot'); $table->timestamp('captured_at')->index(); $table->timestamps();
        });
        Schema::create('docker_containers', function (Blueprint $table): void {
            $table->id(); $table->string('container_id')->index(); $table->string('name'); $table->string('image')->nullable(); $table->string('status'); $table->json('snapshot')->nullable(); $table->timestamp('captured_at')->index(); $table->timestamps();
        });
        Schema::create('subdomains', function (Blueprint $table): void {
            $table->id(); $table->string('name')->unique(); $table->string('url'); $table->boolean('enabled')->default(true); $table->timestamps();
        });
        Schema::create('subdomain_checks', function (Blueprint $table): void {
            $table->id(); $table->foreignId('subdomain_id')->constrained()->cascadeOnDelete(); $table->unsignedSmallInteger('status_code')->nullable(); $table->boolean('available')->default(false); $table->decimal('response_time_ms', 10, 2)->nullable(); $table->text('error')->nullable(); $table->timestamp('checked_at')->index(); $table->timestamps();
        });
        Schema::create('seo_checks', function (Blueprint $table): void {
            $table->id(); $table->foreignId('subdomain_id')->constrained()->cascadeOnDelete(); $table->json('results'); $table->timestamp('checked_at')->index(); $table->timestamps();
        });
        Schema::create('n8n_executions', function (Blueprint $table): void {
            $table->id(); $table->string('execution_id')->unique(); $table->string('workflow_name')->nullable(); $table->string('status')->nullable(); $table->text('error')->nullable(); $table->json('payload')->nullable(); $table->timestamp('started_at')->nullable(); $table->timestamps();
        });
        Schema::create('alerts', function (Blueprint $table): void {
            $table->id(); $table->string('fingerprint')->unique(); $table->string('type'); $table->string('severity'); $table->json('context')->nullable(); $table->timestamp('triggered_at')->nullable(); $table->timestamp('resolved_at')->nullable(); $table->timestamp('notified_at')->nullable(); $table->timestamps();
        });
        Schema::create('activity_logs', function (Blueprint $table): void {
            $table->id(); $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); $table->string('action'); $table->string('target')->nullable(); $table->json('context')->nullable(); $table->string('result'); $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs'); Schema::dropIfExists('alerts'); Schema::dropIfExists('n8n_executions'); Schema::dropIfExists('seo_checks'); Schema::dropIfExists('subdomain_checks'); Schema::dropIfExists('subdomains'); Schema::dropIfExists('docker_containers'); Schema::dropIfExists('system_metrics');
    }
};
