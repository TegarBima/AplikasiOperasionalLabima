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
         Schema::create('odps', function (Blueprint $table) {
            $table->id();
            $table->string('odp_id', 50)->unique();
            $table->string('odc_id', 50);
            $table->string('kode_wilayah', 20);
            $table->string('lokasi_odp');
            $table->integer('jumlah_port');
            $table->integer('sisa_port');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('odc_id')->references('odc_id')->on('odcs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('odps');
    }
};