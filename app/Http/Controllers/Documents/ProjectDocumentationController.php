<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectDocumentRequest\ProjectDocumentCreate;
use App\Http\Requests\ProjectDocumentRequest\ProjectDocumentUpdate;
use App\Models\ExtraDocument;
use App\Models\FileEntry;
use App\Models\Project;
use App\Models\ProjectDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;


class ProjectDocumentationController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::getAllProjects();

        if ($request->search) {

            $search = mb_strtolower($request->search);
            $result = array_filter($projects->getModel()->project_status, function($item) use ($search) {
                return stristr(mb_strtolower($item), $search);
            });

            $entity = array_filter($projects->getModel()::$entities, function($item) use ($search) {
                return stristr(mb_strtolower($item), $search);
            });
            // dd($projects->get());
            $projects->where('projects.name', 'like', '%' . $request->search . '%')
                ->orWhere('project_objects.address', 'like', '%' . $request->search . '%')
                ->orWhere(DB::raw('CONCAT(users.last_name, " ", users.first_name, " ", users.patronymic)'), 'like', '%' . $request->search . '%')
                ->orWhere(DB::raw('CONCAT(contractors.short_name, " ", contractors.inn)'), 'like', '%' . $request->search . '%')
                ->orWhereIn('projects.status', array_keys($result))
                ->orWhereIn('projects.entity', array_keys($entity));
        }

        return view('project_documents.index', [
            'projects' => $projects->orderBy('id', 'desc')->paginate(20)
        ]);
    }


    public function create($id)
    {
        return view('project_documents.create');
    }


    public function store(ProjectDocumentCreate $request, $id)
    {
        DB::beginTransaction();

        $project = Project::findOrFail($id);

        $this->authorize('edit', $project);

        if ($request->documents) {
            foreach($request->documents as $document) {
                $doc = new ProjectDocument();

                $doc->project_id = $id;
                $doc->user_id = Auth::user()->id;
                $doc->version = 1;

                $mime = $document->getClientOriginalExtension();
                $file_name =  'project-' . $id . '/project_document-' . uniqid() . '.' . $mime;

                Storage::disk('project_documents')->put($file_name, File::get($document));

                FileEntry::create([
                    'filename' => $file_name,
                    'size' => $document->getSize(),
                    'mime' => $document->getClientMimeType(),
                    'original_filename' => $document->getClientOriginalName(),
                    'user_id' => Auth::user()->id
                ]);

                $doc->name = $document->getClientOriginalName();
                $doc->file_name = $file_name;

                $doc->save();
            }
        }

        DB::commit();

        return redirect()->back()->with('project_document', 'Новый документ добавлен');
    }


    public function update(ProjectDocumentUpdate $request)
    {
        DB::beginTransaction();

        $doc_update = new ExtraDocument();

        $doc_update->project_document_id = $request->project_document_id;
        $doc_update->user_id = Auth::user()->id;

        $doc_first = ProjectDocument::findOrFail($request->project_document_id);

        $doc_update->version = $doc_first->version;
        $doc_update->created_at = \Carbon\Carbon::parse($doc_first->updated_at);

        $doc_first->version++;

        $doc_update->project_id = $doc_first->project_id;

        $project = Project::findOrFail($doc_update->project_id);

        $this->authorize('edit', $project);

        if ($request->document) {
            $mime = $request->document->getClientOriginalExtension();
            $file_name =  'project-' . $doc_first->project_id . '/project_document-' . uniqid() . '.' . $mime;

            Storage::disk('project_documents')->put($file_name, File::get($request->document));

            FileEntry::create(['filename' => $file_name, 'size' => $request->document->getSize(),
                'mime' => $request->document->getClientMimeType(), 'original_filename' => $request->document->getClientOriginalName(), 'user_id' => Auth::user()->id, ]);

            $doc_update->file_name = $doc_first->file_name;
            $doc_first->file_name = $file_name;
        }

        $doc_update->save();
        $doc_first->save();

        DB::commit();

        return redirect()->back()->with('project_document', 'Документ обновлен');
    }


    public function card(Request $request, $id)
    {
        $project_docs = ProjectDocument::where('project_id', $id)
            ->orderBy('id', 'desc')
            ->leftjoin('users', 'users.id', '=', 'project_documents.user_id')
            ->select('project_documents.*', DB::raw('CONCAT(users.last_name, " ", users.first_name, " ", users.patronymic) as user_full_name'));

        $extra_documents = ExtraDocument::orderBy('version', 'desc')
            ->where('project_id', $id)
            ->leftjoin('users', 'users.id', '=', 'extra_documents.user_id')
            ->select('extra_documents.*', DB::raw('CONCAT(users.last_name, " ", users.first_name, " ", users.patronymic) as user_full_name'));

        if ($request->search) {
            $project_docs->where('project_documents.name', 'like', '%' . $request->search . '%');
        }

        $project = Project::findOrFail($id);

        return view('project_documents.card', [
            'project_docs' => $project_docs->paginate(10),
            'project' => $project,
            'extra_documents' => $extra_documents->get()
        ]);
    }


    public function edit()
    {
        return view('project_documents.edit');
    }
}
