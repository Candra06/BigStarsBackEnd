<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKelas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kelas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_mapel')->unsigned();
            $table->integer('id_guru')->unsigned();
            $table->integer('id_siswa')->unsigned();
            $table->integer('spp');
            $table->integer('fee_guru');
            $table->timestamps();
            $table->foreign('id_mapel')->references('id')->on('mapel');
            $table->foreign('id_guru')->references('id')->on('guru');
            $table->foreign('id_siswa')->references('id')->on('siswa');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kelas');
    }
}
