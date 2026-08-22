<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class N8nExecution extends Model
{
    protected $fillable = ['execution_id', 'workflow_name', 'status', 'error', 'payload', 'started_at'];
    protected function casts(): array { return ['payload' => 'array', 'started_at' => 'datetime']; }
}
