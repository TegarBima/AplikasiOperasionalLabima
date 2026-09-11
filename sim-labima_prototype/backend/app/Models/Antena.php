<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Antena extends Model
{
    use HasFactory;

    protected $table = 'antenas';

    protected $fillable = [
        'nama',
        'mac',
        'ssid',
        'password',
        'frequency',
        'channel',
        'alamat',
        'titik_lokasi',
        'keterangan',
    ];

    public function register()
    {
        return $this->hasMany(MasterRegistrasi::class, 'antena_id', 'antena_id');
    }

}