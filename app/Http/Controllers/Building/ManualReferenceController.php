<?php

namespace App\Http\Controllers\Building;

use App\Http\Controllers\Controller;
use App\Models\Manual\ManualReference;
use Illuminate\Http\Request;

class ManualReferenceController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Manual\ManualReference  $manualReference
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ManualReference $manualReference)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Manual\ManualReference  $manualReference
     * @return \Illuminate\Http\Response
     */
    public function destroy(ManualReference $manualReference)
    {
        //
    }
}
