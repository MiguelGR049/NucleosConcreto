<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Recepcion extends Model
{
    protected $table = 'recepciones';
    protected $primaryKey = 'id_seg';

    protected $casts = [
        'eliminado'         => 'boolean',
        'fecha_eliminacion' => 'datetime',
    ];

    public $timestamps = false;

    protected $fillable = [
        'folio',
        'clave_obra',
        'cliente',
        'obra',
        'atencion_a',
        'n_informe',
        'fecha_rec',
        'n_espec',
        'datos_proy',
        'defectos',
        'envia',
        'recibe',
        'fecha_muest',
        'observ',
        'eliminado',
        'fecha_eliminacion',
        'usuario_reg_id',
        'fecha_reg',
    ];

    public function ensayosNucleos()
    {
        return $this->hasMany(EnsayoNucleo::class, 'recepcion_id', 'id_seg');
    }

    public function fechasEnsayo()
    {
        return $this->hasMany(FechaEnsayo::class, 'recepcion_id', 'id_seg');
    }

    public function usuarioRegistro()
    {
        return $this->belongsTo(User::class, 'usuario_reg_id');
    }

    public function scopeActivas($query)
    {
        return $query->where('eliminado', false);
    }
}
