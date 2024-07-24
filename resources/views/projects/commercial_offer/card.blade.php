@extends('layouts.app')

@section('title', 'Коммерческие предложения')

@section('url', route('projects::card', $commercial_offer->project_id))

@section('css_top')
@vite('resources/css/projects.css')
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div aria-label="breadcrumb" role="navigation">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('projects::card', $commercial_offer->project_id) }}" class="table-link">{{ $work_volume->project_name }}</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Коммерческое предложение</li>
            </ol>
        </div>
    </div>
</div>
<div class="card @if ($commercial_offer->isNeedToBeColored()) card-important @endif">
    <div class="card-header">
        <div class="row">
            <div class="col-md-5">
                <h4 class="card-title" style="margin-top: 2px">
                    Коммерческое предложение
                    @if($commercial_offer->file_name)
                        <a rel="tooltip" target="_blank" href="{{ asset('storage/docs/commercial_offers/' . $commercial_offer->file_name) }}" class="btn-info btn-link btn-xs btn padding-actions" data-original-title='Посмотреть документ'>
                            <i class="fa fa-eye"></i>
                        </a>
                    @endif
                    @if(($resp ? $resp->user_id == Auth::id() : 0) and (!$commercial_offer->is_uploaded))
                        <button class="btn btn-sm btn-primary btn-outline btn-round" data-toggle="modal" data-target="#copy_CO_modal">
                            Скопировать КП (сделать основным)
                        </button>
                    @endif
                </h4>
            </div>
            <div class="col-md-7">
                @if(Auth::id() == 6 and !$commercial_offer->is_signed and $commercial_offer->status == 2 and $commercial_offer->file_name and !$commercial_offer->is_uploaded)
                <button id="sign_com_offer_btn" class="btn btn-sm btn-primary btn-outline btn-round pull-right" onclick="open_sign_modal()" style="margin-left:10px">
                    Подписать КП
                </button>
                @endif
                @if ($agree_task)
                    <button class="btn btn-sm btn-primary btn-outline btn-round pull-right" data-toggle="modal" data-target="#task16" style="margin-left:10px">
                         Согласование КП
                    </button>
                    @if(!Request::get('review_mode') and !$commercial_offer->is_uploaded and auth()->id() == 6)
                        <button class="btn btn-sm btn-primary btn-outline btn-round pull-right" onclick="window.location.href += '?review_mode=1'">
                            Включить режим согласования
                        </button>
                    @endif
                @endif
            </div>
        </div>
        <hr style="margin-top:10px">
    </div>
    <div class="card-body">
        <div class="accordions" id="accordion">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        <a data-target="#collapseOne" href="#" data-toggle="collapse">
                            Заявки
                            <b class="caret"></b>
                        </a>
                    </h4>
                </div>
                <div id="collapseOne" class="card-collapse collapse show">
                    <!-- Таблица заявок -->
                    <div class="card-body"><div class="card strpied-tabled-with-hover" >
                            <div class="fixed-table-toolbar toolbar-for-btn">
                                @if($commercial_offer->is_tongue != 2)
                                    @if($commercial_offer->status == 1)
                                        <div class="pull-right">
                                            <button class="btn-success btn-round btn-outline btn-sm add-btn btn" data-toggle="modal" data-target="#create_offer_request">
                                                <i class="fa fa-plus"></i>
                                                Заявка на редактирование
                                            </button>
                                        </div>
                                    @endif
                                @endif
                            </div>
                            @if($commercial_offer_requests->count())
                            <div class="table-responsive">
                                <table class="table table-hover mobile-table">
                                    <thead>
                                        <tr>
                                            <th>Автор</th>
                                            <th>Дата</th>
                                            <th class="text-right">
                                                Действия
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($commercial_offer_requests as $offer_request)
                                        @if ($offer_request->status === 1)
                                        <tr class="confirm">
                                        @elseif ($offer_request->status === 2)
                                        <tr class="reject">
                                        @else
                                        <tr>
                                        @endif
                                            <td data-label="Автор">
                                                @if($offer_request->last_name)
                                                {{ $offer_request->last_name }}
                                                {{ $offer_request->first_name }}
                                                {{ $offer_request->patronymic }}
                                                @else
                                                    Система
                                                @endif
                                            </td>
                                            <td data-label="Дата" class="prerendered-date">{{ $offer_request->updated_at }}</td>
                                            <td data-label="" class="text-right actions">
                                                <button rel="tooltip" class="btn-info btn-link btn-xs btn padding-actions mn-0" data-toggle="modal" data-target="#view-request-offer{{ $offer_request->id }}" data-original-title="Просмотр">
                                                    <i class="fa fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        <a data-target="#collapseTwo" href="#" data-toggle="collapse">
                            Коммерческое предложение
                            <b class="caret"></b>
                        </a>
                    </h4>
                </div>
                @if($split_wv_mat->count() or $work_volume->works->count())
                    <div id="collapseTwo" class="card-collapse collapse show">
                        <div class="card-body">
                            <div class="strpied-tabled-with-hover">


                                <div class="card">
                                    @if($subcontractors->count() > 0)
                                        <div class="card-title">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <h6 style="margin: 30px 15px 20px 5px">
                                                        Подрядчики
                                                    </h6>
                                                </div>
                                                @if($commercial_offer->status == 1)
                                                    @if($resp ? $resp->user_id == Auth::id() : 0)
                                                        <div class="col-md-4">
                                                            <div class="pull-right">
                                                                <a class="btn btn-outline btn-sm edit-btn" href="{{ route('projects::commercial_offer::edit', [$commercial_offer->project_id, $commercial_offer->id]) }}" style="margin-top: 3px">
                                                                    <i class="glyphicon fa fa-pencil-square-o"></i>
                                                                    Редактировать
                                                                </a>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>

                                        <div class="row">
                                        <div class="col-md-12">
                                            <div class="card strpied-tabled-with-hover">
                                                <div class="card table-with-links" style="margin-bottom:0">
                                                    <div class="table-responsive">
                                                        <table class="table mobile-table">
                                                            <thead>
                                                              <tr>
                                                                  <th>Название подрядчика</th>
                                                                  <th>Тип</th>
                                                                  <th>Приложенные файлы</th>
                                                              </tr>
                                                            </thead>
                                                            <tbody>
                                                            @foreach($subcontractors as $subcontractor)
                                                                @foreach($subcontractor->file as $file)
                                                                    <tr>
                                                                        <td data-label="Название подряда">{{ $subcontractor->short_name }}</td>
                                                                        <td data-label="Тип">Субподряд</td>
                                                                        <td data-label="Приложенные файлы">
                                                                            <a  target="_blank" href="{{ asset('storage/docs/commercial_offers_contractor_files/' . $file->file_name) }}">{{ $file->original_name }}</a>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                        <div class="card-title">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <h6 style="margin: 30px 15px 20px 5px">
                                                        Работы
                                                    </h6>
                                                </div>
                                                @if($commercial_offer->status == 1 and ($subcontractors->count() == 0))
                                                    @if($resp ? $resp->user_id == Auth::id() : 0)
                                                        <div class="col-md-4">
                                                            <div class="pull-right">
                                                                <a class="btn btn-outline btn-sm edit-btn" href="{{ route('projects::commercial_offer::edit', [$commercial_offer->project_id, $commercial_offer->id]) }}" style="margin-top: 3px">
                                                                    <i class="glyphicon fa fa-pencil-square-o"></i>
                                                                    Редактировать
                                                                </a>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>

                                        <div class="card strpied-tabled-with-hover">
                                        <div class="card table-with-links" style="margin-bottom:0">
                                            <div class="table-responsive">
                                                <table class="table mobile-table">
                                                    <thead>
                                                      <tr>
                                                          <th>Вид работы</th>
                                                          <th class="text-center">Ед. измерения</th>
                                                          <th class="text-center">Количество</th>
                                                          <th class="text-center">
                                                              Срок, дней</th>
                                                          <th class="text-center">Стоимость за ед.,руб</th>
                                                          <th class="text-center">Общая стоимость, руб</th>
                                                          <th class="text-center">Исполнитель</th>
                                                      </tr>
                                                    </thead>
                                                        <tbody>
                                                        @foreach($works->sortBy('order')->groupBy('work_group_id')->sortKeys() as $id => $group)
                                                            <tr @if($commercial_offer->reviews(2, $id)->get()->where('result_status', 1)->count() > 0 and Request::get('review_mode')) style="background-color: #fff595" @endif class="tr-title">
                                                                <td class="th-text">
                                                                    {{ $work_groups[$id] . ':' }}
                                                                    </td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td>
                                                                    <button rel="tooltip" reviewable_id="{{ $id }}" reviewable_type="{{ str_replace('\\', '.', get_class($group->first()->manual)) }}" data-toggle="modal" data-target="#" class="make_review d-none review-tr btn-danger btn-link btn-xs btn pd-0 mn-0" data-original-title="Оставить комментарий">
                                                                        <i class="fa fa-edit"></i>
                                                                    </button>
                                                                </td>
                                                            </tr>

                                                            <tr class="work" style="border-bottom:none"></tr>
                                                            @foreach($group as $work)
                                                                <tr @if($work->reviews->count() > 0 and Request::get('review_mode')) style="background-color: #fff595" @endif class="work">
                                                                    <td data-label="Вид работы">
                                                                        {{ $work->manual->name }}
                                                                        @if($work->materials->count() and $work->manual->show_materials)
                                                                            @if($id == 2)
                                                                                @php $combine_ids = []; @endphp
                                                                                (
                                                                                @foreach($work->materials->where('combine_id', '!=', null) as $index => $material)
                                                                                @if(!in_array($material->combine_id, $combine_ids))
                                                                                    {{ $material->combine_pile() }}
                                                                                        (
                                                                                        @foreach($work_volume_materials->where('combine_id' , $material->combine_id) as $key =>  $item)
                                                                                            @if(!in_array($item->combine_id, $combine_ids))
                                                                                                {{ $item->name . ' + ' }}
                                                                                            @else
                                                                                                {{ $item->name }}
                                                                                            @endif

                                                                                            @php $combine_ids[] = $material->combine_id; @endphp
                                                                                        @endforeach
                                                                                        @if ($index != $work->materials->where('combine_id', '!=', null)->count() - 2)
                                                                                        ),
                                                                                        @endif
                                                                                    @php
                                                                                        $combine_ids[] = $material->combine_id;
                                                                                    @endphp
                                                                                @endif

                                                                                @endforeach
                                                                                @foreach($work->materials->where('combine_id', '=', null) as $key => $material)
                                                                                    @if($key < $work->materials->where('combine_id', '=', null)->count() - 1)
                                                                                    {{ $material->name . ' ,' }}
                                                                                    @else
                                                                                    {{ $material->name }}
                                                                                    @endif
                                                                                @endforeach
                                                                                )
                                                                            @else
                                                                                (
                                                                                @foreach($work->shown_materials->slice(0, -1) as $material)
                                                                                    {{ $material->name . ','}}
                                                                                @endforeach

                                                                                {{ $work->shown_materials->last()->name }}
                                                                                )
                                                                            @endif
                                                                        @endif
                                                                    </td>
                                                                    <td data-label="Ед. измерения"  class="text-center">{{ $work->unit }}</td>
                                                                    <td data-label="Количество"  class="text-center">{{ number_format($work->count, 3, '.', '') }}</td>
                                                                    <td data-label="Срок, дней" class="text-center">{{ $work->term }}</td>
                                                                    <td data-label="Стоимость за ед.,руб"  class="text-center">{{ number_format($work->price_per_one, 2, ',', ' ') }}</td>
                                                                    <td data-label="Общая стоимость, руб" class="text-center">{{ number_format($work->result_price, 2, ',', ' ') }}</td>
                                                                    <td data-label="Исполнитель">{{ $work->contractor_name ?? ($work->subcontractor()->short_name ?? '') }}</td>
                                                                    <td>
                                                                        <button rel="tooltip" reviewable_id="{{ $work->id }}" reviewable_type="{{ str_replace('\\', '.', get_class($work)) }}" data-toggle="modal" data-target="#" class="make_review d-none review-tr btn-danger btn-link btn-xs btn pd-0 mn-0" data-original-title="Оставить комментарий">
                                                                            <i class="fa fa-edit"></i>
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                            <tr class="total">
                                                                <td class="result">
                                                                    {{ ['Итого по шпунтовым работам:',
                                                                'Итого по устройству свайного поля:',
                                                                'Итого по земельным работам:',
                                                                'Итого по монтажу систем крепления:',
                                                                'Итого по дополнительным работам:',
                                                                ][$id - 1] }}
                                                                    </td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td class="text-center sum">{{ number_format($group->pluck('result_price')->sum(), 2, ',', ' ') }}</td>
                                                                <td></td>
                                                                <td></td>
                                                            </tr>
                                                        @endforeach
                                                        @php $result_works_cost = $works->pluck('result_price')->sum(); @endphp
                                                        <tr class="medium-total">
                                                            <td class="result">Итого по работам:</td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td class="text-center sum">{{ number_format($result_works_cost, 2, ',', ' ') }}</td>
                                                            <td></td>
                                                            <td></td>
                                                        </tr>
                                                        </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                        @if($material_subcontractors->count() > 0)
                                            <div class="card-title">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <h6 style="margin: 30px 15px 20px 5px">
                                                            Поставщики
                                                        </h6>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="card strpied-tabled-with-hover">
                                                        <div class="card table-with-links" style="margin-bottom:0">
                                                            <div class="table-responsive">
                                                                <table class="table mobile-table">
                                                                    <thead>
                                                                    <tr>
                                                                        <th>Название поставщика</th>
                                                                        <th>Тип</th>
                                                                        <th>Приложенные файлы</th>
                                                                    </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                    @foreach($material_subcontractors as $subcontractor)
                                                                        @foreach($subcontractor->file as $file)
                                                                            <tr>
                                                                                <td data-label="Название подряда">{{ $subcontractor->short_name }}</td>
                                                                                <td data-label="Тип">Поставщик</td>
                                                                                <td data-label="Приложенные файлы">
                                                                                    <a  target="_blank" href="{{ asset('storage/docs/commercial_offers_contractor_files/' . $file->file_name) }}">{{ $file->original_name }}</a>
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="card-title">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <h6 style="margin: 30px 15px 20px 5px">
                                                        Материалы
                                                    </h6>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Таблица материалов -->
                                    <div class="card strpied-tabled-with-hover">
                                        <div class="card table-with-links" style="margin-bottom:0">
                                            <div class="table-responsive">
                                                <table class="table mobile-table">
                                                    <thead>
                                                      <tr>
                                                          <th>Наименование материала</th>
                                                          <th class="text-center">Ед. измерения</th>
                                                          <th class="text-center">Количество</th>
                                                          <th class="text-center">Принадлежность</th>
                                                          <th class="text-center">Стоимость за ед.,руб</th>
                                                          <th class="text-center">Общая стоимость, руб</th>
                                                          <th class="text-center">Поставщик</th>
                                                          <th class="text-center">Б/У</th>
                                                      </tr>
                                                    </thead>
                                                    <tbody>
                                                    @php $security_pay = 0; @endphp
                                                    <!-- Материалы для всего -->
                                                    @foreach($work_volume_materials->groupBy('work_group_id') as $id => $group)
                                                        <tr @if($commercial_offer->reviews(1, $group->first()->manual->category->id)->get()->count() > 0 and Request::get('review_mode')) style="background-color: #fffb95" @endif class="tr-title">
                                                            <td class="th-text">
                                                                {{ ['Материалы для устройства шпунтового ограждения:',
                                                                'Материалы для устройства свайного поля:',
                                                                'Материалы для земельных работ:',
                                                                'Материалы для монтажа систем крепления:',
                                                                'Материалы для дополнительных работ:',
                                                                ][$id - 1] }}
                                                            </td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td>
                                                                <button  rel="tooltip" reviewable_id="{{ $group->first()->manual->category->id }}" reviewable_type="{{ 'MaterialWorkRelation' }}" data-toggle="modal" data-target="#" class="make_review d-none review-tr btn-danger btn-link btn-xs btn pd-0 mn-0" data-original-title="Оставить комментарий">
                                                                    <i class="fa fa-edit"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                        @foreach($group as $material)
                                                            @foreach($splits->where('man_mat_id', $material->manual_material_id) as $split)
                                                                    <tr @if($split->reviews->count() > 0 and Request::get('review_mode')) style="background-color: #fff595" @endif>
                                                                        @if(in_array($split->type, [1,3,5]))
                                                                            <td data-label="Наименование материала">{{ $split->name }}</td>
                                                                            <td data-label="Ед. измерения" class="text-center">{{ $material->unit }}</td>
                                                                            <td data-label="Количество" class="text-center">{{ number_format($split->count, 3, '.', '') }}</td>
                                                                            <td data-label="Принадлежность" class="text-center">{{ ['Продажа','Продажа с обратным выкупом',
                                                                    'Аренда '.(($split->type === "3") ? '('.$split->time.' мес.)' : ''),
                                                                    'Аренда '.(($split->type === "4") ? '('.$split->time.' мес.) ' : '').'с обеспечительным платежём','Давальческий'][$split->type - 1] }}
                                                                            </td>
                                                                            <td data-label="Стоимость за ед.,руб" class="text-center">{{ number_format((float)$split->price_per_one, 2, ',', ' ') }}</td>
                                                                            <td data-label="Общая стоимость, руб" class="text-center">{{ number_format((float)$split->result_price, 2, ',', ' ') }}</td>
                                                                            <td data-label="Поставщик" class="text-center">{{ $split->short_name }}</td>
                                                                            <td data-label="Б/У" class="text-center">@if($split->is_used)<i class="fa fa-check"></i>@endif</td>
                                                                            <td>
                                                                                <button rel="tooltip" reviewable_id="{{ $split->id }}" reviewable_type="{{ str_replace('\\', '.', get_class($split))}}" data-toggle="modal" data-target="#" class="make_review d-none review-tr btn-danger btn-link btn-xs btn pd-0 mn-0" data-original-title="Оставить комментарий">
                                                                                    <i class="fa fa-edit"></i>
                                                                                </button>
                                                                            </td>
                                                                        @endif
                                                                    </tr>
                                                                    @if($split->security()->exists())
                                                                        <tr @if($split->security->reviews->count() > 0 and Request::get('review_mode')) style="background-color: #fff595" @endif>
                                                                            <td>Обеспечительный платеж за {{ $split->name }}</td>
                                                                            <td data-label="Ед. измерения" class="text-center">{{ $material->unit }}</td>
                                                                            <td data-label="Количество" class="text-center">{{ number_format($split->security->count, 3, '.', '') }}</td>
                                                                            <td class="text-center"></td>
                                                                            <td data-label="Стоимость за ед.,руб"  class="text-center">{{ number_format((float)$split->security->security_price_one, 2, ',', ' ') }}</td>
                                                                            <td data-label="Общая стоимость, руб" class="text-center general_material_price_td">
                                                                                {{ number_format((float)$split->security->security_price_result, 2, ',', ' ') }}
                                                                            </td>
                                                                            <td class="text-center"></td>
                                                                            <td></td>
                                                                            <td>
                                                                                <button rel="tooltip" reviewable_id="{{ $split->security->id }}" reviewable_type="{{ str_replace('\\', '.', get_class($split->security))}}" data-toggle="modal" data-target="#" class="make_review d-none review-tr btn-danger btn-link btn-xs btn pd-0 mn-0" data-original-title="Оставить комментарий">
                                                                                    <i class="fa fa-edit"></i>
                                                                                </button>
                                                                            </td>
                                                                        </tr>
                                                                    @endif
                                                                    @if($split->buyback()->exists())
                                                                        <tr @if($split->buyback->reviews->count() > 0 and Request::get('review_mode')) style="background-color: #fff595" @endif>
                                                                            <td>Обратный выкуп за {{ $split->name }}</td>
                                                                            <td data-label="Ед. измерения" class="text-center">{{ $material->unit }}</td>
                                                                            <td data-label="Количество" class="text-center">{{ number_format($split->buyback->count, 3, '.', '') }}</td>
                                                                            <td class="text-center"></td>
                                                                            <td data-label="Стоимость за ед.,руб" class="text-center">{{ number_format((float)$split->buyback->security_price_one, 2, ',', ' ') }}</td>
                                                                            <td data-label="Общая стоимость, руб" class="text-center">{{ number_format((float)$split->buyback->security_price_result, 2, ',', ' ') }}</td>
                                                                            <td class="text-center"></td>
                                                                            <td></td>
                                                                            <td>
                                                                                <button rel="tooltip" reviewable_id="{{ $split->buyback->id }}" reviewable_type="{{ str_replace('\\', '.', get_class($split->buyback))}}" data-toggle="modal" data-target="#" class="make_review d-none review-tr btn-danger btn-link btn-xs btn pd-0 mn-0" data-original-title="Оставить комментарий">
                                                                                    <i class="fa fa-edit"></i>
                                                                                </button>
                                                                            </td>
                                                                        </tr>
                                                                    @endif
                                                                @endforeach
                                                        @endforeach
                                                        <tr class="total">
                                                            <td class="result">
                                                                {{ ['Итого по материалам для устройства шпунтового ограждения',
                                                                'Итого по материалам для устройства свайного поля',
                                                                'Итого по материалам для земельных работ',
                                                                'Итого по материалам для монтажа систем крепления',
                                                                'Итого по материалам для дополнительных работ',
                                                                    ][$id - 1] }}
                                                            </td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td class="text-center sum">{{ number_format(array_sum($splits->whereIn('man_mat_id', $group->pluck('manual_material_id')->unique())->pluck('result_price')->toArray()) + array_sum($splits->where('type', 4)->whereIn('man_mat_id', $group->pluck('manual_material_id')->unique())->pluck('security_price_result')->toArray()), 2, ',', ' ') }}</td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                        </tr>
                                                    @endforeach

                                                    @if ($splits->where('type', 4)->count() > 0)
                                                        <tr class="tr-title">
                                                            <td class="th-text">Обеспечительный платёж за материалы:</td>
                                                            <td class="th-text"></td>
                                                            <td class="text-center"></td>
                                                            <td class="text-center"></td>
                                                            <td class="text-center"></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                        </tr>
                                                        @foreach ($splits->where('type', 4) as $secure)
                                                            <tr>
                                                                <td>Обеспечительный платеж за {{ $secure->parent->name }}</td>
                                                                <td data-label="Ед. измерения" class="text-center">{{ $secure->WV_material->unit }}</td>
                                                                <td data-label="Количество" class="text-center">{{ number_format($secure->count, 3, '.', '') }}</td>
                                                                <td class="text-center"></td>
                                                                <td data-label="Стоимость за ед.,руб" class="text-center each_material_price_td">
                                                                    {{ number_format((float)$splits->where('type', 4)->where('man_mat_id', $secure->WV_material->manual_material_id)->pluck('security_price_one')->first(), 2, ',', ' ') }}
                                                                </td>
                                                                <td data-label="Общая стоимость, руб" class="text-center general_material_price_td">
                                                                    {{ number_format((float)$splits->where('type', 4)->where('man_mat_id', $secure->WV_material->manual_material_id)->pluck('security_price_result')->first(), 2, ',', ' ') }}
                                                                </td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                            </tr>
                                                        @endforeach
                                                    @endif

                                                    @if ($splits->where('type', 2)->count() > 0)
                                                        <tr class="tr-title">
                                                            <td class="th-text">Обратный выкуп материалов:</td>
                                                            <td class="th-text"></td>
                                                            <td class="text-center"></td>
                                                            <td class="text-center"></td>
                                                            <td class="text-center"></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                        </tr>
                                                        @foreach ($splits->where('type', 2) as $buyback)
                                                            <tr>
                                                                <td data-label="Наименование" class="material_name_td">Обратный выкуп ({{ $buyback->parent->name }})</td>
                                                                <td data-label="Ед. измерения" class="text-center">{{ $buyback->WV_material->unit }}</td>
                                                                <td data-label="Количество" class="text-center material_count">{{ number_format($buyback->count, 3, '.', '') }}</td>
                                                                <td data-label="Принадлежность" class="text-center material_term"></td>
                                                                <td data-label="Стоимость за ед.,руб" class="text-center each_material_price_td">
                                                                    {{ number_format((float)$buyback->security_price_one, 2, ',', ' ') }}
                                                                </td>
                                                                <td data-label="Общая стоимость, руб" class="text-center general_material_price_td">
                                                                    {{ number_format((float)$buyback->security_price_result, 2, ',', ' ') }}
                                                                </td>

                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                            </tr>
                                                        @endforeach
                                                    @endif
                                                    @if ($splits->whereIn('type', [2,4])->count() > 0)
                                                        <tr class="total">
                                                            <td class="result">
                                                                Итого по {{ [
                                                                2 => 'обратному выкупу',
                                                                4 => 'возврату обеспечительного платежа',
                                                                3 => 'обратному выкупу и возврату обеспечительного платежа'
                                                                ][$splits->whereIn('type', [2,4])->pluck('type')->unique()->count() == 2 ? 3 : $splits->whereIn('type', [2,4])->pluck('type')->unique()[0]] }}
                                                            </td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td class="text-center sum">
                                                                {{ number_format((float)array_sum($splits->pluck('security_price_result')->toArray()), 2, ',', ' ') }}
                                                            </td>
                                                            <td></td>

                                                            <td></td>
                                                            <td></td>
                                                        </tr>
                                                    @endif

                                                        @php $result_material_cost = array_sum($splits->pluck('result_price')->toArray()) + array_sum($splits->where('type', 4)->pluck('security_price_result')->toArray()); @endphp
                                                        @php $result_security_pay = array_sum($splits->pluck('security_price_result')->toArray()); @endphp
                                                        <tr class="medium-total">
                                                            <td class="text-left result">Итого по материалам:</td>
                                                            <td class="text-center"></td>
                                                            <td class="text-center"></td>
                                                            <td class="text-center"></td>
                                                            <td class="text-center"></td>
                                                            <td class="text-center sum">{{ number_format($result_material_cost, 2, ',', ' ') }}</td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                        </tr>
                                                        @php $result_cost = $result_works_cost + $result_material_cost; @endphp
                                                        <tr class="grand-total">
                                                            <td class="text-left result">Итого по проекту:</td>
                                                            <td class="text-center"></td>
                                                            <td class="text-center"></td>
                                                            <td class="text-center"></td>
                                                            <td class="text-center"></td>
                                                            <td class="text-center sum">{{ number_format($result_cost, 2, ',', ' ') }}</td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                        </tr>
                                                        @php $result_cost = $result_cost - $result_security_pay; @endphp

                                                        @if($splits->whereIn('type', [2,4])->count() > 0)
                                                        <tr class="grand-total">
                                                            <td class="text-left result">Итого по проекту после обратного выкупа и возврата обеспечительного платежа:</td>
                                                            <td class="text-center"></td>
                                                            <td class="text-center"></td>
                                                            <td class="text-center"></td>
                                                            <td class="text-center"></td>
                                                            <td class="text-center sum">{{ number_format($result_cost, 2, ',', ' ') }}</td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                        </tr>
                                                        @endif
                                                    <tr>
                                                        <td colspan="3" class="text-left"></td>
                                                        <td class="text-center">НДС(%):</td>
                                                        <td class="text-center nds-1">{{ number_format($commercial_offer->nds, 2, ',', ' ') }}
                                                        </td>
                                                        <td class="text-center nds-2"> {{ number_format($result_cost - $result_cost / ($commercial_offer->nds / 100 + 1), 2, ',', ' ') }}
                                                        </td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                    </tr>
                                                    </tbody>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    @if(($commercial_offer->advancements)->count())
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h6 style="margin:20px 0 15px 0">Авансирование</h6>
                                            <ol>
                                                @foreach($commercial_offer->advancements as $advancement)
                                                <li @if($advancement->reviews->count() > 0 and Request::get('review_mode')) style="background-color: #fff595" @endif>
                                                    {{ $advancement->description }}
                                                    <button rel="tooltip" reviewable_id="{{ $advancement->id }}" reviewable_type="{{ str_replace('\\', '.', get_class($advancement))}}" data-toggle="modal" data-target="#make_review" class="make_review d-none review-li btn-danger btn-link btn-xs btn pd-0 mn-0" data-original-title="Оставить комментарий">
                                                        <i class="fa fa-edit"></i>
                                                    </button>
                                                </li>
                                                @endforeach
                                            </ol>
                                        </div>
                                    </div>
                                    @endif
                                    @if($commercial_offer->notes->count())
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h6 style="margin:20px 0 15px 0">Примечания</h6>
                                                <ol>
                                                    @foreach ($commercial_offer->notes as $note)
                                                        <li @if($note->reviews->count() > 0 and Request::get('review_mode')) style="background-color: #fff595" @endif>
                                                            {{ $note->note }}
                                                            <button rel="tooltip" reviewable_id="{{ $note->id }}" reviewable_type="{{ str_replace('\\', '.', get_class($note))}}" data-toggle="modal" data-target="#make_review" class="make_review d-none review-li btn-danger btn-link btn-xs btn pd-0 mn-0" data-original-title="Оставить комментарий">
                                                                <i class="fa fa-edit"></i>
                                                            </button>
                                                        </li>
                                                    @endforeach
                                                </ol>
                                            </div>
                                        </div>
                                    @endif
                                    @if($commercial_offer->requirements->count())
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h6 style="margin:20px 0 15px 0">Требования</h6>
                                                <ol>
                                                    @foreach ($commercial_offer->requirements as $requirement)
                                                        <li @if($requirement->reviews->count() > 0 and Request::get('review_mode')) style="background-color: #fff595" @endif>
                                                            {{ $requirement->requirement }}
                                                            <button rel="tooltip" reviewable_id="{{ $requirement->id }}" reviewable_type="{{ str_replace('\\', '.', get_class($requirement))}}" class="review-li btn-danger btn-link btn-xs btn pd-0 mn-0 make_review d-none" data-original-title="Оставить комментарий">
                                                                <i class="fa fa-edit"></i>
                                                            </button>
                                                        </li>
                                                    @endforeach
                                                </ol>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="card-footer">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="text-right">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="card-body">
                        <p>Карточка была сформирована автоматически</p>
                        @if($resp ? $resp->user_id == Auth::id() : 0)
                            @if ($commercial_offer->is_tongue == 1)
                                <div>
                                    <button class="btn-round btn-outline btn-sm add-btn btn" data-toggle="modal" data-target="#save-offer" onclick="upload_CO(1)">
                                        Загрузить готовое КП (шпунт)
                                    </button>
                                </div>
                            @endif
                            @if ($commercial_offer->is_tongue == 0)
                                <div>
                                    <button class="btn-round btn-outline btn-sm add-btn btn" data-toggle="modal" data-target="#save-offer" onclick="upload_CO(0)">
                                        Загрузить готовое КП (свая)
                                    </button>
                                </div>
                            @endif
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Модалки -->
@if($commercial_offer->type != 2)
@include('projects.modules.upload_modal')
@endif
@if(Auth::id() == 6 and !$commercial_offer->is_signed and $commercial_offer->status == 2 and $commercial_offer->file_name and !$commercial_offer->is_uploaded)

