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
        ->select('subjects.name as name', DB::raw('SUM(tasks.worked_hours) as worked, SUM(tasks.estimated_hours) as estimated'))
        ->join('subjects', 'tasks.subject_id', '=', 'subjects.id')
        ->where('tasks.user_id', $user->id)
        //->where('type', 'tarea')
        ->groupBy('tasks.subject_id');

        return response()->json([
            'success' => true,
            'response' => [
            'results' => $query->get(),
            ]
          ]);
    }

    public function getHomeworks(Request $request){
        //authorization
        $user = JWTAuth::User();
        //end authorization

        //query total Homeworks
        $query = DB::table('tasks')
        ->select('subject_id', DB::raw('COUNT(*) as Homeworks'))
        ->where('user_id', $user->id)
        ->where('type', 'tarea')
        ->groupBy('subject_id');

        return response()->json([
            'success' => true,
            'response' => [
            'results' => $query->get(),
            ]
          ]);
    }

    public function getExams(Request $request){
        //authorization
        $user = JWTAuth::User();
        //end authorization

        //query total exams
        $query = DB::table('tasks')
        ->select('subject_id', DB::raw('COUNT(*) as Exams'))
        ->where('user_id', $user->id)
        ->where('type', 'examen')
        ->groupBy('subject_id');

        return response()->json([
            'success' => true,
            'response' => [
            'results' => $query->get(),
            ]
          ]);
    }

    public function getProyects(Request $request){
        //authorization
        $user = JWTAuth::User();
        //end authorization

        //query total proyects
        $query = DB::table('tasks')
        ->select('subject_id', DB::raw('COUNT(*) as Proyects'))
        ->where('user_id', $user->id)
        ->where('type', 'proyecto')
        ->groupBy('subject_id');

        return response()->json([
            'success' => true,
            'response' => [
            'results' => $query->get(),
            ]
          ]);
    }
}
