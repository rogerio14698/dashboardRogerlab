<?php

namespace App\Domain\Seo;

interface SeoCheckerInterface
{
    /** @return array<string, mixed> */
    public function check(string $url): array;
}