<div class="modal fade bd-example-modal-lg show" id="sign_com_offer" role="dialog" aria-labelledby="modal-search">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Заявка на КП</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <hr style="margin-top:0">
                <div class="card border-0" >
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <h6 style="margin:10px 0">Выберите сертификат</h6>
                                    <button type="button" onclick="refreshSelect()" class="btn btn-sm btn-success btn-outline">Обновить список сертификатов</button>
                                <select class="selectpicker" id="cert-selector" title="Сертификат не выбран">
                                    <option value="" hidden selected disabled>Обновите список сертификатов</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="getPdf()" class="btn btn-primary">Подписать и скачать</button>
            </div>
        </div>
    </div>
</div>
@endif

@foreach ($commercial_offer_requests as $offer_request)
<div class="modal fade bd-example-modal-lg show" id="view-request-offer{{ $offer_request->id }}" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
       <div class="modal-content">
           <div class="modal-header">
               <h5 class="modal-title">Заявка на КП</h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                   <span aria-hidden="true">×</span>
               </button>
           </div>
           <div class="modal-body">
               <hr style="margin-top:0">
               <div class="card border-0" >
                   <div class="card-body">
                       <div class="row">
                           <div class="col-md-12">
                               <h6 style="margin:10px 0">Описание</h6>
                               <p>
                                   {{ $offer_request->description }}
                               </p>
                           </div>
                       </div>
                       @if ($offer_request->files->where('is_result', 0)->count() > 0)
                           <div class="row">
                               <div class="col-md-12">
                                   <label class="control-label">Приложенные файлы</label>
                                   <br>
                                   @foreach($offer_request->files->where('is_result', 0)->where('is_proj_doc', 0) as $file)
                                       <a target="_blank" href="{{ asset('storage/docs/commercial_offer_request_files/' . $file->file_name) }}">
                                           {{ $file->original_name }}
                                       </a>
                                       <br>
                                   @endforeach

                                   @foreach($offer_request->files->where('is_result', 0)->where('is_proj_doc', 1) as $file)
                                       <a target="_blank" href="{{ asset('storage/docs/project_documents/' . $file->file_name) }}">
                                           {{ $file->original_name }}
                                       </a>
                                       <br>
                                   @endforeach
                               </div>
                           </div>
                       @endif
                       <br>
                       @if(in_array($offer_request->status, [1,2]))
                       <div class="row">
                           <div class="col-md-12">
                               <div class="form-group">
                                   <div class="row">
                                       <div class="col-md-12">
                                           <h6 style="margin:10px 0">
                                               Решение
                                           </h6>
                                           <p class="form-control-static">{{ $offer_request->result_comment }}</p>
                                       </div>
                                   </div>
                                   @if ($offer_request->files->where('is_result', 1)->count() > 0)
                                       <div class="row">
                                           <div class="col-md-12">
                                               <label class="control-label">Приложенные файлы</label>
                                               <br>
                                               @foreach($offer_request->files->where('is_result', 1)->where('is_proj_doc', 0) as $file)
                                                   <a target="_blank" href="{{ asset('storage/docs/commercial_offer_request_files/' . $file->file_name) }}">
                                                       {{ $file->original_name }}
                                                   </a>
                                                   <br>
                                               @endforeach

                                               @foreach($offer_request->files->where('is_result', 1)->where('is_proj_doc', 1) as $file)
                                                   <a target="_blank" href="{{ asset('storage/docs/project_documents/' . $file->file_name) }}">
                                                       {{ $file->original_name }}
                                                   </a>
                                                   <br>
                                               @endforeach
                                           </div>
                                       </div>
                                   @endif
                               </div>
                           </div>
                       </div>
                       @endif
                   </div>
               </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
           </div>
        </div>
    </div>
