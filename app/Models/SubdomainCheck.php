<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubdomainCheck extends Model
{
    protected $fillable = ['subdomain_id', 'status_code', 'available', 'response_time_ms', 'error', 'checked_at'];
    protected function casts(): array { return ['available' => 'boolean', 'checked_at' => 'datetime']; }
    public function subdomain(): BelongsTo { return $this->belongsTo(Subdomain::class); }
}
