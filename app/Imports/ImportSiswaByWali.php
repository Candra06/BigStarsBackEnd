<?php

namespace App\Imports;

use App\Helpers\Helper;
use App\Siswa;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ImportSiswaByWali implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            Siswa::create([
                'id_wali' => $row[0],
                'nama' => $row[1],
                'birth_date' => $row[2],
                'kode_referal' => 'BS'.Helper::generateRandomString(5),
                'status' => 'Aktif',
                'created_at'=>  Carbon::now(),
                'updated_at'=>  Carbon::now(),
            ]);
        }
    }
}