</div>
@endforeach

<div class="modal fade bd-example-modal-lg show" id="make_review" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Оставить комментарий</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <hr style="margin-top:0">
                <div class="card border-0" >
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <h6 style="margin:10px 0">Комментарий</h6>
                                <input type="hidden" id="form_reviewable_id" value="">
                                <input type="hidden" id="form_reviewable_type" value="">
                                <input type="hidden" id="form_reviewable_el" value="">
                                <textarea class="form-control textarea-rows" id="review_text" name="requirement" required placeholder="Укажите комментарий"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                <button type="button" onclick="make_review(this)" class="btn btn-primary float-right" data-dismiss="modal">Сохранить</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade bd-example-modal-lg show" id="copy_CO_modal" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Копирование КП</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <hr style="margin-top:0">
                <div class="card border-0" >
                    <div class="card-body">
                        <form id="make_copy" action="{{ route('projects::commercial_offer::make_copy', [$commercial_offer->project_id, $commercial_offer->id]) }}" method="post">
                        @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <h6 style="margin:10px 0">Выберите проект</h6>
                                    <select id="select_project" name="project_id" style="width:100%;" required>
                                        <option value="">Поиск проекта</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <h6 style="margin:10px 0">Наименование</h6>
                                    <select name="option" id="select-option" style="width:100%" required>
                                        <option value="">Выберите или впишите своё</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                <button type="submit" form="make_copy" class="btn btn-success" >Скопировать</button>
            </div>
        </div>
    </div>
