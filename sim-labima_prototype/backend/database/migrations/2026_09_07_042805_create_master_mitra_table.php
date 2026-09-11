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
        Schema::create("master_mitras", function (Blueprint $table) {
            $table->id('mitra_id');
            $table->string('nama_mitra');
            $table->string('alamat_mitra');
            $table->string('telp_mitra');
            $table->integer('fee_mitra');
            $table->integer('fee_tagihan');
            $table->string('periode_fee');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_mitras');
    }
};
