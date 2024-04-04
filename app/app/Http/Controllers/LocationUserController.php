<?php 

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LocationUser;

class LocationUserController extends Controller
{
    public function store(Request $request)
    {
        $this->validate($request, [
            'user' => 'required|exists:users,id',
            'location' => 'required|exists:locations,id',
        ]);

        $location_id = $request->input('location');
        $user_id = $request->input('user');


        $location = LocationUser::where('location_id', $location_id)->where('user_id', $user_id)->first();

        if (empty($location)) {
            $locationUser = LocationUser::create([
                'user_id' => $user_id,
                'location_id' => $location_id
            ]);
            return response()->json($locationUser, 201); // 201 Created status code
        }

        return response()->json($location);


    }
}
