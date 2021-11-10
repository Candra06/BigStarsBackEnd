<?php

namespace App\Http\Controllers\API;

use App\User;
use App\Walimurid;
use App\Guru;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required',
                'password' => 'required'
            ]);
            $credentials = request(['username', 'password']);
            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'status_code' => 500,
                    'message' => 'Unauthorized'
                ]);
            }
            $user = User::where('username', $request->username)->where('status', 'Active')->first();
            if ($user) {
                if (password_verify($request->password, $user->password)) {
                    $tokenResult = $user->createToken('authToken')->plainTextToken;
                    $data = [];
                    if ($user->role == 'Admin') {
                        $data = $user;
                    } else if ($user->role == 'Guru'){
                        $guru = Guru::where('id_users', $user->id)->first();
                        $data['nama'] = $guru->nama;
                        $data['alamat'] = $guru->alamat;
                        $data['birth_date'] = $guru->birth_date;
                        $data['id_users'] = $guru->id_users;
                        $data['phone'] = $guru->phone;
                        $data['role'] = $user->role;
                        $data['username'] = $user->username;
                        $data['status'] = $user->status;
                    } else if ($user->role == 'Walimurid'){
                        $guru = Walimurid::where('id_users', $user->id)->first();
                        $data['nama'] = $guru->nama;
                        $data['alamat'] = $guru->alamat;
                        $data['id_users'] = $guru->id_users;
                        $data['phone'] = $guru->phone;
                        $data['role'] = $user->role;
                        $data['username'] = $user->username;
                        $data['status'] = $user->status;
                    }

                    return response()->json([
                        'status_code' => 200,
                        'access_token' => $tokenResult,
                        'token_type' => 'Bearer',
                        'data' => $data
                    ]);
                } else {
                    return response()->json([
                        'status_code' => 401,
                        'message' => 'Password Salah',

                    ]);
                }
            } else {
                return response()->json([
                    'status_code' => 401,
                    'message' => 'Username tidak terdaftar',

                ]);
            }

            // return $user;
            if (!password_verify($request->password, $user->password)) {
                throw new \Exception('Error in Login');
            }
        } catch (Exception $error) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Error in Login',
                'error' => $error,
            ]);
        }
    }

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
        //
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
