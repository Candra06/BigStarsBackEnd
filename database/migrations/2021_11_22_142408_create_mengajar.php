<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMengajar extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mengajar', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_guru')->unsigned();
            $table->integer('id_kelas')->unsigned();
            $table->enum('tipe', ['Asli', 'Pengganti']);
            $table->integer('spp');
            $table->integer('fee_pengajar');
            $table->integer('poin_siswa');
            $table->enum('status', ['Waiting', 'Done', 'Cancel']);
            $table->string('materi');
            $table->string('file_materi');
            $table->text('jurnal');
            $table->timestamps();
            $table->foreign('id_guru')->references('id')->on('guru');
            $table->foreign('id_kelas')->references('id')->on('kelas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mengajar');
    }
}
