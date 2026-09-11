<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Odp extends Model
{
    use SoftDeletes;
    protected $table      = 'odps';
    protected $primaryKey = 'id';
    public $incrementing  = true;
    protected $keyType    = 'int';

    protected $fillable = [
        'odp_id',
        'odc_id',
        'kode_wilayah',
        'lokasi_odp',
        'jumlah_port',
        'sisa_port',
    ];

    public function odc()
    {
        return $this->belongsTo(Odc::class, 'odc_id', 'odc_id');
    }

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class, 'kode_wilayah', 'kode_wilayah');
    }

    public function register()
    {
        return $this->hasMany(MasterRegistrasi::class, 'odp_id', 'odp_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (blank($model->odp_id)) {
                DB::transaction(function () use ($model) {
                    $prefix    = $model->odc_id;
                    $pad       = 4;
                    $startPos  = strlen($prefix) + 1;
                    $maxNumber = self::where('odp_id', 'REGEXP', '^ODP[0-9]+$')
                        ->lockForUpdate()
                        ->selectRaw("COALESCE(MAX(CAST(SUBSTRING(odp_id, {$startPos}) AS UNSIGNED)), 0) as max_num")
                        ->value('max_num');
                    $next = (int) $maxNumber + 1;
                    do {
                        $candidate = $prefix . str_pad($next, $pad, '0', STR_PAD_LEFT);
                        if (! self::where('odp_id', $candidate)->exists()) {
                            $model->odp_id = $candidate;
                            break;
                        }
                        $next++;
                    } while (true);
                });

            }
        });

        static::deleting(function (Odp $odp) {
            $datapsb = MasterRegistrasi::where('odp_id', $odp->odp_id)->get();
            $datapsb->each(function (MasterRegistrasi $masterRegister) {
                $masterRegister->delete();
            });
        });
    }
}