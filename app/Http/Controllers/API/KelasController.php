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
    public function index()
    {
        try {
            $data = [];
            $dt = [];
            if (Auth::user()->role == 'Admin') {
                $dt = Kelas::leftJoin('siswa', 'siswa.id', 'kelas.id_siswa')
                    ->leftJoin('guru', 'guru.id', 'kelas.id_guru')
                    ->leftJoin('mapel', 'mapel.id', 'kelas.id_mapel')
                    ->select('siswa.nama as siswa', 'guru.nama as guru', 'mapel.mapel', 'kelas.*')
                    ->get();
            } else if (Auth::user()->role == 'Guru') {
                $guru = Guru::where('id_users', Auth::user()->id)->first();
                $dt = Kelas::leftJoin('siswa', 'siswa.id', 'kelas.id_siswa')
                    ->leftJoin('guru', 'guru.id', 'kelas.id_guru')
                    ->leftJoin('mapel', 'mapel.id', 'kelas.id_mapel')
                    ->select('siswa.nama as siswa', 'guru.nama as guru', 'mapel.mapel', 'kelas.*')
                    ->where('kelas.id_guru', $guru->id)
                    ->get();
            } else {
                $siswa = Guru::where('id_users', Auth::user()->id)->first();
                $dt = Kelas::leftJoin('siswa', 'siswa.id', 'kelas.id_siswa')
                    ->leftJoin('guru', 'guru.id', 'kelas.id_guru')
                    ->leftJoin('mapel', 'mapel.id', 'kelas.id_mapel')
                    ->select('siswa.nama as siswa', 'guru.nama as guru', 'mapel.mapel', 'kelas.*')
                    ->where('kelas.id_siswa', $siswa->id)
                    ->get();
            }
            foreach ($dt as $key) {
                $detail = DetailKelas::where('id_kelas', $key->id)->first();
                $data['id'] = $key->id;
                $data['guru'] = $key->guru;
                $data['siswa'] = $key->siswa;
                $data['mapel'] = $key->mapel;
                $data['id_mapel'] = $key->id_mapel;
                $data['id_guru'] = $key->id_guru;
                $data['id_siswa'] = $key->id_siswa;
                $data['spp'] = $key->spp;
                $data['fee_guru'] = $key->fee_guru;
                $data['status'] = $key->status;
                $data['jam_mulai'] = $detail->jam_mulai;
                $data['jam_selesai'] = $detail->jam_selesai;
                $data['created_at'] = $key->created_at;
                $data['updated_at'] = $key->updated_at;
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
                ->select('siswa.nama as siswa', 'guru.nama as guru', 'mapel.mapel', 'kelas.*')
                ->where('siswa.nama', 'like', '%' . $siswa . '%')
                ->orWhere('guru.nama', 'like', '%' . $guru . '%')
                ->orWhere('kelas.status', $status)
                ->get();

            foreach ($dt as $key) {
                $detail = DetailKelas::where('id_kelas', $key->id)->first();
                $data['id'] = $key->id;
                $data['guru'] = $key->guru;
                $data['siswa'] = $key->siswa;
                $data['mapel'] = $key->mapel;
                $data['id_mapel'] = $key->id_mapel;
                $data['id_guru'] = $key->id_guru;
                $data['id_siswa'] = $key->id_siswa;
                $data['spp'] = $key->spp;
                $data['fee_guru'] = $key->fee_guru;
                $data['status'] = $key->status;
                $data['jam_mulai'] = $detail->jam_mulai;
                $data['jam_selesai'] = $detail->jam_selesai;
                $data['created_at'] = $key->created_at;
                $data['updated_at'] = $key->updated_at;
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
}
