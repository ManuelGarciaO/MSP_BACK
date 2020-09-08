<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;
use JWTAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Mail;
use Str;
use App\Mail\PasswordRecovery as MailPasswordRecovery;

class Session extends Model
{
    
    //create a session
    public static function create($user_id, $encrypted_password, $password, $user){

        //check if the password (from request) is correct
        if( !password_verify($password, $encrypted_password) ){
            //no authorized
            abort(403, "Check your password.");
        }

        /* AUTHORIZED! */
              //update user info
              $user->sign_in_count=$user->sign_in_count+1;
              $user->last_sign_in_ip = $user->current_sign_in_ip;//IP
              $user->last_sign_in_at = $user->current_sign_in_at;//DateTime

              //new info
              $user->current_sign_in_ip = $_SERVER['REMOTE_ADDR'];
              $user->current_sign_in_at = (new \DateTime())->format('Y-m-d H:i:s');
            //   dd((new \DateTime())->format('Y-m-d H:i:s'));
            //   \Carbon::parse($moment->created_at)->format('Y-m-d h:m:s');

              $user->save();//saved

              //generete credentials
              $token = JWTAuth::fromUser($user);


              return response()->json([
                  'success' => true,
                  'response' => [
                    'token' => $token,
                    'type' => 'bearer',
                    'expire' =>  ( (int) env('JWT_TTL', 60)) * 7200 
                ]
              ], 201);

    }


    

    public static function sendEmailCode(Request $request, $user_id, $email){
        $redis_prefix = "api::user::recovery_password::$user_id";

        //check if exists current secret
        $recovery_password = Redis::get($redis_prefix);

        if($recovery_password){
            //re-send secret
            $recovery_password = json_decode($recovery_password, true);

            //secret
            $secret = $recovery_password['secret'];
            
            //re-send mail with the secret
            Mail::to($email)->send(
                new MailPasswordRecovery($secret)
            );
  
             return response()->json([
                'success' => true,
                'message' => 'The code has been sent again'
             ]);
        }
        
        /* GENERATE NEW SECRET */

        //generate a random number (6 digits)
        $secret =  strtoupper(Str::random(6));

        //save in redis
        Redis::setex($redis_prefix, 60 * 60 * 2, json_encode([
            'secret' => $secret,
            'user_id' => $user_id,
            'attempts' => 0,
            // 'sent_at' => new \DateTime()
        ]));//expires in 2 hours

        // send mail with the secret
        Mail::to($email)->send(
          new MailPasswordRecovery($secret)
        );

        return response()->json([
            'success' => true,
            'message' => 'A code has been sent to your email'
        ]);

    }

    public static function exchange_code($user, $secret){
        $redis_prefix = 'api::user::recovery_password::' . $user->id;

        //get the secret
        $secret_container = Redis::get($redis_prefix) ?? abort(404, 'code not found');

        $secret_container = json_decode($secret_container, true);

        //check attempts
        if($secret_container['attempts'] >= 3){
            return response()->json([
                'success' => false,
                'message' => 'The function has been temporarily locked',
                'lock_expiration' => Redis::ttl($redis_prefix)
            ], 403)->send();
        }

        //check equals secret
        if($secret_container['secret'] != $secret){
            //+1 attempt
            $secret_container['attempts'] += 1;
            Redis::setex($redis_prefix, Redis::ttl($redis_prefix), json_encode($secret_container));

            return response()->json([
                'success' => false,
                'message' => 'Your code is wrong',
                'remaining_attempts' => 3 - $secret_container['attempts']
            ], 403)->send();
        }
        

              /* SECRET OK */

              //remove code from redis
              Redis::del($redis_prefix);

              //update user info
              $user->last_sign_in_ip = $user->current_sign_in_ip;//IP
              $user->last_sign_in_at = $user->current_sign_in_at;//DateTime

              //new info
              $user->current_sign_in_ip = $_SERVER['REMOTE_ADDR'];
              $user->current_sign_in_at = new \DateTime();

              $user->save();//saved

              //generete credentials
              $token = JWTAuth::fromUser($user);


              return response()->json([
                  'success' => true,
                  'response' => [
                    'token' => $token,
                    'type' => 'bearer',
                    'expire' =>  ( (int) env('JWT_TTL', 60)) * 60 
                ]
              ], 201);
        
    }



}
