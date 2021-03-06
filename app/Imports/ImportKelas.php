<?php

namespace App\Imports;

use App\DetailKelas;
use App\Kelas;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ImportKelas implements ToCollection
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $hari = explode(",", $row[5]);
            $jamMulai = explode(",", $row[6]);
            $jamSelesai = explode(",", $row[7]);
            $kelas =  Kelas::create([
                'id_mapel' => $row[2],
                'id_guru' => $row[1],
                'id_siswa' => $row[0],
                'spp' => $row[3],
                'fee_guru' => $row[4],
                'status' => 'Active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            for ($i = 0; $i < count($hari); $i++) {
                $detail['id_kelas'] = $kelas->id;
                $detail['hari'] = $hari[$i];
                $detail['jam_mulai'] = $jamMulai[$i];
                $detail['jam_selesai'] =$jamSelesai[$i];
                DetailKelas::create($detail);
            }
        }
    }
}
