<?php

namespace App\Http\Controllers\API;

use App\Guru;
use App\Http\Controllers\Controller;
use App\Kelas;
use App\Mengajar;
use App\PembayaranFEE;
use App\PembayaranSPP;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MengajarController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function sharingKelas(Request $request, $idKelas)
    {
        try {

            $kelas = Kelas::where('id', $idKelas)->first();
            $mengajar['id_guru'] = $request->id_guru;
            $mengajar['id_kelas'] = $kelas->id;
            $mengajar['tipe'] = 'Pengganti';
            $mengajar['spp'] = $kelas->spp;
            $mengajar['fee_pengajar'] = $kelas->fee_guru;
            $mengajar['poin_siswa'] = 0;
            $mengajar['status'] = 'Waiting';
            $mengajar['file_materi'] = '-';
            $mengajar['materi'] = '-';
            $mengajar['jurnal'] = '-';
            $mengajar['latitude'] = $request->latitude;
            $mengajar['longitude'] = $request->longitude;
            $mengajar['created_at'] = Carbon::now();
            $mengajar['updated_at'] = Carbon::now();
            Mengajar::create($mengajar);
            return response()->json([
                'status_code' => 200,
                'message' => 'Success'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status_code' => 401,
                'message' => $th,
            ]);
        }
    }

    public function updateKehadiranKelas(Request $request, $id)
    {
        $request->validate([
            'materi' => 'required',
            'jurnal' => 'required',
            'status' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        ]);
        try {
            $mengajar = [];
            $fee = [];
            $spp = [];

            if ($request->status == 'Cancel') {
                $data = Mengajar::where('id', $id)->first();
                $spp = $data->spp / 2;
                $fee = 10000;
                $mengajar['spp'] = $spp;
                $mengajar['fee_pengajar'] = $fee;
                $mengajar['status'] = $request->status;
                $mengajar['latitude'] = $request->latitude;
                $mengajar['longitude'] = $request->longitude;
                $mengajar['file_materi'] = '-';
            } else {
                // return $request->file_materi
                if ($request->file_materi) {
                    if ($request->file_materi == '-') {
                        $mengajar['file_materi'] = '-';
                    } else {
                        $name = str_replace(" ", "_", $request->file_materi->getClientOriginalName());
                        $mengajar['file_materi'] = Storage::putFileAs('materi', $request->file('file_materi'), $name);
                    }
                } else {
                    $mengajar['file_materi'] = '-';
                }
                $mengajar['poin_siswa'] = $request->poin;
                $mengajar['status'] = $request->status;
                $mengajar['materi'] = $request->materi;
                $mengajar['jurnal'] = $request->jurnal;
                $mengajar['latitude'] = $request->latitude;
                $mengajar['longitude'] = $request->longitude;
            }
            $mengajar['updated_at'] = Carbon::now();


            $result = Mengajar::where('id', $id)->update($mengajar);

            $idSiswa = Kelas::where('id', $result->id_kelas)->first();
            $tmpSpp =  PembayaranSPP::whereMonth('tagihan_bulan', Carbon::now()->format('m'))->where('id_siswa', $idSiswa->id_siswa)->first();
            $tmpFee =  PembayaranFEE::whereMonth('tagihan_bulan', Carbon::now()->format('m'))->where('id_guru', $result->id_guru)->first();
            // return $tmpFee;
            $upSpp = (int)$tmpSpp->jumlah + (int)$result->spp;
            $upFee = (int)$tmpFee->jumlah + (int)$result->fee_pengajar;
            PembayaranSPP::where('id', $tmpSpp->id)->update(['jumlah'=> $upSpp]);
            PembayaranFEE::where('id', $tmpSpp->id)->update(['jumlah'=> $upFee]);

            return response()->json([
                'status_code' => 200,
                'message' => 'Success'
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
    }

    public function absensi($id)
    {
        try {
            $data = Mengajar::leftJoin('kelas', 'kelas.id', 'mengajar.id_kelas')
                ->leftJoin('siswa', 'siswa.id', 'kelas.id_siswa')
                ->select('mengajar.*', 'siswa.nama')
                ->where('mengajar.id_guru', $id)
                ->orderBy('mengajar.created_at', 'DESC')
                ->get();
            return response()->json([
                'status_code' => 200,
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $idKelas)
    {
        $request->validate([
            'materi' => 'required',
            'jurnal' => 'required',
            'status' => 'required',
            'poin' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        ]);
        try {
            $idGuru = Guru::where('id_users', Auth::user()->id)->first();

            $mengajar = [];
            $data = Kelas::where('id', $idKelas)->first();
            $mengajar['id_guru'] = $idGuru->id;
            $mengajar['id_kelas'] = $data->id;
            $mengajar['tipe'] = 'Asli';
            if ($request->status == 'Cancel') {
                $spp = $data->spp / 2;
                $fee = 10000;
                $mengajar['spp'] = $spp;
                $mengajar['fee_pengajar'] = $fee;
                $mengajar['status'] = $request->status;

                $mengajar['latitude'] = '-8.2074597';
                $mengajar['longitude'] = '113.697264';
                $mengajar['materi'] = '-';
                $mengajar['jurnal'] = '-';
            } else {
                if ($request->file_materi != '-') {
                    $name = str_replace(" ", "_", $request->file_materi->getClientOriginalName());
                    $mengajar['file_materi'] = Storage::putFileAs('materi', $request->file('file_materi'), $name);
                } else {
                    $mengajar['file_materi'] = '-';
                }
                $mengajar['spp'] = $data->spp;
                $mengajar['fee_pengajar'] = $data->fee_guru;
                $mengajar['poin_siswa'] = $request->poin;
                $mengajar['status'] = $request->status;
                $mengajar['materi'] = $request->materi;
                $mengajar['jurnal'] = $request->jurnal;
                $mengajar['status'] =  $request->status;
                $mengajar['latitude'] = $request->latitude;
                $mengajar['longitude'] = $request->longitude;
            }
            $mengajar['created_at'] = Carbon::now();
            $mengajar['updated_at'] = Carbon::now();


            $result=Mengajar::create($mengajar);
            // return $result;
            $idSiswa = Kelas::where('id', $result->id_kelas)->first();
            $tmpSpp =  PembayaranSPP::whereMonth('tagihan_bulan', Carbon::now()->format('m'))->where('id_siswa', $idSiswa->id_siswa)->first();
            $tmpFee =  PembayaranFEE::whereMonth('tagihan_bulan', Carbon::now()->format('m'))->where('id_guru', $result->id_guru)->first();
            // return $tmpFee;
            $upSpp = (int)$tmpSpp->jumlah + (int)$result->spp;
            $upFee = (int)$tmpFee->jumlah + (int)$result->fee_pengajar;
            PembayaranSPP::where('id', $tmpSpp->id)->update(['jumlah'=> $upSpp]);
            PembayaranFEE::where('id', $tmpSpp->id)->update(['jumlah'=> $upFee]);

            return response()->json([
                'status_code' => 200,
                'message' => 'Success'
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
            $kelas = Kelas::where('id', $id)->first();
            $mengajar['id_guru'] = $kelas->id_guru;
            $mengajar['id_kelas'] = $kelas->id;
            $mengajar['tipe'] = 'Asli';
            $mengajar['spp'] = $kelas->spp;
            $mengajar['fee_pengajar'] = $kelas->fee_guru;
            $mengajar['poin_siswa'] = 0;
            $mengajar['status'] = 'Done';
            $mengajar['file_materi'] = '-';
            $mengajar['materi'] = $request->materi;
            $mengajar['jurnal'] = $request->jurnal;
            $mengajar['latitude'] = '-8.2074597';
            $mengajar['longitude'] = '113.697264';
            $mengajar['created_at'] = $request->tglKelas;
            $mengajar['updated_at'] = $request->tglKelas;
            $result = Mengajar::create($mengajar);
            $idSiswa = Kelas::where('id', $result->id_kelas)->first();
            $tmpSpp =  PembayaranSPP::whereMonth('tagihan_bulan', Carbon::now()->format('m'))->where('id_siswa', $idSiswa->id_siswa)->first();
            $tmpFee =  PembayaranFEE::whereMonth('tagihan_bulan', Carbon::now()->format('m'))->where('id_guru', $result->id_guru)->first();
            // return $tmpFee;
            $upSpp = (int)$tmpSpp->jumlah + (int)$result->spp;
            $upFee = (int)$tmpFee->jumlah + (int)$result->fee_pengajar;
            PembayaranSPP::where('id', $tmpSpp->id)->update(['jumlah'=> $upSpp]);
            PembayaranFEE::where('id', $tmpSpp->id)->update(['jumlah'=> $upFee]);
            return response()->json([
                'status_code' => 200,
                'message' => 'Success'
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
