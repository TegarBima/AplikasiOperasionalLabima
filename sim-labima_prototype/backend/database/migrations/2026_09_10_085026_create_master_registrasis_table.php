<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_registrasis', function (Blueprint $table) {
            $table->id("register_id"); // Primary Key disamakan menjadi register_id
            $table->string('type_pelanggan', 50)->nullable();
            $table->unsignedBigInteger('metro_id')->nullable();
            $table->unsignedBigInteger('cabang_id')->nullable();
            $table->unsignedBigInteger('produk_id')->nullable();

            $table->string('nama_pelanggan');
            $table->text('alamat_pelanggan');
            $table->string('rt', 5)->nullable();
            $table->string('rw', 5)->nullable();
            $table->string('kode_wilayah', 20)->nullable();
            $table->string('telp_pelanggan', 50)->nullable();
            $table->string('map_pelanggan', 255)->nullable();
            $table->string('nama_cp')->nullable();
            $table->string('telp_cp', 50)->nullable();
            $table->string('nomor_identitas', 100)->nullable();
            $table->text('photo_identitas')->nullable();

            $table->string('odp_id', 50)->nullable();
            $table->unsignedBigInteger('mitra_id')->nullable();
            $table->unsignedBigInteger('antena_id')->nullable();

            $table->integer('fee_marketing')->nullable();
            $table->integer('fee_mitra')->nullable();
            $table->integer('fee_tagih')->nullable();
            $table->string('periode_fee', 50)->nullable();

            $table->dateTime('tgl_register')->nullable();
            $table->dateTime('tgl_survey')->nullable();
            $table->dateTime('tgl_input_survey')->nullable();
            $table->string('status_survey', 50)->nullable();
            $table->string('petugas_survey', 100)->nullable();
            $table->text('photo_lokasi')->nullable();
            $table->text('photo_lokasi_ont')->nullable();
            $table->text('photo_rute')->nullable();

            $table->float('estimasi_kabel')->nullable();
            $table->dateTime('tgl_pasang')->nullable();
            $table->dateTime('tgl_input_pasang')->nullable();
            $table->string('status_pasang', 50)->nullable();
            $table->string('petugas_pasang', 100)->nullable();
            $table->text('keterangan_pasang')->nullable();
            $table->text('photo_serah_terima')->nullable();
            $table->text('photo_bukti_bayar')->nullable();

            $table->dateTime('tgl_aktifasi')->nullable();
            $table->integer('biaya_pasang')->nullable();
            $table->string('status_bayar', 50)->nullable();
            $table->dateTime('mulai_abonemen')->nullable();

            $table->text('keterangan_regist')->nullable();
            $table->text('keterangan_survey')->nullable();
            $table->text('keterangan_tolak')->nullable();

            $table->string('id_pelanggan', 25)->nullable();
            $table->string('pppoe_id', 100)->nullable();
            $table->string('pppoe_password', 100)->nullable();
            $table->integer('vlan_id')->nullable();
            $table->string('mikrotik_name', 100)->nullable();

            $table->integer('tgl_jatuh_tempo')->nullable();
            $table->integer('ppn')->nullable();
            $table->integer('discount')->nullable();

            $table->boolean('status_fee_marketing')->default(false);
            $table->dateTime('tgl_bayar_fee_marketing')->nullable();
            $table->string('status_alat', 50)->nullable();

            $table->string('status', 50)->nullable();
            $table->string('status_langganan', 50)->nullable();

            $table->boolean('auto_isolir')->default(false);
            $table->boolean('auto_broadcast')->default(false);

            $table->unsignedBigInteger('titik_jemput_id')->nullable();
            $table->string('penjemputan', 100)->nullable();

            $table->unsignedBigInteger('register_by')->nullable();
            $table->unsignedBigInteger('survey_by')->nullable();
            $table->unsignedBigInteger('pasang_by')->nullable();
            $table->unsignedBigInteger('aktivasi_by')->nullable();

            $table->dateTime('tgl_isolir')->nullable();
            $table->dateTime('tgl_berhenti')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Relasi Foreign Key
            $table->foreign('cabang_id')->references('cabang_id')->on('cabang')->nullOnDelete();
            $table->foreign('odp_id')->references('odp_id')->on('odps')->nullOnDelete();
            $table->foreign('mitra_id')->references('mitra_id')->on('master_mitras')->nullOnDelete();
            $table->foreign('produk_id')->references('produk_id')->on('produks')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_registrasis');
    }
};