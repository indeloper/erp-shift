@extends('layouts.app')

@section('title', 'Задачи')

@section('url', route('tasks::index'))
@section('css_top')
<style media="screen">
    @media (max-width: 1350px) {
        .table-responsive {
        overflow-x: auto;
        }
    }

</style>
@endsection

@section('content')

    @if(Auth::user()->can('dashboard_smr'))
        @include('tasks.modules.dashboard_smr')
    @endif
    @if(Auth::user()->can('dashbord'))

        <div class="row" id="statistic">
    <div class="col-md-12 dashboard-card-container">
            <div class="row">
                <div class="col-sm-12 col-xl-6 mobile-card-padding offer-more-info">
                    <el-row :gutter="12">
                        <el-col>
                            <el-card shadow="hover">
                                <div class="dashboard-min-card__item offer-item">
                                    <div class="dashboard-min-card__value">
                                        {{ $offers->count() }}
                                    </div>
                                    <div class="dashboard-min-card__unit">
                                        Коммерческих <br> предложений
                                    </div>
                                </div>
                                <div class="offer-status-card">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <table class="dashboard-table">
                                                <thead>
                                                    <tr>
                                                        <th></th>
                                                        <th class="text-center">Сваи</th>
                                                        <th class="text-center">Шпунт</th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>
                                                            <a href="{{ route('commercial_offers::index', ['search' => 'commercial_offers.status', 'values' => 'В работе', 'parameters' => 'Статус']) }}" class="dashboard-min-card__table-link">
                                                            • В работе ({{ $offers->where('status', 1)->count() }})
                                                            </a>
                                                        </td>
                                                        <td class="text-center"><a class="dashboard-min-card__table-link" href="{{ route('commercial_offers::index', ['search' => 'commercial_offers.status,commercial_offers.name', 'values' => 'В работе,свайное', 'parameters' => 'Статус,Название']) }}" target="_blank">
                                                                {{ $offers->where('status', 1)->where('is_tongue', 0)->count() }}

                                                            </a></td>
                                                        <td class="text-center"><a class="dashboard-min-card__table-link" href="{{ route('commercial_offers::index', ['search' => 'commercial_offers.status,commercial_offers.name', 'values' => 'В работе,шпунт', 'parameters' => 'Статус,Название']) }}" target="_blank">
                                                                {{ $offers->where('status', 1)->where('is_tongue', 1)->count() }}</a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <a href="{{ route('commercial_offers::index', ['search' => 'commercial_offers.status', 'values' => 'На согласовании', 'parameters' => 'Статус']) }}" class="dashboard-min-card__table-link">
                                                                • На согласовании ({{ $offers->where('status', 2)->count() }})
                                                            </a>
                                                        </td>
                                                        <td class="text-center"><a class="dashboard-min-card__table-link" href="{{ route('commercial_offers::index', ['search' => 'commercial_offers.status,commercial_offers.name', 'values' => 'На согласовании,свайное', 'parameters' => 'Статус,Название']) }}" target="_blank">
                                                                {{ $offers->where('status', 2)->where('is_tongue', 0)->count() }}
                                                            </a></td>
                                                        <td class="text-center"><a class="dashboard-min-card__table-link" href="{{ route('commercial_offers::index', ['search' => 'commercial_offers.status,commercial_offers.name', 'values' => 'На согласовании,шпунт', 'parameters' => 'Статус,Название']) }}" target="_blank">
                                                                {{ $offers->where('status', 2)->where('is_tongue', 1)->count() }}
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <a href="{{ route('commercial_offers::index', ['search' => 'commercial_offers.status', 'values' => 'Согласовано', 'parameters' => 'Статус']) }}" class="dashboard-min-card__table-link">
                                                                • Согласовано ({{ $offers->where('status', 4)->count() }})
                                                            </a>
                                                        </td>
                                                        <td class="text-center"><a class="dashboard-min-card__table-link" href="{{ route('commercial_offers::index', ['search' => 'commercial_offers.status,commercial_offers.name', 'values' => 'Согласовано,свайное', 'parameters' => 'Статус,Название']) }}" target="_blank">
                                                                {{ $offers->where('status', 4)->where('is_tongue', 0)->count() }}
                                                            </a></td>
                                                        <td class="text-center"><a class="dashboard-min-card__table-link" href="{{ route('commercial_offers::index', ['search' => 'commercial_offers.status,commercial_offers.name', 'values' => 'Согласовано,шпунт', 'parameters' => 'Статус,Название']) }}" target="_blank">
                                                                {{ $offers->where('status', 4)->where('is_tongue', 1)->count() }}
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding-bottom:10px">
                                                            <a href="{{ route('commercial_offers::index', ['search' => 'commercial_offers.status', 'values' => 'Отправлено', 'parameters' => 'Статус']) }}" class="dashboard-min-card__table-link">
                                                                • Отправлено заказчику ({{ $offers->where('status', 5)->count() }})
                                                            </a>
                                                        </td>
                                                        <td style="padding-bottom:10px" class="text-center"><a class="dashboard-min-card__table-link" href="{{ route('commercial_offers::index', ['search' => 'commercial_offers.status,commercial_offers.name', 'values' => 'Отправлено,свайное', 'parameters' => 'Статус,Название']) }}" target="_blank">
                                                                {{ $offers->where('status', 5)->where('is_tongue', 0)->count() }}
                                                            </a></td>
                                                        <td style="padding-bottom:10px" class="text-center"><a class="dashboard-min-card__table-link" href="{{ route('commercial_offers::index', ['search' => 'commercial_offers.status,commercial_offers.name', 'values' => 'Отправлено,шпунт', 'parameters' => 'Статус,Название']) }}" target="_blank">
                                                                {{ $offers->where('status', 5)->where('is_tongue', 1)->count() }}
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <tr class="total-offer-statistic">
                                                        <td></td>
                                                        <td class="text-center"><a class="dashboard-min-card__table-link" href="{{ route('commercial_offers::index', ['search' => 'commercial_offers.name', 'values' => 'свайное', 'parameters' => 'Название']) }}" target="_blank"><b>
                                                                {{ $offers->where('is_tongue', 0)->count() }}
                                                                </b></a></td>
                                                        <td class="text-center"><a class="dashboard-min-card__table-link" href="{{ route('commercial_offers::index', ['search' => 'commercial_offers.name', 'values' => 'шпунт', 'parameters' => 'Название']) }}" target="_blank"><b>
                                                                {{ $offers->where('is_tongue', 1)->count() }}
                                                                </b></a></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                </div>
                            </el-card>
                        </el-col>
                    </el-row>
                </div>
                <div class="col-sm-6 col-xl-3 mobile-card-padding">
                    <el-row :gutter="12">
                        <el-col>
                            <el-card shadow="hover">
                                <div class="dashboard-min-card__item wv-item">
                                    <div class="dashboard-min-card__value">
                                        {{ $work_volumes->count() }}
                                    </div>
                                    <div class="dashboard-min-card__unit">
                                        Объемов <br> работ
                                    </div>
                                </div>
                                <div class="wv-status-card">
                                    <a href="{{ route('work_volumes::index', ['search' => 'work_volumes.status', 'values' => 'В работе', 'parameters' => 'Статус']) }}" class="dashboard-min-card__link">
                                        <div class="row">
                                            <div class="col-md-12 dashboard-min-card__status-item">
                                                <div class="status-name">
                                                    • В работе
                                                </div>
                                                <div class="status-value">
                                                    {{ $work_volumes->where('status', 1)->count() }}
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                    <a href="{{ route('work_volumes::index', ['search' => 'work_volumes.status', 'values' => 'Отправлен', 'parameters' => 'Статус']) }}" class="dashboard-min-card__link">
                                        <div class="row">
                                            <div class="col-md-12 dashboard-min-card__status-item">
                                                <div class="status-name">
                                                    • Отправлено
                                                </div>
                                                <div class="status-value">
                                                    {{ $work_volumes->where('status', 2)->count() }}
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </el-card>
                        </el-col>
                    </el-row>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <el-row :gutter="12">
                        <el-col>
                            <el-card shadow="hover">
                                <div class="dashboard-min-card__item contract-item">
                                    <div class="dashboard-min-card__value">
                                        {{ $contracts->count() }}
                                    </div>
                                    <div class="dashboard-min-card__unit">
                                        Договоров
                                    </div>
                                </div>
                                <div class="contracts-status-card">
                                    <a href="{{ route('contracts::index', ['contracts.status' => '1']) }}" class="dashboard-min-card__link">
                                        <div class="row">
                                            <div class="col-md-12 dashboard-min-card__status-item">
                                                <div class="status-name">
                                                    • В работе
                                                </div>
                                                <div class="status-value">
                                                    {{ $contracts->where('status', 1)->count() }}
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                    <a href="{{ route('contracts::index', ['contracts.status' => '2']) }}" class="dashboard-min-card__link">
                                        <div class="row">
                                            <div class="col-md-12 dashboard-min-card__status-item">
                                                <div class="status-name">
                                                    • На согласовании
                                                </div>
                                                <div class="status-value">
                                                    {{ $contracts->where('status', 2)->count() }}
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                    <a href="{{ route('contracts::index', ['contracts.status' => '4']) }}" class="dashboard-min-card__link">
                                        <div class="row">
                                            <div class="col-md-12 dashboard-min-card__status-item">
                                                <div class="status-name">
                                                    • Согласован
                                                </div>
                                                <div class="status-value">
                                                    {{ $contracts->where('status', 4)->count() }}
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                    <a href="{{ route('contracts::index', ['contracts.status' => '5']) }}" class="dashboard-min-card__link">
                                        <div class="row">
                                            <div class="col-md-12 dashboard-min-card__status-item">
                                                <div class="status-name">
                                                    • На гарантии
                                                </div>
                                                <div class="status-value">
                                                    {{ $contracts->where('status', 5)->count() }}
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                    <a href="{{ route('contracts::index', ['contracts.status' => '6']) }}" class="dashboard-min-card__link">
                                        <div class="row">
                                            <div class="col-md-12 dashboard-min-card__status-item" style="margin-bottom:0">
                                                <div class="status-name">
                                                    • Подписан
                                                </div>
                                                <div class="status-value">
                                                    {{ $contracts->where('status', 6)->count() }}
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </el-card>
                        </el-col>
                    </el-row>
                </div>
            </div>
        </template>
    </div>
