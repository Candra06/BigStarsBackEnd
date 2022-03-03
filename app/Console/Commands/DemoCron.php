<?php

namespace App\Console\Commands;

use App\Guru;
use App\Mengajar;
use App\PembayaranFEE;
use App\PembayaranSPP;
use App\Referal;
use App\Siswa;
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
            $idSiswa = Siswa::where('status', 'Aktif')->get();
            // return $idSiswa;
            foreach ($idSiswa as $key) {
                $absen['jumlah'] = 0;
                $absen['keterangan'] = '-';

                $now = Carbon::now();
                $absen['no_invoice'] = '#SPP' . $now->year . '' . $now->month . '' . $key->id;
                $absen['id_siswa'] = $key->id;
                $absen['tagihan_bulan'] = Carbon::now()->format('Y-m-d');
                $absen['status'] = 'Belum Lunas';
                $absen['created_by'] = Auth::user()->id;
                $absen['updated_by'] = Auth::user()->id;
                $absen['created_at'] = $now;
                $absen['updated_at'] = $now;
                PembayaranSPP::create($absen);
            }
            $guru = Guru::where('status', 'Active')->get();
            foreach ($guru as $key) {
                $now = Carbon::now();
                $absen['no_invoice'] = '#FEE' . $now->year . '' . $now->month . '' . $key->id;
                $absen['id_guru'] = $key->id;
                $absen['jumlah'] = 0;
                $absen['tagihan_bulan'] = $now->format('Y-m-d');
                $absen['status'] = 'Belum Lunas';
                $absen['created_by'] = Auth::user()->id;
                $absen['updated_by'] = Auth::user()->id;
                $absen['created_at'] = $now;
                $absen['updated_at'] = $now;
                PembayaranFEE::create($absen);
            }
            $this->info('Tagihan SPP berhasil digenerate');
        } catch (\Throwable $th) {
            // $this->info('Tagihan SPP berhasil digenerate');
            return $th;
            return response()->json([
                'status_code' => 401,
                'message' => $th,
            ]);
        }
    }
}
