<div class="card">
    <div class="card-header">
        <h4 class="card-title">
            <a data-target="#collapseWork" href="#" data-toggle="collapse">
                Объем работ
                <b class="caret"></b>
            </a>
        </h4>
    </div>
    <div id="collapseWork" class="card-collapse collapse {{ !\Session::get('work_volume') ?: "show" }}">
        <div class="card-body card-body-table">
            <!-- Таблица объемов -->
            @if($work_volumes->count())
            <div class="card strpied-tabled-with-hover">
                <div class="fixed-table-toolbar toolbar-for-btn">
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mobile-table">
                        <thead>
                            <tr>
                                <th>Дата создания</th>
                                <th class="text-center">Версия</th>
                                <th class="text-center">Наименование</th>
                                <th>Тип</th>
                                <th>Статус</th>
                                <th class="text-center">Заявки</th>
                                <th class="text-right">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($work_volumesForeach as $type => $wv_group_by_type)
                                @foreach($wv_group_by_type as $work_volumes_type)
                                    @foreach($work_volumes_type as $key => $work_volume)
                                        @if($key == 0)
                                            <tr>
                                                <td data-label="Дата создания" data-toggle="collapse" class="collapsed tr-pointer prerendered-date-time" href=".wv{{ hash('md5', $work_volume->option) . 'type-' . $work_volume->type }}" role="button" aria-expanded="false" aria-controls="wv{{ hash('md5', $work_volume->option) . 'type-' . $work_volume->type }}">
                                                    {{ $work_volume->created_at }}
                                                </td>
                                                <td data-label="Версия" data-toggle="collapse" class="collapsed tr-pointer text-center" href=".wv{{ hash('md5', $work_volume->option) . 'type-' . $work_volume->type }}" role="button" aria-expanded="false" aria-controls="wv{{ hash('md5', $work_volume->option) . 'type-' . $work_volume->type }}">
                                                    {{ $work_volume->version }}
                                                </td>
                                                <td data-label="Наименование" data-toggle="collapse" class="collapsed tr-pointer text-center" href=".wv{{ hash('md5', $work_volume->option) . 'type-' . $work_volume->type }}" role="button" aria-expanded="false" aria-controls="wv{{ hash('md5', $work_volume->option) . 'type-' . $work_volume->type }}">
                                                    {{ $work_volume->option ? $work_volume->option: ' id: ' . $work_volume->id }}
                                                </td>
                                                <td data-label="Тип" data-toggle="collapse" class="collapsed tr-pointer" href=".wv{{ hash('md5', $work_volume->option) . 'type-' . $work_volume->type }}" role="button" aria-expanded="false" aria-controls="wv{{ hash('md5', $work_volume->option) . 'type-' . $work_volume->type }}">
                                                    {{ $work_volume->wv_type[$work_volume->type] }}
                                                </td>
                                                <td data-label="Статус" data-toggle="collapse" class="collapsed tr-pointer" href=".wv{{ hash('md5', $work_volume->option) . 'type-' . $work_volume->type }}" role="button" aria-expanded="false" aria-controls="wv{{ hash('md5', $work_volume->option) . 'type-' . $work_volume->type }}">
                                                    {{ $work_volume->wv_status[$work_volume->status] }}
                                                </td>
                                                <td data-label="Заявки" data-toggle="collapse" class="collapsed tr-pointer text-center" href=".wv{{ hash('md5', $work_volume->option) . 'type-' . $work_volume->type }}" role="button" aria-expanded="false" aria-controls="wv{{ hash('md5', $work_volume->option) . 'type-' . $work_volume->type }}" >
                                                    {{ $work_volume->get_requests->count() }}
                                                </td>
                                                <td data-label="" class="text-right actions">
                                                    <a @if ($work_volume->type == 0) href="{{ route('projects::work_volume::card_tongue', [$project->id, $work_volume->id]) }}"
                                                       @elseif ($work_volume->type == 1) href="{{ route('projects::work_volume::card_pile', [$project->id, $work_volume->id]) }}" @endif
                                                       rel="tooltip" class="btn-info btn-link btn-xs" style="color:#0976b4" >
                                                        <i class="fa fa-share-square-o"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @else
                                            <tr class="collapse contact-note wv{{ hash('md5', $work_volume->option) . 'type-' . $work_volume->type }}">
                                                <td data-label="Дата создания">{{ $work_volume->created_at }}</td>
                                                <td data-label="Версия" class="text-center">{{ $work_volume->version }}</td>
                                                <td data-label="Наименование" class="text-center">{{ $work_volume->option ? $work_volume->option: ' id: ' . $work_volume->id }}</td>
                                                <td data-label="Тип">{{ $work_volume->wv_type[$work_volume->type] }}</td>
                                                <td data-label="Статус">{{ $work_volume->wv_status[$work_volume->status] }}</td>
                                                <td data-label="Заявки" class="text-center">{{ $work_volume->get_requests->count() }}</td>
                                                <td data-label="" class="text-right actions">
                                                    <a @if ($work_volume->type == 0) href="{{ route('projects::work_volume::card_tongue', [$project->id, $work_volume->id]) }}"
                                                       @elseif ($work_volume->type == 1) href="{{ route('projects::work_volume::card_pile', [$project->id, $work_volume->id]) }}" @endif
                                                       rel="tooltip" class="btn-info btn-link btn-xs" style="color:#0976b4" >
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
            </div>
            @endif

            <!-- Таблица заявок -->

            <div class="card strpied-tabled-with-hover" >
                <div class="fixed-table-toolbar toolbar-for-btn">
                    @if($work_volume_requests->count())
                    <h6 style="padding-top:15px;" class="pull-left">Текущие заявки на редактирование</h6>
                    @endif
                        <div class="pull-right">
                            @if(!$work_volumes->count() && (Auth::user()->department_id == 14/*6*/ || Auth::id()) == 27)
                            <button class="btn-success btn-round btn-outline btn-sm add-btn btn"
                                    data-toggle="modal" data-target="#edit-work">
                                <i class="fa fa-plus"></i>
                                    Заявка на создание
                            </button>
                            @elseif($work_volumes->count() && in_array(Auth::id(), $allRespUsers))
                                <button style="margin-top:25px" class="btn-success btn-round btn-outline btn-sm add-btn btn"
                                        data-toggle="modal" data-target="#edit-work">
                                    Новая заявка
                                </button>
                            @endif
                        </div>
                </div>
                @if($work_volume_requests->count())
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
                        @foreach ($work_volume_requests as $wv_request)
                            <tr>
                                <td data-label="Автор">
                                    @if($wv_request->user_id)
                                        {{ $wv_request->last_name }}
                                        {{ $wv_request->first_name }}
                                        {{ $wv_request->patronymic }}
                                    @else
                                        Система
                                    @endif
                                </td>
                                <td data-label="Тип">{{ $wv_request->tongue_pile ? 'Свайное направление' : 'Шпунтовое направление' }}</td>
                                <td data-label="Дата">{{ $wv_request->updated_at }}</td>
                                <td data-label="" class="text-right actions">
                                    <button rel="tooltip" onclick="" class="btn-info btn-link btn-xs btn padding-actions mn-0" data-toggle="modal" data-target="#view-request{{ $wv_request->id }}" data-original-title="Просмотр">
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


