<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class EnsayoNucleo extends Model
{
    protected $table = 'ensayos_nucleo';
    protected $primaryKey = 'id_seg';

    protected $fillable = [
        'recepcion_id',
        'hoja_id',
        'columna',
        'no_nucleo',
        'elemento',
        'espesor',
        'localizacion',
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

    protected function elemento(): Attribute
    {
        return Attribute::make(set: fn(string $value) => strtoupper(trim($value)));
    }

    public function recepcion()
    {
        return $this->belongsTo(Recepcion::class, 'recepcion_id', 'id_seg');
    }

    public function hoja()
    {
        return $this->belongsTo(HojaEnsayo::class, 'hoja_id', 'id_seg');
    }
}
