<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListadoObra extends Model
{
    protected $table = 'listado_obras';
    protected $primaryKey = 'ID_SEGUIMIENTO';
    public $timestamps = false;
}