<!-- arrival story -->
@if($operation->materialsPart->count())
<div class="card tasks-sidebar__item strpied-tabled-with-hover" style="margin-bottom:30px">
    <div class="card-body story-collapse-card">
        <div class="accordions">
            <div class="card" style="margin-bottom:0">
                @if($operation->type != 4 && $operation->type != 3)
                <div class="card-header">
                    <h5 class="materials-info-title mb-10__mobile">
                        <a class="collapsed story-collapse-card__link show" data-target="#collapse2" href="#" data-toggle="collapse">
                            История операции
                            <b class="caret" style="margin-top:8px"></b>
                        </a>
                    </h5>
                </div>
                @endif
                <div id="collapse2" class="card-collapse collapse show">
                    <div class="card-body without-shadow" id="materials_story">
                        <div class="table-responsive">
                            <table class="table table-hover mobile-table">
                                <thead>
                                    <tr>
                                        <th>Дата</th>
                                        @if($operation->type > 2)
                                        <th>Тип операции</th>
                                        @endif
                                        <th>Материал</th>
                                        <!-- <th class="text-center">Ед. измерения</th> -->
                                        <th>Количество</th>
                                        <th class="text-center">Автор</th>
                                        <th class="text-right">Действия</th>
                                    </tr>
                                </thead>
                                <tbody class="history-composit-body">
                                    @foreach($operation->materialsPart as $key => $material)

                                    <tr>
                                        <td data-label="Дата">
                                            <i class="el-icon-time" style="margin-right: 5px;"></i>
                                            <div class="prerendered-date" style="display: inline">{{ $material->fact_date ?? $material->created_at }}</div>
                                        </td>
                                        @if($operation->type > 2)
                                            <td data-label="Тип операции">
                                                {{ $material->type == 8 ? 'Отправка' : 'Получение' }}
                                            </td>
                                        @endif
                                        <td data-label="Материал">
                                            {{ $material->comment_name }}
                                        </td>
                                        <!-- <td data-label="Ед. измерения" class="text-center">
                                            {{ $material->manual->category_unit }}
                                        </td> -->
                                        <td data-label="Количество">
                                            @if($material->count)
                                                <b>{{ round($material->count, 3) }}</b> {{ $material->units_name[$material->unit] }}<br>
                                                @foreach($material->manual->convert_from($material->units_name[$material->unit]) as $item)
                                                    <span class="mat-count"><b>{{ round($item->value * $material->count, 3) }}</b> {{ $item->unit }}</span><br>
                                                @endforeach
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td data-label="Автор" class="text-center">{{ $material->materialAddition->user->full_name ?? '-' }}</td>
                                        <td class="text-right actions">
                                            @if ($material->type === 9 and ( $material->materialFiles()->exists() or $operation->type === 1) and $material->materialFiles()->where('type', 3)->doesntExist() and !in_array($operation->object_id_to, [76, 192]) and in_array($operation->type, [1, 4]))
                                                <el-tooltip placement="top" content="Прикрепить сертификат">
                                                    <el-upload
                                                            class="btn btn-primary btn-link btn-xs padding-actions mn-0"
                                                            :headers="{ 'X-CSRF-TOKEN': '{{ csrf_token() }}' }"
                                                            action="{{ route('building::mat_acc::part_upload', [$operation->id, 'operation_material_id' => $material->id, 'type' => 'cert']) }}"
                                                            data-original-title="Посмотреть"
                                                            :on-remove="delete_uploaded_file"
                                                    >
                                                        <i class="fas fa-file-alt"></i>
                                                    </el-upload>
                                                </el-tooltip>
                                            @endif
                                            <button  rel="tooltip"
                                                     type="button"
                                                     class="btn btn-primary btn-link btn-xs padding-actions mn-0"
                                                     @click="showModal('material_arrival{{$material->id}}')"
                                                     {{--data-toggle="modal"
                                                     data-target="#material_arrival{{$material->id}}"--}}
                                                     data-original-title="Посмотреть"
                                            >
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            @if ($material->operation->status != 3)
                                                @if ((($operation->responsible_users->whereIn('type', ($material->type == 8 ? [0, 1] : [0, 2]))->contains('user_id', Auth::id())) and !$material->delete_task) or $operation->isAuthor())
                                                    @if(!$material->updated_material)
                                                    <template>
                                                        <button  rel="tooltip"
                                                                 type="button"
                                                                 class="btn btn-success btn-link btn-xs padding-actions mn-0"
                                                                 {{--@click="materials[{{ $key }}].drawer = true"--}}
                                                                 @click="showModal('material_edit_{{$key}}')"
                                                                 data-original-title="Отредактировать"
                                                        >
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    </template>
                                                    @endif
                                                    <button  rel="tooltip"
                                                             type="button"
                                                             onclick="delete_history({{$material->id}}, {{ \Carbon\Carbon::now() < \Carbon\Carbon::parse($material->created_at)->addDay() or $operation->isAuthor()}})"
                                                             class="btn btn-danger btn-link btn-xs padding-actions mn-0"
                                                             data-original-title="Удалить">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @foreach($operation->materialsPart as $key => $material)
                            <div class="modal fade bd-example-modal-lg show" id="material_edit_{{$key}}" role="dialog" aria-labelledby="modal-search" style="display: none;">
                                <div class="modal-dialog modal-lg" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Редактирование материала</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">×</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="card border-0" >
                                                <div class="card-body">
                                                    <div class="materials">
                                                        <div class="row" style="margin-top: 7px;">
                                                            <div class="col-lg-5">
                                                                <label class="show-mobile-label mt-10__mobile">
                                                                    Материал <span class="star">*</span>
                                                                </label>
                                                                <template>
                                                                    <el-select ref="usernameInput" v-model="materials[{{ $key }}].manual_material_id" clearable filterable @clear="search(``)" :remote-method="search" remote size="large" placeholder="Выберите материал">
                                                                        <el-option
                                                                            v-for="item in manual_materials"
                                                                            :label="item.label"
                                                                            :key="item.id"
                                                                            :value="item.id">
                                                                        </el-option>
                                                                    </el-select>
                                                                </template>
                                                            </div>
                                                            <div class="col-lg-2">
                                                                <label class="show-mobile-label mt-10__mobile">
                                                                    Ед. изм. <span class="star">*</span>
                                                                </label>
                                                                <template>
                                                                    <el-select v-model="materials[{{ $key }}].unit" placeholder="Ед. измерения">
                                                                        <el-option
                                                                            v-for="item in units"
                                                                            :key="item.id"
                                                                            :value="item.id"
                                                                            :label="item.text">
                                                                        </el-option>
                                                                    </el-select>
                                                                </template>
                                                            </div>
                                                            <div class="col-lg-3">
                                                                <label for="" class="show-mobile-label mt-10__mobile">
                                                                    Количество <span class="star">*</span>
                                                                </label>
                                                                <template>
                                                                    <el-input-number v-model="materials[{{ $key }}].count" :min="0" :precision="3" :step="0.001" :max="10000000" required></el-input-number>
                                                                </template>
                                                            </div>
                                                            <div class="col-lg-1">
                                                                <label for="" class="show-mobile-label mt-10__mobile">
                                                                    Б/У
                                                                </label>
                                                                <template>
                                                                    <el-checkbox v-model="materials[{{ $key }}].used"
                                                                         border
                                                                         @canany(['mat_acc_base_move_to_new', 'mat_acc_base_move_to_used']) @change="changeUsageValue" @endcanany @cannot('mat_acc_base_move_to_new') disabled @elsecannot('mat_acc_base_move_to_used') disabled @endcannot
                                                                    ></el-checkbox>
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row" style="margin-top:25px; margin-bottom:10px">
                                                        <div class="col-md-12" >
                                                            <label for="">
                                                                Комментарий
                                                            </label>
                                                            <textarea v-model="materials[{{ $key }}].material_addition.description" class="form-control textarea-rows"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="form-group" style="margin-top:30px">
                                                        <div class="row">
                                                            <label class="col-sm-5 col-form-label" for="">
                                                                Сопроводительные документы
                                                            </label>
                                                            <div class="col-sm-7" style="padding-top:0px;">
                                                                <el-upload
                                                                    class="upload-demo"
                                                                    :headers="{ 'X-CSRF-TOKEN': csrf }"
                                                                    action="{{ route('building::mat_acc::part_upload', [$operation->id, 'operation_material_id' => $material->id]) }}"
                                                                    :file-list="materials[{{ $key }}].material_files.filter(function(item){ if (item.type==1) {return item;}})"
                                                                    multiple
                                                                    :on-remove="delete_file"
                                                                >
                                                                    <el-button size="small" type="primary">Загрузить</el-button>
                                                                    <div slot="tip" class="el-upload__tip">pdf/doc файлы не более 100мб</div>
                                                                </el-upload>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div id="drop-area" class="drop-area">
                                                                <div slot="tip" class="el-upload__tip" style="margin-bottom:15px">Необходимо загрузить фотографии транспорта спереди и сзади.</div>
                                                                <el-upload
                                                                    :headers="{ 'X-CSRF-TOKEN': csrf }"
                                                                    action="{{ route('building::mat_acc::part_upload', [$operation->id, 'operation_material_id' => $material->id]) }}"
                                                                    list-type="picture-card"
                                                                    :file-list="materials[{{ $key }}].material_files.filter(function(item){if (item.type==2) {return item;}})"
                                                                    multiple
                                                                    :on-remove="delete_file"
                                                                >
                                                                    <i class="el-icon-plus"></i>
                                                                </el-upload>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                                            <el-button style="margin-top: 5px;" type="primary" :loading="is_send_update" @click="update(materials[{{ $key }}], {{ \Carbon\Carbon::now() < \Carbon\Carbon::parse($material->created_at)->addDay() or $operation->isAuthor()}})">Редактировать</el-button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modals_for_history_from">
