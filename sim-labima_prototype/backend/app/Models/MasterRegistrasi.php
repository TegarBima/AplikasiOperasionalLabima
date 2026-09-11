<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterRegistrasi extends Model
{
    use SoftDeletes;
    use HasFactory;
    protected $table      = "MasterRegistrasi";
    protected $primaryKey = 'registrasi_id';
    public $incrementing  = true;
    protected $keyType    = 'int';

    protected $fillable = [
        'nama_pelanggan',
        'alamat_pelanggan',
        'kode_wilayah',
        'rt',
        'rw',
        'telp_pelanggan',
        'map_pelanggan',
        'nama_cp',
        'telp_cp',
        'nomor_identitas',
        'photo_identitas',
        'odp_id',
        'mitra_id',
        'cabang_id',
        'fee_marketing',
        'fee_mitra',
        'fee_tagih',
        'periode_fee',
        'tgl_register',
        'tgl_survey',
        'tgl_input_survey',
        'status_survey',
        'petugas_survey',
        'photo_lokasi',
        'photo_lokasi_ont',
        'photo_rute',
        'produk_id',
        'estimasi_kabel',
        'tgl_pasang',
        'tgl_input_pasang',
        'status_pasang',
        'petugas_pasang',
        'keterangan_pasang',
        'photo_serah_terima',
        'photo_bukti_bayar',
        'tgl_aktifasi',
        'biaya_pasang',
        'status_bayar',
        'mulai_abonemen',
        'keterangan_regist',
        'keterangan_survey',
        'keterangan_tolak',
        'id_pelanggan',
        'pppoe_id',
        'pppoe_password',
        'vlan_id',
        'mikrotik_name',
        'tgl_jatuh_tempo',
        'ppn',
        'discount',
        'status_fee_marketing',
        'tgl_bayar_fee_marketing',
        'status_alat',
        'status',
        'status_langganan',
        'antena_id',
        'auto_isolir',
        'auto_broadcast',
        'type_pelanggan',
        'metro_id',
        'titik_jemput_id',
        'penjemputan',
        'register_by',
        'survey_by',
        'pasang_by',
        'aktivasi_by',
        'tgl_isolir',
        'tgl_berhenti',
    ];

    protected $casts = [
        'auto_isolir'     => 'boolean',
        'auto_broadcast'  => 'boolean',
        'tgl_jatuh_tempo' => 'integer',
    ];

    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'cabang_id', 'cabang_id');
    }

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class, 'kode_wilayah', 'kode_wilayah');
    }

    public function odp()
    {
        return $this->belongsTo(Odp::class, 'odp_id', 'odp_id');
    }

    public function mitra()
    {
        return $this->belongsTo(MasterMitra::class, 'mitra_id', 'mitra_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id', 'produk_id');
    }
}