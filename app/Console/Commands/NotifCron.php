<?php

namespace App\Console\Commands;

use App\Guru;
use App\Notifikasi;
use App\NotifikasiDetail;
use App\Siswa;
use App\User;
use App\Walimurid;
use Carbon\Carbon;
use Illuminate\Console\Command;

class NotifCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notif:cron';

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
        $siswa = Siswa::whereMonth('birth_date', Carbon::now()->format('m'))->whereDay('birth_date', Carbon::now()->format('d'))->get();
        $guru = Guru::whereMonth('birth_date', Carbon::now()->format('m'))->whereDay('birth_date', Carbon::now()->format('d'))->get();
        // return $siswa;
        foreach ($siswa as $s) {
            // return $s->birth_date;
            // return Carbon::now()->format('Y-m-d');
            // if ($s->birth_date == Carbon::now()->format('Y-m-d')) {
            $user = User::where('role', '!=', 'Walimurid')->get();
            $notifAll = Notifikasi::create([
                'judul' => 'Hari ulang tahun ' . $s->nama,
                'konten' => 'Hari ini adalah hari ulang tahun ' . $s->nama . '. Ucapkan selamat ' . $s->nama,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            foreach ($user as $u) {
                NotifikasiDetail::create([
                    'id_notif' => $notifAll->id,
                    'id_penerima' => $u->id,
                    'status' => 'Unread',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
            $wali = Walimurid::where('id', $s->id_wali)->first();
            $notifWali = Notifikasi::create([
                'judul' => 'Selamat ulang tahun ' . $s->nama,
                'konten' => 'Selamat ulang tahun ' . $s->nama . '. Semoga sehat selalu dan semua yang diinginkan dapat tercapai.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            NotifikasiDetail::create([
                'id_notif' => $notifWali->id,
                'id_penerima' => $wali->id_users,
                'status' => 'Unread',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            // }
        }
        foreach ($guru as $g) {
            // if ($s->birth_date == Carbon::now()->format('Y-m-d')) {
            $user = User::where('role', '!=', 'Walimurid')->get();
            $notifAll = Notifikasi::create([
                'judul' => 'Hari ulang tahun ' . $g->nama,
                'konten' => 'Hari ini adalah hari ulang tahun ' . $g->nama . '. Ucapkan selamat ' . $g->nama,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            foreach ($user as $u) {
                if ($u->id == $g->id_users) {
                    $not = Notifikasi::create([
                        'judul' => 'Selamat ulang tahun ' . $u->nama,
                        'konten' => 'Selamat ulang tahun ' . $u->nama . '. Semoga sehat selalu dan semua yang diinginkan dapat tercapai.',
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                    NotifikasiDetail::create([
                        'id_notif' => $not->id,
                        'id_penerima' => $u->id_users,
                        'status' => 'Unread',
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                } else {
                    NotifikasiDetail::create([
                        'id_notif' => $notifAll->id,
                        'id_penerima' => $u->id,
                        'status' => 'Unread',
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }
            }
            // }
        }
        $this->info('Cron success');
        return 0;
    }
}