@foreach($operation->materialsPart as $material)
<div class="modal fade bd-example-modal-lg show" id="material_arrival{{$material->id}}" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
       <div class="modal-content">
           <div class="modal-header">
               <h5 class="modal-title">{{ $operation->type_name }}</h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                   <span aria-hidden="true">×</span>
               </button>
           </div>
           <div class="modal-body">
               <hr style="margin-top:0">
               <div class="card border-0" >
                   <div class="card-body ">
                       <div class="row">
                           <div class="col-md-12" v-if="">
                               <div class="story-info-block">
                                   <h6 class="story-info-label">Дата</h6>
                                   <span class="story-info-item">{{ $material->created_at }}</span>
                               </div>
                               <div class="story-info-block">
                                   <h6 class="story-info-label">Материал</h6>
                                   <span class="story-info-item">{{ $material->comment_name }}</span>
                               </div>
                               <div class="story-info-block">
                                   <h6 class="story-info-label">Количество</h6>
                                   <span class="story-info-item">{{ round($material->count, 3) }} {{ $material->units_name[$material->unit] }}</span>
                               </div>
                           </div>
                           <div class="col-md-12">
                               <h6 class="h6-none-transform">Комментарий исполнителя</h6>
                               <blockquote>
                                   <p style="font-size:14px">
                                       {{ $material->materialAddition->description ?? '-' }}
                                   </p>
                                   <div class="row">
                                       <div class="col-md-7">

                                       </div>
                                       <div class="col-md-5 text-right">
                                           <small>{{ $material->materialAddition->user->full_name ?? '-' }}</small>
                                       </div>
                                   </div>
                                   <div class="clearfix"></div>
                               </blockquote>
                           </div>
                           @if($material->materialFiles->where('type', 2)->count())
                           <div class="col-md-12">
                               <h6 class="h6-none-transform">Приложенные фото</h6>
                               <div class="row">
                                     <div class="col-md-12">
                                         @foreach($material->materialFiles->where('type', 2) as $photo)
                                         <div class="report-photo">
                                             <div class="about-photo">
                                                 {{ $photo->created_at }}
                                             </div>
                                             <a href="{{ $photo->url }}" target="_blank">
                                             <div class="operation-story-photo">
                                                 <img class="operation-story__attached-photo" src="{{ $photo->url }}" alt="фото">
                                             </div>
                                             </a>
                                         </div>
                                         @endforeach
                                     </div>
                                 </div>
                           </div>
                           @endif
                           @if($material->materialFiles->whereIn('type', [0, 1, 3])->count())
                           <div class="col-md-12">
                               <h6 class="h6-none-transform">Сопроводительная документация</h6>
                               <div class="table-responsive">
                                   <table class="table table-hover mobile-table">
                                       <thead>
                                           <tr>
                                               <th class="sort">Имя файла</th>
                                               <th class="sort">Дата добавления</th>
                                               <th id="upload" class="sort">Действия
                                                   <el-tooltip placement="top" content="Прикрепить другой сертификат">
                                                       <el-upload
                                                           class="btn btn-primary btn-link btn-xs padding-actions mn-0"
                                                           :headers="{ 'X-CSRF-TOKEN': '{{ csrf_token() }}' }"
                                                           action="{{ route('building::mat_acc::part_upload', [$operation->id, 'operation_material_id' => $material->id, 'type' => 'cert']) }}"
                                                           data-original-title="Посмотреть"
                                                           :on-remove="delete_uploaded_file"
                                                       >
                                                           <i class="fas fa-file-alt"></i>
                                                       </el-upload>
                                                   </el-tooltip>
                                               </th>
                                           </tr>
                                       </thead>
                                       <tbody>
                                           @foreach($material->materialFiles->whereIn('type', [0, 1, 3]) as $file)
                                           <tr>
                                               <td data-label="Имя файла">{{ $file->type != 3 ? $file->file_name : 'Сертификат соответствия' }}</td>
                                               <td data-label="Дата добавления">{{ $file->created_at }}</td>
                                               <td data-label="Действия">
                                                   <a href="{{ $file->url }}" target="_blank">
                                                       <i class="fa fa-eye"></i>
                                                   </a>
                                               </td>
                                           </tr>
                                           @endforeach
                                       </tbody>
                                   </table>
                               </div>
                           </div>
                           @endif
                      </div>
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
</div>

