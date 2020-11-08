<?php

namespace App\Http\Controllers\Documents;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use PDF;
use Carbon\Carbon;

use App\Models\Contractors\Contractor;
use App\Models\Contractors\ContractorContact;
use App\Models\Project;
use App\Models\ProjectObject;

class DocumentTemplateController extends Controller
{
    public function index()
    {
        return view('document_templates.index');
    }


    public function create_offer_template()
    {
        return view('document_templates.form_commercial_doc');
    }


    public function create_offer_template_store(Request $request)
    {
        $new_request = $request->all();
        // dd($request);
        if($request->service_count) {
            foreach($request->service_count as $key => $value) {
                $new_request['material_count'][$key] = [$value];
                $new_request['material_name'][$key] = [''];
            }
        }
        if($request->service_price) {
            foreach($request->service_price as $key => $value) {
                $new_request['material_price'][$key] = [$value];
            }
        }

        if($request->service_nds) {
            foreach($request->service_nds as $key => $value) {
                $new_request['material_nds'][$key] = [$value];
            }
        }

        // dd($new_request);
        $contractor = Contractor::findOrFail($request->contractor_id);
        $project = Project::findOrFail($request->project_id);
        $object = ProjectObject::findOrFail($project->object_id);
        $contact = ContractorContact::where('id', $request->contact_id)->first();

        $data = [
            'project' => $project,
            'contractor' => $contractor,
            'object' => $object,
            'request' => $new_request,
            'contact' => $contact,
            'time' => Carbon::now(),
        ];

        $pdf = PDF::loadView('document_templates.commercial_doc', $data);

        return $pdf->stream('document.pdf');
        // return view('document_templates.commercial_doc', $data);

    }
}