</div>


@if($agree_task)
<div class="modal fade bd-example-modal-lg show" id="task16" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Согласование КП</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <hr style="margin-top:0">
                <div class="card border-0" >
                    <form id="form_solve_task" class="form-horizontal" action="{{ route('tasks::solve_task', $agree_task->id) }}" method="post">
                        @csrf
                        <div class="form-group">
                            <div class="row">
                                <label class="col-sm-3 col-form-label">Результат<star class="star">*</star></label>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <select id="status_result" name="status_result" class="selectpicker" data-title="Результат" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                                            <option value="accept">Согласовано</option>
                                            <option value="decline">Отклонить</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group collapse" id="comment">
                            <div class="row">
                                <label class="col-sm-3 col-form-label">Комментарий<star class="star">*</star></label>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <textarea id="result_note" class="form-control textarea-rows" maxlength="300" name="final_note" placeholder="Опишите причину" required></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                <button id="sendForm" form="form_solve_task" class="btn btn-info d-none" disabled="disabled">Отправить</button>
            </div>
        </div>
    </div>
</div>
@endif

@if($commercial_offer->is_tongue != 2)
<div class="modal fade bd-example-modal-lg show" id="create_offer_request" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
       <div class="modal-content">
           <div class="modal-header">
               <h5 class="modal-title">
                   @if(!$commercial_offer_requests->count())
                   Заявка на создание КП
                   @else
                   Заявка на редактирование КП
                   @endif
               </h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                   <span aria-hidden="true">×</span>
               </button>
           </div>
           <div class="modal-body">
               <hr style="margin-top:0">
               <div class="card border-0" >
                   <div class="card-body ">
                       <form id="create_offer_request_form" class="form-horizontal" action="{{ route('projects::commercial_offer::requests::store', [$commercial_offer->project_id, 'offer_id' => $commercial_offer->id]) }}" method="post" enctype="multipart/form-data">
                           @csrf
                           <div class="row">
                               <div class="col-sm-12">
                                   <div class="form-group">
                                       <label>Описание<star class="star">*</star></label>
                                       <textarea class="form-control textarea-rows" name="description" required maxlength="500"></textarea>
                                   </div>
                               </div>
                           </div>
                           <div class="row">
                               <div class="col-sm-12">
                                   <label for="" style="font-size:0.80">
                                       Приложенные файлы
                                   </label>
                                   <div class="col-sm-6">
                                       <div class="file-container">
                                           <div id="fileName" class="file-name"></div>
                                           <div class="file-upload ">
                                               <label class="pull-right">
                                                   <span><i class="nc-icon nc-attach-87" style="font-size:25px; line-height: 40px"></i></span>
                                                   <input type="file" name="documents[]" accept="*" id="uploadedOfferFiles" class="form-control-file file" onchange="getFileName(this)" multiple>
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
                <button id="submit_offer_request" type="submit" form="create_offer_request_form" class="btn btn-info">Сохранить</button>
           </div>
        </div>
    </div>
