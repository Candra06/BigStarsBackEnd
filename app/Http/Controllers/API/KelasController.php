<?php

namespace App\Http\Controllers\API;

use App\DetailKelas;
use App\Guru;
use App\Http\Controllers\Controller;
use App\Kelas;
use App\Mengajar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KelasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $data = [];
            $dt = [];
            // return $request;
            if (Auth::user()->role == 'Admin') {
                $dt = Kelas::leftJoin('siswa', 'siswa.id', 'kelas.id_siswa')
                    ->leftJoin('guru', 'guru.id', 'kelas.id_guru')
                    ->leftJoin('mapel', 'mapel.id', 'kelas.id_mapel')
                    ->select('siswa.nama as siswa', 'guru.nama as guru', 'mapel.mapel', 'kelas.*');
            } else if (Auth::user()->role == 'Guru') {
                $guru = Guru::where('id_users', Auth::user()->id)->first();
                $dt = Kelas::leftJoin('siswa', 'siswa.id', 'kelas.id_siswa')
                    ->leftJoin('guru', 'guru.id', 'kelas.id_guru')
                    ->leftJoin('mapel', 'mapel.id', 'kelas.id_mapel')
                    ->select('siswa.nama as siswa', 'guru.nama as guru', 'mapel.mapel', 'kelas.*')
                    ->where('kelas.id_guru', $guru->id);
            } else {
                $siswa = Guru::where('id_users', Auth::user()->id)->first();
                $dt = Kelas::leftJoin('siswa', 'siswa.id', 'kelas.id_siswa')
                    ->leftJoin('guru', 'guru.id', 'kelas.id_guru')
                    ->leftJoin('mapel', 'mapel.id', 'kelas.id_mapel')
                    ->select('siswa.nama as siswa', 'guru.nama as guru', 'mapel.mapel', 'kelas.*')
                    ->where('kelas.id_siswa', $siswa->id);
            }
            if ($request->siswa) {
                // $param = $request->query('siswa');
                $dt = $dt->where('siswa.nama', 'like', '%' . $request->siswa . '%');
            }
            if ($request->query('guru') != '') {
                $param = $request->query('guru');
                // return $param;
                $dt = $dt->where("guru.nama", "LIKE", "%$param%");
            }
            if ($request->status) {
                $dt = $dt->where('kelas.status', $request->status);
            }
            $result = $dt->get();

            foreach ($result as $key) {
                $detail = DetailKelas::where('id_kelas', $key->id)->first();
                $tmp['id'] = $key->id;
                $tmp['guru'] = $key->guru;
                $tmp['siswa'] = $key->siswa;
                $tmp['mapel'] = $key->mapel;
                $tmp['id_mapel'] = $key->id_mapel;
                $tmp['id_guru'] = $key->id_guru;
                $tmp['id_siswa'] = $key->id_siswa;
                $tmp['spp'] = $key->spp;
                $tmp['fee_guru'] = $key->fee_guru;
                $tmp['status'] = $key->status;
                $tmp['jam_mulai'] = $detail->jam_mulai;
                $tmp['jam_selesai'] = $detail->jam_selesai;
                $tmp['created_at'] = $key->created_at;
                $tmp['updated_at'] = $key->updated_at;
                array_push($data, $tmp);
            }
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

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_mapel' => 'required',
            'id_guru' => 'required',
            'id_siswa' => 'required',
            'spp' => 'required',
            'fee_guru' => 'required',
            'hari' => 'required',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
        ]);
        try {
            $hari = explode(",", $request->hari);
            $kelas = Kelas::create([
                'id_mapel' => $request->id_mapel,
                'id_guru' => $request->id_guru,
                'id_siswa' => $request->id_siswa,
                'spp' => $request->spp,
                'fee_guru' => $request->fee_guru,
                'fee_guru' => $request->fee_guru,
                'status' => 'Active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            for ($i = 0; $i < count($hari); $i++) {
                $detail['id_kelas'] = $kelas->id;
                $detail['hari'] = $hari[$i];
                $detail['jam_mulai'] = $request->jam_mulai;
                $detail['jam_selesai'] = $request->jam_selesai;
                DetailKelas::create($detail);
            }
            return response()->json([
                'status_code' => 200,
                'message' => 'Success'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status_code' => 401,
                'message' => 'Failed update data',
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
            $data = Kelas::leftJoin('siswa', 'siswa.id', 'kelas.id_siswa')
                ->leftJoin('guru', 'guru.id', 'kelas.id_guru')
                ->leftJoin('mapel', 'mapel.id', 'kelas.id_mapel')
                ->select('siswa.nama as siswa', 'guru.nama as guru', 'mapel.mapel', 'kelas.*')
                ->where('kelas.id', $id)
                ->first();
            $hari = DetailKelas::where('id_kelas', $id)->get();
            return response()->json([
                'status_code' => 200,
                'message' => 'Success',
                'data' => $data,
                'hari' => $hari,
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
    public function filterKelas($siswa, $guru, $status)
    {
        try {
            $data = [];
            $dt = [];
            $dt = Kelas::leftJoin('siswa', 'siswa.id', 'kelas.id_siswa')
                ->leftJoin('guru', 'guru.id', 'kelas.id_guru')
                ->leftJoin('mapel', 'mapel.id', 'kelas.id_mapel')
                ->select('siswa.nama as siswa', 'guru.nama as guru', 'mapel.mapel', 'kelas.*');
            // ->where('siswa.nama', 'like', '%' . $siswa . '%')
            // ->orWhere('guru.nama', 'like', '%' . $guru . '%')
            // ->orWhere('kelas.status', $status)
            $wer = '';
            if ($siswa != '-') {
                $dt = $dt->where('siswa.nama', 'like', '%' . $siswa . '%');
            }
            if ($guru != '-') {
                $dt = $dt->where('guru.nama', 'like', '%' . $guru . '%');
            }
            if ($status  != '-') {
                $dt = $dt->where('kelas.status', $status);
            }
            $result = $dt->get();

            // return $result;
            foreach ($result as $key) {
                $detail = DetailKelas::where('id_kelas', $key->id)->first();
                $tmp['id'] = $key->id;
                $tmp['guru'] = $key->guru;
                $tmp['siswa'] = $key->siswa;
                $tmp['mapel'] = $key->mapel;
                $tmp['id_mapel'] = $key->id_mapel;
                $tmp['id_guru'] = $key->id_guru;
                $tmp['id_siswa'] = $key->id_siswa;
                $tmp['spp'] = $key->spp;
                $tmp['fee_guru'] = $key->fee_guru;
                $tmp['status'] = $key->status;
                $tmp['jam_mulai'] = $detail->jam_mulai;
                $tmp['jam_selesai'] = $detail->jam_selesai;
                $tmp['created_at'] = $key->created_at;
                $tmp['updated_at'] = $key->updated_at;
                array_push($data, $tmp);
            }
            return response()->json([
                'status_code' => 200,
                'message' => 'Success',
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            // return $th;
            return response()->json([
                'status_code' => 401,
                'message' => $th,
            ]);
        }
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
        $request->validate([
            // 'hari' => 'required',
            'id_jadwal' => 'required',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
        ]);

        try {

            $detail['jam_mulai'] = $request->jam_mulai;
            $detail['jam_selesai'] = $request->jam_selesai;
            $detail['hari'] = $request->hari;
            DetailKelas::where('id_kelas', $id)->update($detail);

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

    public function addJadwal(Request $request, $id)
    {
        $request->validate([
            // 'hari' => 'required',
            'id_jadwal' => 'required',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
        ]);

        try {
            $detail['jam_mulai'] = $request->jam_mulai;
            $detail['jam_selesai'] = $request->jam_selesai;
            $detail['hari'] = $request->hari;
            $detail['id_kelas'] = $id;
            DetailKelas::create($detail);

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

    public function kehadiranByKelas($id)
    {
        try {

            $data = Mengajar::leftJoin('guru', 'guru.id', 'mengajar.id_guru')
                ->where('mengajar.id_kelas', $id)
                ->select('guru.nama', 'mengajar.*')
                ->get();

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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            DetailKelas::where('id', $id)->delete();

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
    public function destroyKelas($id)
    {
        try {
            Kelas::where('id', $id)->update(['status' => 'Deleted', 'updated_at' => Carbon::now()]);

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

    public function updateStatus(Request $request, $id)
    {
        try {
            // return $request;
            Kelas::where('id', $id)->update(['status' => $request->status, 'updated_at' => Carbon::now()]);

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
}