@push('js_footer')
<script>

function delete_history(material_id, is_unlocked) {
    if (is_unlocked) {
        swal({
            title: "Удаление операции",
            html: "Вы действительно хотите <b><i>удалить</i></b> эту операцию?",
            // type: 'warning',
            showCancelButton: true,
            animation: true,
            confirmButtonText: "Да",
            cancelButtonText: "Нет",
            focusCancel: true,
        }).then( result => {
            if (result.value) {
                $.ajax({
                    url:"{{ route('building::mat_acc::delete_part_operation') }}", //SET URL
                    type: 'POST', //CHECK ACTION
                    data: {
                        _token: CSRF_TOKEN,
                        material_id: material_id, //SET ATTRS
                    },
                    dataType: 'JSON',
                    success: function (data) {
                        if (data.status == 'success') {
                            location.reload()
                        } else if (data.status == 'error') {
                            modals.$message({
                                showClose: true,
                                message: data.message,
                                type: 'error',
                                duration: 10000
                            });
                        }
                    }
                });
            }
        });
    } else {
        swal({
            title: "Запрос на удаление операции",
            html: "Прошло слишком много времени.<br><br>Для удаления придётся запросить разрешение у руководителя проектов.<br><br>Хотите <b><i>удалить</i></b> эту операцию?",
            // type: 'warning',
            showCancelButton: true,
            animation: true,
            confirmButtonText: "Да",
            cancelButtonText: "Нет",
            focusCancel: true,
        }).then( result => {
            if (result.value) {
                $.ajax({
                    url:"{{ route('building::mat_acc::store_deletion_task') }}", //SET URL
                    type: 'POST', //CHECK ACTION
                    data: {
                        _token: CSRF_TOKEN,
                        material_id: material_id, //SET ATTRS
                    },
                    dataType: 'JSON',
                    success: function (data) {
                        console.log(data);
                        if (data.status == 'success') {
                            location.reload()
                        } else if (data.status == 'error') {
                            modals.$message({
                                showClose: true,
                                message: data.message,
                                type: 'error',
                                duration: 10000
                            });
                        }
                    }
                });
            }
        });
    }
}

