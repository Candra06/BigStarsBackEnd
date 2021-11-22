<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Kelas;
use App\Mengajar;
use Illuminate\Http\Request;

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
        //
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
            $mengajar['created_at'] = $request->tglKelas;
            $mengajar['updated_at'] = $request->tglKelas;
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
