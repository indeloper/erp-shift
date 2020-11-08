<?php

namespace App\Http\Controllers\Tasks;

use App\Traits\TimeCalculator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Events\NotificationCreated;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

use App\Models\Task;
use App\Models\TaskFile;
use App\Models\FileEntry;
use App\Models\User;
use App\Models\Group;
use App\Models\Contractors\{Contractor, ContractorContact, BankDetail};
use App\Models\Project;
use App\Models\TaskRedirect;
use App\Models\ProjectContact;
use App\Models\Notification;

use App\Http\Requests\TaskRequests\TaskCallRequest;

class TaskCallController extends Controller
{
    use TimeCalculator;

    public function new_call(Request $request, $id) {
        $call = Task::findOrFail($id);

        if ($call->status != 2) {
            abort(404);
        }

        if ($call->is_solved == 1) {
            abort(403);
        }

        $contractor = isset(old()['contractor_id']) ? Contractor::findOrFail(old()['contractor_id']) :
            (isset($request->contractor_id) ? Contractor::findOrFail($request->contractor_id) : Contractor::where('phone_number', $call->incoming_phone)->first());

        $project = isset(old()['project_id']) ? Project::findOrFail(old()['project_id']) :
            (isset($request->project_id) ? Project::findOrFail($request->project_id) : '');

        $contact = isset(old()['contact_id']) ? ContractorContact::findOrFail(old()['contact_id']) :
         (isset($request->contact_id) ? ContractorContact::findOrFail($request->contact_id) : ContractorContact::where('phone_number', $call->incoming_phone)->first());

        if (!$contractor and $contact) {
            $contractor = Contractor::where('id', $contact->contractor_id)->first();
        }

        $call->is_seen = 1;

        Notification::where('task_id', $call->id)
            ->where('name', $call->name)
            ->update(['is_seen' => 1]);

        $call->save();

        return view('tasks.call', [
            'contractor' => $contractor,
            'contact' => $contact,
            'call' => $call,
            'project' => $project
        ]);
    }


    public function close_call(TaskCallRequest $request, $id)
    {
        DB::beginTransaction();

        if ($request->contractor_full_name != null) {
            $contractor = Contractor::updateOrCreate(
                ['id' => $request->contractor_id],
                [
                    'full_name' => $request->contractor_full_name,
                    'short_name' => $request->contractor_short_name,
                    'inn' => $request->contractor_inn,
                    'kpp' => $request->contractor_kpp,
                    'ogrn' => $request->contractor_ogrn,
                    'legal_address' => $request->contractor_legal_address,
                    'physical_adress' => $request->contractor_physical_adress,
                    'general_manager' => $request->contractor_general_manager,
                    'phone_number' => $request->contractor_phone_number,
                    'email' => $request->contractor_email,
                ]
            );

            $bank = BankDetail::updateOrCreate(
                ['contractor_id' => $contractor->id],
                [
                    'bank_name' => $request->contractor_bank_name,
                    'check_account' => $request->contractor_check_account,
                    'cor_account' => $request->contractor_cor_account,
                    'bik' => $request->contractor_bik
                ]
            );
        }

        $call = Task::findOrFail($id);

        $call->status_result = $request->status_result;
        $call->contractor_id = isset($contractor->id) ? $contractor->id : $request->contractor_id;
        $call->project_id = $request->project_id;
        $call->contact_id = isset($contact->id) ? $contact->id : $request->contact_id;
        $call->final_note = $request->final_note;
        $call->is_solved = 1;
        $call->project_id = $request->project_id != null ? $request->project_id : '';

        $call->save();

        if($request->project_id) {
            if($request->contact_id) {
                $project_contact = ProjectContact::updateOrCreate(
                    ['contact_id' => $request->contact_id, 'project_id' => $request->project_id]
                );
            }
        }

        DB::commit();

//        event(new NotificationCreated());

        return redirect()->route('tasks::index');
    }


    public function choose_contractor(Request $request)
    {
        $contractor = Contractor::where('contractors.id', $request->contractor_id)
            ->leftJoin('bank_details', 'bank_details.contractor_id', 'contractors.id')
            ->first();

        return \GuzzleHttp\json_encode($contractor);
    }


    public function choose_contact(Request $request)
    {
        $contact = ContractorContact::findOrFail($request->contact_id);

        return \GuzzleHttp\json_encode($contact);
    }


    public function get_contacts(Request $request, $contractor_id)
    {
        $contacts = ContractorContact::where('contractor_id', $contractor_id);
        if ($request->q) {
            $contacts = $contacts->where('last_name', 'like', '%' . trim($request->q) . '%')
                ->orWhere('patronymic', 'like', '%' . trim($request->q) . '%')
                ->orWhere('position', 'like', '%' . trim($request->q) . '%')
                ->orWhere('position', 'like', '%' . trim($request->q) . '%')
                ->orWhere('patronymic', 'like', '%' . trim($request->q) . '%');
        }

        $contacts = $contacts->take(6)->get();

        $results[] = [
            'id' => '',
            'text' => 'Не выбран',
        ];
        foreach ($contacts as $contact) {
            $results[] = [
                 'id' => $contact->id,
                 'text' => $contact->last_name . ' ' . $contact->first_name . ' ' . $contact->patronymic . ', Должность: ' . $contact->position,
                 'name' => $contact->last_name . ' ' . $contact->first_name . ' ' . $contact->patronymic
             ];
        }

        return ['results' => $results];
    }


    public function makeTestCall($id)
    {
        $call = new Task();

        $call->name = 'Обработка входящего звонка';

        if ($id == 1) {
            $call->incoming_phone = ContractorContact::inRandomOrder()->where('phone_number', '!=', null)->first()->phone_number;
        }
        elseif ($id == 2) {
            $call->incoming_phone = Contractor::inRandomOrder()->where('phone_number', '!=', null)->first()->phone_number;

            if (!$call->incoming_phone) {
                return 'Контрагента с телефоном не существует';
            }
        }
        else {
            $call->incoming_phone = rand(79000000000, 79999999999);
        }

        $call->internal_phone = Auth::user()->work_phone;
        $call->responsible_user_id = Auth::user()->id;
        $call->status = 2;
        $call->expired_at = $this->addHours(2);

        $call->save();

        Notification::create([
            'name' => $call->name,
            'task_id' => $call->id,
            'user_id' => $call->responsible_user_id,
            'type' => 4
        ]);

        return response()->json(['data' => $call->toArray()], 201);
    }
}