var modals = new Vue({
    el: '#materials_story',
    data : {
        material_id: '',
        material_unit: '',
        material_count: '',
        materials: {!! json_encode($operation->materialsPart) !!},
        units: {!! json_encode($operation->materials()->getModel()::$main_units) !!},
        manual_materials: [],
        comment: '',
        is_send_update: false,
        csrf: "{{ csrf_token() }}",
    },
    mounted: function () {
        var that = this;

        that.ids = that.materials.map(function(item) {return item.manual_material_id; })

        axios.post('{{ route('building::mat_acc::report_card::get_materials') }}', {material_ids: that.ids}).then(function (response) {
            that.manual_materials = response.data;
        });

        $('.prerendered-date').each(function() {
            const date = $(this).text();
            const content = that.isValidDate(date, 'DD.MM.YYYY') ? that.weekdayDate(date, 'DD.MM.YYYY') : '-';
            const innerSpan = $('<span/>', {
                'class': that.isWeekendDay(date, 'DD.MM.YYYY') ? 'weekend-day' : ''
            });
            innerSpan.text(content);
            $(this).html(innerSpan);
        });
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
        delete_file(file, fileList) {
            axios.post('{{ route('building::mat_acc::delete_file', $operation->id) }}', {file_name: file.file_name}).then(function (response) {
                console.log(response.data);
            })
        },
        delete_uploaded_file(file, fileList) {
            axios.post('{{ route('building::mat_acc::delete_file', $operation->id) }}', {file_name: file.response.file_name}).then(function (response) {
                console.log(response.data);
            })
        },
        showModal(id) {
            $(`#${id}`).modal('show');
        },
        search(query) {
            var that = this;

            if (query !== '') {
              setTimeout(() => {
                axios.post('{{ route('building::mat_acc::report_card::get_materials') }}', {q: query}).then(function (response) {
                    that.manual_materials = response.data;
                })
            }, 1000);
            } else {
                axios.post('{{ route('building::mat_acc::report_card::get_materials') }}', {materials_ids: that.ids}).then(function (response) {
                    that.manual_materials = response.data;
                });
            }
        },
        changeUsageValue(value) {
            this.$emit('update:used', value);
        },
        update(material, is_unlocked) {
            this.is_send_update = true;
            if (is_unlocked) {
                axios.post('{{ route('building::mat_acc::update_part_operation') }}', {
                    material_id: material.id,
                    base_id: material.base_id,
                    manual_material_id: material.manual_material_id,
                    material_unit: material.unit,
                    material_count: material.count,
                    used: material.used,
                    description: material.material_addition.description ? material.material_addition.description : '',

            }).then(function (response) {
                    if (response.data.status == 'success') {
                        location.reload()
                    } else if (response.data.status == 'error') {
                        modals.is_send_update = false;

                        modals.$message({
                            showClose: true,
                            message: response.data.message,
                            type: 'error',
                            duration: 10000
                        });
                    }
                });
            } else {
                axios.post('{{ route('building::mat_acc::store_update_task') }}', {
                    material_id: material.id,
                    base_id: material.base_id,
                    manual_material_id: material.manual_material_id,
                    material_unit: material.unit,
                    material_count: material.count,
                    description: material.material_addition.description ? material.material_addition.description : '',
                    used: material.used,
                }).then(function(response) {
                    if (response.data.status == 'success') {
                        location.reload()
                    } else {
                        modals.is_send_update = false;

                        modals.$message({
                            showClose: true,
                            message: response.data.message,
                            type: 'error',
                            duration: 10000
                        });
                    }
                });

            }

        }
    }
});
</script>
@endpush

@endif
