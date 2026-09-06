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
        Schema::create('wilayah', function (Blueprint $table) {
            $table->id();
            $table->string('kode_wilayah', 20)->unique();
            $table->string('wilayah', 100);
            $table->enum('type_wilayah', [
                'provinsi',
                'kabupaten/kota',
                'kecamatan',
                'desa/kampung/kelurahan',
            ]);
            $table->string('lingkup', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wilayah');
    }
};