<?php

namespace App\Http\Controllers;

use App\Services\BundleService;
use Illuminate\Http\Request;

class BundleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function select() {
        BundleService::selectBundle();
    }

    public function store(Request $request) {
        $this->validate($request, [
            'title' => 'required|unique:App\Models\Bundle,title|string',
            'rate' => 'required|min:1|integer',
        ]);

        return response()->json(
            BundleService::createBundle($request->title, $request->rate)
        );
    }

    public function update(Request $request) {
        $this->validate($request, [
            'id' => 'required|integer|min:1',
            'title' => 'required|unique:App\Models\Bundle|string',
            'rate' => 'required|min:1|integer',
        ]);

        return response()->json(
            BundleService::updateBundle(
                $request->id,
                $request->title,
                $request->rate
            )
        );
    }

    public function delete(Request $request) {
        $this->validate($request, [
            'id' => 'required|integer|min:1'
        ]);
        BundleService::deleteBundle($request->id);
    }


    public function storeTemplate(Request $request, int $id) {
        $this->validate($request, [
            'item_id' => 'required|min:1|integer',
            'kind' => 'required|in:PRODUCT,LEDGER',
            'rate' => 'required|min:1|numeric',
            'quantity' => 'required|integer|min:1'
        ]);

        return response()->json(
            BundleService::createTemplate(
                $id,
                $request->input('item_id'),
                $request->input('kind'),
                $request->input('rate'),
                $request->input('quantity')
            )
        );
    }

    public function deleteTemplate(Request $request) {
        $this->validate($request, [
            'id' => [
                'exists:App\Models\BundleTemplate,id',
                'required',
                'integer',
                'min:1'
            ]
        ]);
        BundleService::deleteTemplate($request->id);
    }
}