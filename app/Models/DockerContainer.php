<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DockerContainer extends Model
{
    protected $fillable = ['container_id', 'name', 'image', 'status', 'snapshot', 'captured_at'];
    protected function casts(): array { return ['snapshot' => 'array', 'captured_at' => 'datetime']; }
}
