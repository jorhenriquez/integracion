<?php

namespace App\Models\Meribia;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Devlinext extends Model
{
    protected $connection = 'meribia';
    protected $table = 'DEVLINEXT';      // <-- ajusta si corresponde
    protected $primaryKey = 'CODIGO';        // <-- ajusta si tu PK tiene otro nombre
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
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

    protected static function boot()
    {
        parent::boot();

        // Autoasignar GUID si viene vacío
        static::creating(function ($model) {
            if (empty($model->REG_GUID)) {
                $model->REG_GUID = (string) Str::uuid();  // o Str::orderedUuid() si prefieres
            }
            $model->REG_DBNAME = config('database.connections.meribia.database');
            $model->REG_SUSCRIPTOR = '';
            // Si quieres setear timestamps custom:
            $model->REG_CREACION = now();
            $model->REG_CREACION_UTC = now()->utc();
            $model->REG_MODIF = NULL;
            $model->REG_MODIF_UTC = NULL;
        });

        // Si quieres actualizar tus campos de modificación:
        // static::updating(function ($model) {
        //     $model->REG_MODIF = now();
        //     $model->REG_MODIF_UTC = now()->utc();
        // });
    }

    public function pedext()
    {
        return $this->belongsTo(Pedext::class, 'PEDEXT', 'CODIGO');
    }
}
