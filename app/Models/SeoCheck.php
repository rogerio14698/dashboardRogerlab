<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeoCheck extends Model
{
    protected $fillable = ['subdomain_id', 'results', 'checked_at'];
    protected function casts(): array { return ['results' => 'array', 'checked_at' => 'datetime']; }
    public function subdomain(): BelongsTo { return $this->belongsTo(Subdomain::class); }
}
