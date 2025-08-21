<?php

namespace App\Models\Meribia;

use Illuminate\Database\Eloquent\Model;

class Pedext extends Model
{
    protected $connection = 'meribia';
    protected $table = 'PEDEXT';          // <-- ajusta si corresponde
    protected $primaryKey = 'ID';         // <-- ajusta si tu PK tiene otro nombre
    public $incrementing = true;          // IDENTITY en SQL Server
    protected $keyType = 'int';
    public $timestamps = false;           // si la tabla no tiene created_at/updated_at

    protected $fillable = [
        // AJUSTA con tus columnas reales de PEDEXT:
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

    // Relaciones
    public function lineas()
    {
        // si la FK en DEVLINEXT es ID_PEDEXT
        return $this->hasMany(Devlinext::class, 'ID_PEDEXT', $this->primaryKey);
    }
}
