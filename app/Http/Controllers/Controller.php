<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Authorization as LogAuthorization;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Validator;
use App\Role;
use App\Permission;
use DB;
use App\RoleAuthorization\Authorization as AuthorizationRole;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    //abort request
    public static function abort($status, $response, $http_code, $message_code){
      response()->json([
        'success' => $status,
        'response' => $response,
        'message_code' => $message_code
      ], $http_code)->send();
    }

    //check if the request was authorize
    public function authorization($user, $subject_class, $action){

      //get the user-role
      $role_id = (DB::table('roles_users')
                    ->select('role_id')
                    ->where('user_id', $user->id)
                    ->limit(1)
                    ->first() ?? abort(403, 'user has no role'))->role_id;

      //prevent inactive roles
      if( (Role::find($role_id) ?? abort(403, 'your role does not exist'))->inactive ) abort(403, 'your role is inactive');


      //get role's permissions
      $permissions =  DB::table('permissions_roles')
                     ->select('permission_id')
                     ->where('role_id', $role_id)
                     ->get();

      //get action's permission_id
      $action_permission_id = Permission::where('subject_class', $subject_class)
                    ->where('action', $action)
                    ->limit(1)
                    ->first() ?? abort(403, 'action has no permission');

      //prevent inactive permissions
      if($action_permission_id->inactive) abort(403, 'The permission is inactive');

      //check if the role-permission
      $authorized = false;
      foreach ($permissions as $permission) {
        if($permission->permission_id === $action_permission_id->id){
          $authorized = true;
        }
      }

      if(!$authorized){
        abort(401, 'You do not have permission for this action');
      }


        return true;//authorized
    }



    //get values from request-body (json)
    public function getValidatedJson($request, $rules){

        //get valid keys
        $rules_key = [];
        foreach ($rules as $key => $value) {
          $rules_key[] = $key;
        }

        //get request json
        $json = json_decode($request->getContent(), true);
                   if(json_last_error() != 0){
                      abort(400, 'invalid body-json');
                    }

               //validar que no se envien parametros no autorizados
               foreach ($json as $key => $value) {
                 if(!in_array($key, $rules_key)){
                   abort(400, "$key is not valid");
                 }
               }
               if(count($json) == 0){
                 abort(400, 'at least one value is necessary');
               }

        //validator
        $validator = Validator::make($json, $rules);


        if ($validator->fails()) {
               return response()->json([
                 'success' => false,
                 'errors' => $validator->messages()
               ], 400, [
                   'Access-Control-Allow-Origin' => '*'
               ])->send();
            }

        //valid
        return $json;
      }

      public function getValidatedJsonFromString($request, $rules){

        //get valid keys
        $rules_key = [];
        foreach ($rules as $key => $value) {
          $rules_key[] = $key;
        }

        //get request json
        $json = json_decode($request, true);
                   if(json_last_error() != 0){
                      abort(400, 'invalid body-json');
                    }

               //validar que no se envien parametros no autorizados
               foreach ($json as $key => $value) {
                 if(!in_array($key, $rules_key)){
                   abort(400, "$key is not valid");
                 }
               }
               if(count($json) == 0){
                 abort(400, 'at least one value is necessary');
               }

        //validator
        $validator = Validator::make($json, $rules);


        if ($validator->fails()) {
               return response()->json([
                 'success' => false,
                 'errors' => $validator->messages()
               ], 400)->send();
            }

        //valid
        return $json;
      }
}
