<?php

namespace App\Http\Controllers;
use App\Subject;
use Illuminate\Http\Request;
use DB;
use JWTAuth;
use Illuminate\Support\Facades\Validator as MkVAlidator;

class SubjectController extends Controller
{
    public function create(Request $request)
    {
        //authorization
         $user = JWTAuth::User();
         //$this->authorization($user, $this->subject_class, 'create');
        //end authorization

        $validatedData = self::getValidatedJson($request, [
            'name' => ['required', 'string', 'max:255', 'min:1']
        ]);

        //new task
        $subject = new Subject();

        //fill
        $subject->fill($validatedData);

        $subject->user_id=$user->id;

        //save data
        $subject->save();

        return response()->json([
            'success' => true,
            'response' => $subject
        ]);
    }
}
