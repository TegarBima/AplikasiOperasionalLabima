<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Metro extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'metros';
    protected $fillable = [
        'nama',
        'nama_cp',
        'telp_cp',
        'email_cp',
        'status',
        'keterangan',
    ];
}