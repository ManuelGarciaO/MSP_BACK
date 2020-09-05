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
            'password' => ['required', 'string', 'alpha_dash', 'max:50', 'min:6'],
            'inactive' => ['boolean'],
            ]);

        User::create($validatedData);
        
        
        $user = User::orderBy('id', 'desc')->first();

        $user->encrypted_password = password_hash($validatedData['password'], PASSWORD_DEFAULT);
        $user->save();
        return response()->json([
            'response' => $user
        ], 201);
    }


    //get owner data
    public function getOwnerInfo(Request $request)
    {
        $user = JWTAuth::user();

        return response()->json([
            'success' => true,
            'response' => $user
        ]);
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


    //update role
    public function updateRole(Request $request)
    {
        //authorization
        $user = JWTAuth::user();
        $this->authorization($user, $this->subject_class, 'update');
        //end authorization

        $validatedData = $this->getValidatedJson($request, [
            'role_id' => ['required', 'integer', 'min:1', 'exists:roles,id'],
        ]);

        //delete user role
        DB::table('roles_users')
              ->where('user_id', $user->id ?? abort(500, 'internal error') )
              ->delete();


        //set the role_id
        DB::table('roles_users')->insert([
            'user_id' => $user->id,
            'role_id' => $validatedData['role_id']
        ]);


        /* REPORTS */
               //role
               if(isset($validatedData['role_id'])){
                   $role = Role::find($validatedData['role_id']);
                   ReportEmail::role_changed($role->name, $user->email);
               }
        /* END REPORTS */





        return response()->json([
            'success' => true
        ]);
    }


    //update owner data
    public function updateOwnerInfo(Request $request)
    {
        //authorization
        $this->authorization(JWTAuth::user(), $this->subject_class, 'update');
        //end authorization

         $validatedData = $this->getValidatedJson($request, [
            'name' => ['string', 'max:255', 'min:5'],
            'email' => ['email', 'max:255', 'min:3', 'unique:users'],
            'parking_lot_id' => ['nullable', 'integer', 'min:1', 'exists:parking_lots,id'],
            'inactive' => ['boolean'],
            'area_id' => ['nullable', 'integer', 'min:1', 'exists:areas,id'],
            'accounting_account_id' => ['nullable', 'integer', 'min:1', 'exists:accounting_accounts,id']
        ]);

        //fill
        $user->fill($validatedData);

        //save data
        $user->save();


        return response()->json([
            'success' => true,
            'response' => $user
        ]);
    }



    //update user's data (admin of user is required)
    public function update(Request $request)
    {
        //authorization
        $this->authorization(JWTAuth::user(), $this->subject_class, 'update');
        //end authorization

        $validatedData = $this->getValidatedJson($request, [
            'name' => ['string', 'max:255', 'min:5'],
            'email' => ['email', 'max:255', 'min:3', 'unique:users'],
            'parking_lot_id' => ['integer', 'min:1', 'exists:parking_lots,id'],
            'area_id' => ['integer', 'min:1', 'exists:areas,id'],
            'inactive' => ['boolean'],
            'accounting_account_id' => ['integer', 'min:1', 'exists:accounting_accounts,id']
        ]);

        //find the resource
        $user = User::find($request->id) ?? abort(404, 'resource not found');

        /* REPORTS */
               //email
               if(isset($validatedData['email']) && $validatedData['email'] != $user->email){
                   $current_email = $user->email;
                   $new_email = $validatedData['email'];
                   ReportEmail::email_changed($current_email, $new_email);
               }
        /* END REPORTS */

        //fill
        $user->fill($validatedData);

        //save data
        $user->save();


        return response()->json([
            'success' => true,
            'response' => $user
        ]);
    }


    //delete
    public function delete(Request $request){
        //authorization
        $this->authorization(JWTAuth::user(), $this->subject_class, 'delete');
        //end authorization


        //find the resource
        $user = User::find($request->id) ?? abort(404, 'resource not found');

        //destroy
        $user->delete() ?? abort(500, 'could not delete resource');


        return response()->json([
            'success' => true
        ]);
    }


    //searcher (manage)
    public function searcher(Request $request){

        //authorization
        $this->authorization(JWTAuth::user(), $this->subject_class, 'manage');
        //end authorization

          //validate
          $validator = MkVAlidator::make($request->all(), [
            'email' => 'string|max:50|min:1',
            'name' => 'string|max:50|min:1',
            'inactive' => 'boolean',
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
                  ->select('id', 'inactive', 'name', 'email', 'sign_in_count', 'current_sign_in_at', 'current_sign_in_ip', 'last_sign_in_ip', 'last_sign_in_at', 'created_at', 'updated_at', 'parking_lot_id', 'area_id','accounting_account_id');

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



      //change password
      public function changePassword(Request $request){

        $validatedData = self::getValidatedJson($request, [
            'password' => ['required', 'string', 'alpha_dash', 'max:50', 'min:6'],
        ]);

        //authorization
        $user = JWTAuth::user();


        $user->encrypted_password = password_hash($validatedData['password'], PASSWORD_DEFAULT);

        //save
        $user->save();

        return response()->json([
            'success' => true
        ]);

      }
}
