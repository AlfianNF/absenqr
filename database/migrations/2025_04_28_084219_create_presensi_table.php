<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('presensis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_user')->onDelete('cascade');
            $table->unsignedBigInteger('id_setting')->onDelete('cascade');
            $table->time('jam_masuk');
            $table->time('jam_keluar')->nullable();
            $table->enum('status', ['tepat waktu','terlambat','alfa'])->default('alfa');
            $table->timestamps();

            $table->foreign('id_user')->references('id')->on('users');
            $table->foreign('id_setting')->references('id')->on('setting_presensis');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensi');
    }
};
