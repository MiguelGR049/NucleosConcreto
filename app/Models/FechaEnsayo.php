<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FechaEnsayo extends Model
{
    protected $table = 'fechas_ensayo';
    protected $primaryKey = 'id_seg';

    protected $fillable = ['recepcion_id', 'dia', 'fecha_ensayo', 'capturada', 'ensayada'];

    protected $casts = [
        'fecha_ensayo' => 'date',
        'capturada'    => 'boolean',
        'ensayada'     => 'boolean',
    ];

    public function recepcion()
    {
        return $this->belongsTo(Recepcion::class, 'recepcion_id', 'id_seg');
    }

    public function hoja()
    {
        return $this->hasOne(HojaEnsayo::class, 'fecha_ensayo_id', 'id_seg');
    }
}
