@if($com_offersForeach->isNotEmpty() or in_array(Auth::id(), [$respTongueKP, $respPileKP]))
<div class="card">
    <div class="card-header">
        <h4 class="card-title">
            <a data-target="#collapseCP" href="#" data-toggle="collapse">
                Коммерческие предложения
                <b class="caret"></b>
            </a>
        </h4>
    </div>
    <div id="collapseCP" class="card-collapse collapse {{ !\Session::get('com_offer') ?: "show" }} @if(Request::has('task_16')) show @endif">
        <div class="card-body card-body-table">
            <div class="card strpied-tabled-with-hover">
                <div class="fixed-table-toolbar toolbar-for-btn">
                    <!-- @if ($respTongueKP == Auth::id() && ($com_offers->where('is_tongue', 1)->where('status', 5)->count() != 0 && $com_offers->where('is_tongue', 0)->where('status', 5)->count() != 0))
                        @if (!$com_offers->where('is_tongue', 1)->where('status', 5)->first()->is_uploaded && !$com_offers->where('is_tongue', 0)->where('status', 5)->first()->is_uploaded)
                            <div class="pull-right">
                                <button class="btn-round btn-outline btn-sm add-btn btn" onclick="createDoubleKP()" style="margin-left:10px">
                                    Объединить КП
                                </button>
                            </div>
                        @endif
                    @endif -->
                    @if ($respTongueKP == Auth::id())
                        <div class="pull-right">
                            <button class="btn-round btn-outline btn-sm add-btn btn" data-toggle="modal" data-target="#save-offer" onclick="upload_CO(1)">
                                Загрузить готовое КП (шпунт)
                            </button>
                        </div>
                    @endif
                    @if ($respPileKP == Auth::id())
                        <div class="pull-right">
                            <button class="btn-round btn-outline btn-sm add-btn btn" data-toggle="modal" data-target="#save-offer" onclick="upload_CO(0)">
                                Загрузить готовое КП (свая)
                            </button>
                        </div>
                    @endif
                </div>
                @if($com_offersForeach->isNotEmpty())
                    <div class="table-responsive">
                    <table class="table table-hover mobile-table">
                        <thead>
                            <tr>
                                <th>Дата изменения</th>
                                <th>Тип</th>
                                <th class="text-center">Версия</th>
                                <th class="text-center">Наименование</th>
                                <th>Статус</th>
                                <th class="text-center">
                                    Заявки</th>
                                <th class="text-right">
                                    Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($com_offersForeach as $type => $offer_group_by_is_tongue)
                            @foreach($offer_group_by_is_tongue as $offers_type)
                                @foreach($offers_type as $key => $offer)
                                    @if($key == 0)
                                        <tr>
                                            <td data-label="Дата изменения" data-toggle="collapse" href=".o{{ hash('md5', $offer->option) . 'is_tongue-' . $offer->is_tongue }}" role="button" class="collapsed tr-pointer prerendered-date-time" aria-expanded="false" aria-controls="o{{ hash('md5', $offer->option) . 'is_tongue-' . $offer->is_tongue }}">
                                                {{ $offer->created_at }}
                                            </td>
                                            <td data-label="Тип" data-toggle="collapse" href=".o{{ hash('md5', $offer->option) . 'is_tongue-' . $offer->is_tongue }}" role="button" class="collapsed tr-pointer" aria-expanded="false" aria-controls="o{{ hash('md5', $offer->option) . 'is_tongue-' . $offer->is_tongue }}">
                                                {{ $offer->is_tongue == 2 ? 'Объединенное' : ($offer->is_tongue == 1 ? 'Шпунтовое направление' : 'Свайное направление') . ($offer->is_uploaded ? ' (загружено)' : '') }}
                                            </td>
                                            <td data-label="Версия" data-toggle="collapse" href=".o{{ hash('md5', $offer->option) . 'is_tongue-' . $offer->is_tongue }}" role="button" class="collapsed tr-pointer text-center" aria-expanded="false" aria-controls="o{{ hash('md5', $offer->option) . 'is_tongue-' . $offer->is_tongue }}">
                                                {{ $offer->version }}
                                            </td>
                                            <td data-label="Наименование" data-toggle="collapse" class="collapsed tr-pointer text-center" href=".o{{ hash('md5', $offer->option) . 'is_tongue-' . $offer->is_tongue }}" role="button" aria-expanded="false" aria-controls="o{{ hash('md5', $offer->option) . 'is_tongue-' . $offer->is_tongue }}">
                                                {{ $offer->option ? $offer->option: ' id: ' . $offer->id }}
                                            </td>
                                        <!-- <td>{{ $offer->last_name }} {{ $offer->first_name }} {{ $offer->patronymic }}</td> -->
                                            <td data-label="Статус" data-toggle="collapse" href=".o{{ hash('md5', $offer->option) . 'is_tongue-' . $offer->is_tongue }}" role="button" class="collapsed tr-pointer" aria-expanded="false" aria-controls="o{{ hash('md5', $offer->option) . 'is_tongue-' . $offer->is_tongue }}">
                                                {{ $offer->com_offer_status[$offer->status] }}
                                            </td>
                                            <td data-label="Заявки" data-toggle="collapse" href=".o{{ hash('md5', $offer->option) . 'is_tongue-' . $offer->is_tongue }}" role="button" class="collapsed tr-pointer text-center" aria-expanded="false" aria-controls="o{{ hash('md5', $offer->option) . 'is_tongue-' . $offer->is_tongue }}">
                                                {{ $offer->get_requests->count() }}
                                            </td>
                                            <td data-label="" class="text-right actions">
                                                @if($type == 0)
                                                    @if($offer->status == 5 and ((isset($COpile) ? $COpile->user_id : 0) == Auth::user()->id))
                                                        <button rel="tooltip" class="btn-link btn-xs btn-success btn padding-actions mn-0" data-original-title='Подтвердить согласование КП с заказчиком' onclick="alertRespUser(this, {{ $offer->id }})">
                                                            <i class="fa fa-check-square-o"></i>
                                                        </button>
                                                    @endif
                                                @elseif ($type == 1)
                                                    @if($offer->status == 5 and ((isset($COtongue) ? $COtongue->user_id : 0) == Auth::user()->id))
                                                        <button rel="tooltip" class="btn-link btn-xs btn-success btn padding-actions mn-0" data-original-title='Подтвердить согласование КП с заказчиком' onclick="alertRespUser(this, {{ $offer->id }})">
                                                            <i class="fa fa-check-square-o"></i>
                                                        </button>
                                                    @endif
                                                @elseif ($type == 2)
                                                    @if($offer->status == 5 and ((isset($COtongue) ? $COtongue->user_id : 0) == Auth::user()->id))
                                                        <button rel="tooltip" class="btn-link btn-xs btn-success btn padding-actions mn-0" data-original-title='Подтвердить согласование КП с заказчиком' onclick="alertRespUser(this, {{ $offer->id }})">
                                                            <i class="fa fa-check-square-o"></i>
                                                        </button>
                                                    @endif
                                                @endif
                                                @if($offer->file_name)
                                                    <a rel="tooltip" style="padding-top: 3px" target="_blank" href="{{ asset('storage/docs/commercial_offers/' . $offer->file_name) }}" class="btn-info btn-link btn-xs btn padding-actions" data-original-title='Посмотреть документ'>
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                @endif
                                                @if($offer->work_volume)
                                                    <a rel="tooltip" href="{{ route('projects::commercial_offer::card_' . ($offer->is_tongue == 2 ? 'double' : ($offer->is_tongue == 1 ? 'tongue' : 'pile')), [$project->id, $offer->id]) }}" class="btn-open btn-link btn-xs btn padding-actions" data-original-title="Открыть">
                                                        <i class="fa fa-share-square-o"></i>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @else
                                        <tr class="collapse o{{ hash('md5', $offer->option) . 'is_tongue-' . $offer->is_tongue }}">
                                            <td data-label="Дата">{{ $offer->created_at }}</td>
                                            <td data-label="Тип">{{ $offer->is_tongue == 2 ? 'Объединенное' : ($offer->is_tongue == 1 ? 'Шпунтовое направление' : 'Свайное направление') }}</td>
                                            <td data-label="Версия" class="text-center">{{ $offer->version }}</td>
                                            <td data-label="Версия" class="text-center">{{ $offer->option ? $offer->option: ' id: ' . $offer->id }}</td>
                                        <!-- <td>{{ $offer->last_name }} {{ $offer->first_name }} {{ $offer->patronymic }}</td> -->
                                            <td data-label="Статус">{{ $offer->com_offer_status[$offer->status] }}</td>
                                            <td data-label="Заявки" class="text-center">{{ $offer->get_requests->count() }}</td>
                                            <td data-label="" class="text-right actions">
                                                @if($offer->file_name)
                                                    <a rel="tooltip" style="padding-top: 3px" target="_blank" href="{{ asset('storage/docs/commercial_offers/' . $offer->file_name) }}" class="btn-info btn-link btn-xs btn padding-actions" data-original-title='Посмотреть документ'>
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                @endif
                                                <a rel="tooltip" href="{{ route('projects::commercial_offer::card_' . ($offer->is_tongue == 2 ? 'double' : ($offer->is_tongue == 1 ? 'tongue' : 'pile')), [$project->id, $offer->id]) }}" class="btn-open btn-link btn-xs btn padding-actions" data-original-title="Открыть">
                                                    <i class="fa fa-share-square-o"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            @endforeach
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            <!-- Таблица заявок -->
            <div class="card strpied-tabled-with-hover">
                <div class="fixed-table-toolbar toolbar-for-btn">
                    @if($commercial_offer_requests->count())
                        <h6 style="padding-top:15px;">Текущие заявки на редактирование</h6>
                    @endif

                    @if(!$work_volumes_all->where('type', 0)->where('status', 2)->isEmpty() || !$work_volumes_all->where('type', 1)->where('status', 2)->isEmpty())
                        @if ((in_array(Auth::id(), $allRespUsers) or !isset($contractLogic)) and !in_array(Auth::id(), $project->respUsers->whereIn('role', [5, 6])->pluck('user_id')->toArray()))
                            <div class="pull-right">
                                <button class="btn-success btn-round btn-outline btn-sm add-btn btn" style="margin-top:25px;" data-toggle="modal" data-target="#create_offer_request">
                                    Новая заявка
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
                                <th>Тип</th>
                                <th>Дата</th>
                                <th class="text-right">
                                    Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($commercial_offer_requests as $offer_request)
                            <tr>
                                <td data-label="Автор">
                                    @if($offer_request->user_id)
                                        {{ $offer_request->last_name }}
                                        {{ $offer_request->first_name }}
                                        {{ $offer_request->patronymic }}
                                    @else
                                        Система
                                    @endif
                                </td>
                                <td data-label="Тип">{{ $offer_request->is_tongue ? $offer_request->is_tongue == 2 ? 'Объединённое' : 'Шпунтовое направление' : 'Свайное направление' }}</td>
                                <td data-label="Дата">{{ $offer_request->updated_at }}</td>
                                <td data-label="" class="text-right actions">
                                    <button style="padding-top: 3px" rel="tooltip" class="btn-info btn-link btn-xs btn padding-actions mn-0" data-toggle="modal" data-target="#view-request-offer{{ $offer_request->id }}" data-original-title="Просмотр">
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
                               <label class="">Описание</label>
                               <p>
                                   {{ $offer_request->description }}
                               </p>
                           </div>
                       </div>
                       @if ($offer_request->files->where('is_result', 0)->count() > 0)
                           <div class="row">
                               <div class="col-md-3">
                                   <label class="control-label">Приложенные файлы</label>
                               </div>
                               <div class="col-md-9">
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
                       <form id="create_offer_request_form" class="form-horizontal" action="{{ route('projects::commercial_offer::requests::store', $project->id) }}" method="post" enctype="multipart/form-data">
                           @csrf
                           <p>Выберите редактируемое направление КП</p>
                           @if(isset($com_offersForeach[1]))
                               @if(!$work_volumes_all->where('type', 0)->where('status', 2)->isEmpty())
                                   <div class="row">
                                       <div class="col-md-12">
                                           <div class="form-check">
                                               <label class="form-check-label" style="padding-left:22px">
                                                   <input class="form-check-input" type="checkbox" value="0" id="addSpPile" onclick="toggleRequired(this)" name="add_tongue" required>
                                                   <span class="form-check-sign"></span>
                                                   <span class="lable-check" style="text-transform:none;font-size:14px">
                                                       Шпунтовое направление
                                                   </span>
                                               </label>
                                           </div>
                                           <div id="spInfoPile" style="display:none">
                                               <div class="row">
                                                   <label class="col-sm-3 col-form-label">Объем работ<star class="star">*</star></label>
                                                   <div class="col-sm-9">
                                                       <select name="tongue_offer_id" id="select-com_offer_tongue" style="width:100%" required>
                                                           @foreach($com_offers_options->where('is_tongue', 1) as $offer)
                                                           <option value="{{ $offer->id }}">{{ $offer->option ? $offer->option: ' id: ' . $offer->id  }}</option>
                                                           @endforeach
                                                       </select>
                                                   </div>
                                               </div>
                                               <div class="row">
                                                   <div class="col-md-3">
                                                       <label>Описание<star class="star">*</star></label>
                                                   </div>
                                                   <div class="col-sm-9">
                                                       <div class="form-group">
                                                           <textarea class="form-control textarea-rows" name="descriptionTongue" maxlength="500"></textarea>
                                                       </div>
                                                   </div>
                                               </div>
                                               <div class="row">
                                                   <div class="col-md-3">
                                                       <label for="" style="font-size:0.80">
                                                           Приложенные файлы
                                                       </label>
                                                   </div>
                                                   <div class="col-sm-9">
                                                       <div class="file-container">
                                                           <div id="fileName" class="file-name"></div>
                                                           <div class="file-upload ">
                                                               <label class="pull-right">
                                                                   <span><i class="nc-icon nc-attach-87" style="font-size:25px; line-height: 40px"></i></span>
                                                                   <input type="file" name="tongue_documents[]" accept="*" id="uploadedOfferPileTongueFiles" class="form-control-file file" onchange="getFileName(this)" multiple>
                                                               </label>
                                                           </div>
                                                       </div>
                                                   </div>
                                               </div>
                                               <div class="row">
                                                   <div class="col-sm-3">
                                                       <label class="p-0">
                                                           Проектная документация
                                                       </label>
                                                   </div>
                                                   <div class="col-sm-9">
                                                       <select class="js-select-proj-doc" name="project_documents_tongue[]" data-title="Выберите документ" data-style="btn-default btn-outline" multiple data-menu-style="dropdown-blue" style="width:100%;">
                                                       </select>
                                                   </div>
                                               </div>
                                               <hr>
                                           </div>
                                       </div>
                                   </div>

                               @endif
                           @endif
                           @if(isset($com_offersForeach[0]))
                               @if(!$work_volumes_all->where('type', 1)->where('status', 2)->isEmpty())
                                   <div class="row">
                                       <div class="col-md-12">
                                           <div class="form-check">
                                               <label class="form-check-label" style="padding-left:22px">
                                                   <input class="form-check-input" type="checkbox" value="0" id="addSvPile" onclick="toggleRequired(this)" name="add_pile">
                                                   <span class="form-check-sign"></span>
                                                   <span class="lable-check" style="text-transform:none;font-size:14px">
                                                       Свайное направление
                                                   </span>
                                               </label>
                                           </div>
                                           <div id="svInfoPile" style="display:none">
                                               <div class="row">
                                                   <label class="col-sm-3 col-form-label">Объем работ<star class="star">*</star></label>
                                                   <div class="col-sm-9">
                                                       <select name="pile_offer_id" id="select-com_offer_pile" style="width:100%" required>
                                                           @foreach($com_offers_options->where('is_tongue', 0) as $offer)
                                                           <option value="{{ $offer->id }}">{{ $offer->option ? $offer->option: ' id: ' . $offer->id  }}</option>
                                                           @endforeach
                                                       </select>
                                                   </div>
                                               </div>
                                               <div class="row">
                                                   <div class="col-md-3">
                                                       <label>Описание<star class="star">*</star></label>
                                                   </div>
                                                   <div class="col-sm-9">
                                                       <div class="form-group">
                                                           <textarea class="form-control textarea-rows" name="descriptionPile" maxlength="500"></textarea>
                                                       </div>
                                                   </div>
                                               </div>
                                               <div class="row">
                                                   <div class="col-md-3">
                                                       <label for="" style="font-size:0.80">
                                                           Приложенные файлы
                                                       </label>
                                                   </div>
                                                   <div class="col-sm-7">
                                                       <div class="file-container">
                                                           <div id="fileName" class="file-name"></div>
                                                           <div class="file-upload ">
                                                               <label class="pull-right">
                                                                   <span><i class="nc-icon nc-attach-87" style="font-size:25px; line-height: 40px"></i></span>
                                                                   <input type="file" name="pile_documents[]" accept="*" id="uploadedOfferPilePileFiles" class="form-control-file file" onchange="getFileName(this)" multiple>
                                                               </label>
                                                           </div>
                                                       </div>
                                                   </div>
                                               </div>
                                               <div class="row">
                                                   <div class="col-md-3">
                                                       <label class="p-0">
                                                           Проектная документация
                                                       </label>
                                                   </div>
                                                   <div class="col-sm-9">
                                                       <select class="js-select-proj-doc" name="project_documents_pile[]" data-title="Выберите документ" data-style="btn-default btn-outline" multiple data-menu-style="dropdown-blue" style="width:100%;">
                                                       </select>
                                                   </div>
                                               </div>
                                           </div>
                                       </div>
                                   </div>
                               @endif
                           @endif
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

