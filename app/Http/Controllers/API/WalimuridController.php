<?php

namespace App\Http\Controllers\API;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Referal;
use App\Siswa;
use App\User;
use App\Walimurid;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class WalimuridController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $data = Walimurid::leftJoin('users', 'users.id', 'wali_siswa.id_users')
                ->select('wali_siswa.*', 'users.username', 'users.phone', 'users.status')
                ->where('wali_siswa.status', '!=', 'Deleted')
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
    public function create(Request $request)
    {
        try {

            $siswa['id_wali'] = $request->id_wali;
            $siswa['nama'] = $request->nama;
            $siswa['status'] = 'Aktif';
            $siswa['kode_referal'] = 'BS'.Helper::generateRandomString(5);
            $siswa['birth_date'] = $request->birth_date;
            Siswa::create($siswa);
            return response()->json([
                'status_code' => 200,
                'message' => 'Success',
            ]);
        } catch (\Throwable $th) {
            throw $th;
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
            $user['username'] = $request->username;
            $user['phone'] = $request->phone;
            $user['foto'] = '-';
            $user['password'] = bcrypt($request->password);
            $user['role'] = 'Walimurid';
            $user['status'] = 'Active';
            $inputUser = User::create($user);
            $wali['id_users'] = $inputUser->id;
            $wali['nama'] = $request->nama_wali;
            $wali['alamat'] = $request->alamat;
            $wali['status'] = 'Active';
            $inputWali = Walimurid::create($wali);
            $siswa['id_wali'] = $inputWali->id;
            $siswa['nama'] = $request->nama_siswa;
            $siswa['birth_date'] = $request->birth_date;
            $siswa['kode_referal'] =  'BS'.Helper::generateRandomString(5);
            $siswa['status'] = 'Aktif';

            $inputSiswa = Siswa::create($siswa);
            if ($request->kode_referal) {
                $from = Siswa::where('kode_referal', $request->kode_referal)->first();
                $reff['reff_id'] = $from->id;
                $reff['id_siswa'] = $inputSiswa->id;
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
            $data = [];
            $detail = Walimurid::leftJoin('users', 'users.id', 'wali_siswa.id_users')
                ->select('wali_siswa.*', 'users.username', 'users.phone', 'users.status')
                ->where('wali_siswa.id', $id)
                ->first();
            $siswa = Siswa::where('id_wali', $id)->where('status', 'Aktif')->get();
            $data['detail'] = $detail;
            $data['siswa'] = $siswa;
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
            $idUser = Walimurid::where('id', $id)->first();
            $user['username'] = $request->username;
            $user['phone'] = $request->phone;
            // $user['foto'] = '-';
            $user['password'] = bcrypt($request->password);
            $user['status'] = $request->status;
            User::where('id', $idUser->id_users)->update($user);
            $wali['nama'] = $request->nama;
            $wali['alamat'] = $request->alamat;
            Walimurid::where('id', $id)->update($wali);
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
            Walimurid::where('id', $id)->update(['status'=>'Deleted', 'updated_at'=>Carbon::now()]);
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
}
