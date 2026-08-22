<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    protected $fillable = ['fingerprint', 'type', 'severity', 'context', 'triggered_at', 'resolved_at', 'notified_at'];
    protected function casts(): array { return ['context' => 'array', 'triggered_at' => 'datetime', 'resolved_at' => 'datetime', 'notified_at' => 'datetime']; }
}
