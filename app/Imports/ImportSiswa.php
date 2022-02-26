<?php

namespace App\Imports;

use App\Helpers\Helper;
use App\Siswa;
use App\User;
use App\Walimurid;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ImportSiswa implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {

            $user = User::create([
               'username' => $row[5],
               'phone' => $row[4],
               'foto' => '-',
               'password' => bcrypt($row[6]),
               'role' => 'Walimurid',
               'status' => 'Active',
               'created_at' => Carbon::now(),
               'updated_at' => Carbon::now(),
           ]);
           $idWali = Walimurid::create([
               'id_users'=>  $user->id,
               'nama'=>  $row[2],
               'alamat'=>  $row[3],
               'status'=>  'Active',
               'created_at'=>  Carbon::now(),
               'updated_at'=>  Carbon::now(),
           ]);
           Siswa::create([
               'id_wali' => $idWali->id,
               'nama' => $row[0],
               'birth_date' => $row[1],
               'kode_referal' => 'BS'.Helper::generateRandomString(5),
               'status' => 'Aktif',
               'created_at'=>  Carbon::now(),
               'updated_at'=>  Carbon::now(),
           ]);

       }
    }
}
