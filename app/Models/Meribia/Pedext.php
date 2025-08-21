<?php

namespace App\Models\Meribia;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Pedext extends Model
{
    protected $connection = 'meribia';
    protected $table = 'PEDEXT';
    protected $primaryKey = 'CODIGO';
    public $incrementing = true;      // IDENTITY en SQL Server
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'TIPO',
        'NIFCLI',
        'NIFCON',
        'NIFREM',
        'NIFDES',
        'NOMCOM',
        'FECHA',
        'FECLIM',
        'REFERENCIA',
        'NUMPLA',
        'CMR',
        'CRT',
        'DTMI',
        'NOMTIPMER',
        'NOMCAT',
        'NOMCLA',
        'CODPRY',
        'CODRUT',
        'PGORI',
        'ORIGEN',
        'DIRORI1',
        'DIRORI2',
        'CPORI',
        'POBLAORI',
        'PROVIORI',
        'NOMPAIORI',
        'ZONORI',
        'FECHORORI',
        'HASFECHORORI',
        'REFORI',
        'CONORI',
        'TELORI',
        'MOVORI',
        'PGDES',
        'DESTINO',
        'DIRDES1',
        'DIRDES2',
        'CPDES',
        'POBLADES',
        'PROVIDES',
        'NOMPAIDES',
        'ZONDES',
        'FECHORDES',
        'HASFECHORDES',
        'REFDES',
        'CONDES',
        'TELDES',
        'MOVDES',
        'NOMMER',
        'PESO',
        'NOMENV',
        'ENVASES',
        'UNIDADES',
        'VOLUMEN',
        'METROS',
        'NUMDOC',
        'DOCUMENTOS',
        'REQMAT',
        'NOMTIPMAT',
        'TIPFAC',
        'CANTIDAD',
        'PREUNI',
        'VALMER',
        'TELEFONO',
        'DESCRIPCION',
        'CLAVE1',
        'OBSERVACIONES',
        'PROCESANDO',
        'PROCESADO',
        'CODPDC',
        'BORRADO',
        'ERROR',
        'NOTASERROR',
        'DISTRIBUCION',
        'MEDIO',
        'INSCAR',
        'NOMDELCLI',
        'ESTCARGA',
        'REG_GUID',
        'REG_DBNAME',
        'REG_SUSCRIPTOR',
        'REG_CREACION',
        'REG_CREACION_UTC',
        'REG_MODIF',
        'REG_MODIF_UTC',

    ];

    # Casts útiles para GUID y fechas
    protected $casts = [
        'REG_GUID'        => 'string',    // uniqueidentifier se maneja como string
        'REG_CREACION'    => 'datetime',
        'REG_CREACION_UTC'=> 'datetime',
        'REG_MODIF'       => 'datetime',
        'REG_MODIF_UTC'   => 'datetime',
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

    public function lineas()
    {
        return $this->hasMany(Devlinext::class, 'ID_PEDEXT', $this->primaryKey);
    }
}
