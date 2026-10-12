<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnsayoNucleo extends Model
{
    protected $table = 'ensayos_nucleo';
    protected $primaryKey = 'id_seg';

    protected $fillable = [
        'hoja_id',
        'columna',
        'no_nucleo',
        'masa_g',
        'diametro_1_mm',
        'diametro_2_mm',
        'altura_1_sin_cabeceo_mm',
        'altura_2_sin_cabeceo_mm',
        'altura_1_con_cabeceo_mm',
        'altura_2_con_cabeceo_mm',
        'carga_kn',
        'tipo_falla',
    ];

    public function hoja()
    {
        return $this->belongsTo(HojaEnsayo::class, 'hoja_id', 'id_seg');
    }
}
