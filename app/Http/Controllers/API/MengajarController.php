<?php

namespace App\Http\Controllers\API;

use App\Guru;
use App\Http\Controllers\Controller;
use App\Kelas;
use App\Mengajar;
use App\PembayaranFEE;
use App\PembayaranSPP;
use App\Referal;
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
            $mengajar['latitude'] = '-8.2074597';
            $mengajar['longitude'] = '113.697264';
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
            $date = Carbon::now();
            //Get date and time
            $date->toDateTimeString();
            $newDateTime = date('G:i', strtotime($date->toDateTimeString()));
            // cek apakah jam melebihi jam 9 malamm
            if (strtotime($newDateTime) >= strtotime("21:00")) {
                return response()->json([
                    'status_code' => 402,
                    'message' => 'Waktu absensi sudah ditutup, silahkan menghubungi admin',
                ], 402);
            } else {
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


                Mengajar::where('id', $id)->update($mengajar);
                $result = Mengajar::where('id', $id)->first();
                // return $result;
                $idSiswa = Kelas::where('id', $result->id_kelas)->first();
                $tmpSpp =  PembayaranSPP::whereMonth('tagihan_bulan', Carbon::now()->format('m'))->where('id_siswa', $idSiswa->id_siswa)->first();
                $tmpFee =  PembayaranFEE::whereMonth('tagihan_bulan', Carbon::now()->format('m'))->where('id_guru', $result->id_guru)->first();
                // return $tmpSpp;
                //cek ada atau belum tagihan spp pada bulan x dan id siswa x
                if ($tmpSpp) { //jika ada
                    // return 'sini';
                    $reff = Referal::where('reff_id',  $idSiswa->id_siswa)->where('status', 'Aktif')->count();
                    $jumlahSpp =  (int)$result->spp;
                    // percabangan ketika ada data atau tidak
                    if ($reff > 0) {
                        $jumlahSpp = (int)$result->spp * ($reff * 10) / 100;
                    } else {
                        $jumlahSpp = (int)$result->spp;
                    }
                    // menghitung jumlah tagihan
                    $upSpp = (int)$tmpSpp->jumlah + $jumlahSpp;
                    PembayaranSPP::where('id', $tmpSpp->id)->update(['jumlah' => $upSpp, 'keterangan' => 'Potongan ' . $reff . '0% dari mengundang ' . $reff . ' teman']);
                } else { //jika tidak ada maka buat tagihan baru
                    $reff = Referal::where('reff_id',  $idSiswa->id_siswa)->where('status', 'Aktif')->count();
                    $jumlahSpp =  (int)$result->spp;
                    // percabangan ketika ada data atau tidak
                    if ($reff > 0) {
                        $jumlahSpp = (int)$result->spp * ($reff * 10) / 100;
                        $absen['keterangan'] = 'Potongan ' . $reff . '0% dari mengundang ' . $reff . ' teman';
                    } else {
                        $jumlahSpp = (int)$result->spp;
                        $absen['keterangan'] = '-';
                    }
                    $absen['jumlah'] = $jumlahSpp;


                    $now = Carbon::now();
                    $absen['no_invoice'] = '#SPP' . $now->year . '' . $now->month . '' . $idSiswa->id_siswa;
                    $absen['id_siswa'] = $idSiswa->id_siswa;
                    $absen['tagihan_bulan'] = Carbon::now()->format('Y-m-d');
                    $absen['status'] = 'Belum Lunas';
                    $absen['created_by'] = Auth::user()->id;
                    $absen['updated_by'] = Auth::user()->id;
                    $absen['created_at'] = $now;
                    $absen['updated_at'] = $now;
                    PembayaranSPP::create($absen);
                    // return 'here';
                }
                //cek ada atau belum tagihan fee pada bulan x dan id guru x
                if ($tmpFee) { // jika ada
                    $upFee = (int)$tmpFee->jumlah + (int)$result->fee_pengajar;

                    PembayaranFEE::where('id', $tmpFee->id)->update(['jumlah' => $upFee]);
                } else {
                    $now = Carbon::now();
                    $absen['no_invoice'] = '#FEE' . $now->year . '' . $now->month . '' . $result->id_guru;
                    $absen['id_guru'] = $result->id_guru;
                    $absen['jumlah'] = (int)$result->fee_pengajar;
                    $absen['tagihan_bulan'] = $now->format('Y-m-d');
                    $absen['status'] = 'Belum Lunas';
                    $absen['created_by'] = Auth::user()->id;
                    $absen['updated_by'] = Auth::user()->id;
                    $absen['created_at'] = $now;
                    $absen['updated_at'] = $now;
                    PembayaranFEE::create($absen);
                }

                // return $tmpFee;
                // cek apakah ada referal



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
            // 'poin' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        ]);
        try {
            $date = Carbon::now();
            //Get date and time
            $date->toDateTimeString();
            $newDateTime = date('G:i', strtotime($date->toDateTimeString()));
            // cek apakah jam melebihi jam 9 malamm
            // return strtotime("13:00");
            if (strtotime($newDateTime) >= strtotime("23:00")) {
                return response()->json([
                    'status_code' => 402,
                    'message' => 'Waktu absensi sudah ditutup, silahkan menghubungi admin',
                ], 402);
            } else {
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


                $result = Mengajar::create($mengajar);
                // return $result;
                $idSiswa = Kelas::where('id', $result->id_kelas)->first();
                $tmpSpp =  PembayaranSPP::whereMonth('tagihan_bulan', Carbon::now()->format('m'))->where('id_siswa', $idSiswa->id_siswa)->first();
                $tmpFee =  PembayaranFEE::whereMonth('tagihan_bulan', Carbon::now()->format('m'))->where('id_guru', $result->id_guru)->first();
                // return $tmpFee;

                // cek apakah tagihan spp sudah ada?
                if ($tmpSpp) {
                    // cek apakah ada referal
                    $reff = Referal::where('reff_id',  $idSiswa->id_siswa)->where('status', 'Aktif')->count();
                    $jumlahSpp =  (int)$result->spp;
                    // percabangan ketika ada data atau tidak
                    if ($reff > 0) {
                        $jumlahSpp = (int)$result->spp * ($reff * 10) / 100;
                    } else {
                        $jumlahSpp = (int)$result->spp;
                    }
                    // menghitung jumlah tagihan
                    $upSpp = (int)$tmpSpp->jumlah + $jumlahSpp;
                    PembayaranSPP::where('id', $tmpSpp->id)->update(['jumlah' => $upSpp, 'keterangan' => 'Potongan ' . $reff . '0% dari mengundang ' . $reff . ' teman']);
                } else {
                    $reff = Referal::where('reff_id',  $idSiswa->id_siswa)->where('status', 'Aktif')->count();
                    $jumlahSpp =  (int)$result->spp;
                    // percabangan ketika ada data atau tidak
                    if ($reff > 0) {
                        $jumlahSpp = (int)$result->spp * ($reff * 10) / 100;
                        $absen['keterangan'] = 'Potongan ' . $reff . '0% dari mengundang ' . $reff . ' teman';
                    } else {
                        $jumlahSpp = (int)$result->spp;
                        $absen['keterangan'] = '-';
                    }
                    $absen['jumlah'] = $jumlahSpp;


                    $now = Carbon::now();
                    $absen['no_invoice'] = '#SPP' . $now->year . '' . $now->month . '' . $idSiswa->id_siswa;
                    $absen['id_siswa'] = $idSiswa->id_siswa;
                    $absen['tagihan_bulan'] = Carbon::now()->format('Y-m-d');
                    $absen['status'] = 'Belum Lunas';
                    $absen['created_by'] = Auth::user()->id;
                    $absen['updated_by'] = Auth::user()->id;
                    $absen['created_at'] = $now;
                    $absen['updated_at'] = $now;
                    PembayaranSPP::create($absen);
                }
                if ($tmpFee) {
                    $upFee = (int)$tmpFee->jumlah + (int)$result->fee_pengajar;

                    PembayaranFEE::where('id', $tmpFee->id)->update(['jumlah' => $upFee]);
                } else {
                    $now = Carbon::now();
                    $absen['no_invoice'] = '#FEE' . $now->year . '' . $now->month . '' . $result->id_guru;
                    $absen['id_guru'] = $result->id_guru;
                    $absen['jumlah'] = (int)$result->fee_pengajar;
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
            $created = $request->tglKelas . ' ' . date('H:i:s');

            // cek apakah jam melebihi jam 9 malamm
            // if (strtotime($newDateTime) >= strtotime("21:10")) {
            //     return response()->json([
            //         'status_code' => 402,
            //         'message' => 'Waktu absensi sudah ditutup, silahkan menghubungi admin',
            //     ], 402);
            // }
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
            $mengajar['created_at'] = date('Y-m-d H:i:s', strtotime($created));
            $mengajar['updated_at'] = date('Y-m-d H:i:s', strtotime($created));
            $result = Mengajar::create($mengajar);
            // select data siswa berdasarkan kelas
            $idSiswa = Kelas::where('id', $result->id_kelas)->first();

            // get data tagihan
            $tmpSpp =  PembayaranSPP::whereMonth('tagihan_bulan', Carbon::now()->format('m'))->where('id_siswa', $idSiswa->id_siswa)->first();
            $tmpFee =  PembayaranFEE::whereMonth('tagihan_bulan', Carbon::now()->format('m'))->where('id_guru', $result->id_guru)->first();

            // cek apakah tagihan spp sudah ada?
            if ($tmpSpp) {
                // cek apakah ada referal
                $reff = Referal::where('reff_id',  $idSiswa->id_siswa)->where('status', 'Aktif')->count();
                $jumlahSpp =  (int)$result->spp;
                // percabangan ketika ada data atau tidak
                if ($reff > 0) {
                    $jumlahSpp = (int)$result->spp * ($reff * 10) / 100;
                } else {
                    $jumlahSpp = (int)$result->spp;
                }
                // menghitung jumlah tagihan
                $upSpp = (int)$tmpSpp->jumlah + $jumlahSpp;
                PembayaranSPP::where('id', $tmpSpp->id)->update(['jumlah' => $upSpp, 'keterangan' => 'Potongan ' . $reff . '0% dari mengundang ' . $reff . ' teman']);
            } else {
                $reff = Referal::where('reff_id',  $idSiswa->id_siswa)->where('status', 'Aktif')->count();
                $jumlahSpp =  (int)$result->spp;
                // percabangan ketika ada data atau tidak
                if ($reff > 0) {
                    $jumlahSpp = (int)$result->spp * ($reff * 10) / 100;
                    $absen['keterangan'] = 'Potongan ' . $reff . '0% dari mengundang ' . $reff . ' teman';
                } else {
                    $jumlahSpp = (int)$result->spp;
                    $absen['keterangan'] = '-';
                }
                $absen['jumlah'] = $jumlahSpp;


                $now = Carbon::now();
                $absen['no_invoice'] = '#SPP' . $now->year . '' . $now->month . '' . $idSiswa->id_siswa;
                $absen['id_siswa'] = $idSiswa->id_siswa;
                $absen['tagihan_bulan'] = Carbon::now()->format('Y-m-d');
                $absen['status'] = 'Belum Lunas';
                $absen['created_by'] = Auth::user()->id;
                $absen['updated_by'] = Auth::user()->id;
                $absen['created_at'] = $now;
                $absen['updated_at'] = $now;
                PembayaranSPP::create($absen);
            }
            if ($tmpFee) {
                $upFee = (int)$tmpFee->jumlah + (int)$result->fee_pengajar;

                PembayaranFEE::where('id', $tmpFee->id)->update(['jumlah' => $upFee]);
            } else {
                $now = Carbon::now();
                $absen['no_invoice'] = '#FEE' . $now->year . '' . $now->month . '' . $result->id_guru;
                $absen['id_guru'] = $result->id_guru;
                $absen['jumlah'] = (int)$result->fee_pengajar;
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
        try {
            $result = Mengajar::where('id', $id)->first();
            $idSiswa = Kelas::where('id', $result->id_kelas)->first();
            $tmpSpp =  PembayaranSPP::whereMonth('tagihan_bulan', Carbon::now()->format('m'))->where('id_siswa', $idSiswa->id_siswa)->first();
            $tmpFee =  PembayaranFEE::whereMonth('tagihan_bulan', Carbon::now()->format('m'))->where('id_guru', $result->id_guru)->first();
            $upSpp = (int)$tmpSpp->jumlah - (int)$result->spp;
            $upFee = (int)$tmpFee->jumlah - (int)$result->fee_pengajar;
            PembayaranSPP::where('id', $tmpSpp->id)->update(['jumlah' => $upSpp]);
            PembayaranFEE::where('id', $tmpFee->id)->update(['jumlah' => $upFee]);
            Mengajar::where('id', $id)->delete();
            return response()->json([
                'status_code' => 200,
                'message' => 'Success'
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