<!-- Форма для двойного КП -->
<div class="d-none">
    <form id="doubleKP" target="_blank" action="{{ route('projects::commercial_offer::create_double_kp') }}" method="POST">
        @csrf
        <input id="firstKP" type="hidden" name="firstKP" value="{{ isset($com_offers->where('is_tongue', 1)->where('status', 5)->first()->id) ? $com_offers->where('is_tongue', 1)->where('status', 5)->first()->id : 0 }}">
        <input id="secondKP" type="hidden" name="secondKP" value="{{ isset($com_offers->where('is_tongue', 0)->where('status', 5)->first()->id) ? $com_offers->where('is_tongue', 0)->where('status', 5)->first()->id : 0 }}">
    </form>
</div>

@include('projects.modules.upload_modal')

<!-- Модалка для новой логики согласования -->
@foreach($agree_tasks as $agree_task)
<div class="modal fade bd-example-modal-lg show" id="agreeKP{{ $agree_task->target_id }}" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Согласование КП с заказчиком</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <hr style="margin-top:0">
                <div class="card border-0" >
                    <form id="form_solve_task{{ $agree_task->target_id }}" class="form-horizontal" action="{{ route('tasks::solve_task', $agree_task->id) }}" method="post">
                        @csrf
                        <input type="hidden" name="where_from" value="project">
                        <div class="form-group">
                            <div class="row">
                                <label class="col-sm-3 col-form-label">Результат<star class="star">*</star></label>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <select id="status_result{{ $agree_task->target_id }}" name="status_result" offer_id="{{ $agree_task->target_id }}" class="selectpicker" data-title="Результат" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                                            <option value="accept">Согласовано</option>
                                            <option value="archive">В архив</option>
                                            <option value="transfer">Перенести дату</option>
                                            <option value="change">Требуются изменения</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group collapse" id="comment{{ $agree_task->target_id }}">
                            <div class="row">
                                <label class="col-sm-3 col-form-label">Комментарий<star class="star">*</star></label>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <textarea id="result_note{{ $agree_task->target_id }}" class="form-control textarea-rows" maxlength="300" name="final_note" placeholder="Опишите причину" required></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="fromСontainer{{ $agree_task->target_id }}" class="row d-none">
                            <label class="col-sm-3 col-form-label">Перенести на дату<star class="star">*</star></label>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <input id="from" name="revive_at" class="form-control datepicker task-transfer" autocomplete="off">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                <button id="sendForm{{ $agree_task->target_id }}" form="form_solve_task{{ $agree_task->target_id }}" class="btn btn-info d-none" disabled="disabled">Отправить</button>
            </div>
        </div>
    </div>
</div>
@endforeach

@push ('js_footer')
<script>
    var message = new Vue({});

    $('.task-transfer').datetimepicker({
        format: 'DD.MM.YYYY',
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
        minDate: moment().add(1, 'd'),
        date: null
    });

    $('#upload').click(function(){
        $('#file').fadeIn(300);
        $('#upload').hide();
    });

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
    }

    $('#select-com_offer_pile').select2();
    $('#select-com_offer_tongue').select2();
</script>
@endpush
