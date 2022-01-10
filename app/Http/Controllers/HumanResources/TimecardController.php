<?php

namespace App\Http\Controllers\HumanResources;

use App\Models\HumanResources\TimecardAddition;
use App\Models\HumanResources\TimecardDay;
use App\Models\HumanResources\TimecardRecord;
use App\Http\Requests\TimecardRequests\{TimecardBonusesUpdateRequest,
    TimecardCompensationsUpdateRequest,
    TimecardDealsGroupDestroyRequest,
    TimecardDealsGroupUpdateRequest,
    TimecardFinesUpdateRequest,
    TimecardKtuUpdateRequest,
    TimecardOpennessUpdateRequest};
use App\Models\HumanResources\Timecard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TimecardController extends Controller
{
    /**
     * Function update openness,
     * can close or open timecard
     * @param TimecardOpennessUpdateRequest $request
     * @param Timecard $timecard
     * @return array
     */
    public function updateOpenness(TimecardOpennessUpdateRequest $request, Timecard $timecard): array
    {
        DB::beginTransaction();

        $timecard->update(['is_opened' => $request->is_opened]);

        DB::commit();

        return [
            'result' => 'success'
        ];
    }

    /**
     * Function update timecard ktu property
     * @param TimecardKtuUpdateRequest $request
     * @param Timecard $timecard
     * @return array
     */
    public function updateKtu(TimecardKtuUpdateRequest $request, Timecard $timecard): array
    {
        DB::beginTransaction();

        $timecard->update(['ktu' => $request->ktu]);

        DB::commit();

        return [
            'result' => 'success'
        ];
    }

    /**
     * Function update timecard compensations
     * @param TimecardCompensationsUpdateRequest $request
     * @param Timecard $timecard
     * @return array
     */
    public function updateCompensations(TimecardCompensationsUpdateRequest $request, Timecard $timecard): array
    {
        DB::beginTransaction();
        $data = [];
        if (! empty($request->deleted_addition_ids)) {
            $timecard->deleteAdditions($request->deleted_addition_ids);
        }
        if (! empty($request->compensations)) {
            $data = $timecard->updateAdditions('compensation', $request->compensations ?? []);
        }
        $timecard->generateAction('compensation list update');

        DB::commit();

        return [
            'result' => 'success',
            'data' => $data
        ];
    }

    /**
     * Function update timecard bonuses
     * @param TimecardBonusesUpdateRequest $request
     * @param Timecard $timecard
     * @return array
     */
    public function updateBonuses(TimecardBonusesUpdateRequest $request, Timecard $timecard): array
    {
        DB::beginTransaction();

        if (! empty($request->deleted_addition_ids)) {
            $timecard->deleteAdditions($request->deleted_addition_ids);
        }
        if (! empty($request->bonuses)) {
            $timecard->updateAdditions('bonus', $request->bonuses ?? []);
        }
        $timecard->generateAction('bonus list update');

        DB::commit();

        return [
            'result' => 'success'
        ];
    }

    /**
     * Function update timecard fines
     * @param TimecardFinesUpdateRequest $request
     * @param Timecard $timecard
     * @return array
     */
    public function updateFines(TimecardFinesUpdateRequest $request, Timecard $timecard): array
    {
        DB::beginTransaction();

        if (! empty($request->deleted_addition_ids)) {
            $timecard->deleteAdditions($request->deleted_addition_ids);
        }
        if (! empty($request->fines)) {
            $timecard->updateAdditions('fine', $request->fines ?? []);
        }
        $timecard->generateAction('fine list update');

        DB::commit();

        return [
            'result' => 'success'
        ];
    }

    /**
     * Function updates deals by length or tariff_type
     * @param TimecardDealsGroupUpdateRequest $updateRequest
     * @param TimecardDay $timecardDay
     * @return array
     */
    public function updateDealsGroup(TimecardDealsGroupUpdateRequest $updateRequest, Timecard $timecard): array
    {
        DB::beginTransaction();

        $timecard->updateDealsGroup($updateRequest->all());

        $timecard->generateAction('deals group update');
        DB::commit();

        return [
            'result' => 'success',
        ];
    }

    public function destroyDealsGroup(TimecardDealsGroupDestroyRequest $request)
    {
        DB::beginTransaction();

        $timecard = Timecard::findOrFail($request->timecard_id);

        $timecard->deals()
            ->where('tariff_id', $request->tariff_id)
            ->where('length', $request->length)
            ->get()->each->delete();

        $timecard->generateAction('deals group deleted');

        DB::commit();

        return [
            'result' => 'success',
        ];
    }

    /**
     * Function return timecard addition names json for given filters
     * @param Request $request
     * @return array
     */
    public function getAdditionNames(Request $request): array
    {
        $additions = TimecardAddition::byType($request);

        if ($request->q) {
            $additions->where('name', 'like', "%{$request->q}%");
        }

        return $additions->groupBy('name')->limit(20)->get()->map(function ($addition) {
            return ['code' => $addition->id . '', 'label' => $addition->name];
        })->toArray();
    }

    /**
     * Function return timecards json for given filters
     * @param Request $request
     * @return array
     */
    public function getSummaryReport(Request $request): array
    {
        $timecards = Timecard::reportFilter($request);
        $dateStringLength = mb_strlen($request->date);
        if ($dateStringLength === 7) {
            // YYYY-MM
            // Month
            [$year, $month] = explode('-', $request->date);
            $start = now()->month($month)->startOfMonth()->format('Y-m-d');
            $end = now()->month($month)->endOfMonth()->format('Y-m-d');
        } elseif ($dateStringLength === 21) {
            // YYYY-MM-DD|YYYY-MM-DD
            // Period
            [$start, $end] = explode('|', $request->date);
        } elseif ($dateStringLength === 0) {
            return [];
        }

        $users = $timecards->get()->map(function ($timecard) use ($start, $end, $request) {
            $user = $timecard->user;
            $project_id = $request->project_id;
            return [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'long_full_name' => $user->long_full_name,
                'user_info' => "$user->group_name / $user->company_name",
                'timecard' => $timecard->records()
                    ->when($project_id, function ($query, $project_id) {
                        return $query->where(function ($q) use ($project_id) {
                            $q->where('project_id', $project_id)->orWhereNull('project_id');
                        });
                    })
                    ->whereIn('type', [TimecardRecord::TYPES_ENG['working hours'], TimecardRecord::TYPES_ENG['deals']])
                    ->get()
                    ->whereBetween('date', [$start, $end])
                    ->reduce(function ($records, $record) {
                        if (! array_key_exists($record->tariff_id, $records)) {
                            $records[$record->tariff_id] = ['name' => $record->tariff_rate->name, 'sum' => $record->amount];
                        } else {
                            $records[$record->tariff_id]['sum'] += $record->amount;
                        }
                        if ($record->type == TimecardRecord::TYPES_ENG['working hours']) {
                            $records[0]= ['name' => 'Сумма часов','sum' => ($record->amount + ($records[0]['sum'] ?? 0))];
                        }
                        return $records;
                    }, [])
            ];
        });
        $users = $users->reduce(function ($result, $user) {
            $duplicate_id = false;
            foreach($result as $key => $users) {
                if ($users['id'] == $user['id']) {
                    $duplicate_id = $key;
                    break;
                }
            }

            if ($duplicate_id !== false) {
                foreach ($result[$duplicate_id]['timecard'] as $key => $record) {
                    foreach ($user['timecard'] as $new_user_record) {
                        if ($new_user_record['name'] == $record['name']) {
                            $result[$duplicate_id]['timecard'][$key]['sum'] += $new_user_record['sum'];
                            break;
                        }
                    }
                }
                return $result;
            }
            $result[]= $user;
            return $result;
        }, []);

        return [
            'data' => [
                'users' => $users
            ]
        ];
    }
}
