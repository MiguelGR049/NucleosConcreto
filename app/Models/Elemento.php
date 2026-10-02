<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Elemento extends Model
{
    public $timestamps = false;
    protected $fillable = ['nombre'];
}