<!-- Добавление заявки на редактирование-->
<div class="modal fade bd-example-modal-lg show" id="edit-work" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
       <div class="modal-content">
           <div class="modal-header">
               <h5 class="modal-title">Заявка</h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                   <span aria-hidden="true">×</span>
               </button>
           </div>
           <div class="modal-body">
               <hr style="margin-top:0">
               <div class="card border-0" >
                   <div class="card-body ">
                       <form id="create_request_form" @submit.prevent="preSubmitCheck" class="form-horizontal" action="{{ route('projects::work_volume_request::store', $project->id) }}" method="post" enctype="multipart/form-data">
                           @csrf
                           <p>Выберите необходимые виды работ</p>
                               <div class="row">
                                   <div class="col-md-12">
                                       <div class="form-check">
                                           <label class="form-check-label" style="padding-left:22px">
                                               <input class="form-check-input" type="checkbox" value="0" onclick="toggleRequired(this)" id="addSp" name="add_tongue" v-model="add_tongue" required>
                                               <span class="form-check-sign"></span>
                                               <span class="lable-check" style="text-transform:none;font-size:14px">
                                               Шпунтовое направление
                                           </span>
                                           </label>
                                       </div>
                                       <div id="spInfo" style="display:none">
                                           <div class="row">
                                               <label class="col-sm-3 col-form-label">Объем работ<span class="star">*</span></label>
                                               <div class="col-sm-9">
                                                   <select name="work_volume_tongue_id" id="select-work_volume_tongue" style="width:100%" required>
                                                       <option value="new">Новый объем работ</option>
                                                       @foreach($work_volumes_options->where('type', 0) as $work_volume)
                                                       <option value="{{ $work_volume->id }}"> {{ $work_volume->option ? $work_volume->option: ' id: ' . $work_volume->id  }} </option>
                                                       @endforeach
                                                   </select>
                                               </div>
                                           </div>
                                            <div class="row" id="option_block_tongue">
                                                <label class="col-sm-3 col-form-label">Наименование<span class="star">*</span></label>
                                                <div class="col-sm-9">
                                                    <input placeholder="Вибро/Статика" id="option_block_tongue_input" name="option_tongue" v-model="option_tongue" class="form-control" max="50">
                                                </div>
                                            </div>
                                           <div class="row">
                                               <label class="col-sm-3 col-form-label">Описание
                                                   <span class="star">*</span>
                                               </label>
                                               <div class="col-sm-9">
                                                   <div class="form-group">
                                                       <textarea class="form-control textarea-rows " name="tongue_description" maxlength="65000"></textarea>
                                                   </div>
                                               </div>
                                           </div>
                                           <div class="row">
                                               <label class="col-sm-3 col-form-label" for="" >
                                                   Приложенные файлы
                                               </label>
                                               <div class="col-sm-6">
                                                   <div class="file-container">
                                                       <div id="fileName" class="file-name"></div>
                                                       <div class="file-upload ">
                                                           <label class="pull-right">
                                                               <span><i class="nc-icon nc-attach-87" style="font-size:25px; line-height: 40px"></i></span>
                                                               <input type="file" name="tongue_documents[]" accept="*" id="uploadedTongueFiles" class="form-control-file file" onchange="getFileName(this)" multiple>
                                                           </label>
                                                       </div>
                                                   </div>
                                               </div>
                                           </div>
                                           <div class="row">
                                               <label class="col-sm-3 col-form-label">
                                                   Проектная документация
                                               </label>
                                               <div class="col-sm-6">
                                                   <select class="js-select-proj-doc" name="project_documents_tongue[]" data-title="Выберите документ" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" multiple style="width:100%;">
                                                   </select>
                                               </div>
                                           </div>
                                           <hr>
                                       </div>
                                   </div>
                               </div>
                           <div class="row">
                               <div class="col-md-12">
                                   <div class="form-check">
                                       <label class="form-check-label" style="padding-left:22px">
                                           <input class="form-check-input" type="checkbox" value="0" id="addSv" onclick="toggleRequired(this)" name="add_pile" v-model="add_pile" required>
                                           <span class="form-check-sign"></span>
                                           <span class="lable-check" style="text-transform:none;font-size:14px">
                                               Свайное направление
                                           </span>
                                       </label>
                                   </div>
                                   <div id="svInfo" style="display:none">
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label">Объем работ<span class="star">*</span></label>
                                            <div class="col-sm-9">
                                                <select name="work_volume_pile_id" id="select-work_volume_pile" style="width:100%" required>
                                                    <option value="new">Новый объем работ</option>
                                                    @foreach($work_volumes_options->where('type', 1) as $work_volume)
                                                    <option value="{{ $work_volume->id }}"> {{ $work_volume->option ? $work_volume->option: ' id: ' . $work_volume->id  }} </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row" id="option_block_pile">
                                            <label class="col-sm-3 col-form-label">Наименование<span class="star">*</span></label>
                                            <div class="col-sm-9">
                                                <input placeholder="Составные" id="option_block_pile_input" name="option_pile" v-model="option_pile" class="form-control" max="50">
                                            </div>
                                        </div>
                                       <div class="row">
                                           <label class="col-sm-3 col-form-label">
                                               Описание<span class="star">*</span>
                                           </label>
                                           <div class="col-sm-9">
                                               <div class="form-group">
                                                   <textarea class="form-control textarea-rows" name="pile_description" maxlength="65000"></textarea>
                                               </div>
                                           </div>
                                       </div>
                                       <div class="row">
                                           <label class="col-sm-3 col-form-label" for="" style="font-size:0.80">
                                               Приложенные файлы
                                           </label>
                                           <div class="col-sm-6">
                                               <div class="file-container">
                                                   <div id="fileName" class="file-name"></div>
                                                   <div class="file-upload">
                                                       <label class="pull-right">
                                                           <span><i class="nc-icon nc-attach-87" style="font-size:25px; line-height: 40px"></i></span>
                                                           <input type="file" name="pile_documents[]" accept="*" id="uploadedPileFiles" class="form-control-file file" onchange="getFileName(this)" multiple>
                                                       </label>
                                                   </div>
                                               </div>
                                           </div>
                                       </div>
                                       <div class="row">
                                           <label class="col-sm-3 col-form-label">
                                               Проектная документация
                                           </label>
                                           <div class="col-sm-6">
                                               <select class="js-select-proj-doc" name="project_documents_pile[]" data-title="Выберите документ" data-style="btn-default btn-outline" multiple data-menu-style="dropdown-blue" style="width:100%;">
                                               </select>
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
                <button id="submit_wv" type="submit" form="create_request_form" class="btn btn-info">Сохранить</button>
           </div>
        </div>
    </div>
