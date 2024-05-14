<?php

namespace App\Http\Controllers\Timesheet;

use App\Http\Controllers\StandardEntityResourceController;
use App\Models\Group;
use App\Models\Timesheet\TimesheetCard;
use App\Models\Timesheet\TimesheetPostTariff;
use App\Services\Common\FileSystemService;
use App\Services\SystemService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostTariffsController extends StandardEntityResourceController
{
    public function __construct()
    {
        parent::__construct();

        $this->baseModel = new TimesheetCard();
        $this->routeNameFixedPart = 'timesheet::posts::';
        $this->sectionTitle = 'Должности и тарифы';
        $this->baseBladePath = resource_path().'/views/timesheet/posts';

        $this->isMobile = is_dir($this->baseBladePath.'/mobile') && SystemService::determineClientDeviceType($_SERVER['HTTP_USER_AGENT']) === 'mobile';

        $this->componentsPath = $this->isMobile ? $this->baseBladePath.'/mobile/components' : $this->baseBladePath.'/desktop/components';

        $this->components = (new FileSystemService)->getBladeTemplateFileNamesInDirectory($this->componentsPath, $this->baseBladePath);
        $this->modulePermissionsGroups = [];
    }

    public function index(Request $request)
    {
        $data = Group::get(['id', 'name']);

        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function update(Request $request, int $id)
    {
        DB::beginTransaction();

        $id = json_decode($id);
        $data = json_decode($request->input('data'), true);

        DB::commit();

        return response()->json(['result' => 'ok', 'data' => $data]);
    }

    public function show($id)
    {
        $group = Group::findOrFail($id);
        $group['postTariffs'] = TimesheetPostTariff::where('post_id', '=', $group->id)->get();

        return $group;
    }
}