</div>
@endif

@can('work_with_digital_signature')
    @include('sections.ecp')
@endcan

@endsection

@section('js_footer')
@if(Request::has('req'))
    <script>
        $('#view-request-offer' + {{ Request::get('req') }}).modal('show');
    </script>
@endif

<script>

    var project_id = 0;

    $('#select_project').select2({
        language: "ru",
        ajax: {
            url: '{{ route('tasks::get_projects') }}',
            dataType: 'json',
            delay: 250,
        }
    });

    $('#select-option').select2({
        disabled: true,
        tags: true,
        ajax: {
            url: '{{ route('projects::get_options') }}',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term,
                    project_id: project_id,
                    type: {{ $commercial_offer->is_tongue }},
                };
            },
        }
    });


    $('#select_project').on('select2:select', function (e) {
        project_id = e.params.data.id;
        $('#select-option').prop("disabled", false);
    });
    var message = new Vue({});

    function upload_CO(is_tongue) {
        // $('#uploadedFile5')[0].value = '';
        // $('#uploadedFile5').trigger('change');
        if (is_tongue) {
            $('#select_upload_tongue_block').show();
            $('#select_upload_pile_block').hide();
        } else {
            $('#select_upload_tongue_block').hide();
            $('#select_upload_pile_block').show();
        }

        $('#submit_form_attach_document').attr('disabled', '');
        $('#uploaded_CO_type').val(is_tongue);
        var refuse_pile = {{ $work_volumes->where('type', 1)->where('status', 1)->count() }};
        var refuse_tongue = {{ $work_volumes->where('type', 0)->where('status', 1)->count() }};
        if (is_tongue ? refuse_tongue : refuse_pile) {
            $('#refuse_upload').text('На данный момент есть объем работ в статусе "В работе". Его необходимо выполнить.');
            message.$message({
                showClose: true,
                message: 'На данный момент есть объем работ в статусе "В работе". Его необходимо выполнить.',
                type: 'error',
                duration: 5000
            });
        } else {
            $('#refuse_upload').text('При загрузке готового документа коммерческого предложения будет недоступна функция "Объединить КП".');
        }
    }

    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    $('#upload').click(function(){
        $('#file').fadeIn(300);
        $('#upload').hide();
    });


