<?php

namespace App\Models\Meribia;

use Illuminate\Database\Eloquent\Model;

class Devlinext extends Model
{
    protected $connection = 'meribia';
    protected $table = 'DEVLINEXT';      // <-- ajusta si corresponde
    protected $primaryKey = 'ID';        // <-- ajusta si tu PK tiene otro nombre
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'CODIGO',
        'PEDEXT',
        'ORDEN',
        'FECHA',
        'NUMBUL',
        'NUMBULAPL',
        'NUMFAC',
        'KILOS',
        'UNIDADES',
        'BULTOS',
        'VOLUMEN',
        'UNIVOL',
        'TIPFAC',
        'CONENT',
        'INSTRUMENTO',
        'DOCUMENTO',
        'BANCO',
        'IMPFAC',
        'IMPORTE',
        'VALMER',
        'PREUNIREP',
        'IMPREP',
        'OBSERVACIONES',
        'REG_GUID',
        'REG_DBNAME',
        'REG_SUSCRIPTOR',
        'REG_CREACION',
        'REG_CREACION_UTC',
        'REG_MODIF',
        'REG_MODIF_UTC',

    ];

    public function pedext()
    {
        return $this->belongsTo(Pedext::class, 'ID_PEDEXT', 'ID');
    }
}
