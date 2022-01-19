<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReferal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('referal', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('reff_id')->unsigned();
            $table->integer('id_siswa')->unsigned();
            $table->enum('status', ['Aktif', 'Nonaktif']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('referal');
    }
}
