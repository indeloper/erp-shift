<?php

namespace App\Http\Controllers\q3wMaterial;

use App\Models\q3wMaterial\q3wMaterialAccountingType;
use App\Models\q3wMaterial\q3wMaterialBrand;
use App\Models\q3wMaterial\q3wMaterialBrandsRelation;
use App\Models\q3wMaterial\q3wMaterialStandard;
use App\Models\q3wMaterial\q3wMeasureUnit;
use App\Models\q3wMaterial\q3wStandardPropertiesRelations;
use App\Models\q3wMaterial\q3wStandardProperty;
use http\Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class q3wMaterialStandardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index()
    {
        return view('materials.material-standard')->with([
            'measureUnits' => q3wMeasureUnit::all('id','value')->toJson(JSON_UNESCAPED_UNICODE),
            'accountingTypes' => q3wMaterialAccountingType::all('id','value')->toJson(JSON_UNESCAPED_UNICODE),
            'materialTypes' => DB::table('q3w_material_types as a')
                ->leftJoin('q3w_measure_units as b', 'a.measure_unit', '=', 'b.id')
                ->get(['a.id as id', 'a.name as name', 'b.value as measure_unit_value'])
                                ->toJson(JSON_UNESCAPED_UNICODE)
        ]);
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
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        $data = json_decode($request->all()["data"], JSON_OBJECT_AS_ARRAY);

        if (isset($data["standard_properties"])) {
            $standardProperties = $data["standard_properties"];
            unset($data["standard_properties"]);
        }

        if (isset($data["brands"])) {
            $brands = $data["brands"];
            unset($data["brands"]);
        }

        $materialStandard = new q3wMaterialStandard($data);
        $materialStandard->save();

        if (isset($standardProperties)){
            q3wStandardPropertiesRelations::where('standard_id', $materialStandard->id)->forceDelete();

            foreach ($standardProperties as $property) {
                $relation = new q3wStandardPropertiesRelations([
                    "standard_property_id" => $property,
                    "standard_id" => $materialStandard->id
                ]);

                $relation->save();
            }
        }

        if (isset($brands)){
            q3wMaterialBrandsRelation::where('standard_id', $materialStandard->id)->forceDelete();

            foreach ($brands as $editedBrand) {
                $brand = new q3wMaterialBrandsRelation([
                    "brand_id" => $editedBrand,
                    "standard_id" => $materialStandard->id
                ]);

                $brand->save();
            }
        }

        DB::commit();

        return response()->json([
            'result' => 'ok',
            'key' => $materialStandard->id
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @return string
     */
    public function show(Request $request): string
    {
        $options = json_decode($request['data']);

        return (new q3wMaterialStandard())
            ->dxLoadOptions($options)
            ->leftJoin('q3w_material_types as b', 'q3w_material_standards.material_type', '=', 'b.id')
            ->leftJoin('q3w_measure_units as d', 'b.measure_unit', '=', 'd.id')
            ->leftJoin('q3w_standard_properties_relations', 'q3w_material_standards.id', '=', 'q3w_standard_properties_relations.standard_id')
            ->leftJoin('q3w_material_brands_relations', 'q3w_material_standards.id', '=', 'q3w_material_brands_relations.standard_id')
            ->groupBy(['q3w_material_standards.id'])
            ->select(['q3w_material_standards.*',
                DB::Raw('GROUP_CONCAT(DISTINCT `standard_property_id`) as `standard_property_ids`'),
                DB::Raw('GROUP_CONCAT(DISTINCT `brand_id`) as `brand_ids`'),
                'b.measure_unit',
                'd.value as measure_unit_value'])
            ->get()
            ->toJSON(JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\q3wMaterial\q3wMaterialStandard  $q3wMaterialStandard
     * @return \Illuminate\Http\Response
     */
    public function edit(q3wMaterialStandard $q3wMaterialStandard)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\q3wMaterial\q3wMaterialStandard  $q3wMaterialStandard
     * @return JsonResponse
     */
    public function update(Request $request, q3wMaterialStandard $q3wMaterialStandard)
    {
        DB::beginTransaction();

        $id = $request->all()["key"];
        $modifiedData = json_decode($request->all()["modifiedData"], JSON_OBJECT_AS_ARRAY);

        $materialStandard = q3wMaterialStandard::findOrFail($id);

        if (isset($modifiedData["standard_properties"])){
            $standardProperties = $modifiedData["standard_properties"];

            q3wStandardPropertiesRelations::where('standard_id', $materialStandard->id)->forceDelete();

            foreach ($standardProperties as $property) {
                $relation = new q3wStandardPropertiesRelations([
                   "standard_property_id" => $property,
                   "standard_id" => $materialStandard->id
                ]);

                $relation->save();
            }

            unset($modifiedData["standard_properties"]);
        }

        if (isset($modifiedData["brands"])){
            $brands = $modifiedData["brands"];

            q3wMaterialBrandsRelation::where('standard_id', $materialStandard->id)->forceDelete();

            foreach ($brands as $editedBrand) {
                $brand = new q3wMaterialBrandsRelation([
                    "brand_id" => $editedBrand,
                    "standard_id" => $materialStandard->id
                ]);

                $brand->save();
            }

            unset($modifiedData["brands"]);
        }

        $materialStandard -> update($modifiedData);

        DB::commit();

        return response()->json([
            'result' => 'ok'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        $id = $request->all()["key"];

        $materialStandard = q3wMaterialStandard::find($id);
        $materialStandard->delete();

        return response()->json([
            'result' => 'ok'
        ], 200);
    }

    public function list(Request $request): string
    {
        $dxLoadOptions = json_decode($request['data'])->dxLoadOptions;

        return (new q3wMaterialStandard())->dxLoadOptions($dxLoadOptions)
            ->leftJoin('q3w_material_types as b', 'q3w_material_standards.material_type', '=', 'b.id')
            ->leftJoin('q3w_measure_units as d', 'b.measure_unit', '=', 'd.id')
            ->orderBy('selection_counter', 'desc')
            ->orderBy('q3w_material_standards.name')
            ->get(['q3w_material_standards.id',
                'q3w_material_standards.id as standard_id',
                'q3w_material_standards.name as standard_name',
                'q3w_material_standards.weight',
                'q3w_material_standards.material_type',
                'q3w_material_standards.participates_in_search',
                'q3w_material_standards.name',
                'q3w_material_standards.selection_counter',
                'b.name as material_type_name',
                'b.measure_unit',
                'b.accounting_type',
                'd.value as measure_unit_value'])
            ->toJson(JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function incriminateSelectionCounter(Request $request): JsonResponse {
        $requestData = json_decode($request->getContent(), false);
        $standardId = $requestData->standardId;
        $standard = q3wMaterialStandard::findOrFail($standardId);
        $standard->selection_counter += 1;
        $standard->save();

        return response()->json([
            'result' => 'ok',
            'value' => $standard->selection_counter
        ], 200);
    }

    public function standardPropertiesList(Request $request)
    {
        $options = json_decode($request['data']);

        return (new q3wStandardProperty())->dxLoadOptions($options)->get()->toJson(JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function brandsList(Request $request)
    {
        $options = json_decode($request['data']);

        return (new q3wMaterialBrand())->dxLoadOptions($options)
            ->leftJoin('q3w_material_types', 'q3w_material_brands.material_type_id', '=', 'q3w_material_types.id')
            ->orderBy(DB::Raw("CONCAT(`q3w_material_types`.`name`, ' ', `q3w_material_brands`.`name`)"))
            ->get([
                'q3w_material_brands.id',
                'q3w_material_brands.name',
                DB::Raw("CONCAT(`q3w_material_types`.`name`, ' ', `q3w_material_brands`.`name`) as `full_name`")
            ])
            ->toJson(JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }
}
