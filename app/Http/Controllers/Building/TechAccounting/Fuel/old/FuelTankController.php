<?php

namespace App\Http\Controllers\Building\TechAccounting\Fuel\Old;

use Illuminate\View\View;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Requests\Building\TechAccounting\FuelTank\FuelTankLevelRequest;
use App\Http\Requests\Building\TechAccounting\FuelTank\FuelTankRequest;
use App\Models\TechAcc\FuelTank\FuelTank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FuelTankController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request): View
    {
        $data = [];
        $paginated = FuelTank::filter($request->toArray())->paginate(15);
        $data['fuel_tanks'] = $paginated->items();
        $data['fuel_tanks_count'] = $paginated->total();
        $data['class'] = FuelTank::getModel();

        return view('tech_accounting.fuel.old.capacities', ['data' => $data]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  FuelTankStoreRequest  $request
     * @return array
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(FuelTankRequest $request)
    {
        $this->authorize('store', FuelTank::class);

        $fuelTank = FuelTank::create($request->all());

        return [
            'status' => 'success',
            'data' => $fuelTank->load('object'),
        ];
    }

    public function show(FuelTank $fuelTank)
    {
        return [
            'fuelTank' => $fuelTank,
        ];
    }

    /**
     * Update the specified resource in storage.
     *
     * @return array
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, FuelTank $fuelTank)
    {
        $this->authorize('update', $fuelTank);

        $fuelTank->update($request->except(['fuel_level']));

        return [
            'status' => 'success',
            'data' => $fuelTank->load('object'),
        ];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return array
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(FuelTank $fuelTank)
    {
        $this->authorize('destroy', $fuelTank);

        $fuelTank->close();

        return [
            'status' => 'success',
        ];
    }

    public function getFuelTanks(Request $request): Response
    {
        $fuelTankQuery = FuelTank::query();

        if ($request->q) {
            $fuel_tanks = $fuelTankQuery->where('tank_number', 'like', $request->q)->take(10)->get();
        } else {
            $fuel_tanks = $fuelTankQuery->take(10)->get();
        }

        return response($fuel_tanks);
    }

    public function getFuelTanksByObject(Request $request): Response
    {
        $fuelTankQuery = FuelTank::filter($request->all());

        if ($request->q) {
            $fuel_tanks = $fuelTankQuery->where('tank_number', 'like', $request->q)->take(10)->get();
        } else {
            $fuel_tanks = $fuelTankQuery->take(10)->get();
        }

        return response($fuel_tanks);
    }

    public function getFuelTanksPaginated(Request $request): Response
    {
        $output = [];
        parse_str(parse_url($request->url)['query'] ?? '', $output);

        $paginated = FuelTank::filter($output)->paginate(15);

        $fuelTankCount = $paginated->total();
        $fuelTanks = $paginated->items();

        return response(['fuelTanks' => $fuelTanks, 'fuelTanksCount' => $fuelTankCount]);
    }

    public function changeFuelLevel(FuelTankLevelRequest $request, FuelTank $fuelTank)
    {
        $fuelTank->fuel_level = $request->fuel_level;

        if ($fuelTank->isDirty('fuel_level')) {
            $fuelTank->operations()->create([
                'author_id' => Auth::user()->id,
                'object_id' => $fuelTank->object_id,
                'value' => ($fuelTank->fuel_level - $fuelTank->getOriginal('fuel_level')),
                'type' => 3,
                'description' => $request->description,
                'operation_date' => now(),
            ]);
        }

        return ['status' => 'success'];
    }

    public function display_trashed(Request $request): View
    {
        $this->authorize('tech_acc_fuel_tanks_trashed');

        $data = [];
        $paginated = FuelTank::filter($request->toArray())->onlyTrashed()->paginate(15);
        $data['fuel_tanks'] = $paginated->items();
        $data['fuel_tanks_count'] = $paginated->total();
        $data['class'] = FuelTank::getModel();

        return view('tech_accounting.fuel.old.trashed_capacities', ['data' => $data]);
    }

    public function getTrashedFuelTanksPaginated(Request $request): Response
    {
        $output = [];
        parse_str(parse_url($request->url)['query'] ?? '', $output);

        $paginated = FuelTank::onlyTrashed()->filter($output)->paginate(15);

        $fuelTankCount = $paginated->total();
        $fuelTanks = $paginated->items();

        return response(['fuelTanks' => $fuelTanks, 'fuelTanksCount' => $fuelTankCount]);
    }

    public function show_trashed($fuelTankId)
    {
        return [
            'fuelTank' => FuelTank::onlyTrashed()->with('trashed_operations')->findOrFail($fuelTankId),
        ];
    }
}
