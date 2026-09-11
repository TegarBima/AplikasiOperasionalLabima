<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterMitra extends Model
{
    use SoftDeletes;

    protected $table      = 'mitra';
    protected $primaryKey = 'mitra_id';
    public $incrementing  = true;
    protected $keyType    = 'int';

    protected $fillable = [
        'nama_mitra',
        'alamat_mitra',
        'telp_mitra',
        'fee_mitra',
        'fee_tagihan',
        'periode_fee',
    ];

    public function register()
    {
        return $this->hasMany(MasterRegistrasi::class, "mitra_id", "mitra_id");
    }
}