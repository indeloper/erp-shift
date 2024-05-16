<?php

namespace App\Http\Controllers\Building\TechAccounting\Technic\old;

use App\Http\Controllers\Controller;
use App\Http\Requests\OurTechnicStoreRequest;
use App\Http\Requests\OurTechnicUpdateRequest;
use App\Models\TechAcc\FuelTank\FuelTank;
use App\Models\TechAcc\OurTechnic;
use App\Services\TechAccounting\TechnicCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OurTechnicController extends Controller
{
    protected $service;

    public function __construct()
    {
        parent::__construct();

        $this->service = new TechnicCategoryService();
    }

    public function index(Request $request, $category_id): View
    {
        $data = $this->service->collectDataForTransport($request->all(), $category_id);

        return view('tech_accounting.technics.technics_list', $data);
    }

    public function store(OurTechnicStoreRequest $request): JsonResponse
    {
        $technic = $this->service->createOurTechnicWithAttributes($request->all());

        return response()->json([
            'result' => 'success',
            'data' => $technic,
        ]
        );
    }

    public function update(OurTechnicUpdateRequest $request, $category, OurTechnic $ourTechnic): JsonResponse
    {
        $technic = $this->service->updateOurTechnicWithAttributes($ourTechnic, $request->all());

        return response()->json([
            'result' => 'success',
            'data' => $technic,
        ]
        );
    }

    public function destroy($category_id, OurTechnic $ourTechnic)
    {
        DB::beginTransaction();

        $ourTechnic->delete();

        DB::commit();

        return \GuzzleHttp\json_encode([
            'result' => 'success',
        ]);

    }

    public function get_technics(Request $request): Response
    {
        $ourTechnicQuery = OurTechnic::query();
        if ($request->free_only) {
            $ourTechnicQuery = OurTechnic::free();
        }
        if ($request->relations) {
            $ourTechnicQuery->with($request->relations);
        }
        if ($request->q) {
            $technics = $ourTechnicQuery->where('model', 'like', "%$request->q%")
                ->orWhere('brand', 'like', "%$request->q%")->get();
        } else {
            $technics = $ourTechnicQuery->get();
        }

        return response([
            'data' => $technics,
        ]);
    }

    public function get_all_technics(Request $request)
    {
        $technics = OurTechnic::query();
        $fuelTanks = FuelTank::query();

        if ($request->q) {
            $technics = $technics
                ->where('model', 'like', $request->q)
                ->orWhere('brand', 'like', $request->q);

            $fuelTanks = $fuelTanks->where('tank_number', 'like', $request->q);
        }

        return response([
            'data' => $technics->get()->map(function ($technic) {
                $technic['defectable_type'] = 1;

                return $technic;
            })->toBase()->merge($fuelTanks->get()->map(function ($fuelTank) {
                $fuelTank['defectable_type'] = 2;

                return $fuelTank;
            })),
        ]);
    }

    public function getTechnicsPaginated(Request $request, $ourTechnicCategoryId): Response
    {
        $output = [];
        parse_str(parse_url($request->url)['query'] ?? '', $output);

        $paginated = OurTechnic::where('technic_category_id', $ourTechnicCategoryId)
            ->filter($output)
            ->paginate(15);

        $ourTechnicCount = $paginated->total();
        $ourTechnics = $paginated->items();

        return response([
            'ourTechnics' => $ourTechnics,
            'ourTechnicCount' => $ourTechnicCount,
        ]);
    }

    public function getTrashedTechnicsPaginated(Request $request, $ourTechnicCategoryId): Response
    {
        $output = [];
        parse_str(parse_url($request->url)['query'] ?? '', $output);

        $paginated = OurTechnic::onlyTrashed()->where('technic_category_id', $ourTechnicCategoryId)
            ->filter($output)
            ->paginate(15);

        $ourTechnicCount = $paginated->total();
        $ourTechnics = $paginated->items();

        return response([
            'ourTechnics' => $ourTechnics,
            'ourTechnicCount' => $ourTechnicCount,
        ]);
    }

    public function display_trashed(Request $request, $category_id): View
    {
        $data = $this->service->collectDataForTrashedTransport($request->all(), $category_id);

        return view('tech_accounting.technics.trashed_technics_list', $data);
    }
}
