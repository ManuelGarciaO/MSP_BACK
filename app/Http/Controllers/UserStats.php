<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use JWTAuth;

class UserStats extends Controller
{
    //get stats from user subject by subject
    public function getHours(Request $request){
        //authorization
        $user = JWTAuth::User();
        //end authorization

        //query worked and estimated hours
        $query = DB::table('tasks')
        ->select(DB::raw('SUM(tasks.estimated_hours) as estimated'))
        ->where('tasks.user_id', $user->id)->first();


        return response()->json([
            'success' => true,
            'response' => [
            'results' => $query,
            ]
          ]);
    }

    public function getHomeworks(Request $request){
        //authorization
        $user = JWTAuth::User();
        //end authorization

        //query total Homeworks
        $query = DB::table('tasks')
        ->select( DB::raw('COUNT(*) as Homeworks'))
        ->where('user_id', $user->id)
        ->where('type', 'tarea');

        return response()->json([
            'success' => true,
            'response' => [
            'results' => $query->first(),
            ]
          ]);
    }

    public function getExams(Request $request){
        //authorization
        $user = JWTAuth::User();
        //end authorization

        //query total exams
        $query = DB::table('tasks')
        ->select(DB::raw('COUNT(*) as Exams'))
        ->where('user_id', $user->id)
        ->where('type', 'examen');

        return response()->json([
            'success' => true,
            'response' => [
            'results' => $query->first(),
            ]
          ]);
    }

    public function getProyects(Request $request){
        //authorization
        $user = JWTAuth::User();
        //end authorization

        //query total proyects
        $query = DB::table('tasks')
        ->select(DB::raw('COUNT(*) as Proyects'))
        ->where('user_id', $user->id)
        ->where('type', 'proyecto');

        return response()->json([
            'success' => true,
            'response' => [
            'results' => $query->first(),
            ]
          ]);
    }
}
