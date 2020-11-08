@extends('layouts.app')

@section('title', 'Проектная документация')

@section('url', route('project_documents::index'))

@section('css_top')
    <style>

    @media (min-width: 4000px)  {
        .tooltip {
            left:65px!important;
        }
    }

    @media (min-width: 3600px) and (max-width: 4000px)  {
        .tooltip {
            left:45px!important;
        }
    }
    @media (min-width: 2500px) and (max-width: 3600px)  {
            .tooltip {
                left:35px!important;
            }
        }
    </style>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div aria-label="breadcrumb" role="navigation">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('project_documents::index') }}" class="table-link">Проектная документация</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">{{ $project->name }}</li>
            </ol>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="fixed-table-toolbar toolbar-for-btn">
                <div class="fixed-search">
                    <form action="{{ route('project_documents::card', $project->id) }}">
                        <input class="form-control" type="text" value="{{ Request::get('search') }}" name="search" placeholder="Поиск">
                    </form>
                </div>
                <div class="pull-right">
                    <a>
                        <button class="btn btn-round btn-outline btn-sm add-btn" data-toggle="modal" data-target="#add-document">
                            <i class="glyphicon fa fa-plus"></i>
                            Добавить
                        </button>
                    </a>
                </div>
            </div>
            @if(!$project_docs->isEmpty())
            <div class="table-responsive">
                <table class="table table-hover mobile-table">
                    <thead>
                        <tr>
                            <th class="text-left">ID</th>
                            <th>Название</th>
                            <th class="text-center">Дата добавления</th>
                            <th>Автор</th>
                            <th class="text-center">Версия</th>
                            <th class="text-right">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($project_docs as $document)
                            <tr style="cursor:default" class="header">
                                <td data-label="ID" data-target=".collapse{{$document->id}}" data-toggle="collapse" class="collapsed tr-pointer" aria-expanded="false">
                                    {{ $document->id }}
                                </td>
                                <td data-label="Название" data-target=".collapse{{$document->id}}" data-toggle="collapse" class="collapsed tr-pointer" aria-expanded="false">
                                    {{ $document->name }}
                                </td>
                                <td data-label="Дата добавления" class="text-center">{{ $document->updated_at }}</td>
                                <td data-label="Автор"><a @if(!$document->user_full_name) @else href="{{ route('users::card', $document->user_id) }} @endif" class="table-link">{{ $document->user_full_name ? $document->user_full_name : 'Опросный лист' }}</a></td>
                                <td data-label="Версия" class="text-center">{{ $document->version }}</td>
                                <td data-label="" class="td-actions text-right actions">
                                    <button rel="tooltip" onClick="updateDocId({{ $document->id }})" class="btn-success btn-link btn-xs btn padding-actions mn-0"  data-toggle="modal" data-target="#update-document" data-original-title="Добавить новую версию">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                    <a target="_blank" href="{{ asset('storage/docs/project_documents/' . $document->file_name) }}" rel="tooltip" class="btn-info btn-link btn-xs padding-actions mn-0 btn" data-original-title="Просмотр">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                                @foreach($extra_documents->where('project_document_id', $document->id) as $extra_document)
                                    <tr class="collapse{{$document->id}} contact-note card-collapse collapse">
                                        <td></td>
                                        <td></td>
                                        <td class="text-center">{{ $extra_document->created_at }}</td>
                                        <td>
                                            <a href="{{ route('users::card', $document->user_id) }}" class="table-link">{{ $extra_document->user_full_name }}</a>
                                        </td>
                                        <td class="text-center">{{ $extra_document->version }}</td>
                                        <td class="td-actions text-right">
                                            <a target="_blank" href="{{ asset('storage/docs/project_documents/' . $extra_document->file_name) }}" rel="tooltip" class="btn-default btn-link btn-xs" data-original-title="Просмотр">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
                <p class="text-center">Документы не найдены</p>
            @endif
            <div class="col-md-12 fix-pagination">
                <div class="right-edge">
                    <div class="page-container">
                        {{ $project_docs->appends(['search' => Request::get('search')])->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade bd-example-modal-lg" id="add-document" tabindex="-1" role="dialog" aria-labelledby="modal-search" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-add">Добавить документ</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card border-0">
                    <div class="card-body">
                        <form id="form_add_document" class="form-horizontal" action="{{ route('project_documents::store', $project->id) }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="exampleFormControlFile1">Документы<star class="star">*</star></label>
                                    <div class="col-sm-6" style="padding-top:0px;">
                                        <div class="file-container">
                                            <div id="fileName" class="file-name"></div>
                                            <div class="file-upload ">
                                                <label class="pull-right">
                                                    <span><i class="nc-icon nc-attach-87" style="font-size:25px; line-height: 40px"></i></span>
                                                    <input type="file" name="documents[]" onchange="getFileName(this);" id="uploadedFile" required multiple>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                <button type="submit" form="form_add_document" class="btn btn-info">Добавить</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade bd-example-modal-lg" id="update-document" tabindex="-1" role="dialog" aria-labelledby="modal-search" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-update">Обновить документ</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card border-0">
                    <div class="card-body">
                        <form id="form_update_document" class="form-horizontal" action="{{ route('project_documents::update') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <input name="project_document_id" id="project_document_id" type="hidden">
                            <div class="form-group">
                                <div class="row">
                                    <label class="col-sm-3 col-form-label" for="exampleFormControlFile1">Документ<star class="star">*</star></label>
                                    <div class="col-sm-6" style="padding-top:0px;">
                                        <div class="file-container">
                                            <div id="fileName" class="file-name"></div>
                                            <div class="file-upload">
                                                <label class="pull-right">
                                                    <span><i class="nc-icon nc-attach-87" style="font-size:25px; line-height: 40px"></i></span>
                                                    <input type="file" name="document" onchange="getFileName(this);" id="uploadedFile" required>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                <button type="submit" form="form_update_document" class="btn btn-info">Добавить</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js_footer')
<script src="{{ mix('js/form-validation.js') }}" type="text/javascript"></script>

<script>
    function updateDocId(id) {
        $('#project_document_id').val(id);
    }
</script>
@endsection
