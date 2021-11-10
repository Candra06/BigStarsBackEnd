<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SiswaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('siswa')->insert([
            'id_wali' => '1',
            'nama' => 'Siswa Bigstars',
            'birth_date' => '2000-02-06',
        ]);
    }
}
