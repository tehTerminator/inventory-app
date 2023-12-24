<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function indexCustomer(Request $request)
    {
        $contact = Contact::customer();
        $title = $request->input('title', NULL);
        return response()->json($contact->where('title', 'LIKE', "$title%")->take(5)->get());
    }

    public function indexSupplier(Request $request)
    {
        $contact = Contact::supplier();
        $title = $request->input('title', NULL);
        return response()->json($contact->where('title', 'LIKE', "$title%")->take(5)->get());
    }

    public function indexContacts(Request $request)
    {
        $title = $request->input('title', NULL);
        $contact = Contact::where('title', 'LIKE', "$title%")
        ->take(5)
        ->select(['id', 'title'])
        ->get();
        return response()->json($contact);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        // return response('ContactController@store');
        $this->validate($request, [
            'title' => 'required|unique:App\Models\Contact,title',
            'address' => 'required',
            'mobile' => 'required',
        ]);

        $contact = new Contact();

        $contact->title = $request->input('title');
        $contact->address = $request->input('address');
        $contact->mobile = $request->input('mobile');
        $contact->kind = $request->input('kind');

        $contact->save();

        return response()->json($contact);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $contact = Contact::findOrFail($id);
        return response()->json($contact);
    }

    public function searchSupplier(Request $request)
    {
        $title = $request->input('title');
        $this->search($title, 'SUPPLIER');
    }

    private function search($title, $kind)
    {
        $contact = Contact::where('title', 'LIKE', $title)
            ->where('KIND', $kind)
            ->take(10)
            ->toSql();
        return response($contact);
        // return response()->json($contact);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Lumen\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $contact = Contact::findOrFail($id);
        $request->validate([
            'title' => 'required',
            'address' => 'required',
            'mobile' => 'required',
        ]);

        $contact = Contact::findOrFail($id);

        $contact->title = $request->input('title');
        $contact->address = $request->input('address');
        $contact->mobile = $request->input('mobile');

        $contact->save();

        return response()->json($contact);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $contact = Contact::findOrFail($id);
        $contact->delete();
        return response('Contact Deleted Successfully');
    }
}
