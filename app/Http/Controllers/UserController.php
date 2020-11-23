<?php

namespace App\Http\Controllers;
use App\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use DB;
use JWTAuth;
use Illuminate\Support\Facades\Validator as MkVAlidator;

class UserController extends Controller
{
    protected $subject_class = 'User';

    //new users;
    public function register(Request $request){

        //authorization
        //$this->authorization(JWTAuth::user(), $this->subject_class, 'create');


        $validatedData = $this->getValidatedJson($request, [
            'name' => ['required', 'string', 'max:255', 'min:1'],
            'last_name' => ['required', 'string', 'max:255', 'min:1'],
            'email' => ['required', 'email', 'max:255', 'min:3', 'unique:users'],
            'password' => ['required', 'string', 'max:50', 'min:6']
            ]);

        User::create($validatedData);
        
        
        $user = User::orderBy('id', 'desc')->first();

        $user->encrypted_password = password_hash($validatedData['password'], PASSWORD_DEFAULT);
        $user->save();
        return response()->json([
            'response' => $user
        ], 201);
    }

    //get user by id
    public function getUserById(Request $request)
    {
        //authorization
        $this->authorization(JWTAuth::user(), $this->subject_class, 'read');
        //end authorization

        //find the resource
        $user = User::find($request->id) ?? abort(404, 'resource not found');

        return response()->json([
            'success' => true,
            'response' => $user
        ]);
    }
}
