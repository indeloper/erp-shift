<?php

namespace App\Http\Controllers\HumanResources;

use App\Http\Controllers\Controller;
use App\Http\Requests\BrigadeRequests\{BrigadeCreateRequest,
    BrigadeDeleteRequest,
    BrigadeUpdateRequest,
    BrigadeUsersUpdateRequest};
use App\Models\HumanResources\Brigade;
use App\Traits\AdditionalFunctions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BrigadeController extends Controller
{
    use AdditionalFunctions;

    public function index(Request $request)
    {
        $newRequest = $this->createNewRequest($request->toArray());
        $brigades = Brigade::filter($newRequest)->with('foreman')->withCount('users')->paginate(15);

        return view('human_resources.brigades.index', [
            'data' => [
                'brigades' => $brigades->items(),
                'brigades_count' => $brigades->total(),
                'directions' => Brigade::DIRECTIONS
            ],
        ]);
    }


    public function create()
    {
        return view('human_resources.brigades.create', [
            'data' => [
                'directions' => Brigade::DIRECTIONS
            ]
        ]);
    }


    public function store(BrigadeCreateRequest $request)
    {
        DB::beginTransaction();

        $brigade = Brigade::create($request->all());
        $brigade->checkForemanStatus($request);

        DB::commit();

        return [
            'result' => 'success',
            'redirect' => route('human_resources.brigade.show', $brigade->id),
        ];
    }


    public function show(Brigade $brigade)
    {
        return view('human_resources.brigades.show', [
            'brigade' => $brigade->load('users', 'foreman'),
            'directions' => Brigade::DIRECTIONS,
        ]);
    }

    public function users(Brigade $brigade)
    {
        return view('human_resources.users_wrapper', [
            'data' => [
                'brigade' => $brigade->load( 'users'),
                'source' => 'brigade',
            ]
        ]);
    }

    public function edit(Brigade $brigade)
    {
        return view('human_resources.brigades.edit', [
            'data' => [
                'brigade' => $brigade,
                'directions' => Brigade::DIRECTIONS,
            ]
        ]);
    }


    public function update(BrigadeUpdateRequest $request, Brigade $brigade)
    {
        DB::beginTransaction();

        $brigade->update($request->all());
        $brigade->checkForemanStatus($request);

        DB::commit();

        return [
            'result' => 'success',
            'redirect' => route('human_resources.brigade.show', $brigade->id),
        ];
    }


    public function destroy(BrigadeDeleteRequest $request, Brigade $brigade)
    {
        DB::beginTransaction();

        $brigade->delete();

        DB::commit();

        return [
            'result' => 'success',
            'redirect' => route('human_resources.brigade.index'),
        ];
    }


    public function getBrigadesPaginated(Request $request)
    {
        $output = [];
        parse_str(parse_url($request->url)['query'] ?? '', $output);
        $newRequest = $this->createNewRequest($output);

        $result = Brigade::filter($newRequest)->with('foreman')->withCount('users')->paginate(15);

        return response()->json([
            'data' => [
                'brigades' => $result->items(),
                'brigades_count' => $result->total(),
            ],
        ]);
    }


    public function getBrigades(Request $request)
    {
        $brigades = Brigade::query();

        if ($request->q) {
            $brigades = $brigades->where('number', 'like', "%{$request->q}%");
        }
        $brigades = $brigades->take(6)->get();
        $brigades_json = [];

        foreach ($brigades as $brigade) {
            $brigades_json[] = ['code' => $brigade->id . '', 'label' => $brigade->name];
        }

        return $brigades_json;
    }


    public function updateUsers(BrigadeUsersUpdateRequest $request, Brigade $brigade)
    {
        DB::beginTransaction();

        $users = $brigade->updateUsers($request->all());

        DB::commit();

        return [
            'result' => 'success',
            'users' => $users,
        ];
    }
}
