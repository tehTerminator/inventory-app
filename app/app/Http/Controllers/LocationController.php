<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\StockLocationInfo;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    /**
     * Display a listing of all locations.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $locations = Location::all();

        return response()->json($locations);
    }

    public function indexLocationProducts(Request $request) {

    }

    public function indexUsers(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|exists:locations,id'
        ]);

        $id = $request->input('id');

        $users = User::whereIn('id', function($query) use ($id) {
            $query->select('user_id')
            ->from('location_users')
            ->where('location_id', $id);
        })->get();

        return response()->json($users);
        // return response($request->id);
    }

    public function indexInventory(Request $request) {
        $this->validate($request, [
            'id' => 'required|exists:locations,id'
        ]);
        $id = $request->id;

        $data = StockLocationInfo::where('location_id', $id)->with('product')->get();
        return response()->json($data);
    }

    /**
     * Display the specified location.
     *
     * @param  \App\Models\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function find($id)
    {
        $location = Location::findOrFail($id);
        return response()->json($location);
    }

    public function findBytitle(string $title) {
        $locations = Location::where('title', 'LIKE', $title)->get();
        return response()->json($locations);
    }

    /**
     * Store a newly created location in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required|unique:locations'
        ]);

        $location = new Location();
        $location->title = $request->input('title');
        $location->save();

        return response()->json($location, 201);
    }

    /**
     * Update the specified location in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $id = $request->input('id');
        $title = $request->input('title');
        $location = Location::findOrFail($id);
        $location->title = $title;
        $location->save();

        return response()->json($location, 200);
    }

    /**
     * Remove the specified location from storage.
     *
     * @param  \App\Models\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function destroy(Location $location)
    {
        $location->delete();

        return response()->json([], 204);
    }
}