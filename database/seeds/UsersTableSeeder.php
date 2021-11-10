<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'username' => 'admin@bigstars.com',
            'foto' => '-',
            'phone' => '08983368286',
            'role' => 'Admin',
            'status' => 'Active',
            'password' => bcrypt('admin')
        ]);
        DB::table('users')->insert([
            'username' => 'guru@bigstars.com',
            'foto' => '-',
            'phone' => '08983368287',
            'role' => 'Guru',
            'status' => 'Active',
            'password' => bcrypt('guru')
        ]);
        DB::table('users')->insert([
            'username' => 'wali@bigstars.com',
            'foto' => '-',
            'phone' => '08983368288',
            'role' => 'Walimurid',
            'status' => 'Active',
            'password' => bcrypt('wali')
          ]);
    }
}
