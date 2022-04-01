<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Kelas;
use App\Mengajar;
use App\PembayaranFEE;
use App\PembayaranSPP;
use App\Referal;
use App\Siswa;
use App\Walimurid;
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

    public function indexFinance(Request $request)
    {

        try {
            $querySpp = PembayaranSPP::where('status', 'Lunas');
            $queryFee = PembayaranFEE::where('status', 'Lunas');
            if ($request->bulan) {
                $querySpp = $querySpp->whereMonth('tagihan_bulan', $request->bulan);
                $queryFee = $queryFee->whereMonth('tagihan_bulan', $request->bulan);
            }
            if ($request->tahun) {
                $querySpp = $querySpp->whereYear('tagihan_bulan', $request->tahun);
                $queryFee = $queryFee->whereYear('tagihan_bulan', $request->tahun);
            }
            $spp = $querySpp->get();
            $totalSpp = 0;
            foreach ($spp as $key) {
                $totalSpp += $key->jumlah;
            }
            $fee = $queryFee->get();
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
            // return $role;
            if ($role == 'Walimurid') {
                $idWali = Walimurid::where('id_users', Auth::user()->id)->first();
                $idSiswa = Siswa::where('id_wali', $idWali->id)->get();
                // return $idSiswa;
                $result = [];
                foreach ($idSiswa as $id) {
                    $query = PembayaranSPP::select('*')
                        ->where('id_siswa', $id->id);
                    if ($request->bulan) {
                        $query = $query->whereMonth('pembayaran_spp.tagihan_bulan',  $request->bulan);
                    }

                    if ($request->tahun) {
                        $query = $query->whereYear('pembayaran_spp.tagihan_bulan',  $request->tahun);
                    }
                    $data = $query->get();
                    // return $data;
                    foreach ($data as $dt) {
                        $tmp['id_siswa'] = $dt->id_siswa;
                        $tmp['nama'] = $id->nama;
                        $tmp['id'] = $dt->id;
                        $tmp['no_invoice'] = $dt->no_invoice;
                        $tmp['tagihan_bulan'] = $dt->tagihan_bulan;
                        $tmp['jumlah'] = $dt->jumlah;
                        $tmp['status'] = $dt->status;
                        $tmp['keterangan'] = $dt->keterangan;
                        $tmp['created_by'] = $dt->created_by;
                        $tmp['updated_by'] = $dt->updated_by;
                        $tmp['created_at'] = $dt->created_at;
                        $tmp['updated_at'] = $dt->updated_at;
                        array_push($result, $tmp);
                    }
                }
                return response()->json([
                    'status_code' => 200,
                    'message' => 'Success',
                    // 'query' => $q,
                    'data' => $result,
                ]);
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

            return response()->json([
                'status_code' => 200,
                'message' => 'Success',
                // 'query' => $q,
                'data' => $data,
            ]);
        } catch (\Throwable $th) {
            return $th;
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
                $absen['created_by'] = 1;
                $absen['updated_by'] = 1;
                $absen['created_at'] = $now;
                $absen['updated_at'] = $now;
                PembayaranSPP::create($absen);
            }

            return response()->json([
                'status_code' => 200,
                'message' => 'Success',
            ]);
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
            $month = explode('-', $detail->tagihan_bulan);
            $bulan = intval($month[1]);
            $year = intval($month[0]);
            $b = $bulan == 0 ? 12 : $bulan;
            $y = $b == 12 ? $year : $month[0];

            if ($b  < 10) {
                $b = '0' . $b;
            } else {
                $b = $b;
            }
            // return $y;
            $kelas = Kelas::where('id_siswa', $detail->id_siswa)->get();
            $total = 0;
            foreach ($kelas as $key) {
                $mengajar = Mengajar::where('id_kelas', $key->id)
                    ->whereMonth('created_at', $b)
                    ->whereYear('created_at', $y)
                    ->count();
                $total += $mengajar;
            }
            $list = [];
            // return $total;
            // return $y . '-' . $b;
            $list = Mengajar::leftJoin('guru', 'guru.id', 'mengajar.id_guru')
                ->leftJoin('kelas', 'kelas.id', 'mengajar.id_kelas')
                ->leftJoin('mapel', 'mapel.id', 'kelas.id_mapel')
                ->select('mengajar.*', 'mapel.mapel', 'guru.nama')
                ->where('kelas.id_siswa', $detail->id_siswa)
                ->whereMonth('mengajar.created_at', $b)
                ->whereYear('mengajar.created_at', $y)
                ->get();
            // return $list;
            $data['no_invoice'] = $detail->no_invoice;
            $data['nama'] = $detail->nama;
            $data['tagihan_bulan'] = $detail->tagihan_bulan;
            $data['jumlah'] = $detail->jumlah;
            $data['status'] = $detail->status;
            $data['total_pertemuan'] = $total;
            $data['keterangan'] = $detail->keterangan;
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
