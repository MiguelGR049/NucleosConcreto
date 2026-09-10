<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $table = 'usuarios'; // le decimos a Laravel el nombre real de la tabla

    public $timestamps = false; // porque la tabla no tiene created_at/updated_at

    protected $fillable = [
        'nombre',
        'AP_paterno',
        'AP_materno',
        'area',
        'usuario',
        'password',
    ];

    protected $hidden = [
        'password',
    ];
}