</div>
@endif


@if(Auth::user()->can('tasks'))
<div class="row">
    <div class="col-md-12">
        <div class="card strpied-tabled-with-hover">
            <div class="fixed-table-toolbar toolbar-for-btn">
                <div class="fixed-search">
                    <form action="{{ route('tasks::index') }}">
                        <input class="form-control" type="text" value="{{ Request::get('search') }}" name="search" placeholder="Поиск">
                        <input type="hidden" value="{{ Request::get('name') }}" name="name">
                        <input  type="hidden" value="{{ Request::get('date') }}" name="date">
                    </form>
                </div>
                @if(Gate::check('tasks_default_myself') || Gate::check('tasks_default_others'))
                <div class="pull-right">
                    <button class="btn btn-round btn-outline btn-sm add-btn" data-toggle="modal" data-target="#add-new-task">
                        <i class="glyphicon fa fa-plus"></i>
                        Добавить
                    </button>
                </div>
                @endif
            </div>
            @if(!$tasks->isEmpty())
            <div class="table-responsive">
                <table class="table table-hover mobile-table">
                    <thead>
                        <tr>
                            <th>
                                <a
                                   @if(Request::get('name') == 'asc')
                                       href="{{ route('tasks::index', ['name' => 'desc', 'search' => Request::get('search'), 'page' => Request::get('page')]) }}" style="color: #9A9A9A;">Задача<img src="{{ asset('img/sort/sort-1.svg') }}" style="cursor: pointer; margin-left: 10px;"
                                   @elseif(Request::get('name') == 'desc')
                                       href="{{ route('tasks::index', ['search' => Request::get('search'), 'page' => Request::get('page')]) }}" style="color: #9A9A9A;">Задача<img src="{{ asset('img/sort/desc_sort.svg') }}" style="cursor: pointer; margin-left: 10px;"
                                   @else
                                       href="{{ route('tasks::index', ['name' => 'asc', 'search' => Request::get('search'), 'page' => Request::get('page')]) }}" style="color: #9A9A9A;">Задача<img src="{{ asset('img/sort/no_sort.gif') }}" style="cursor: pointer; margin-left: 10px;"
                                   @endif
                                ></a>
                            </th>
                            <th>Предыдущее событие</th>
                            <th>Проект</th>
                            <th>Адрес</th>
                            <th>Контрагент</th>
                            <th><a
                                    @if(Request::get('date') == 'asc')
                                        href="{{ route('tasks::index', ['date' => 'desc', 'search' => Request::get('search'), 'page' => Request::get('page')]) }}" style="color: #9A9A9A;">Срок исполнения<img src="{{ asset('img/sort/sort-1.svg') }}" style="cursor: pointer; margin-left: 10px;"
                                    @elseif(Request::get('date') == 'desc')
                                        href="{{ route('tasks::index', ['search' => Request::get('search'), 'page' => Request::get('page')]) }}" style="color: #9A9A9A;">Срок исполнения<img src="{{ asset('img/sort/desc_sort.svg') }}" style="cursor: pointer; margin-left: 10px;"
                                    @else
                                        href="{{ route('tasks::index', ['date' => 'asc', 'search' => Request::get('search'), 'page' => Request::get('page')]) }}" style="color: #9A9A9A;">Срок исполнения<img src="{{ asset('img/sort/no_sort.gif') }}" style="cursor: pointer; margin-left: 10px;"
                                    @endif
                                    ></a>
                            </th>
                            <th>Автор</th>
                            <th class="td-0"></th>
                        </tr>
                    </thead>
                    <tbody>
                    @if (Request::has(['date']) or Request::has(['name']))
                        @foreach($tasks as $task)
                            <tr data-href="{{ $task->task_route() }}" class="
                                @if ($task->revive_at)
                                    delayed-task
                                @else
                                    @if ($task->is_overdue())
                                        overdue-task
                                    @elseif($task->is_seen != 1)
                                        new-task
                                    @endif
                                @endif
                                href" style="cursor:default">
                                <td data-label="Задача">{{ $task->name }}</td>
                                <td data-label="Предыдущее событие" @if(isset($task->prev_task)) data-target="#show_task_info{{ $task->prev_task->id }}" data-toggle="modal" @endif>
                                    @if($task->prev_task)
                                        {{ $task->prev_task->name }}
                                    @else
                                        {{ 'Отсутствует' }}
                                    @endif
                                </td>
                                <td data-label="Проект">{{ $task->project_name ? $task->project_name : 'Не выбран' }}</td>
                                <td data-label="Адрес">
                                    @if($task->status == 21 and $task->operation)
                                        {{ $task->operation->address_text ?? 'Не выбран' }}
                                    @else
                                        {{ $task->project_address ? $task->project_address : 'Не выбран' }}
                                    @endif
                                </td>
                                <td data-label="Контрагент">{{ $task->contractor_name ? $task->contractor_name : 'Не выбран'}}</td>
                                @if($task->revive_at)
                                    <td data-label="Срок исполнения" class="delayed-date">
                                        {{$task->revive_at}}
                                    </td>
                                @else
                                    <td data-label="Срок исполнения" class="prerendered-date">
                                        {{$task->expired_at}}
                                    </td>
                                @endif
                                <td data-label="Автор">
                                    @if($task->user_id)
                                    <a href="{{ route('users::card', $task->user_id) }}" class="table-link">
                                        {{ $task->full_name }}
                                    </a>
                                    @else
                                        Система
                                    @endif
                                </td>
                                <td class="td-0"></td>
                            </tr>
                        @endforeach
                    @else
                        @foreach($tasks as $task)
                            <tr style="cursor:default" data-href="{{ $task->task_route() }}" class="
                                 @if ($task->revive_at)
                                    delayed-task
                                 @else
                                    @if ($task->is_overdue())
                                        overdue-task
                                    @elseif($task->is_seen != 1)
                                                                    new-task
                                    @endif
                                @endif
                                href">
                                <td data-label="Задача">{{ $task->name }}</td>
                                <td data-label="Предыдущее событие" @if(isset($task->prev_task)) data-target="#show_task_info{{ $task->prev_task->id }}" data-toggle="modal" @endif>
                                    @if($task->prev_task)
                                        {{ $task->prev_task->name }}
                                    @else
                                        {{ 'Отсутствует' }}
                                    @endif
                                </td>
                                <td data-label="Проект">{{ $task->project_name ? $task->project_name : 'Не выбран' }}</td>
                                <td data-label="Адрес">
                                    @if($task->status == 21 and $task->operation)
                                        {{ $task->operation->address_text ?? 'Не выбран' }}
                                    @else
                                        {{ $task->project_address ? $task->project_address : 'Не выбран' }}
                                    @endif
                                </td>
                                <td data-label="Контрагент">{{ $task->contractor_name ? $task->contractor_name : 'Не выбран'}}</td>
                                @if($task->revive_at)
                                    <td data-label="Срок исполнения" class="delayed-date">
                                        {{$task->revive_at}}
                                    </td>
                                @else
                                    <td data-label="Срок исполнения" class="prerendered-date">
                                        {{$task->expired_at}}
                                    </td>
                                @endif

                                <td data-label="Автор">
                                    @if($task->user_id)
                                        <a href="{{ route('users::card', $task->user_id) }}" class="table-link">
                                            {{ $task->full_name }}
                                        </a>
                                    @else
                                        Система
                                    @endif
                                </td>
                                <td class="td-0"></td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>
            @else
                <p class="text-center">Задачи не найдены</p>
            @endif
            <div class="col-md-12 fix-pagination">
                <div class="right-edge">
                    <div class="page-container">
                        {{ $tasks->appends(['search' => Request::get('search'), 'name' => Request::get('name'), 'date' => Request::get('date') ])->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(Gate::check('tasks_default_myself') || Gate::check('tasks_default_others'))
<div class="modal fade bd-example-modal-lg show" id="add-new-task" role="dialog" aria-labelledby="modal-search" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
       <div class="modal-content">
           <div class="modal-header">
               <h5 class="modal-title">Новая задача</h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                   <span aria-hidden="true">×</span>
               </button>
           </div>
           <div class="modal-body">
               <div class="card border-0">
                   <div class="card-body ">
                       <form id="create_task_form" class="form-horizontal" action="{{ route('tasks::store') }}" method="post" enctype="multipart/form-data">
                           @csrf
                           <div class="row">
                               <label class="col-sm-3 col-form-label">Название<star class="star">*</star></label>
                               <div class="col-sm-9">
                                   <div class="form-group">
                                       <input class="form-control" type="text" name="name" required maxlength="50">
                                   </div>
                               </div>
                           </div>
                           <div class="row">
                               <label class="col-sm-3 col-form-label">Описание<star class="star">*</star></label>
                               <div class="col-sm-9">
                                   <div class="form-group">
                                       <textarea class="form-control textarea-rows" name="description" required maxlength="250"></textarea>
                                   </div>
                               </div>
                           </div>
                           <div class="row">
                               <label class="col-sm-3 col-form-label">Контрагент<star class="star">*</star></label>
                               <div class="col-sm-6">
                                   <div class="form-group select-accessible-140">
                                       <select id="js-select-contractor" name="contractor_id" style="width:100%;" required>
                                           @if(Request::has('contractor_id'))
                                               <option value="{{ $contractor->id }}" selected>{{ $contractor->short_name }}. ИНН: {{ $contractor->inn }}</option>
                                           @endif
                                           <option value=""></option>
                                       </select>
                                   </div>
                               </div>
                           </div>
                           <div id="project_choose" class="row d-none">
                               <label class="col-sm-3 col-form-label">Проект</label>
                               <div class="col-sm-6">
                                   <div class="form-group">
                                       <select name="project_id" id="js-select-project" style="width:100%;">
                                           @if(Request::has('project_id'))
                                               <option value="{{ $project->id }}" selected>Название: {{ $project->name }}</option>
                                           @endif
                                           <option value="">Не выбрано</option>
                                       </select>
                                   </div>
                               </div>
                           </div>
                           <div class="row">
                               <label class="col-sm-3 col-form-label">Ответственное лицо<star class="star">*</star></label>
                               <div class="col-sm-6">
                                   <div class="form-group select-accessible-140">
                                       <select id="js-select-user" name="responsible_user_id"  style="width:100%;" required>
                                       </select>
                                   </div>
                               </div>
                           </div>
                           <div class="row">
                               <label class="col-sm-3 col-form-label">Срок выполнения<star class="star">*</star></label>
                               <div class="col-sm-6">
                                   <div class="form-group">
                                       <input id="datetimepicker" name="expired_at" type="text" min="{{ \Carbon\Carbon::now()->addMinutes(30) }}" class="form-control datetimepicker" placeholder="Укажите дату" required>
                                   </div>
                               </div>
                           </div>
                           <div class="row">
                               <label class="col-sm-3 col-form-label" for="" style="font-size:0.80">Приложенные файлы</label>
                               <div class="col-sm-6" style="padding-top:0;">
                                   <div class="file-container">
                                       <div id="fileName" class="file-name"></div>
                                       <div class="file-upload ">
                                           <label class="pull-right">
                                               <span><i class="nc-icon nc-attach-87" style="font-size:25px; line-height: 40px"></i></span>
                                               <input type="file" name="documents[]" accept="*" id="uploadedFile" class="form-control-file file" onchange="getFileName(this)" multiple>
                                           </label>
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
                <button type="submit" id="submit" form="create_task_form" class="btn btn-info">Сохранить</button>
           </div>
        </div>
    </div>
</div>
@endif

@foreach($tasks as $task)
    @if($task->prev_task)
<div class="modal fade bd-example-modal-lg"  id="show_task_info{{ $task->prev_task->id }}" tabindex="-1" role="dialog" aria-labelledby="modal-search" aria-hidden="true">
    <div class="modal-task-info modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="task-info__modal-body">
                <div class="row" style="flex-direction: row-reverse">
                    <div class="col-md-5">
                        <div class="right-bar-info">
                            <div class="row">
                                <div class="col-md-12">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true" style="color:#fff;font-size: 24px;font-weight: 300;">&times;</span>
                                    </button>
                                </div>
                            </div>
                            <div class="right-bar-info__item" style="margin-bottom:0;">
                                <div class="task-info__text-unit">
                                        <span class="task-info__head-title">
                                            Время создания
                                        </span>
                                    <span class="task-info__body-title">
                                            {{ $task->prev_task->created_at }}
                                        </span>
                                </div>
                                <div class="task-info__text-unit">
                                        <span class="task-info__head-title">
                                            Автор
                                        </span>
                                    @if($task->prev_task->user_id)
                                        <a class="task-info__link task-info__body-title" href="{{ route('users::card', $task->prev_task->user_id) }}" style="font-size:14px">
                                            {{ $task->prev_task->user->full_name }}
                                        </a>
                                    @else
                                        <span class="task-info__body-title">
                                                СИСТЕМА
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="right-bar-info__item" style="margin-bottom:0;">
                                @if($task->prev_task->status > 2)
                                    <div class="task-info__text-unit">
                                            <span class="task-info__head-title">
                                                Адрес объекта
                                            </span>
                                        <span class="task-info__body-title" @if($task->prev_task->project_id) href="{{ route('projects::card', $task->prev_task->project_id) }}" @endif>{{ $task->prev_task->object_address ? $task->prev_task->object_address : 'Проект не указан' }}</span>
                                    </div>
                                @endif
                                <div class="task-info__text-unit">
                                        <span class="task-info__head-title">
                                            Проект
                                        </span>
                                    <span class="task-info__body-title">
                                            <a href="{{ route('projects::card', $task->prev_task->project_id) }}" class="task-info__link task-info__body-title" target="_blank">{{ $task->prev_task->project_name }}</a>
                                        </span>
                                </div>
                                <div class="task-info__text-unit">
                                        <span class="task-info__head-title">
                                            Контрагент
                                        </span>
                                    <a class="task-info__link task-info__body-title" @if($task->prev_task->contractor_id) href="{{ route('contractors::card', $task->prev_task->contractor_id) }}" @endif>{{ $task->prev_task->contractor_name ? $task->prev_task->contractor_name : 'Контрагент не выбран' }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="left-bar-main">
                            <h5 class="task-info__title">{{ $task->prev_task->name }}</h5>
                            <div class="row">
                                <div class="col-md-12">
                                    <h6>
                                        Описание
                                    </h6>
                                    @if($task->prev_task->status == 1)
                                        <p class="task-info__ml">{{ $task->prev_task->description }}</p>
                                    @endif
                                    @if($task->prev_task->status > 2)
                                        <p class="task-info__ml">
                                            @if(in_array($task->prev_task->status, [3,4]))
                                                Необходимо произвести расчет объемов работ по проекту
                                            @elseif($task->prev_task->status == 5)
                                                Необходимо сформировать коммерческое предложение на основании данных объёмов работ
                                            @elseif(in_array($task->prev_task->status, [6,16]))
                                                Укажите результат согласования коммерческого предложения
                                            @elseif($task->prev_task->status == 7)
                                                Необходимо сформировать/изменить договор на основании коммерческих предложений
                                            @elseif($task->prev_task->status == 8)
                                                Необходимо согласовать договор
                                            @elseif($task->prev_task->status == 9)
                                                Необходимо приложить подписанный договор или гарантийное письмо
                                            @elseif($task->prev_task->status == 10)
                                                Необходимо подтвердить подписание договора
                                            @elseif($task->prev_task->status == 11)
                                                Необходимо подтвердить согласование договора с заказчиком
                                            @elseif($task->prev_task->status == 12)
                                                Сформирована новая версия коммерческого предложения. Необходимо провести контроль изменений и при необходимости исправить существующие или сформировать новые
                                            @elseif($task->prev_task->status == 14)
                                                Необходимо назначить исполнителя на расчет объемов
                                            @elseif($task->prev_task->status == 15)
                                                Необходимо назначить исполнителя на формирование коммерческого предложения
                                            @elseif($task->prev_task->status == 17)
                                                Ваша заявка на объём работ была отклонена. Вы можете отказаться от заявки или отправить её повторно (заявку можно изменить)
                                            @elseif($task->prev_task->status == 18)
                                                Ознакомьтесь с приведенным ОР, согласуйте или отклоните его
                                            @endif
                                            {{-- Описание из задачи (Пример: Необходимо сформировать коммерческое предложение на основании данных объёмов работ).--}}
                                        </p>
                                    @endif

                                </div>
                            </div>
                            @if(!$task->prev_task->redirects->where('task_id', $task->prev_task->id)->isEmpty())
                                @foreach($task->prev_task->redirects->where('task_id', $task->prev_task->id) as $task_redirect)
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <p><b>Исполнитель изменен {{ $task_redirect->created_at }}</b></p>
                                        </div>
                                        <div class="col-sm-9">
                                            <p style="padding-left:20px;">
                                                Предыдущий исполнитель : {{ $task->prev_task->responsible_user->where('id', $task_redirect->old_user_id)->first()->long_full_name }}
                                            <p style="padding-left:20px;">
                                                Новый исполнитель : {{ $task->prev_task->responsible_user->where('id', $task_redirect->responsible_user_id)->first()->long_full_name }}
                                            </p>
                                            <p style="padding-left:20px;">Комментарий: {{ $task_redirect->redirect_note }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                            @if(!$task->prev_task->task_files->where('task_id', $task->prev_task->id)->isEmpty())
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="left-bar-main_unit">
                                                <span class="task-info_label" style="margin-top:5px;">
                                                    Приложенные файлы
                                                </span>
                                            @foreach($task->prev_task->task_files->where('task_id', $task->prev_task->id) as $task_file)
                                                <a class="task-info_file" target="_blank" href="{{ asset('storage/docs/task_files/' . $task_file->file_name) }}" data-original-title=="{{ $task_file->created_at }} {{ $task_file->full_name }}">
                                                    {{ $task_file->original_name }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="row" style="flex-direction: row-reverse">
                    <div class="col-md-5">
                        <div class="right-bar-info__item close-date" >
                            <div class="task-info__text-unit">
                                    <span class="task-info__head-title">
                                        Установленный срок исполнения
                                    </span>
                                <span class="task-info__body-title prerendered-date">
                                        {{ $task->prev_task->expired_at }}
                                    </span>
                            </div>
                            @if($task->prev_task->is_solved)
                                <div class="task-info__text-unit">
                                        <span class="task-info__head-title">
                                            Время закрытия
                                        </span>
                                    <span class="task-info__body-title">
                                            {{ $task->prev_task->updated_at }}
                                        </span>
                                </div>
                                <div class="task-info__text-unit">
                                        <span class="task-info__head-title">
                                            Исполнитель
                                        </span>
                                    <span class="task-info__body-title">
                                            @if($task->prev_task->responsible_user_id)
                                            @if($task->prev_task->status == 1 || $task->prev_task->status == 2)
                                                {{ $task->prev_task->responsible_user->long_full_name }}
                                            @else
                                                {{ $task->prev_task->responsible_user->find($task->prev_task->responsible_user_id)->last_name }}
                                                {{ $task->prev_task->responsible_user->find($task->prev_task->responsible_user_id)->first_name }}
                                                {{ $task->prev_task->responsible_user->find($task->prev_task->responsible_user_id)->patronymic }}
                                            @endif
                                        @else
                                            Не найден
                                        @endif
                                        </span>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="left-bar-main">
                            @if($task->prev_task->is_solved)
                                <div class="row task-info__result">
                                    <div class="col-md-12">
                                        <h6 style="margin-top:5px;">
                                            Результат
                                        </h6>
                                        @if($task->prev_task->status == 1)
                                            <p class="task-info__ml">
                                                Задача выполнена
                                            </p>
                                        @endif
                                        @if($task->prev_task->status > 2)
                                            @if(in_array($task->prev_task->status, [4,5,7,12]))
                                                <p class="task-info__ml" > <i>{!! ($task->prev_task->revive_at ? "Задача была перенесена на  <b>" . date('d.m.Y' ,strtotime($task->prev_task->revive_at)) . '</b>' : 'Задача решена') !!} </i></p>
                                                <br>
                                                <span class="task-info_label" style="margin-top:5px; margin-left: 10px;">
                                                    Комментарий исполнителя
                                                </span>
                                                <p class="task-info__ml">   {{ $task->prev_task->descriptions[$task->prev_task->status] }}</p>
                                            @elseif(in_array($task->prev_task->status, [3,6,8,9,10,11,12,14,15,16,17,18,19,20]))

                                                <p class="task-info__ml">  <i>{!!($task->prev_task->revive_at ? "Задача была перенесена на <b>" . date('d.m.Y', strtotime($task->prev_task->revive_at)) . '</b>' : 'Задача решена') !!} </i></p>
                                                <br>
                                                <span class="task-info_label" style="margin-top:5px; margin-left: 10px;">
                                                    Комментарий исполнителя
                                                </span>
                                                <p class="task-info__ml"> {{ ($task->prev_task->final_note ? $task->prev_task->final_note : '') }} </p>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            @endif
                            <div class="row" style="margin-top:40px">
                                <div class="col-md-12">
                                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Закрыть</button>
                                    <a href="{{ route('projects::tasks', $task->prev_task->project_id) }}" class="btn btn-sm btn-secondary pull-right">События проекта</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endforeach
@endif
@endsection

@section('js_footer')
    <sctipt src="{{ asset('js/components/dashboard.js') }}"></sctipt>
<script>
 $(".select2, .select2-multiple").on('select2:open', function (e) {
     $('.select2-search input').prop('focus',false);
 });
</script>
<script>
    $('#js-select-contractor').select2({
        language: "ru",
        ajax: {
            url: '/tasks/get-contractors',
            dataType: 'json',
            delay: 250
        }
    });

    $('#js-select-contractor').on('change', function() {
        $('#project_choose').removeClass('d-none');
        $('#js-select-project').select2({
            language: "ru",
            ajax: {
                url: '/tasks/get-projects?contractor_id=' + $('#js-select-contractor').select2('val'),
                dataType: 'json',
                delay: 250
            }
        });
    });

    $('#js-select-user').select2({
        language: "ru",
        ajax: {
            url: '/tasks/get-users',
            dataType: 'json',
            delay: 250
        }
    });

    Date.prototype.addMinutes = function(m) {
        this.setMinutes(this.getMinutes() + m);
        return this;
    }

    add_datetime();

    setInterval(function(){
        $('#datetimepicker').datetimepicker('destroy');
        add_datetime();
    }, 55000);

    function add_datetime() {
        $('#datetimepicker').datetimepicker({
            minDate: new Date().addMinutes(32),
            sideBySide: true,
            locale: 'ru',
            icons: {
                time: "fa fa-clock-o",
                date: "fa fa-calendar",
                up: "fa fa-chevron-up",
                down: "fa fa-chevron-down",
                previous: 'fa fa-chevron-left',
                next: 'fa fa-chevron-right',
                today: 'fa fa-screenshot',
                clear: 'fa fa-trash',
                close: 'fa fa-remove'
            },
        });
    }

    $('input#uploadedFile').change(function(){
        var files = $(this)[0].files;
        document.getElementById('fileName').innerHTML = 'Количество файлов: ' + files.length;
        if(files.length === 1) {
            document.getElementById('fileName').innerHTML = 'Имя файла: ' + $('#uploadedFile').val().split('\\').pop();
            $('#submit').removeClass('d-none');
        } else if(files.length > 10){
            swal({
                title: "Внимание",
                text: "К задаче можно прикрепить не более десяти файлов!",
                type: 'warning',
            });
            $('#submit').addClass('d-none');
        } else {
            $('#submit').removeClass('d-none');
        }
    });
</script>

@if(Request::has('project_id') or Request::has('contractor_id'))
    <script>
        $('#project_choose').removeClass('d-none');
        $('#js-select-project').select2({
            language: "ru",
            ajax: {
                url: '/tasks/get-projects?contractor_id=' + $('#js-select-contractor').select2('val'),
                dataType: 'json',
                delay: 250
            }
        });
    </script>
@endif
<script type="text/javascript">
    var statistic = new Vue ({
        el: '#statistic',
        mounted() {
            const that = this;
            $('.prerendered-date').each(function() {
                const date = $(this).text();
                const content = that.isValidDate(date, 'DD.MM.YYYY HH:mm:ss') ? that.weekdayDate(date, 'DD.MM.YYYY HH:mm:ss', 'DD.MM.YYYY dd HH:mm:ss') : '-';
                const innerSpan = $('<span/>', {
                    'class': that.isWeekendDay(date, 'DD.MM.YYYY HH:mm:ss') ? 'weekend-day' : ''
                });
                innerSpan.text(content);
                $(this).html(innerSpan);
            })

            $('.delayed-date').each(function() {
                const date = $(this).text().trim();
                const content = that.isValidDate(date, 'DD.MM.YYYY HH:mm:ss') ? that.weekdayDate(date, 'DD.MM.YYYY HH:mm:ss', 'DD.MM.YYYY') : '-';
                const innerSpan = $('<span/>', {
                    'class': that.isWeekendDay(date, 'DD.MM.YYYY') ? 'weekend-day' : ''
                });
                innerSpan.text('Отложена до ' + content);
                $(this).html(innerSpan);
            })
        },
        methods: {
            isWeekendDay(date, format) {
                return [5, 6].indexOf(moment(date, format).weekday()) !== -1;
            },
            isValidDate(date, format) {
                return moment(date, format).isValid();
            },
            weekdayDate(date, inputFormat, outputFormat) {
                return moment(date, inputFormat).format(outputFormat ? outputFormat : 'DD.MM.YYYY dd');
            },
        }
    })

</script>

@endsection
