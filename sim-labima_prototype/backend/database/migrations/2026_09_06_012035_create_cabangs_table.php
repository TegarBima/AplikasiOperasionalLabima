<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        if (!Schema::hasTable('cabang')) {
            Schema::create('cabang', function (Blueprint $table) {
                $table->id('cabang_id');
                $table->string('nama_cabang');
                $table->text('alamat_cabang')->nullable();
                $table->string('lokasi_cabang')->nullable();
                $table->string('nama_PIC')->nullable();
                $table->string('email_PIC')->nullable();
                $table->string('telp_PIC')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cabangs');
    }
};