</div>

<!-- Просмотр заявки -->

@foreach ($work_volume_requests as $wv_request)
<div class="modal fade bd-example-modal-lg show" id="view-request{{ $wv_request->id }}" role="dialog" aria-labelledby="modal-search" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
       <div class="modal-content">
           <div class="modal-header">
               <h5 class="modal-title">Заявка</h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                   <span aria-hidden="true">×</span>
               </button>
           </div>
           <div class="modal-body">
               <hr style="margin-top:0">
               <div class="card border-0" >
                   <div class="card-body">
                        <div class="card">
                            <div class="row">
                                <div class="col-md-12">
                                    <h5 style="margin-bottom:5px">
                                        {{ $wv_request->tongue_pile ? 'Свайное направление' : 'Шпунтовое направление' }}
                                    </h5>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <h6 style="margin:5px 0 0 0">
                                                        Описание
                                                    </h6>
                                                    <p class="form-control-static">{{ $wv_request->description }}</p>
                                                </div>
                                             </div>
                                            @if ($wv_request->excavation_description !== null)
                                             <div class="row">
                                                 <div class="col-md-12">
                                                     <label class="control-label">Земляные работы</label>
                                                     <p>{{ $wv_request->excavation_description }}</p>
                                                 </div>
                                             </div>
                                            @endif
                                            @if ($wv_request->files->where('is_result', 0)->count() > 0)
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <label class="control-label">Приложенные файлы</label>
                                                        <br>
                                                        @foreach($wv_request->files->where('is_result', 0)->where('is_proj_doc', 0) as $file)
                                                            <a target="_blank" href="{{ asset('storage/docs/work_volume_request_files/' . $file->file_name) }}">
                                                                {{ $file->original_name }}
                                                            </a>
                                                            <br>
                                                        @endforeach

                                                        @foreach($wv_request->files->where('is_result', 0)->where('is_proj_doc', 1) as $file)
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
                            </div>
                       </div>
                   </div>
               </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                <!-- <button id="" type="submit" form="create_task_form" class="btn btn-info">Сохранить</button> -->
           </div>
        </div>
    </div>
