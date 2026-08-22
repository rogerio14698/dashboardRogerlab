<?php

namespace App\Console\Commands;

use App\Domain\Seo\BasicSeoChecker;
use App\Models\SeoCheck;
use App\Models\Subdomain;
use Illuminate\Console\Command;

class CheckSeo extends Command
{
    protected $signature = 'monitor:seo';
    protected $description = 'Capture basic SEO checks for configured subdomains.';

    public function handle(BasicSeoChecker $checker): int
    {
        Subdomain::where('enabled', true)->each(function (Subdomain $subdomain) use ($checker): void {
            SeoCheck::create([
                'subdomain_id' => $subdomain->id,
                'results' => $checker->check($subdomain->url),
                'checked_at' => now(),
            ]);
        });

        return self::SUCCESS;
    }
}
