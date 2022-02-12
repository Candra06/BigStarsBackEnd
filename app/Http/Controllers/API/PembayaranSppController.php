<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Kelas;
use App\Mengajar;
use App\PembayaranFEE;
use App\PembayaranSPP;
use App\Referal;
use App\Siswa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PembayaranSppController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function indexFinance($bulan)
    {

        try {
            $spp = PembayaranSPP::whereMonth('tagihan_bulan',  date('m', strtotime($bulan)))->where('status', 'Lunas')->get();
            $totalSpp = 0;
            foreach ($spp as $key) {
                $totalSpp += $key->jumlah;
            }
            $fee = PembayaranFEE::whereMonth('tagihan_bulan', date('m', strtotime($bulan)))->where('status', 'Lunas')->get();
            $totalFee = 0;
            // return $fee;
            foreach ($fee as $key) {
                $totalFee += $key->jumlah;
            }
            $data['total_spp'] = $totalSpp;
            $data['total_fee'] = $totalFee;
            $data['laba'] = $totalSpp - $totalFee;
            return response()->json([
                'status_code' => 200,
                'message' => 'Success',
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            return $th;
            return response()->json([
                'status_code' => 401,
                'message' => $th,
            ]);
        }
    }
    public function index(Request $request)
    {
        try {
            DB::enableQueryLog();
            $query = [];
            $role = Auth::user()->role;
            if ($role == 'Walimurid') {
                $idSiswa = Siswa::where('id_wali', Auth::user()->id)->get();
                foreach ($idSiswa as $id) {
                    $query = PembayaranSPP::leftJoin('siswa', 'siswa.id', 'pembayaran_spp.id_siswa')
                        ->select('siswa.id as id_siswa', 'siswa.nama', 'pembayaran_spp.*')
                        ->where('siswa.id', $id->id);
                }
            } else {
                $query = PembayaranSPP::leftJoin('siswa', 'siswa.id', 'pembayaran_spp.id_siswa')
                    ->select('siswa.id as id_siswa', 'siswa.nama', 'pembayaran_spp.*');
            }

            if ($request->bulan) {
                $query = $query->whereMonth('pembayaran_spp.tagihan_bulan',  $request->bulan);
            }

            if ($request->tahun) {
                $query = $query->whereYear('pembayaran_spp.tagihan_bulan',  $request->tahun);
            }

            if ($request->nama) {
                $query = $query->where('siswa.nama',  'like', '%' . $request->nama . '%');
            }

            $data = $query->get();
            $q = DB::getQueryLog();
            // return $data;
            return response()->json([
                'status_code' => 200,
                'message' => 'Success',
                // 'query' => $q,
                'data' => $data,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status_code' => 401,
                'message' => $th,
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
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
                    ->select('siswa.id')
                    ->groupBy('id')
                    ->get();
                // return $idSiswa;
                $tmpTotalSpp = [];
                for ($i = 0; $i < $m; $i++) {
                    array_push($tmpTotalSpp, 0);
                }
                $a = 0;
                for ($i = 0; $i < count($dt); $i++) {
                    if ($i > 0) {
                        if ($dt[$i]['id'] == $dt[$i - 1]['id']) {
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
                    }
                }
                // return $m;
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
                    $absen['no_invoice'] = '#SPP' . $now->year . '' . $now->month . '' . $idSiswa[$i]->id;
                    $absen['id_siswa'] = $idSiswa[$i]->id;
                    $absen['tagihan_bulan'] = $now->format('Y-m-d');
                    $absen['status'] = 'Belum Lunas';
                    $absen['created_by'] = Auth::user()->id;
                    $absen['updated_by'] = Auth::user()->id;
                    $absen['created_at'] = $now;
                    $absen['updated_at'] = $now;
                    PembayaranSPP::create($absen);
                }

                return response()->json([
                    'status_code' => 200,
                    'message' => 'Success',
                ]);
            }
        } catch (\Throwable $th) {
            return $th;
            return response()->json([
                'status_code' => 401,
                'message' => $th,
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    public function filter($nama, $bulan, $status)
    {
        try {
            $data = [];
            $role = Auth::user()->role;
            if ($role == 'Walimurid') {
                $idSiswa = Siswa::where('id_wali', Auth::user()->id)->get();
                foreach ($idSiswa as $id) {
                    $data = PembayaranSPP::leftJoin('siswa', 'siswa.id', 'pembayaran_spp.id_siswa')
                        ->select('siswa.id as id_siswa', 'siswa.nama', 'pembayaran_spp.*')
                        ->where('siswa.id', $id->id)
                        ->whereMonth('pembayaran_spp.tagihan_bulan', date('m', strtotime($bulan)))
                        ->orWhere('pembayaran_spp.status', 'like', '%' . $status . '%')
                        ->get();
                }
            } else {
                $data = PembayaranSPP::leftJoin('siswa', 'siswa.id', 'pembayaran_spp.id_siswa')
                    ->select('siswa.id as id_siswa', 'siswa.nama', 'pembayaran_spp.*')
                    ->where('siswa.nama', 'like', '%' . $nama . '%')
                    ->orWhereMonth('pembayaran_spp.tagihan_bulan', date('m', strtotime($bulan)))
                    ->orWhere('pembayaran_spp.status', $status)
                    ->get();
            }
            return response()->json([
                'status_code' => 200,
                'message' => 'Success',
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status_code' => 401,
                'message' => $th,
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $detail = PembayaranSPP::leftJoin('siswa', 'siswa.id', 'pembayaran_spp.id_siswa')
                ->select('siswa.nama', 'pembayaran_spp.*')
                ->where('pembayaran_spp.id', $id)
                ->first();
            // return $detail;
            $kelas = Kelas::where('id_siswa', $detail->id_siswa)->get();
            $total = 0;
            foreach ($kelas as $key) {
                $mengajar = Mengajar::where('id_kelas', $key->id)->count();
                $total += $mengajar;
            }
            $list = [];
            $month = explode('-', $detail->tagihan_bulan);
            $bulan = intval($month[1]) - 1;
            $year = intval($month[0]) - 1;
            $b = $bulan == 0 ? 12 : $bulan;
            $y = $b == 12 ? $year : $month[0];

            $list = Mengajar::leftJoin('guru', 'guru.id', 'mengajar.id_guru')
                ->leftJoin('kelas', 'kelas.id', 'mengajar.id_kelas')
                ->leftJoin('mapel', 'mapel.id', 'kelas.id_mapel')
                ->select('mengajar.*', 'mapel.mapel', 'guru.nama')
                ->where('mengajar.created_at', 'LIKE', '%' . $y . '-' . $b . '%')->get();

            $data['no_invoice'] = $detail->no_invoice;
            $data['nama'] = $detail->nama;
            $data['tagihan_bulan'] = $detail->tagihan_bulan;
            $data['jumlah'] = $detail->jumlah;
            $data['status'] = $detail->status;
            $data['total_pertemuan'] = $total;
            $data['created_at'] = $detail->created_at;
            $data['udpated_at'] = $detail->udpated_at;
            $data['histori_kehadiran'] = $list;
            return response()->json([
                'status_code' => 200,
                'message' => 'Success',
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status_code' => 401,
                'message' => $th,
            ]);
        }

        return $data;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            PembayaranSPP::where('id', $id)->update([
                'updated_by' => Auth::user()->id,
                'updated_at' => Carbon::now(),
                'status' => 'Lunas'
            ]);
            return response()->json([
                'status_code' => 200,
                'message' => 'Success',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status_code' => 401,
                'message' => $th,
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