</div>
@endforeach
<!-- end modal -->


@push ('js_footer')
<script>
    function toggleRequired(e)
    {
        var textarea = $(e).closest('.row').find('textarea');
        var checks = $(e).closest('form').find('.form-check-input');
        var req_checks = $(e).closest('form').find(".form-check-input[value='1']").length;

        if (textarea.attr('required')) {
            $.each(checks, function(index, elem) {
                if (req_checks == 1) {
                    $(elem).attr('required', 'required');
                }
            });

            textarea.removeAttr('required');
        } else {
            $.each(checks, function(index, elem) {
                $(elem).removeAttr('required');
            });

            textarea.attr('required', 'required');
        }
    }

    $('.js-select-proj-doc').select2({
        language: "ru",
        closeOnSelect: false,
        ajax: {
            url: '/projects/ajax/get_project_documents/' + {{ $project->id }},
            dataType: 'json',
            delay: 250,
        }
    }).on("select2:unselecting", function(e) {
        $(this).data('state', 'unselected');
    }).on("select2:open", function(e) {
        if ($(this).data('state') === 'unselected') {
            $(this).removeData('state');
            var self = $(this);
            setTimeout(function() {
                self.select2('close');
            }, 1);
        }
    });


    var workWolumeExistenceChecker = new Vue({
        el: '#edit-work',
        data: {
            option_tongue: '',
            option_pile: '',
            add_tongue: 0,
            add_pile: 0,
        },
        mounted() {
            $('#select-work_volume_tongue').select2();
            $('#select-work_volume_pile').select2();



            $('#select-work_volume_pile').on('change', function() {
                if ($(this).val() == 'new') {
                    $('#option_block_pile').show();
                    $('#option_block_pile_input').attr('required', 'true');
                } else {
                    $('#option_block_pile').hide();
                    $('#option_block_pile_input').removeAttr('required');
                }
            });

            $('#select-work_volume_tongue').on('change', function() {
                if ($(this).val() == 'new') {
                    $('#option_block_tongue').show();
                    $('#option_block_tongue_input').attr('required', 'true');
                } else {
                    $('#option_block_tongue').hide();
                    $('#option_block_tongue_input').removeAttr('required');
                }
            });
        },
        methods: {
            preSubmitCheck(submitEvent) {
                payload = {};
                payload.add_tongue = workWolumeExistenceChecker.add_tongue === true ? 1 : 0;
                payload.add_pile = workWolumeExistenceChecker.add_pile === true ? 1 : 0;
                payload.option_tongue = workWolumeExistenceChecker.option_tongue;
                payload.option_pile = workWolumeExistenceChecker.option_pile;
                payload.tongue_description = '1';
                payload.pile_description = '1';
                payload.axios = true;

                axios.post('{{ route('projects::work_volume_request::store', $project->id) }}', payload)
                .then(function (response) {
                    workWolumeExistenceChecker.$off('submit');
                    document.getElementById("create_request_form").submit()
                })
                .catch(function (request) {
                    var errors = Object.values(request.response.data.errors);

                    errors.forEach(function (error, key) {
                        setTimeout(function () {
                            workWolumeExistenceChecker.$message({
                                showClose: true,
                                message: error[0],
                                type: 'error',
                                duration: 5000
                            });
                        }, (key + 1) * 100);
                    });
                });
            }
        }
    });
</script>

@endpush
