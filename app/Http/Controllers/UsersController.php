<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    public function index()
    {
        $users = User::all();

        return response()->json($users);
    }

    public function store(Request $request)
    {

        $this->validate($request, [
            'name' => 'required|string',
            'username' => 'required|unique:Users,username',
            'password' => 'required',
            'role_id' => 'numeric'
        ]);

        $user = new User();
        $user->name = $request->input('name');
        $user->username = $request->input('username');
        $user->password = Hash::make($request->input('password'));
        $user->mobile = $request->input('mobile');
        $user->role = $request->input('role', 2);
        $user->save();

        return response()->json($user);
    }

    public function show(User $user)
    {
        return response()->json($user);
    }


    public function update(Request $request, User $user)
    {
        $user->name = $request->input('name');
        $user->username = $request->input('username');
        $user->email = $request->input('email');
        $user->password = $request->input('password');
        $user->save();

        return response()->json($user);
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
            return response()->json($user->fresh());
        }

        return response()->json(['message' => 'Invalid Password'], 401);
    }
}
