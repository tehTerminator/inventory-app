<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Location;

class LocationsController extends Controller {
    public function __construct() {
    }

    public function fetchAll() {
        return response()->json( Location::all() );
    }

    public function fetchOne( Request $request ) {
        $this->validate( $request, [
            'id' => [ 'required', 'numeric', 'exists:locations,id' ]
        ] );

        return response()->json( Location::find( $request->id ) );
    }

    public function create( Request $request ) {
        $this->validate( $request, [
            'title' => [ 'required', 'unique:locations,title', 'string' ]
        ] );

        $location = Location::create( [ 'title' => $request->title ] );
        return response()->json( $location );
    }

    public function update( Request $request ) {
        $this->validate( $request, [
            'title' => [ 'required', 'unique:locations,title', 'string' ],
            'id' => [ 'required', 'exists:locations,id' ]
        ] );

        $location = Location::findOrFail( $request->input( 'id' ) );
        $location->title = $request->input( 'title' );
        $location->save();
        return response()->json( $location );
    }

    public function delete( Request $request ) {
        $this->validate( $request, [
            'id' => [ 'required', 'exists:locations,id' ]
        ] );
    }
}