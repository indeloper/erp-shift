@extends('layouts.app')

@section('title', 'Проекты')

@section('url', route('projects::index'))

@section('content')
<div class="row">
    <div class="col-sm-12 col-xl-9 mr-auto ml-auto">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-5">
                        <h4 class="card-title" style="margin-top: 5px">Редактирование проекта</h4>
                    </div>
                </div>
                <hr>
            </div>
            <div class="card-body">
                <h5 style="margin-bottom:20px">Основная информация</h5>
                <form id="form_edit_project" class="form-horizontal" action="{{ route('projects::update', $project->id) }}" method="post">
                    @csrf
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Название проекта<star class="star">*</star></label>
                        <div class="col-sm-9">
                            <div class="form-group">
                                <input class="form-control" type="text" name="name" value="{{ $project->name }}" required maxlength="200">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Объект<star class="star">*</star></label>
                        <div class="col-sm-9">
                            <div class="form-group">
                                <select name="object_id" id="js-select-objects" style="width:100%;" required>
                                    <option value="{{ $object->id }}">{{ $object->name }}. Адрес: {{ $object->address }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Юр. лицо<star class="star">*</star></label>
                        <div class="col-sm-9">
                            <div class="form-group">
                                <select name="entity" class="selectpicker" data-title="Выберите юр. лицо" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                                    @foreach($project::$entities as $key => $entity)
                                        <option value="{{ $key }}" {{ $key == $project->entity ? 'selected' : '' }}>{{ $entity }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Направления<star class="star">*</star></label>
                        <div class="col-sm-9">
                            <div class="form-group">
                                <select name="type[]" class="selectpicker" multiple data-title="Выберите направление" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                                        <option value="is_tongue" {{ $project->is_tongue ? 'selected' : '' }}>Шпунтовое направление</option>
                                        <option value="is_pile" {{ $project->is_pile ? 'selected' : '' }}>Свайное направление</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Описание</label>
                        <div class="col-sm-9">
                            <div class="form-group">
                                <textarea class="form-control textarea-rows" name="description" maxlength="200">{{ $project->description }}</textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center">
                <button type="submit" form="form_edit_project" class="btn btn-info">Сохранить</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js_footer')

<script>
$('#js-select-objects').select2({
    language: "ru",
    ajax: {
        url: '/projects/ajax/get-objects'
    }
});
</script>
@endsection
