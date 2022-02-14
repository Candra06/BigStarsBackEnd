<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Rules;
use Illuminate\Http\Request;


class RulesController extends Controller
{
    public function updateRules(Request $request, $id)
    {
        try {
            Rules::where('id', $id)->update(['rules' => $request->rules]);
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

    public function postRules(Request $request)
    {
        try {
            Rules::create(['rules_for' => $request->rules_for, 'rules' => $request->rules]);
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

    public function index()
    {
        try {
            $data = Rules::all();
            return response()->json([
                'status_code' => 200,
                'message' => 'Success',
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status_code' => 401,
                'message' => 'Failed update data',
                'error' => $th
            ]);
        }
    }

    public function detail(Request $request)
    {
        try {
            $data = [];
            if ($request->id) {
                $data =  Rules::where('id', $request->id)->first();
            } else {
                $data =  Rules::where('rules_for', $request->rules_for)->first();
            }

            return response()->json([
                'status_code' => 200,
                'message' => 'Success',
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status_code' => 401,
                'message' => 'Failed update data',
                'error' => $th
            ]);
        }
    }
}
