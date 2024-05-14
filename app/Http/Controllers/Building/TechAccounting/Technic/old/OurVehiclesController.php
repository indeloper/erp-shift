<?php

namespace App\Http\Controllers\Building\TechAccounting\Technic\old;

use App\Http\Controllers\Controller;
use App\Http\Requests\OurVehicleRequests\OurVehicleDeleteRequest;
use App\Http\Requests\OurVehicleRequests\OurVehicleStoreRequest;
use App\Http\Requests\OurVehicleRequests\OurVehicleUpdateRequest;
use App\Models\FileEntry;
use App\Models\TechAcc\Vehicles\OurVehicles;
use App\Models\TechAcc\Vehicles\VehicleCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OurVehiclesController extends Controller
{
    public function index(VehicleCategories $vehicle_category)
    {
        return view('tech_accounting.vehicles.vehicles_list', [
            'data' => [
                'owners' => OurVehicles::OWNERS,
                'category' => $vehicle_category,
                'vehicles' => $vehicle_category->vehicles,
            ],
        ]);
    }

    public function store(OurVehicleStoreRequest $request)
    {
        DB::beginTransaction();

        $vehicle = OurVehicles::create($request->all());
        $vehicle->parameters()->createMany($request->parameters ?? []);
        $vehicle->documents()->saveMany(FileEntry::find($request->file_ids) ?? []);
        $vehicle->refresh()->load('parameters', 'documents');

        DB::commit();

        return response()->json([
            'result' => 'success',
            'data' => $vehicle,
        ]
        );
    }

    public function update(OurVehicleUpdateRequest $request, VehicleCategories $vehicle_category, OurVehicles $our_vehicle)
    {
        DB::beginTransaction();

        $our_vehicle->update($request->all());
        $our_vehicle->updateParameters($request->parameters ?? []);
        $our_vehicle->documents()->saveMany(FileEntry::find($request->file_ids) ?? []);
        $our_vehicle->refresh()->load('parameters', 'documents');

        DB::commit();

        return response()->json([
            'result' => 'success',
            'data' => $our_vehicle,
        ]);
    }

    public function destroy(OurVehicleDeleteRequest $request, VehicleCategories $vehicle_category, OurVehicles $our_vehicle)
    {
        DB::beginTransaction();

        $our_vehicle->delete();

        DB::commit();

        return response()->json(true);
    }

    public function get_vehicles(Request $request)
    {
        if ($request->q != '') {
            $vehicles = OurVehicles::where('model', 'like', "%{$request->q}%")
                ->orWhere('mark', 'like', "%{$request->q}%")->get();
        } else {
            $vehicles = OurVehicles::take(20)->get();
        }

        return response([
            'data' => $vehicles,
        ]);
    }

    public function index_trashed($vehicle_category)
    {
        $vehicle_category = VehicleCategories::withTrashed()->findOrFail($vehicle_category);

        return view('tech_accounting.vehicles.trashed_vehicles_list', [
            'data' => [
                'owners' => OurVehicles::OWNERS,
                'category' => $vehicle_category,
                'vehicles' => $vehicle_category->vehicles()->onlyTrashed()->get(),
            ],
        ]);
    }
}
