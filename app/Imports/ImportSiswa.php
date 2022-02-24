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
               'username' => $row[4],
               'phone' => $row[3],
               'foto' => '-',
               'password' => bcrypt($row[5]),
               'role' => 'Wali',
               'status' => 'Active',
               'created_at' => Carbon::now(),
               'updated_at' => Carbon::now(),
           ]);
           $idWali = Walimurid::create([
               'id_users'=>  $user->id,
               'nama'=>  $row[0],
               'alamat'=>  $row[2],
               'status'=>  'Active',
               'created_at'=>  Carbon::now(),
               'updated_at'=>  Carbon::now(),
           ]);
           Siswa::create([
               'id_wali' => $idWali->id,
               'nama' => $row[0],
               'birth_date' => $row[0],
               'kode_referal' => 'BS'.Helper::generateRandomString(5),
               'status' => 'Aktif',
               'created_at'=>  Carbon::now(),
               'updated_at'=>  Carbon::now(),
           ]);

       }
    }
}
