<?php

namespace App\Imports;

use App\Guru;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Facades\Hash;

class ImportGuru implements ToCollection
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
                'role' => 'Guru',
                'status' => 'Active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            Guru::create([
                'id_users'=>  $user->id,
                'nama'=>  $row[0],
                'alamat'=>  $row[2],
                'birth_date'=>  $row[1],
                'created_at'=>  Carbon::now(),
                'updated_at'=>  Carbon::now(),
            ]);

        }
    }
}
