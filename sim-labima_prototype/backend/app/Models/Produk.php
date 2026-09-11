<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Produk extends Model
{
    use SoftDeletes;

    protected $table      = 'produk';
    protected $primaryKey = 'produk_id';
    public $incrementing  = true;
    protected $keyType    = 'int';

    protected $fillable = [
        'nama',
        'kecepatan',
        'harga',
        'local_address',
        'pool',
    ];

    public function registrasi()
    {
        return $this->hasMany(MasterRegistrasi::class, "produk_id", "produk_id");
    }
}