<?php

namespace App\Http\Controllers;

use App\Task;
use Illuminate\Http\Request;
use DB;
use JWTAuth;
use Illuminate\Support\Facades\Validator as MkVAlidator;

class TaskController extends Controller
{
    protected $subject_class = 'Task';

    public function create(Request $request)
    {
        //authorization
         $user = JWTAuth::User();
         //$this->authorization($user, $this->subject_class, 'create');
        //end authorization

        $validatedData = self::getValidatedJson($request, [
            'name' => ['required', 'string', 'max:255', 'min:1'],
            'description' => ['required', 'string', 'max:255', 'min:1'],
            'type' => ['required', 'string', 'max:255', 'min:1'],
            'deadline' => ['date_format:Y-m-d', 'min:1', 'max:50'],
            'status' => ['required', 'string', 'max:255', 'min:1'],
            'estimated_hours' => ['integer', 'min:0'],
            'worked_hours' => ['integer', 'min:0'],
            'link' => ['string', 'max:255', 'min:1']
        ]);

        //new task
        $task = new Task();

        //fill
        $task->fill($validatedData);

        $task->user_id=$user->id;

        //save data
        $task->save();

        return response()->json([
            'success' => true,
            'response' => $task
        ]);
    }

    //get by id
    public function getById(Request $request)
    {
        //authorization
        $user = JWTAuth::User();
        //$this->authorization($user, $this->subject_class, 'read');
        //end authorization

        //find resource
        $task = Task::find($request->id) ?? self::abort(false, null, 404, 404);

        return response()->json([
        'success' => true,
        'response' => $task
    ]);
    }

    //update task
    public function update(Request $request)
    {
        //authorization
        $user = JWTAuth::User();
        //$this->authorization($user, $this->subject_class, 'update');
        //end authorization

        $validatedData = self::getValidatedJson($request, [
            'name' => ['string', 'max:255', 'min:1'],
            'description' => ['string', 'max:255', 'min:1'],
            'type' => ['string', 'max:255', 'min:1'],
            'deadline' => ['date_format:Y-m-d', 'min:1', 'max:50'],
            'status' => ['string', 'max:255', 'min:1'],
            'estimated_hours' => ['integer', 'min:0'],
            'worked_hours' => ['integer', 'min:0'],
            'link' => ['string', 'max:255', 'min:1'],
        ]);

        //find the resource
        $task = Task::find($request->id);

        //fill
        $task->fill($validatedData);

        //save
        $task->save();

        return response()->json([
            'success' => true,
            'response' => $task
        ]);
    }

    //update task
    public function archive(Request $request)
    {
        //authorization
        $user = JWTAuth::User();
        //$this->authorization($user, $this->subject_class, 'update');
        //end authorization

        //find the resource
        $task = Task::find($request->id);

        //fill
        $task->archived=1;

        //save
        $task->save();

        return response()->json([
            'success' => true,
            'response' => $task
        ]);
    }

    //searcher (manage)
    public function searcher(Request $request){

        //authorization
        $user = JWTAuth::User();
        //$this->authorization($user, $this->subject_class, 'manage');
        //end authorization
  
        //validate
        $validator = MkVAlidator::make($request->all(), [
          'name' => 'string|min:1|max:30',
          'description' => 'string|min:1|max:30',
          'type' => 'string|min:1|max:30',
          'deadline' => 'date_format:Y-m-d',
          'status' => 'string|min:1|max:30',
          'estimated_hours' => 'numeric|min:0',
          'worked_hours' => 'numeric|min:0',
          'link' => 'string|min:1|max:30',
          'user_id' => 'integer|min:1',
          'archived' => 'boolean',
  
          'order_by' => 'string|in:id,archived,user_id,name,description,type,deadline,status,estimated_hours,worked_hours,link',
          'order' => 'string|in:asc,desc',
          'page' => 'integer|min:1|max:1000',
          'limit' => 'integer|min:1|max:1000',
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
          $query = DB::table('tasks')
          ->select('id', 'archived','user_id','name','description','type','deadline','status','estimated_hours','worked_hours','link','created_at' ,'updated_at');
  
          //filters
          foreach ($validatedData as $key => $value) {
            switch ($key) {
                case 'archived':
                    $query->where($key, $value);
                    break;
                case 'user_id':
                    $query->where($key, $value);
                    break;
                case 'name':
                    $query->where($key, 'like', "%$value%");
                    break;
                case 'description':
                    $query->where($key, $value);
                    break;
                case 'type':
                    $query->where('subtotal', '>=', $value);
                    break;
                case 'deadline':
                    $query->where('subtotal', '<=', $value);
                    break;
                case 'status':
                    $query->where($key, $value);
                    break;
                case 'estimated_hours':
                    $query->where('iva', '>=', $value);
                    break;
                case 'worked_hours':
                    $query->where('iva', '<=', $value);
                    break;
                case 'link':
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
}
