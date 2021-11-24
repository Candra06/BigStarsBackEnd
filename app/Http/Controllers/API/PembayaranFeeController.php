<?php

namespace App\Http\Controllers\API;

use App\Guru;
use App\Http\Controllers\Controller;
use App\Mengajar;
use App\PembayaranFEE;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PembayaranFeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $data = [];
            $role = Auth::user()->role;
            if ($role == 'Guru') {
                $idGuru = Guru::where('id_users', Auth::user()->id)->first();
                $data = PembayaranFEE::leftJoin('guru', 'guru.id', 'pembayaran_fee.id_guru')
                    ->select('guru.id as id_guru', 'guru.nama', 'pembayaran_fee.*')
                    ->where('guru.id', $idGuru->id)
                    ->get();
            } else {
                $data = PembayaranFEE::leftJoin('guru', 'guru.id', 'pembayaran_fee.id_guru')
                    ->select('guru.id as id_guru', 'guru.nama', 'pembayaran_fee.*')
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
                foreach ($guru as $g) {
                    $dt = Mengajar::where('id_guru', $g->id)->get();
                    foreach ($dt as $key) {
                        $total += $key->fee_pengajar;
                    }
                    $now = Carbon::now();
                    $absen['no_invoice'] = '#FEE' . $now->year . '' . $now->month . '' . $g->id;
                    $absen['id_guru'] = $g->id;
                    $absen['jumlah'] = $total;
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
                    'message' => 'Success'
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
                    ->orWhere('pembayaran_fee.status',$status )
                    ->get();
            } else {
                $data = PembayaranFEE::leftJoin('guru', 'guru.id', 'pembayaran_fee.id_guru')
                    ->select('guru.id as id_guru', 'guru.nama', 'pembayaran_fee.*')
                    ->where('guru.nama', 'like', '%' . $nama . '%')
                    ->orWhere('pembayaran_fee.tagihan_bulan', 'like', '%' . $bulan . '%')
                    ->orWhere('pembayaran_fee.status',  $status )
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
                'updated_by'=>Auth::user()->id,
                'updated_at'=>Carbon::now(),
                'status'=>'Lunas'
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
