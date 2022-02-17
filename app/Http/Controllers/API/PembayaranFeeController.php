<?php

namespace App\Http\Controllers\API;

use App\Guru;
use App\Http\Controllers\Controller;
use App\Mengajar;
use App\PembayaranFEE;
use App\PembayaranSPP;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PembayaranFeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $query = [];
            $role = Auth::user()->role;

            if ($role == 'Guru') {
                $idGuru = Guru::where('id_users', Auth::user()->id)->first();
                $query = PembayaranFEE::leftJoin('guru', 'guru.id', 'pembayaran_fee.id_guru')
                    ->select('guru.id as id_guru', 'guru.nama', 'pembayaran_fee.*')
                    ->where('guru.id', $idGuru->id);
                // ->get();
            } else {
                $query = PembayaranFEE::leftJoin('guru', 'guru.id', 'pembayaran_fee.id_guru')
                    ->select('guru.id as id_guru', 'guru.nama', 'pembayaran_fee.*');
                // ->get();
            }
            if ($request->bulan) {
                $query = $query->whereMonth('pembayaran_fee.tagihan_bulan',  $request->bulan);
            }
            if ($request->tahun) {
                $query = $query->whereYear('pembayaran_fee.tagihan_bulan',  $request->tahun);
            }
            if ($request->nama) {
                $query = $query->where('guru.nama',  'like', '%' . $request->nama . '%');
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
            $cek = PembayaranFEE::whereMonth('tagihan_bulan', Carbon::now()->format('m'))->first();

            if ($cek) {
                return response()->json([
                    'status_code' => 400,
                    'message' => 'Tagihan sudah ada'
                ]);
            } else {
                $guru = Guru::all();
                $absen = [];
                $total = 0;
                $dt = Mengajar::whereMonth('created_at', Carbon::now()->subMonth()->month)
                    ->select('id_guru', 'fee_pengajar')
                    ->orderBy('id_guru')
                    ->get();
                $m = Mengajar::whereMonth('created_at', Carbon::now()->subMonth()->month)
                    ->select('id_guru', 'fee_pengajar')
                    ->orderBy('id_guru')
                    ->distinct()
                    ->count('id_guru');
                $idGuru = Mengajar::whereMonth('created_at', Carbon::now()->subMonth()->month)
                    ->select('id_guru')
                    ->orderBy('id_guru')
                    ->groupBy('id_guru')
                    ->get();
                // return $idGuru;
                $tmpTotalFee = [];
                for ($i = 0; $i < $m; $i++) {
                    array_push($tmpTotalFee, 0);
                }
                $a = 0;

                for ($i = 0; $i < count($dt); $i++) {
                    if ($i > 0) {
                        if ($dt[$i]['id_guru'] == $dt[$i - 1]['id_guru']) {
                            $total += $dt[$i]['fee_pengajar'];
                            if ($i == count($dt) - 1) {
                                $tmpTotalFee[$a] = $total;
                            }
                        } else {
                            $tmpTotalFee[$a] = $total;
                            $total = $dt[$i]['fee_pengajar'];
                            $a++;
                            if ($i == count($dt) - 1) {
                                $tmpTotalFee[$a] = $total;
                            }
                        }
                    } else {
                        $total = $dt[$i]['fee_pengajar'];
                    }
                }
                // return $tmpTotalFee; value fee pengajar

                for ($i = 0; $i < $m; $i++) {

                    $now = Carbon::now();
                    $absen['no_invoice'] = '#FEE' . $now->year . '' . $now->month . '' . $idGuru[$i]->id_guru;
                    $absen['id_guru'] = $idGuru[$i]->id_guru;
                    $absen['jumlah'] = $tmpTotalFee[$i];
                    $absen['tagihan_bulan'] = $now->format('Y-m-d');
                    $absen['status'] = 'Belum Lunas';
                    $absen['created_by'] = Auth::user()->id;
                    $absen['updated_by'] = Auth::user()->id;
                    $absen['created_at'] = $now;
                    $absen['updated_at'] = $now;
                    PembayaranFEE::create($absen);
                }

                return response()->json([
                    'status_code' => 200,
                    'message' => 'Success',
                    'data' => $absen
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

    public function report(Request $request)
    {
        try {
            DB::enableQueryLog();
            $data = [];
            $querySpp = PembayaranSPP::leftJoin('siswa', 'siswa.id', 'pembayaran_spp.id_siswa')
                ->select('siswa.nama', 'pembayaran_spp.*');

            $queryFee = PembayaranFEE::leftJoin('guru', 'guru.id', 'pembayaran_fee.id_guru')
                ->select('guru.nama', 'pembayaran_fee.*');
            if ($request->bulan) {
                $queryFee = $queryFee->whereMonth('pembayaran_fee.tagihan_bulan',  $request->bulan);
                $$querySpp = $$querySpp->whereMonth('pembayaran_spp.tagihan_bulan',  $request->bulan);
            }
            if ($request->tahun) {
                $queryFee = $queryFee->whereYear('pembayaran_fee.tagihan_bulan',  $request->tahun);
                $querySpp = $querySpp->whereYear('pembayaran_spp.tagihan_bulan',  $request->tahun);
            }
            if ($request->nama) {
                $queryFee = $queryFee->where('guru.nama',  'like', '%' . $request->nama . '%');
                $querySpp = $querySpp->where('siswa.nama',  'like', '%' . $request->nama . '%');
            }
            $spp = $querySpp->get();
            $fee = $queryFee->get();

            return $q;
            foreach ($spp as $s) {
                $tmp['id'] = $s->id;
                $tmp['tipe'] = 'SPP';
                $tmp['invoice'] = $s->no_invoice;
                $tmp['nama'] = $s->nama;
                $tmp['status'] = $s->status;
                $tmp['tagihan_bulan'] = $s->tagihan_bulan;
                $tmp['nominal'] = $s->jumlah;
                $tmp['created_at'] = $s->created_at;
                $tmp['updated_at'] = $s->updated_at;
                array_push($data, $tmp);
            }
            foreach ($fee as $f) {
                $tmp['id'] = $f->id;
                $tmp['tipe'] = 'FEE';
                $tmp['invoice'] = $f->no_invoice;
                $tmp['nama'] = $f->nama;
                $tmp['status'] = $f->status;
                $tmp['tagihan_bulan'] = $f->tagihan_bulan;
                $tmp['nominal'] = $f->jumlah;
                $tmp['created_at'] = $f->created_at;
                $tmp['updated_at'] = $f->updated_at;
                array_push($data, $tmp);
            }
            $tmpData = collect($data);
            $sorted = $tmpData->sortBy('created_at', SORT_REGULAR, false);
            $dt = [];
            // return $sorted;
            foreach ($sorted as $key => $value) {
                // $tmp['key'] = $value;
                array_push($dt, $value);
            }
            // return $dt;
            $q = DB::getQueryLog();
            return $q;
            return response()->json([
                'status_code' => 200,
                'data' => $dt
            ]);
        } catch (\Throwable $th) {
            //throw $th;
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

    public function filter($nama, $status, $bulan)
    {
        try {
            $data = [];
            $role = Auth::user()->role;
            if ($role == 'Guru') {
                $idGuru = Guru::where('id_users', Auth::user()->id)->first();
                $data = PembayaranFEE::leftJoin('guru', 'guru.id', 'pembayaran_fee.id_guru')
                    ->select('guru.id as id_guru', 'guru.nama', 'pembayaran_fee.*')
                    ->where('guru.id', $idGuru->id)
                    ->orWhere('pembayaran_fee.tagihan_bulan', 'like', '%' . $bulan . '%')
                    ->orWhere('pembayaran_fee.status', $status)
                    ->get();
            } else {
                $data = PembayaranFEE::leftJoin('guru', 'guru.id', 'pembayaran_fee.id_guru')
                    ->select('guru.id as id_guru', 'guru.nama', 'pembayaran_fee.*')
                    ->where('guru.nama', 'like', '%' . $nama . '%')
                    ->orWhere('pembayaran_fee.tagihan_bulan', 'like', '%' . $bulan . '%')
                    ->orWhere('pembayaran_fee.status',  $status)
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
            $detail = PembayaranFEE::leftJoin('guru', 'guru.id', 'pembayaran_fee.id_guru')
                ->select('guru.nama', 'pembayaran_fee.*')->where('pembayaran_fee.id', $id)->first();
            $total = Mengajar::where('id_guru', $detail->id_guru)->count();

            $data['no_invoice'] = $detail->no_invoice;
            $data['nama'] = $detail->nama;
            $data['fee_bulan'] = $detail->tagihan_bulan;
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
            PembayaranFEE::where('id', $id)->update([
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
