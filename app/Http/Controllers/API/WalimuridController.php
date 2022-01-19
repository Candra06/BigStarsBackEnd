<?php

namespace App\Http\Controllers\API;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Siswa;
use App\User;
use App\Walimurid;
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
                ->leftJoin('siswa', 'siswa.id_wali', 'wali_siswa.id')
                ->select('wali_siswa.*', 'users.username', 'users.phone', 'users.status')
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
            return Helper::generateRandomString(5);
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
            $inputWali = Walimurid::create($wali);
            $siswa['id_wali'] = $inputWali->id;
            $siswa['nama'] = $request->nama_siswa;
            $siswa['birth_date'] = $request->birth_date;
            Siswa::create($siswa);
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
            $siswa = Siswa::where('id_wali', $id)->get();
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
        //
    }
}
