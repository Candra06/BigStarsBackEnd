<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Kelas;
use App\Mengajar;
use App\PembayaranFEE;
use App\PembayaranSPP;
use App\Siswa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
                $siswa = Siswa::all();
                $absen = [];
                $total = 0;
                foreach ($siswa as $g) {
                    $dt = Mengajar::leftJoin('kelas', 'kelas.id', 'mengajar.id_kelas')
                        ->leftJoin('siswa', 'siswa.id', 'kelas.id_siswa')
                        ->where('siswa.id', $g->id)->get();
                    foreach ($dt as $key) {
                        $total += $key->spp;
                    }
                    $now = Carbon::now();
                    $absen['no_invoice'] = '#SPP' . $now->year . '' . $now->month . '' . $g->id;
                    $absen['id_siswa'] = $g->id;
                    $absen['jumlah'] = $total;
                    $absen['tagihan_bulan'] = $now->format('Y-m-d');
                    $absen['status'] = 'Belum Lunas';
                    $absen['created_by'] = Auth::user()->id;
                    $absen['updated_by'] = Auth::user()->id;
                    $absen['created_at'] = $now;
                    $absen['updated_at'] = $now;
                    PembayaranSPP::create($absen);
                    // return $absen;
                }
                return response()->json([
                    'status_code' => 200,
                    'message' => 'Success'
                    // 'data' => $g
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
            $kelas = Kelas::where('id_siswa', $detail->id_siswa)->get();
            $total = 0;
            foreach ($kelas as $key) {
                $mengajar = Mengajar::where('id_kelas', $key->id)->count();
                $total += $mengajar;
            }
            $data['no_invoice'] = $detail->no_invoice;
            $data['nama'] = $detail->nama;
            $data['tagihan_bulan'] = $detail->tagihan_bulan;
            $data['jumlah'] = $detail->jumlah;
            $data['status'] = $detail->status;
            $data['total_pertemuan'] = $total;
            $data['created_at'] = $detail->created_at;
            $data['udpated_at'] = $detail->udpated_at;
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
