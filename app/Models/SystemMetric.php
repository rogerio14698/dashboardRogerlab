<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemMetric extends Model
{
    protected $fillable = ['snapshot', 'captured_at'];
    protected function casts(): array { return ['snapshot' => 'array', 'captured_at' => 'datetime']; }
}
