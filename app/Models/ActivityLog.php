<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = ['user_id', 'action', 'target', 'context', 'result'];
    protected function casts(): array { return ['context' => 'array']; }
}
