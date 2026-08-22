<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subdomain extends Model
{
    protected $fillable = ['name', 'url', 'enabled'];
    protected function casts(): array { return ['enabled' => 'boolean']; }
    public function checks(): HasMany { return $this->hasMany(SubdomainCheck::class); }
    public function seoChecks(): HasMany { return $this->hasMany(SeoCheck::class); }
}
