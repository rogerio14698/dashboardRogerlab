<?php

namespace App\Console\Commands;

use App\Domain\Uptime\UptimeCheckerInterface;
use App\Models\Subdomain;
use App\Models\SubdomainCheck;
use Illuminate\Console\Command;

class CheckUptime extends Command
{
    protected $signature = 'monitor:uptime';
    protected $description = 'Check configured subdomains over HTTP.';

    public function handle(UptimeCheckerInterface $checker): int
    {
        Subdomain::where('enabled', true)->each(function (Subdomain $subdomain) use ($checker): void {
            SubdomainCheck::create(array_merge(['subdomain_id' => $subdomain->id, 'checked_at' => now()], $checker->check($subdomain->url)));
        });
        return self::SUCCESS;
    }
}
