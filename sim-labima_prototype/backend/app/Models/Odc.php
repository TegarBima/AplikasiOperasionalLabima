<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Odc extends Model
{
    use SoftDeletes;
    protected $table      = 'odcs';
    protected $primaryKey = 'id';
    public $incrementing  = true;
    protected $keyType    = 'int';

    protected $fillable = [
        'odc_id',
        'lokasi_odc',
        'kode_wilayah',
        'jumlah_port',
        'sisa_port',
    ];

    /**
     * Relasi ke Desa
     */
    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class, 'kode_wilayah', 'kode_wilayah');
    }

    /**
     * Relasi ke ODP
     */
    public function odps()
    {
        return $this->hasMany(Odp::class, 'odc_id', 'odc_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (blank($model->odc_id)) {
                DB::transaction(function () use ($model) {
                    $prefix    = 'ODC';
                    $pad       = 4;
                    $startPos  = strlen($prefix) + 1;
                    $maxNumber = self::where('odc_id', 'REGEXP', '^ODC[0-9]+$')
                        ->lockForUpdate()
                        ->selectRaw("COALESCE(MAX(CAST(SUBSTRING(odc_id, {$startPos}) AS UNSIGNED)), 0) as max_num")
                        ->value('max_num');
                    $next = (int) $maxNumber + 1;
                    do {
                        $candidate = $prefix . str_pad($next, $pad, '0', STR_PAD_LEFT);
                        if (! self::where('odc_id', $candidate)->exists()) {
                            $model->odc_id = $candidate;
                            break;
                        }
                        $next++;
                    } while (true);
                });

            }
        });

        static::deleting(function (Odc $odc) {
            $odc->odps()->get()->each(function (Odp $odp) {
                $odp->delete();
            });
        });
    }
}