<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WaliTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('wali_siswa')->insert([
            'id_users' => '3',
            'nama' => 'Wali Bigstars',
            'alamat' => 'Jember',
        ]);
    }
}
