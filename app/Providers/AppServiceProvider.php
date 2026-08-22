<?php

namespace App\Providers;

use App\Domain\Docker\DockerClientInterface;
use App\Domain\Docker\DockerSocketClient;
use App\Domain\N8n\N8nClientInterface;
use App\Domain\N8n\N8nHttpClient;
use App\Domain\SystemMetrics\SystemMetricsCollector;
use App\Domain\SystemMetrics\SystemMetricsCollectorInterface;
use App\Domain\Uptime\HttpUptimeChecker;
use App\Domain\Uptime\UptimeCheckerInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SystemMetricsCollectorInterface::class, SystemMetricsCollector::class);
        $this->app->bind(DockerClientInterface::class, DockerSocketClient::class);
        $this->app->bind(UptimeCheckerInterface::class, HttpUptimeChecker::class);
        $this->app->bind(N8nClientInterface::class, N8nHttpClient::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Fortify features are configured centrally; keeping its routes enables future 2FA screens.
    }
}
