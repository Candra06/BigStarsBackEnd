<?php

namespace App\Console\Commands;

use App\Mengajar;
use App\PembayaranSPP;
use App\Referal;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class DemoCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $cek = PembayaranSPP::whereMonth('tagihan_bulan', Carbon::now()->format('m'))->first();

            if ($cek) {
                return response()->json([
                    'status_code' => 400,
                    'message' => 'Tagihan sudah ada'
                ]);
            } else {
                $absen = [];
                $total = 0;
                $dt = Mengajar::leftJoin('kelas', 'kelas.id', 'mengajar.id_kelas')
                    ->leftJoin('siswa', 'siswa.id', 'kelas.id_siswa')
                    ->whereMonth('mengajar.created_at', Carbon::now()->subMonth()->month)
                    ->select('siswa.id as id_siswa', 'mengajar.*')
                    ->orderBy('id_siswa')
                    ->get();
                // return Carbon::now()->subMonth()->month;
                // return $dt;
                $m = Mengajar::leftJoin('kelas', 'kelas.id', 'mengajar.id_kelas')
                    ->leftJoin('siswa', 'siswa.id', 'kelas.id_siswa')
                    ->whereMonth('mengajar.created_at', Carbon::now()->subMonth()->month)
                    ->select('siswa.id as id_siswa', 'mengajar.*')
                    ->orderBy('id_siswa')
                    ->distinct()
                    ->count('id_siswa');
                $idSiswa = Mengajar::leftJoin('kelas', 'kelas.id', 'mengajar.id_kelas')
                    ->leftJoin('siswa', 'siswa.id', 'kelas.id_siswa')
                    ->whereMonth('mengajar.created_at', Carbon::now()->subMonth()->month)
                    ->select('siswa.id as id_siswa')
                    ->groupBy('id_siswa')
                    ->get();
                // return $idSiswa;
                $tmpTotalSpp = [];
                for ($i = 0; $i < $m; $i++) {
                    array_push($tmpTotalSpp, 0);
                }
                $a = 0;

                for ($i = 0; $i < count($dt); $i++) {
                    if ($i > 0) {
                        if ($dt[$i]['id_siswa'] == $dt[$i - 1]['id_siswa']) {
                            $total += $dt[$i]['spp'];
                            // $tmpTotal = $total;

                            if ($i == count($dt) - 1) {
                                $tmpTotalSpp[$a] = $total;
                            }
                            // array_push($tmpTotalSpp, $tmpTotal);
                        } else {

                            $tmpTotalSpp[$a] = $total;
                            $total = $dt[$i]['spp'];
                            $a++;
                            if ($i == count($dt) - 1) {
                                $tmpTotalSpp[$a] = $total;
                            }
                        }
                    } else {
                        $total = $dt[$i]['spp'];
                        $tmpTotalSpp[$a] = $total;
                    }
                }
                // return $tmpTotalSpp;
                $tmpTotal = 0;
                for ($i = 0; $i < $m; $i++) {
                    $reff = Referal::where('reff_id',  $idSiswa[$i]->id_siswa)->where('status', 'Aktif')->count();
                    if ($reff > 0) {
                        $disc = $tmpTotalSpp[$i] * ($reff * 10) / 100;
                        $tmpTotal = $tmpTotalSpp[$i] - $disc;
                        $absen['jumlah'] = $tmpTotal;
                        $absen['keterangan'] = 'Potongan ' . ($reff * 10) . '% dari mengundang teman';
                    } else {
                        $absen['jumlah'] = $tmpTotalSpp[$i];
                        $absen['keterangan'] = '-';
                    }
                    $now = Carbon::now();
                    $absen['no_invoice'] = '#SPP' . $now->year . '' . $now->month . '' . $idSiswa[$i]->id_siswa;
                    $absen['id_siswa'] = $idSiswa[$i]->id_siswa;
                    $absen['tagihan_bulan'] = Carbon::now()->subMonth()->format('Y-m-d');
                    $absen['status'] = 'Belum Lunas';
                    $absen['created_by'] = 1;
                    $absen['updated_by'] = 1;
                    $absen['created_at'] = $now;
                    $absen['updated_at'] = $now;
                    PembayaranSPP::create($absen);
                }

                $this->info('Tagihan SPP berhasil digenerate');
            }
        } catch (\Throwable $th) {
            $this->info('Tagihan SPP berhasil digenerate');
            return $th;
            return response()->json([
                'status_code' => 401,
                'message' => $th,
            ]);
        }
    }
}
