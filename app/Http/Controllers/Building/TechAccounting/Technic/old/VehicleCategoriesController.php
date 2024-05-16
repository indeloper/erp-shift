<?php

namespace App\Http\Controllers\Building\TechAccounting\Technic\old;

use App\Http\Controllers\Controller;
use App\Http\Requests\VehicleCategoryRequests\VehicleCategoryDestroyRequest;
use App\Http\Requests\VehicleCategoryRequests\VehicleCategoryStoreRequest;
use App\Http\Requests\VehicleCategoryRequests\VehicleCategoryUpdateRequest;
use App\Models\TechAcc\Vehicles\VehicleCategories;
use App\Services\AuthorizeService;
use Illuminate\Support\Facades\DB;

class VehicleCategoriesController extends Controller
{
    public function index()
    {
        $vehicle_categories = VehicleCategories::withCount('vehicles')->get();

        return view('tech_accounting.vehicles.index', compact('vehicle_categories'));
    }

    public function create()
    {
        (new AuthorizeService())->authorizeVehicleCategoryCreate();

        return view('tech_accounting.vehicles.create_category');
    }

    public function store(VehicleCategoryStoreRequest $request)
    {
        DB::beginTransaction();

        $vehicle_category = VehicleCategories::create($request->all());
        $vehicle_category->characteristics()->createMany($request->characteristics);

        DB::commit();

        return response()->json([
            'result' => 'success',
            'redirect' => route('building::vehicles::vehicle_categories.show', $vehicle_category->id),
        ]);
    }

    public function show(VehicleCategories $vehicle_category)
    {
        return view('tech_accounting.vehicles.category_card', compact('vehicle_category'));
    }

    public function edit(VehicleCategories $vehicle_category)
    {
        (new AuthorizeService())->authorizeVehicleCategoryEdit();

        return view('tech_accounting.vehicles.edit_category', compact('vehicle_category'));
    }

    public function update(VehicleCategoryUpdateRequest $request, VehicleCategories $vehicle_category)
    {
        DB::beginTransaction();

        $vehicle_category->update($request->all());
        if (! empty($request->deleted_characteristic_ids)) {
            $vehicle_category->deleteCharacteristics($request->deleted_characteristic_ids);
        }
        $vehicle_category->updateCharacteristics($request->characteristics ?? []);

        DB::commit();

        return response()->json([
            'result' => 'success',
            'redirect' => route('building::vehicles::vehicle_categories.show', $vehicle_category->id),
        ]);
    }

    public function destroy(VehicleCategoryDestroyRequest $request, VehicleCategories $vehicle_category)
    {
        DB::beginTransaction();

        $vehicle_category->delete();

        DB::commit();

        return response()->json(true);
    }

    public function display_trashed()
    {
        $vehicle_categories = VehicleCategories::onlyTrashed()->withCount('trashed_vehicles')->get();

        return view('tech_accounting.vehicles.index_trashed', compact('vehicle_categories'));
    }

    public function show_trashed($vehicle_category)
    {
        $vehicle_category = VehicleCategories::onlyTrashed()->findOrFail($vehicle_category);

        return view('tech_accounting.vehicles.trashed_category_card', compact('vehicle_category'));
    }
}
