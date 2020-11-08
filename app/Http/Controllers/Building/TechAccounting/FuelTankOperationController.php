<?php

namespace App\Http\Controllers\Building\TechAccounting;

use App\Http\Requests\Building\FuelTank\UpdateFuelTankOperation;
use App\Http\Requests\Building\TechAccounting\FuelTank\StoreFuelTankOperation;
use App\Models\TechAcc\FuelTank\FuelTank;
use App\Models\TechAcc\FuelTank\FuelTankOperation;
use App\Models\TechAcc\OurTechnic;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\User;
use App\Models\ProjectObject;


class FuelTankOperationController extends Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->authorizeResource(FuelTankOperation::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $paginated = FuelTankOperation::filter($request->all())->paginate(10);
        $fuelTankOperationCount = $paginated->total();
        $fuelTankOperations = $paginated->items();

        return view('tech_accounting.fuel.accounting', ['data' => [
            'operations' => $fuelTankOperations,
            'total_count' => $fuelTankOperationCount,
            'owners' => OurTechnic::$owners,
            'types' => FuelTankOperation::getModel()->types_json,
        ]]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreFuelTankOperation $request)
    {
        $operation = FuelTankOperation::create($request->all());

        if ($request->file_ids) {
            $operation->attachFiles($request->file_ids);
        }

        $operation->loadMissing(
            'fuel_tank',
            'author',
            'our_technic',
            'contractor',
            'object'
        );

        return response(['data' => ['operation' => $operation]]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TechAcc\FuelTank\FuelTankOperation  $FuelTankOperation
     * @return \Illuminate\Http\Response
     */
    public function show(FuelTankOperation $FuelTankOperation)
    {
        $FuelTankOperation->loadMissing('fuel_tank',
            'author',
            'object',
            'contractor',
            'videos',
            'not_videos',
            'our_technic',
            'history'
            );

        return response(['data' => ['operation' => $FuelTankOperation]]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TechAcc\FuelTank\FuelTankOperation  $FuelTankOperation
     * @return \Illuminate\Http\Response
     */
    public function edit(FuelTankOperation $FuelTankOperation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TechAcc\FuelTank\FuelTankOperation  $FuelTankOperation
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateFuelTankOperation $request, FuelTankOperation $FuelTankOperation)
    {
        $FuelTankOperation->update($request->all());

        if ($request->file_ids) {
            $FuelTankOperation->attachFiles($request->file_ids);
        }
        $FuelTankOperation->loadMissing(
            'fuel_tank',
            'author',
            'our_technic',
            'contractor',
            'object'
        );

        $FuelTankOperation->refresh();

        return response(['data' => ['operation' => $FuelTankOperation]]);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param FuelTankOperation $FuelTankOperation
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(FuelTankOperation $FuelTankOperation)
    {
        $FuelTankOperation->delete();

        return response(['data' => ['result' => 'success']]);
    }


    public function getFuelTankOperationsPaginated(Request $request)
    {
        $output = [];
        if ($request->url) {
            parse_str(parse_url($request->url)['query'], $output);
        }

        if (empty($output)) {
            $output = $request->all();
        }
        $paginated = FuelTankOperation::filter($output)->paginate(10);
        $fuelTankOperationCount = $paginated->total();
        $fuelTankOperations = $paginated->items();

        return response(['fuelTankOperations' => $fuelTankOperations, 'fuelTankOperationCount' => $fuelTankOperationCount]);
    }


    public function createReport(Request $request)
    {
        $operations = FuelTankOperation::filter($request->toArray())->orderBy('operation_date')->get();
        $responsible_user = User::with('group')->findOrFail($request->responsible_receiver_id);
        $object = ProjectObject::find($request->object_id);
        $fuelTank = FuelTank::find($request->fuel_tank_id);
        $mode = $request->mode;
        $first_operation = $operations->first();
        $start_value = $first_operation->result_value ?? 0 -  ($first_operation ? $first_operation->value_diff : 0);

        if ($start_value < 0) {
            $start_value = 0;
        }

        return view('tech_accounting.fuel.report', compact(['operations', 'responsible_user', 'object', 'start_value', 'fuelTank', 'mode']));
    }

    public function getFuelTanksOperations(Request $request)
    {
        $operations = FuelTankOperation::filter($request->toArray())->count();

        return response()->json($operations);
    }
}
