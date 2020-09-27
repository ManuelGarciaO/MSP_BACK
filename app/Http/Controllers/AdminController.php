<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use DB;
use JWTAuth;
use Illuminate\Support\Facades\Validator as MkVAlidator;

class AdminController extends Controller
{

    public function create(Request $request){

        //authorization
        //$this->authorization(JWTAuth::user(), $this->subject_class, 'create');

        $user = JWTAuth::User();
        if($user->admin!=1){
            abort(403, 'You do not have admin role');
        }
        $validatedData = $this->getValidatedJson($request, [
            'name' => ['required', 'string', 'max:255', 'min:1'],
            'last_name' => ['required', 'string', 'max:255', 'min:1'],
            'email' => ['required', 'email', 'max:255', 'min:3', 'unique:users'],
            'password' => ['required', 'string', 'alpha_dash', 'max:50', 'min:6']
            ]);
        
        User::create($validatedData);
        
        
        $user = User::orderBy('id', 'desc')->first();
        $user->encrypted_password = password_hash($validatedData['password'], PASSWORD_DEFAULT);
        $user->admin=1;
        $user->save();
        return response()->json([
            'response' => $user
        ], 201);
    }

    //searcher (manage)
    public function searcher(Request $request){

        //authorization
        $user = JWTAuth::User();
        if($user->admin!=1){
            abort(403, 'You do not have admin role');
        }
        //end authorization

          //validate
          $validator = MkVAlidator::make($request->all(), [
            'email' => 'string|max:50|min:1',
            'name' => 'string|max:50|min:1',
            'inactive' => 'boolean',
            'admin' => 'boolean',
            'order_by' => 'string|in:id,name,email,sign_in_count,current_sign_in_at,last_sign_in_at,created_at,updated_at',
            'order' => 'string|in:asc,desc',
            'page' => 'integer|min:1|max:1000',
            'limit' => 'integer|min:1|max:100',
          ]);

          //check fails
          if($validator->fails()){
            response()->json([
                  'success' => false,
                  'errors' => $validator->errors()
               ])->send();
          }

          //validated data
          $validatedData = $validator->valid();

          //default page
          if( !isset($validatedData['page']) ){
            //no declared
            $validatedData['page'] = 1;
          }

          //default limit
          if( !isset($validatedData['limit']) ){
            //no declared
            $validatedData['limit'] = -1;
            $pages=1;
          }

               //query
              $query = DB::table('users')
                  ->select('id', 'inactive', 'name', 'last_name','email', 'admin', 'sign_in_count', 'current_sign_in_at', 'current_sign_in_ip', 'last_sign_in_ip', 'last_sign_in_at', 'created_at', 'updated_at');

              //filters
              foreach ($validatedData as $key => $value) {
                switch ($key) {
                    case 'email':
                        $query->where($key, 'like', "%$value%");
                    break;
                    case 'inactive':
                        $query->where($key, $value);
                    break;

                    case 'name':
                        $query->where($key, 'like', "%$value%");
                    break;
                    case 'admin':
                        $query->where($key, $value);
                    break;
                    case 'order_by':
                        $query->orderBy($value, isset($validatedData['order']) ? $validatedData['order'] : 'desc' );
                    break;
                }
            }

            $items_found = $query->count();// total items

            if($validatedData['limit']!=-1){
                //aplicar offset
                $query->offset( ($validatedData['page'] - 1) * $validatedData['limit']);

                //aplicar limit
                $query->limit($validatedData['limit']);


                //results

                //pages
                $pages = ceil($items_found/$validatedData['limit']);

                //page
                $page = (int) $validatedData['page'];
                if($page > $pages && $pages == 0){
                  //no items
                  $page = 0;
                }
            }    

            return response()->json([
              'success' => true,
              'response' => [
              'pages' => $pages,
              'page' => (int) $validatedData['page'],
              'limit' => (int) $validatedData['limit'],
              'total_items' => $items_found,
              'results' => $query->get()
              ]
            ]);
          }
    // \\searcher (manage)

    //disable user
    public function disable(Request $request){
        
        $user = JWTAuth::User();
        if($user->admin!=1){
            abort(403, 'You do not have admin role');
        }

        //find the resource
        $user = User::find($request->id) ?? abort(404, 'resource not found');
        //disable user
        $user->inactive=1;

        $user->save();

        return response()->json([
            'success' => true
        ]);
    }

    //unable user
    public function unable(Request $request){
        
        $user = JWTAuth::User();
        if($user->admin!=1){
            abort(403, 'You do not have admin role');
        }

        //find the resource
        $user = User::find($request->id) ?? abort(404, 'resource not found');
        //disable user
        $user->inactive=0;

        $user->save();

        return response()->json([
            'success' => true
        ]);
    }
    
    //delete
    public function delete(Request $request){
        
        $user = JWTAuth::User();
        if($user->admin!=1){
            abort(403, 'You do not have admin role');
        }


        //find the resource
        $user = User::find($request->id) ?? abort(404, 'resource not found');

        //destroy
        $user->delete() ?? abort(500, 'could not delete resource');


        return response()->json([
            'success' => true
        ]);
    }
}