$( "button.review-li" ).hover(function() {
    var li = $(this)[0].closest('li');
    if (!($(this).attr('fading') == 'true')) {
        $(this).attr('fading', 'true');
        $(li).fadeOut(500);
        $(li).fadeIn(500);
        setTimeout(function(e) {
            $(e).removeAttr('fading');
            }, 1500, this);
    }
}, null);
$( "button.review-tr" ).hover(function() {
    var tr = $(this)[0].closest('tr');
    if (!($(this).attr('fading') == 'true')) {
        $(this).attr('fading', 'true');
        $(tr).fadeOut(500);
        $(tr).fadeIn(500);
        setTimeout(function(e) {
            $(e).removeAttr('fading');
            }, 1500, this);
    }
}, null);

$( ".review-tr,.review-li" ).click(function() {
    var that = this;
    $.ajax({
        url:"{{ route('projects::get_review') }}", //SET URL
        type: 'GET', //CHECK ACTION
        data: {
            _token: CSRF_TOKEN,
            reviewable_type: $(this).attr('reviewable_type'), //SET ATTRS
            reviewable_id: $(this).attr('reviewable_id'), //SET ATTRS
            commercial_offer_id: {{ $commercial_offer->id }}, //SET ATTRS
        },
        dataType: 'JSON',
        success: function (data) {
            $('#form_reviewable_type').val($(that).attr('reviewable_type'));
            $('#form_reviewable_id').val($(that).attr('reviewable_id'));
            $('#form_reviewable_el').val($(that));
            $('#review_text').val(data);
            $('#make_review').modal('toggle');
        }
    });
});

    @if (in_array(Auth::id(), [6, 12]) and Request::get('review_mode') == '1')
        $('.make_review').removeClass('d-none');
    @endif
    function make_review(){
        $.ajax({
            url:"{{ route('projects::store_review', $commercial_offer->id) }}", //SET URL
            type: 'GET', //CHECK ACTION
            data: {
                _token: CSRF_TOKEN,
                review: $('#review_text').val(), //SET ATTRS
                form_reviewable_type: $('#form_reviewable_type').val(), //SET ATTRS
                form_reviewable_id: $('#form_reviewable_id').val(), //SET ATTRS
                commercial_offer_id: {{ $commercial_offer->id }}, //SET ATTRS
                result_status: 0,
            },
            dataType: 'JSON',
            success: function (data) {
                var target_to_paint = $("[reviewable_type='" + $('#form_reviewable_type').val() + "'][reviewable_id='" + $('#form_reviewable_id').val() + "']").parent();
                if (target_to_paint.prop('nodeName') != 'LI') {
                    target_to_paint = target_to_paint.parent();
                }
                target_to_paint.css('background-color', '#fff595');
            }
        });

    }

