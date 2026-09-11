<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('master_registrasis', function (Blueprint $table) {
            $table->id("register_id");
            $table->string('nama_pelanggan');
            $table->text('alamat_pelanggan');
            $table->string('kode_wilayah', 20); 
            $table->string('telp_pelanggan', 50)->nullable();
            $table->string('rt', 2)->nullable();
            $table->string('rw', 2)->nullable();
            $table->string('map_pelanggan', 100)->nullable();
            $table->string('nomor_identitas', 100)->nullable();
            $table->text('photo_identitas')->nullable();
            $table->string('nama_cp')->nullable();
            $table->string('telp_cp', 50)->nullable();
            $table->string('odp_id', 50)->nullable();
            $table->unsignedBigInteger('mitra_id')->nullable();
            $table->dateTime('tgl_register')->nullable();
            $table->text('keterangan_regist')->nullable();
            $table->unsignedBigInteger('produk_id')->nullable();
            $table->integer('fee_mitra')->nullable();

            $table->dateTime('tgl_survey')->nullable();
            $table->dateTime('tgl_input_survey')->nullable();
            $table->string('status_survey', 50)->nullable();
            $table->string('petugas_survey', 100)->nullable();
            $table->text('photo_lokasi')->nullable();
            $table->text('photo_lokasi_ont')->nullable();
            $table->text('photo_rute')->nullable();
            $table->float('estimasi_kabel')->nullable();
            $table->string('keterangan_survey')->nullable();
            $table->string('keterangan_tolak')->nullable();

            $table->dateTime('tgl_pasang')->nullable();
            $table->dateTime('tgl_input_pasang')->nullable();
            $table->string('status_pasang', 50)->nullable();
            $table->string('petugas_pasang', 100)->nullable();
            $table->dateTime('tgl_aktifasi')->nullable();
            $table->integer('biaya_pasang')->nullable();
            $table->string('status_bayar')->nullable();
            $table->text('keterangan_pasang')->nullable();
            $table->dateTime('mulai_abonemen')->nullable();
            $table->text('photo_serah_terima')->nullable();
            $table->text('photo_bukti_bayar')->nullable();

            $table->string('id_pelanggan', 25)->nullable();
            $table->string('pppoe_id')->nullable();
            $table->string('pppoe_password')->nullable();
            $table->integer('vlan_id')->nullable();
            $table->string('status', 50)->nullable();
            $table->string('mikrotik_name', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys nullable
            $table->foreign('odp_id')->references('odp_id')->on('odps')->nullOnDelete();
            $table->foreign('mitra_id')->references('mitra_id')->on('master_mitras')->nullOnDelete();
            $table->foreign('produk_id')->references('produk_id')->on('produks')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_registrasis');
    }
};