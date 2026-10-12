<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HojaEnsayo extends Model
{
    protected $table = 'hojas_ensayo';
    protected $primaryKey = 'id_seg';

    protected $fillable = [
        'fecha_ensayo_id',
        'tmn',
        'resistencia_proyecto',
        'usuario_captura_id',
    ];

    public function fechaEnsayo()
    {
        return $this->belongsTo(FechaEnsayo::class, 'fecha_ensayo_id', 'id_seg');
    }

    public function nucleos()
    {
        return $this->hasMany(EnsayoNucleo::class, 'hoja_id', 'id_seg')->orderBy('columna');
    }
}
