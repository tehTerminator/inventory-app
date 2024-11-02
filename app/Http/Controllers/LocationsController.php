<?php 
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LocationsController extends Controller {
    public function __construct() { }

    public function create(Request $request) {
        $this->validate($request, [
            'title' => ['required', 'unique:locations,title', 'string']
        ]);

        $location = Location::create(['title' => $request->title]);
        return response()->json($location);
    }

    public function delete(Request $request) {
        $this->validate($request, [
            'id' => ['required', 'exists:locations,id']
        ]);
    }
}