<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Notifikasi;
use App\NotifikasiDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class NotifikasiController extends Controller
{
    public function index()
    {
        try {
            $id = Auth::user()->id;
            $data = NotifikasiDetail::leftJoin('notifikasi', 'notifikasi.id', 'notifikasi_detail.id_notif')
                ->where('notifikasi_detail.id_penerima', $id)
                ->select('notifikasi_detail.id', 'notifikasi.judul', 'notifikasi.konten', 'notifikasi_detail.status', 'notifikasi_detail.created_at')
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
                'data' => []
            ]);
        }
    }

    public function readNotif($id)
    {
        try {
            NotifikasiDetail::where('id', $id)->update(['status' => 'Read', 'updated_at' => Carbon::now()]);
            return response()->json([
                'status_code' => 200,
                'message' => 'Success',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status_code' => 401,
                'message' => $th
            ]);
        }
    }
}
