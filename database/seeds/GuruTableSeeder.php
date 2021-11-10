<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GuruTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('guru')->insert([
            'id_users' => '2',
            'nama' => 'Guru Bigstars',
            'alamat' => 'Jember',
            'birth_date' => '1997-02-06',
        ]);
    }
}
