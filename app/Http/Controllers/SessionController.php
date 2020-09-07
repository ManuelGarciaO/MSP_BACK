<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Session;
// use DB;
use JWTAuth;
use App\User;


class SessionController extends Controller
{
        //create new token (user login)
        public function login(Request $request){
            $validatedData = self::getValidatedJson($request, [
                'email' => ['required', 'email', 'max:255', 'min:3', 'exists:users'],//check if email exists in db
                'password' => ['required', 'string', 'alpha_dash', 'max:50', 'min:6'],
            ]);
    
            //get the user by email
            $user = User::where('email', $validatedData)->first();

            //check if is inactive
            if($user->inactive){
                abort(403, 'Your account is inactive');
            }
            
    
            return Session::create($user->id, $user->encrypted_password, $validatedData['password'], $user);
        }

        //get info session
        public function getInfo(Request $request){
            $payload = JWTAuth::parseToken()->payload();
    
            return response()->json([
                'success' => true,
                'response' => [
                    'expire' => $payload['exp'] - time(),
                    'user' => [
                        'id' => $payload['sub']
                    ]
                ]
                ], 200);
        }
    
        //revoke session token
        public function revoke_session(Request $request){
            JWTAuth::parseToken()->invalidate();
    
            return response()->json([
                'success' => true
                ], 200);
        }
    
        //refresh token
        public function refresh_session(Request $request){
            $token = JWTAuth::getToken();
            $new_token = JWTAuth::refresh($token);
    
            return response()->json([
                'success' => true,
                'response' => [
                    'token' => $new_token,
                    'type' => 'bearer',
                    'expire' => ( (int) env('JWT_TTL', 60)) * 60 //in seconds
                ]
                ], 201);
        }



        

      //send email-code
      public function sendEmailCode(Request $request){
          
        $validatedData = self::getValidatedJson($request, [
            'email' => ['required', 'email', 'max:255', 'min:3'],
        ]);

        //get user
        $user = User::where('email', $validatedData['email'])->first() ?? abort(404, 'email not found');


        return Session::sendEmailCode($request, $user->id, $user->email);
    }

    //exchange code, get a bearer token
    public function exchangeEmailCode(Request $request){
        $validatedData = self::getValidatedJson($request, [
            'secret' => ['required', 'string', 'max:6', 'min:6'],
            'email' => ['required', 'email', 'max:255', 'min:3'],
        ]);

        //get user
        $user = User::where('email', $validatedData['email'])->first() ?? abort(404, 'email not found');

        return Session::exchange_code($user, $validatedData['secret']);
    }
    
}
