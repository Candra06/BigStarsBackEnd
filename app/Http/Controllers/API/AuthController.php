<?php

namespace App\Http\Controllers\API;

use App\DetailKelas;
use App\User;
use App\Walimurid;
use App\Guru;
use App\Helpers\Helper as HelpersHelper;
use App\Http\Controllers\Controller;
use App\Kelas;
use App\Siswa;
use Carbon\Carbon;
use Exception;
use App\Helpers\Helper;
use App\Mengajar;
use App\NotifikasiDetail;
use App\PembayaranFEE;
use App\PembayaranSPP;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
                    } else if ($user->role == 'Guru') {
                        $guru = Guru::where('id_users', $user->id)->first();
                        $data['nama'] = $guru->nama;
                        $data['alamat'] = $guru->alamat;
                        $data['birth_date'] = $guru->birth_date;
                        $data['id_users'] = $guru->id_users;
                        $data['phone'] = $guru->phone;
                        $data['role'] = $user->role;
                        $data['username'] = $user->username;
                        $data['status'] = $user->status;
                    } else if ($user->role == 'Walimurid') {
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

    public function logout(Request $request)
    {
        try {
            // auth()->user()->tokens()->delete();
            return [
                'message' => 'Logged out'
            ];
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
    public function dashboardAdmin()
    {
        try {
            $mytime = Carbon::now();

            $kelas = Kelas::count();
            $siswa = Siswa::count();
            $notif = NotifikasiDetail::where('id_penerima', Auth::user()->id)->where('status', 'Unread')->count();
            $guru = Guru::count();
            $kelas_aktif = Kelas::where('status', 'Active')->count();
            $kelas_today = DetailKelas::leftJoin('kelas', 'kelas.id', 'detail_kelas.id_kelas')
                ->leftJoin('siswa', 'siswa.id', 'kelas.id_siswa')
                ->leftJoin('guru', 'guru.id', 'kelas.id_guru')
                ->leftJoin('mapel', 'mapel.id', 'kelas.id_mapel')
                ->select('siswa.nama as siswa', 'guru.nama as guru', 'mapel.mapel', 'kelas.*', 'detail_kelas.hari', 'detail_kelas.jam_mulai', 'detail_kelas.jam_selesai')
                ->where('detail_kelas.hari', Helper::getDay($mytime->format('l')))
                ->get();
            $data['kelas'] = $kelas;
            $data['siswa'] = $siswa;
            $data['guru'] = $guru;
            $data['notif_unread'] = $notif;
            $data['kelas_aktif'] = $kelas_aktif;
            $data['kelas_today'] = $kelas_today;
            return response()->json([
                'status_code' => 200,
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status_code' => 401,
                'message' => $th,
            ]);
        }
    }

    public function dashboardGuru()
    {
        try {
            $mytime = Carbon::now();
            $bulan = explode(" ", $mytime);
            $id = Guru::where('id_users',  Auth::user()->id)->first();
            $notif = NotifikasiDetail::where('id_penerima', Auth::user()->id)->where('status', 'Unread')->count();
            $kelas_aktif = Kelas::where('status', 'Active')->where('id_guru', $id)->count();
            // $fee = Mengajar::where('id_guru', $id->id)->whereMonth('created_at', date('m', strtotime($bulan[0])))->sum('fee_pengajar');
            $fee = PembayaranFEE::where('id_guru', $id->id)->whereMonth('tagihan_bulan', date('m', strtotime($bulan[0])))->first();

            $kelas_today = DetailKelas::leftJoin('kelas', 'kelas.id', 'detail_kelas.id_kelas')
                ->leftJoin('siswa', 'siswa.id', 'kelas.id_siswa')
                ->leftJoin('guru', 'guru.id', 'kelas.id_guru')
                ->leftJoin('mapel', 'mapel.id', 'kelas.id_mapel')
                ->select('siswa.nama as siswa', 'guru.nama as guru', 'mapel.mapel', 'kelas.id as id_kelas', 'kelas.status', 'detail_kelas.jam_mulai', 'detail_kelas.jam_selesai')
                ->where('detail_kelas.hari', Helper::getDay($mytime->format('l')))
                ->where('kelas.id_guru', $id->id)
                ->get();
            // return $bulan[0];
            $sharing =  Mengajar::where('mengajar.id_guru', $id->id)
                ->leftJoin('kelas', 'kelas.id', 'mengajar.id_kelas')
                ->leftJoin('detail_kelas', 'detail_kelas.id_kelas', 'kelas.id')
                ->leftJoin('siswa', 'siswa.id', 'kelas.id_siswa')
                ->leftJoin('guru', 'guru.id', 'kelas.id_guru')
                ->leftJoin('mapel', 'mapel.id', 'kelas.id_mapel')
                ->whereDate('mengajar.created_at', date('Y-m-d'))
                ->where('mengajar.tipe', 'Pengganti')
                ->where('mengajar.status', 'Waiting')
                ->where('detail_kelas.hari', Helper::getDay($mytime->format('l')))
                ->select('siswa.nama as siswa', 'guru.nama as guru', 'mapel.mapel', 'kelas.id as id_kelas', 'kelas.status', 'detail_kelas.jam_mulai', 'detail_kelas.jam_selesai')
                ->get();
            // return $sharing;
            $data['fee'] = $fee->jumlah;
            $data['notif_unread'] = $notif;
            $data['kelas_today'] = $kelas_today;
            $data['kelas_aktif'] = $kelas_aktif;
            $data['sharing'] = $sharing;
            return response()->json([
                'status_code' => 200,
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status_code' => 401,
                'message' => $th,
            ]);
        }
    }

    public function dashboardWali()
    {
        try {
            $mytime = Carbon::now();
            $notif = NotifikasiDetail::where('id_penerima', Auth::user()->id)->where('status', 'Unread')->count();
            $bulan = explode(" ", $mytime);
            $id = Walimurid::where('id_users',  Auth::user()->id)->first();
            $siswa = Siswa::where('id_wali', $id->id)->get();
            $spp = 0;
            foreach ($siswa as $val) {
                # code...
                $tmp = PembayaranSPP::where('id_siswa', $val->id)->whereMonth('tagihan_bulan', date('m', strtotime($bulan[0])))->first();
                $spp += $tmp->jumlah;
            }


            // $fee = Mengajar::where('id_guru', $id->id)->whereMonth('created_at', date('m', strtotime($bulan[0])))->sum('fee_pengajar');
            $kelas = [];
            // return $siswa;
            foreach ($siswa as $value) {
                $kelas_today = DetailKelas::leftJoin('kelas', 'kelas.id', 'detail_kelas.id_kelas')
                    ->leftJoin('siswa', 'siswa.id', 'kelas.id_siswa')
                    ->leftJoin('guru', 'guru.id', 'kelas.id_guru')
                    ->leftJoin('mapel', 'mapel.id', 'kelas.id_mapel')
                    ->select('siswa.nama as siswa', 'guru.nama as guru', 'mapel.mapel', 'kelas.id as id_kelas', 'kelas.status', 'detail_kelas.jam_mulai', 'detail_kelas.jam_selesai')
                    ->where('detail_kelas.hari', Helper::getDay($mytime->format('l')))
                    ->where('kelas.id_siswa', $value->id)
                    ->get();
                foreach ($kelas_today as $key) {
                    array_push($kelas, $key);
                }
            }
            $data['spp'] = $spp;
            $data['notif_unread'] = $notif;
            $data['kelas_today'] = $kelas;

            return response()->json([
                'status_code' => 200,
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status_code' => 401,
                'message' => $th,
            ]);
        }
    }

    public function updateProfileAdmin(Request $request)
    {
        try {
            $user['username'] = $request->username;
            $user['phone'] = $request->phone;
            if ($request->password) {
                $user['password'] = bcrypt($request->password);
            }
            User::where('id', Auth::user()->id)->update($user);
            $data = User::where('id', Auth::user()->id)->first();
            return response()->json([
                'status_code' => 200,
                'message' => 'Success',
                'data' => $data,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status_code' => 401,
                'message' => $th,
            ]);
        }
    }

    public function updateProfileGuru(Request $request)
    {
        try {
            $id =  Auth::user()->id;
            $user['username'] = $request->username;
            $user['phone'] = $request->phone;
            if ($request->password) {
                $user['password'] = bcrypt($request->password);
            }
            $guru['nama'] = $request->nama;
            $guru['birth_date'] = $request->birth_date;
            $guru['alamat'] = $request->alamat;
            Guru::where('id_users', $id)->update($guru);
            User::where('id', $id)->update($user);
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

    public function updateProfileWali(Request $request)
    {
        try {
            $id =  Auth::user()->id;
            $user['username'] = $request->username;
            $user['phone'] = $request->phone;
            if ($request->password) {
                $user['password'] = bcrypt($request->password);
            }
            $wali['nama'] = $request->nama;
            $wali['alamat'] = $request->alamat;
            Walimurid::where('id_users', $id)->update($wali);
            User::where('id', $id)->update($user);
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

    public function updateFoto(Request $request)
    {
        $fileType = $request->file('foto')->extension();
        $name = Str::random(8) . '.' . $fileType;
        $input['foto'] = Storage::putFileAs('foto', $request->file('foto'), $name);

        try {
            User::where('id', Auth::user()->id)->update($input);
            $data = User::where('id', Auth::user()->id)->first();
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
        //
    }
}