$('input#uploadedFile5').change(function(){
    var files = $(this)[0].files;
    var fileExtension = ['jpeg', 'jpg', 'png', 'gif', 'bmp', 'txt', 'doc', 'docx', 'xls', 'xlsx', 'pdf', 'rtf', 'dwg', 'dwl', 'dwl2', 'dxf', 'mpp', 'pptx'];

    if ($.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
        swal({
            title: "Внимание",
            text: "Поддерживаемые форматы: "+fileExtension.join(', '),
            type: 'warning',
        });
        $(this).val('');
        $(this).parent().parent().siblings('#fileName5')[0].innerHTML = '';

        return false;
    } else {
        document.getElementById('fileName5').innerHTML = 'Количество файлов: ' + files.length;
        if (files.length === 1) {
            document.getElementById('fileName5').innerHTML = 'Имя файла: ' + $('#uploadedFile5').val().split('\\').pop();
            @if($work_volumes->where('id', $commercial_offer->work_volume_id)->where('status', 1)->count() === 0)
            $('#submit_form_attach_document').removeAttr('disabled');
            @endif
        }
    }
});

$('#status_result').change(function() {
    opt = $(this).val();
    if (opt == "decline") {
        $('#comment').show();
        $('#result_note').attr('required', 'required');
    } else {
        $('#result_note').removeAttr('required');
        $('#comment').hide();
    }

    $('#sendForm').removeClass('d-none');
    $('#sendForm').removeAttr('disabled');
});

</script>
<script type="text/javascript">
    var vm = new Vue ({
        el: '#collapseOne',
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
