<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotifikasiDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifikasi_detail', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_notif')->unsigned();
            $table->integer('id_penerima')->unsigned();
            $table->enum('status', ['Unread', 'Read']);
            $table->timestamps();
            $table->foreign('id_notif')->references('id')->on('notifikasi');
            $table->foreign('id_penerima')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notifikasi_detail');
    }
}
