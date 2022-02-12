<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mengajar;
use App\Referal;
use App\Siswa;
use App\Walimurid;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SiswaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $data = Siswa::leftJoin('wali_siswa', 'siswa.id_wali', 'wali_siswa.id')
                ->select('wali_siswa.nama as wali', 'wali_siswa.alamat', 'siswa.*')
                ->where('siswa.status', '!=', 'Deleted')
                ->get();
            return response()->json([
                'status_code' => 200,
                'message' => 'Success',
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status_code' => 401,
                'message' => 'Failed show data',
                'error' => $th
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
        //
    }

    public function listByWali()
    {
        try {
            $id = Walimurid::where('id_users', Auth::user()->id)->first();
            $siswa = Siswa::where('id_wali', $id->id)->where('status', 'Aktif')->get();
            $data = [];
            foreach ($siswa as $s) {
                $poin = Mengajar::leftJoin('kelas', 'kelas.id', 'mengajar.id_kelas')
                    ->where('kelas.id_siswa', $s->id)->sum('mengajar.poin_siswa');
                $tmp['id'] = $s->id;
                $tmp['nama'] = $s->nama;
                $tmp['birth_date'] = $s->birth_date;
                $tmp['poin_siswa'] = $poin->poin_siswa;
                array_push($data, $tmp);
            }
            return response()->json([
                'status_code' => 200,
                'message' => 'Success',
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status_code' => 401,
                'message' => 'Failed show data',
                'error' => $th
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
        try {
            $siswa['id_wali'] = $request->id_wali;
            $siswa['nama'] = $request->nama_siswa;
            $siswa['birth_date'] = $request->birth_date;
            $wali['status'] = 'Aktif';
            Siswa::create($siswa);
            if ($request->kode_referal) {
                $idRef = Siswa::where('kode_referal', $request->kode_referal)->first();
                $reff['reff_id'] = $idRef->id;
                $reff['id_siswa'] = $request->id_siswa;
                $reff['status'] = 'Aktif';
                $reff['created_at'] = Carbon::now();
                $reff['updated_at'] = Carbon::now();
                Referal::create($reff);
            }
            return response()->json([
                'status_code' => 200,
                'message' => 'Success',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status_code' => 401,
                'message' => 'Failed create data',
                'error' => $th
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
            $siswa = Siswa::leftJoin('wali_siswa', 'siswa.id_wali', 'wali_siswa.id')
                ->select('wali_siswa.nama as wali', 'wali_siswa.alamat', 'siswa.*')
                ->where('siswa.id', $id)
                ->first();
            $poin = Mengajar::leftJoin('kelas', 'kelas.id', 'mengajar.id_kelas')
                ->where('kelas.id_siswa', $id)->sum('mengajar.poin_siswa');
            $data['wali'] = $siswa->wali;
            $data['alamat'] = $siswa->alamat;
            $data['kode_referal'] = $siswa->kode_referal;
            $data['nama'] = $siswa->nama;
            $data['birth_date'] = $siswa->birth_date;
            $data['status'] = $siswa->status;
            $data['poin_siswa'] = $poin->poin_siswa;
            return response()->json([
                'status_code' => 200,
                'message' => 'Success',
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status_code' => 401,
                'message' => 'Failed show data',
                'error' => $th
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

            $siswa['nama'] = $request->nama;
            $siswa['birth_date'] = $request->birth_date;
            Siswa::where('id', $id)->update($siswa);
            if ($request->status == 'Nonaktif') {
                Referal::where('id_siswa', $id)->update(['status' => 'Inactive']);
            }
            return response()->json([
                'status_code' => 200,
                'message' => 'Success',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status_code' => 401,
                'message' => 'Failed create data',
                'error' => $th
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
            Siswa::where('id', $id)->update(['status' => 'Deleted']);
            $reff =   Referal::where('id_siswa', $id)->count();
            if ($reff > 0) {
                Referal::where('id_siswa', $id)->update(['status' => 'Inactive']);
            }
            return response()->json([
                'status_code' => 200,
                'message' => 'Success',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status_code' => 401,
                'message' => 'Failed delete data',
                'error' => $th
            ]);
        }
    }
}
