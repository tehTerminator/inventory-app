<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Location;

class UsersController extends Controller
{
    public function index()
    {
        $users = User::select(['id', 'name'])->get();

        return response()->json($users);
    }

    public function store(Request $request)
    {

        $this->validate($request, [
            'name' => 'required|string',
            'username' => 'required|unique:users,username',
            'password' => 'required',
            'role_id' => 'numeric'
        ]);

        $user = new User();
        $user->name = $request->input('name');
        $user->username = $request->input('username');
        $user->password = Hash::make($request->input('password'));
        $user->mobile = $request->input('mobile');
        $user->role_id = $request->input('role', 2);
        $user->save();

        return response()->json($user);
    }

    public function show(User $user)
    {
        return response()->json($user);
    }

    public function update(Request $request)
    {
        // // return response('Hello');
        try {
            $this->validate($request, [
                'username' => 'required|string',
                'name' => 'required|string',
                'mobile' => 'required|string',
                'oldPassword' => 'required|string',
                'newPassword' => 'nullable|string',
            ]);


            $user = User::where('username', $request->username)->first();

            if(empty($user) || $user->login($request->oldPassword) == false) {
                return response('No Such User', 401);
            }

        
            // Update user details
            $user->name = $request->name;
            $user->mobile = $request->mobile;

            // Update password if newPassword is provided and its length is greater than 6
            if ($request->newPassword != null && strlen($request->newPassword) > 6) {
                $user->password = Hash::make($request->newPassword);
            }

            $user->save();
            return response()->json($user);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while updating the user', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json($user);
    }

    public function authenticate(Request $request)
    {
        $this->validate($request, [
            'username' => 'required|string',
            'password' => 'required'
        ]);


        $user = User::where('username', $request->username)->first();


        if (empty($user)) {
            return response('No Such User', 401);
        }

        if( $user->login($request->password) )
        {
            $user->refresh();
            return response()->json($user);
        }

        return response()->json(['message' => 'Invalid Password'], 401);
    }

    public function indexLocations(){
        $user_id = Auth::user()->id;

        $location = Location::whereIn('id', function($query) use ($user_id) {
            $query->select('location_id')->from('location_users')->where('user_id', $user_id);
        })->get();

        return response()->json($location);
    }
}
