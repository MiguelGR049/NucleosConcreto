<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Recepcion extends Model
{
    protected $table = 'recepciones';
    protected $primaryKey = 'id_seg';
    public $timestamps = false;

    protected $fillable = [
        'folio', 'clave_obra', 'fecha_rec', 'n_espec', 'elemento',
        'localizacion', 'datos_proy', 'defectos', 'envia', 'recibe',
        'fecha_muest', 'observ', 'usuario_reg_id', 'fecha_reg',
    ];

    protected function elemento(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => strtoupper(trim($value)),
        );
    }

    public function fechasEnsayo()
    {
        return $this->hasMany(FechaEnsayo::class, 'recepcion_id', 'id_seg');
    }

    public function usuarioRegistro()
    {
        return $this->belongsTo(User::class, 'usuario_reg_id');
    }
}