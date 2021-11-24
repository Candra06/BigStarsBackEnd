<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePembayaranFee extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pembayaran_fee', function (Blueprint $table) {
            $table->increments('id');
            $table->string('no_invoice');
            $table->date('tagihan_bulan');
            $table->integer('id_guru')->unsigned();
            $table->integer('jumlah');
            $table->enum('status', ['Lunas', 'Belum Lunas']);
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
            $table->foreign('id_guru')->references('id')->on('guru');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pembayaran_fee');
    }
}
