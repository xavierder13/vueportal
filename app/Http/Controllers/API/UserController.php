<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Carbon\Carbon;
use DB;
use Hash;
use Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\User;
use App\Branch;
use Spatie\Activitylog\Models\Activity;


class UserController extends Controller
{
    public function index()
    {   
        $users = User::with('roles')
                     ->with('roles.permissions')
                     ->with('branch')
                     ->get();
        $roles = Role::with('permissions')->orderBy('id', 'Asc')->get();
        $branches = Branch::all();
        return response()->json(['users' => $users, 'roles' => $roles, 'branches' => $branches], 200);
    }

    public function create() 
    {
        $roles = Role::with('permissions')->orderBy('id', 'Asc')->get();
        $branches = Branch::all();
        return response()->json(['roles' => $roles, 'branches' => $branches], 200);
    }

    public function store(Request $request)
    {   
        // return $request;
        
        $rules = [
            'name.required' => 'Please enter name',
            'email.required' => 'Email is required',
            'email.email' => 'Please enter a valid email',
            'email.unique' => 'Email already exists',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be atleast 8 characters',
            'password.same' => 'Password and Confirm Password did not match',
            'confirm_password.required' => 'Confirm Password is required',
            'branch_id.required' => 'Branch is required',
            'branch_id.integer' => 'Branch must be an integer',
        ];

        $valid_fields = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:8|same:confirm_password',
            'confirm_password' => 'required',
            'branch_id' => 'required|integer',
        ];

        $validator = Validator::make($request->all(), $valid_fields, $rules);

        if($validator->fails())
        {   
            return response()->json($validator->errors(), 200);
        }

        $user = new User();
        $user->name = $request->get('name');
        $user->email = $request->get('email');
        $user->password = Hash::make($request->get('password'));
        $user->branch_id = $request->get('branch_id');
        $user->active = $request->get('active');
        $user->save();

        $user->assignRole($request->get('roles'));

        $user = User::with('roles')
                    ->with('roles.permissions')
                    ->with('branch')
                    ->where('id', '=', $user->id)->first();

        return response()->json(['success' => 'Record has successfully added', 'user' => $user], 200);
    }

    public function edit($user_id)
    {
        $user = User::find($user_id);
        $service_signatories = DB::table('service_signatories')
                                ->join('services', 'service_signatories.serviceid', '=', 'services.id')
                                ->select(DB::raw('services.*'))
                                ->where('userid', $user_id)
                                ->get();
        //if record is empty then display error page
        if(empty($user->id))
        {
            return abort(404, 'Not Found');
        }
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name','name')->all();
        
        return response()->json([
            'user' => $user,
            'service_signatories' => $service_signatories
        ], 200);

    }

    public function update(Request $request, $user_id)
    {   
        // return $request;

        $rules = [
            'name.required' => 'Please enter name',
            'branch_id.required' => 'Branch is required',
            'branch_id.integer' => 'Branch must be an integer',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be atleast 8 characters',
            'password.same' => 'Password and Confirm Password did not match',
            'confirm_password.required' => 'Confirm Password is required',
        ];

        $valid_fields = [
            'name' => 'required|string|max:255',
            'branch_id' => 'required|integer',
        ];

        if($request->get('password') || $request->get('confirm_password'))
        {
            $valid_fields['password'] = 'required|string|min:8|same:confirm_password';
            $valid_fields['confirm_password'] = 'required';
        }


        $validator = Validator::make($request->all(), $valid_fields, $rules);

        if($validator->fails())
        {   
            return response()->json($validator->errors(), 200);
        }

        $user = User::find($user_id);

        //if record is empty then display error page
        if(empty($user->id))
        {
            return abort(404, 'Not Found');
        }

        $user->name = $request->get('name');
        if(!empty($request->get('password')))
        {
            $user->password = Hash::make($request->get('password'));
        }
        $user->branch_id = $request->get('branch_id');
        $user->active = $request->get('active');

        $user->save();
        
        DB::table('model_has_roles')->where('model_id', $user_id)->delete();
        
        $user->assignRole($request->get('roles'));

        $user = User::with('roles')
                    ->with('roles.permissions')
                    ->with('branch')
                    ->where('id', '=', $user_id)->first();
        
        $user_roles = $user->roles->pluck('name')->all();

        $user_permissions = $user->getAllPermissions()->pluck('name');

        return response()->json([
            'success' => 'Record has been updated', 
            'user_roles' => $user_roles, 
            'user_permissions' => $user_permissions,
            'user' => $user
        ], 200);
    }

    public function delete(Request $request)
    {
        $user = User::find($request->get('user_id'));

        //if record is empty then display error page
        if(empty($user->id))
        {
            return abort(404, 'Not Found');
        }

        // admin user
        if($user->id == 1)
        {
            return abort(403, 'Forbidden');
        }

        $user->delete();

        return response()->json(['success' => 'Record has been deleted'], 200);
    }

    public function userRolesPermissions()
    {
        $user_roles = Auth::user()->roles->pluck('name')->all();

        $user_permissions = Auth::user()->getAllPermissions()->pluck('name');

        return response()->json([
            'user_roles' => $user_roles, 
            'user_permissions' => $user_permissions,
        ], 200);
    }
}
