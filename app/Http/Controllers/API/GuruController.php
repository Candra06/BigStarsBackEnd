<?php

namespace App\Http\Controllers\API;

use App\Guru;
use App\Http\Controllers\Controller;
use App\User;
use Carbon\Carbon;
use DateTime;
use GuzzleHttp\Promise\Create;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;

class GuruController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $data = Guru::leftJoin('users', 'users.id', 'guru.id_users')->select('users.username', 'users.phone', 'guru.*')->get();
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $input['username'] = $request->username;
            $input['password'] = bcrypt($request->password);
            $input['foto'] = $request->foto;
            $input['phone'] = $request->phone;
            $input['role'] = 'Guru';
            $input['status'] = 'Active';
            $input['created_at'] = Carbon::now();
            $input['updated_at'] = Carbon::now();
            $user = User::create($input);
            $guru['id_users'] = $user->id;
            $guru['nama'] = $request->nama;
            $guru['alamat'] = $request->alamat;
            $guru['birth_date'] = $request->birth_date;
            $guru['created_at'] = Carbon::now();
            $guru['updated_at'] = Carbon::now();
            Guru::create($guru);
            return response()->json([
                'status_code' => 200,
                'message' => 'Success',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status_code' => 401,
                'message' => 'Failed input data',
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
            $data = Guru::leftJoin('users', 'users.id', 'guru.id_users')->select('users.username', 'users.phone', 'guru.*')->where('guru.id', $id)->first();
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
            $data = Guru::where('id', $id)->first();
            // return $data;
            $input['username'] = $request->username;
            $input['password'] = bcrypt($request->password);
            $input['foto'] = $request->foto;
            $input['phone'] = $request->phone;
            $input['status'] = $request->status;
            $input['updated_at'] = Carbon::now();
            User::where('id', $data->id_users)->update($input);

            $guru['nama'] = $request->nama;
            $guru['alamat'] = $request->alamat;
            $guru['birth_date'] = $request->birth_date;
            $guru['updated_at'] = Carbon::now();
            Guru::where('id', $id)->update($guru);
            return response()->json([
                'status_code' => 200,
                'message' => 'Success',
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $data = Guru::where('id', $id)->first();
            // return $data;
            $id_user = $data->id_users;
            Guru::where('id', $id)->delete();
            User::where('id', $id_user)->delete();
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
