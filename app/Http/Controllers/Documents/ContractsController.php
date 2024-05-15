<?php

namespace App\Http\Controllers\Documents;

use Illuminate\View\View;
use App\Http\Controllers\Controller;
use App\Models\Contract\Contract;
use App\Models\Project;
use App\Traits\AdditionalFunctions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContractsController extends Controller
{
    use AdditionalFunctions;

    public function index(Request $request): View
    {
        $newRequest = $this->createNewRequest($request->toArray());

        $contracts = Contract::base();
        $contracts = $contracts->whereIn('contracts.id', [DB::raw('select max(id) from contracts GROUP BY contract_id, type')]);
        $contract_map = (clone $contracts)->select('contracts.id', 'version', 'contract_id')->get()->each->setAppends([])->groupBy('contract_id');
        $contracts = $contracts->filter($newRequest)->orderBy('contract_id', 'desc')
            ->orderBy('version', 'desc')
            ->paginate(30);

        return view('contracts.index', [
            'data' => [
                'contracts' => collect($contracts->items()),
                'contracts_count' => $contracts->total(),
                'contract_map' => $contract_map,
                'entities' => Project::$entities,
                'types' => Contract::getModel()->contract_types,
                'statuses' => Contract::getModel()->contract_status,
            ],
        ]);
    }

    public function get_contracts(Request $request)
    {
        $contracts_query = Contract::query();
        $contracts_query = $contracts_query->whereIn('contracts.id', [DB::raw('select max(id) from contracts GROUP BY contract_id, type')]);

        if ($request->q) {
            $search = $request->q;
            $contracts_query->where(function ($query) use ($search) {
                return
                    $query->orWhere('foreign_id', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhereHas('subcontractor', function ($q) use ($search) {
                            return $q->where('name', 'like', "%{$search}%");
                        });
            });
        }

        if ($request->object_id and $request->from_mat_acc) {
            $object_id = $request->object_id;
            $contracts_query->whereHas('project', function ($q) use ($object_id) {
                return $q->where('object_id', $object_id);
            })->whereNull('main_contract_id')
                ->where('type', 1)
                ->whereIn('status', [5, 6]);
        }

        $all_versions = $contracts_query->limit(50)->get();

        $group_versions = $all_versions->sortByDesc('version')->groupBy('contract_id');

        $latest_versions = collect();
        foreach ($group_versions as $group) {
            $latest_versions->push($group->first());
        }

        $contracts_json = [];
        foreach ($latest_versions as $contract) {
            $label = $contract->name_for_humans;
            if ($latest_versions->count() > 1) {
                $label .= " {$contract->project->name}";
            }
            $contracts_json[] = ['code' => $contract->id.'', 'label' => $label];
        }

        return response()->json($contracts_json);
    }

    public function contractsFiltered(Request $request)
    {
        $output = [];
        parse_str(parse_url($request->url)['query'] ?? '', $output);
        $newRequest = $this->createNewRequest($output);

        $contracts = Contract::base();
        $contracts = $contracts->whereIn('contracts.id', [DB::raw('select max(id) from contracts GROUP BY contract_id, type')]);
        $contract_map = (clone $contracts)->select('contracts.id', 'version', 'contract_id')->get()->each->setAppends([])->groupBy('contract_id');
        $contracts = $contracts->filter($newRequest)->orderBy('contract_id', 'desc')
            ->orderBy('version', 'desc')
            ->paginate(30);

        return [
            'contracts' => $contracts->items(),
            'contracts_count' => $contracts->total(),
            'contract_map' => $contract_map,
        ];
    }
}
