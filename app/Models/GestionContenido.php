<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GestionContenido extends Model
{
    protected $table = 'gestion_contenidos';

    protected $fillable = [
        'name',
        'database_name',
        'upload_path',
        'host',
        'port',
        'driver',
        'username',
        'password',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'port' => 'integer',
    ];
}
