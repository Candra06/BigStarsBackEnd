<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterMengajarAddLocation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mengajar', function (Blueprint $table) {
            $table->string('latitude')->after('jurnal')->default('-8.2074597');
            $table->string('longitude')->after('latitude')->default('113.697264');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mengajar', function (Blueprint $table) {
            //
        });
    }
}
