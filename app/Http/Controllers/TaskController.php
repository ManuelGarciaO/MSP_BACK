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
            'link' => ['string', 'max:255', 'min:1'],
            'user_id' => ['required', 'integer', 'min:1', 'exists:users,id'],
        ]);

        //new task
        $task = new Task();

        //fill
        $task->fill($validatedData);

        //save data
        $task->save();

        return response()->json([
            'success' => true,
            'response' => $task,
            'user' => $user
        ]);
    }

    //get by id
    public function getById(Request $request)
    {
        //authorization
        $user = JWTAuth::User();
        $this->authorization($user, $this->subject_class, 'read');
        //end authorization

        //find resource
        $inventory = Inventory::find($request->id) ?? self::abort(false, null, 404, 404);

        return response()->json([
        'success' => true,
        'response' => $inventory
    ]);
    }
}
