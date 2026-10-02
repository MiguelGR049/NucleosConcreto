<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FechaEnsayo extends Model
{
    protected $table = 'fechas_ensayo';
    protected $primaryKey = 'id_seg';

    protected $fillable = ['recepcion_id', 'dia', 'fecha_ensayo'];

    public function recepcion()
    {
        return $this->belongsTo(Recepcion::class, 'recepcion_id', 'id_seg');
    }
}