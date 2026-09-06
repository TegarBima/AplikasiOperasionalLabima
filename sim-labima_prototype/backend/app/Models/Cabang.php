<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cabang extends Model
{
    use SoftDeletes;
    protected $table      = 'cabang';
    protected $primaryKey = 'cabang_id';
    public $incrementing  = true;
    protected $keyType    = 'int';

    protected $fillable = [
        'nama_cabang',
        'alamat_cabang',
        'lokasi_cabang',
        'nama_PIC',
        'email_PIC',
        'telp_PIC',
    ];

}