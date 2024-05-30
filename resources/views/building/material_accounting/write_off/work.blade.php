@extends('layouts.app')

@section('title', 'Материальный учет')

@section('url', '')

@section('css_top')
    <style>
        .el-select {
            width: 100%
        }

        .el-date-editor.el-input {
            width: inherit;
        }

        .margin-top-15 {
            margin-top: 15px;
        }

        .el-input-number {
            width: inherit;
        }

        .card-header.accordion-card-header {
            border-bottom: 1px solid #DDDDDD !important;
        }

        .card-title.accordion-card-title {
            margin-top: 0;
            margin-bottom: 0;
            font-size: 18px;
            color: inherit;
        }

        .accordion-title {
            color: #333;
            padding: 0px 0 5px;
            display: block;
            width: 100%;
            font-size: 16px;
        }

        .caret.accordion-caret {
            display: inline-block;
            width: 0;
            height: 0;
            margin-left: 2px;
            vertical-align: middle;
            border-top: 4px dashed;
            border-top: 4px solid \9;
            border-right: 4px solid transparent;
            border-left: 4px solid transparent;
            float: right;
            margin-top: 12px;
            margin-right: 15px;
            -webkit-transition: all 150ms ease-in;
            -moz-transition: all 150ms ease-in;
            -o-transition: all 150ms ease-in;
            -ms-transition: all 150ms ease-in;
            transition: all 150ms ease-in;
        }
    </style>
@endsection

@section('content')
    @include('building.material_accounting.modules.material_notes')

    @include('building.material_accounting.modules.breadcrump')

    @include('building.material_accounting.modules.operation_title')

    <div class="modal fade bd-example-modal-lg show" id="description" role="dialog" aria-labelledby="modal-description" style="display: none;">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content pb-3">
                <div class="modal-header">
                    <h5 class="modal-title">Примечание</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body pt-0">
                    <h6 class="decor-h6-modal">Описание категории</h6>
                    <div class="row">
                        <div class="col" style="line-height: 1.5">
                            @{{ material.default_material_description }}
                        </div>
                    </div>
                    <h6 class="decor-h6-modal">Документация</h6>
                    <div class="row">
                        <div class="col">
                            <ul class="list-unstyled" v-if="material.documents.length > 0">
                                <li v-for="doc in material.documents">
                                    <a :href="doc.source_link" target="_blank">
                                        @{{ doc.original_filename }}
                                    </a>
                                </li>
                            </ul>
                            <span v-else>Нет приложений</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 col-xl-10 ml-auto mr-auto pd-0-min">
            <!-- write-off story -->
        @include('building.material_accounting.modules.history_composit')

        <!-- information about materials -->
            @include('building.material_accounting.modules.info_about_materials_moving')

            @if($operation->comment_author)
                <div class="card strpied-tabled-with-hover">
                    <div class="card-body">
                        <div class="row" id="sender_answer">
                            <div class="col-md-12">
                                <label class="mb-20">Комментарий автора</label>
                                <blockquote>
                                    <p style="font-size:14px">
                                        {{ $operation->comment_author }}
                                    </p>
                                    <div class="row">
                                        <div class="col-md-7">
                                            <a href="#" data-toggle="modal" data-target="#view-photo"
                                               class="table-link blockquote-link">
                                                <i class="fa fa-picture-o "></i>
                                                Фото
                                            </a>
                                            <el-button type="text" @click="dialogTableVisible = true"
                                                       class="iphone-quote">
                                                <i class="fa fa-file"></i>
                                                Сопроводительные документы
                                            </el-button>

                                            <el-dialog title="Сопроводительные документы"
                                                       :visible.sync="dialogTableVisible">
                                                <el-table :data="docsList">
                                                    <el-table-column property="file_name"
                                                                     label="Имя файла"></el-table-column>
                                                    <el-table-column property="created_at"
                                                                     label="Дата добавления"></el-table-column>
                                                    <el-table-column property="url" align="right" label="Действия">
                                                        <template slot-scope="scope">
                                                            <el-button type="text"
                                                                       @click="url_to_doc(scope.$index, scope.row)">
                                                                <i class="fa fa-eye"></i>
                                                            </el-button>
                                                        </template>

                                                    </el-table-column>
                                                </el-table>
                                            </el-dialog>
                                        </div>
                                        <div class="col-md-5 text-right">
                                            <small>{{ $operation->author->full_name }}</small>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                </blockquote>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($operation->hasAccessFrom())

                <div class="card">
                    <div class="card-body">

                        <h5 class="materials-info-title mb-20__mobile">Подтверждение операции</h5>
                        <div id="materials">
                            <div class="card-body">
                                <h6 style="margin-bottom:15px">Номенклатура</h6>
                                <div class="materials">
                                    <div
                                        is="material-item"
                                        v-for="(material_input, index) in material_inputs"
                                        :key="index + '-' + material_input.id"
                                        :index="index"
                                        :material_index="material_input.id"
                                        :material_id.sync="material_input.material_id"
                                        :material_unit.sync="material_input.material_unit"
                                        :material_count.sync="material_input.material_count"
                                        :material_date.sync="material_input.material_date"
                                        :material_input="material_input"
                                        :materials.sync="material_input.materials"
                                        :base_id.sync="material_input.base_id"
                                        :used.sync="material_input.used"
                                        :units.sync="material_input.units"
                                        v-on:remove="material_inputs.splice(index, 1)"
                                        :inputs_length="material_inputs.length">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 text-right" style="margin-top:25px">
                                        <button type="button" v-on:click="add_material"
                                                class="btn btn-round btn-sm btn-success btn-outline add-material">
                                            <i class="fa fa-plus"></i>
                                            Добавить материал
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div id="answer">
                            <div class="row" style="margin-top:25px; margin-bottom:10px">
                                <div class="col-md-12">
                                    <label for="">
                                        Комментарий <span class="star">*</span>
                                    </label>
                                    <textarea v-model="comment" class="form-control textarea-rows"></textarea>
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
                                            action="{{ route('building::mat_acc::part_upload', [$operation->id, 'operation_material_id' => '0']) }}"
                                            :data="request"
                                            multiple
                                            ref="upload_doc"
                                            :on-success="add_files"
                                            :on-remove="remove_files"
                                            :on-progress="in_process"
                                            :on-error="if_error"
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
                                        <el-upload
                                            :headers="{ 'X-CSRF-TOKEN': csrf }"
                                            action="{{ route('building::mat_acc::part_upload', [$operation->id, 'operation_material_id' => '0']) }}"
                                            list-type="picture-card"
                                            :data="request"
                                            ref="upload"
                                            :on-success="add_images"
                                            :on-remove="remove_images"
                                            :on-progress="in_process"
                                            :on-error="if_error"
                                            multiple>
                                            <i class="el-icon-plus"></i>
                                        </el-upload>
                                        <el-dialog :visible.sync="dialogVisible">
                                            <img width="100%" :src="dialogImageUrl" alt="">
                                        </el-dialog>
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="margin-top:20px">
                                <div class="col-md-12 mobile-btn-align">
                                    <div class="d-inline-block" @click="beforeSend">
                                        <el-button type="primary" v-if="inputs_length" v-on:click="part_save"
                                                   :loading="is_loading_send || array_process.length > 0" class="btn-wd"
                                                   style="padding: 11px 20px!important; font-weight:400!important; width: 190px;">
                                            Закрыть частично
                                        </el-button>
                                    </div>
                                    <div class="d-inline-block" @click="beforeSend">
                                        <el-button id="button_send" type="button" @click="send"
                                                   style="vertical-align: inherit; width: 190px;"
                                                   :loading="is_loading_send || array_process.length > 0"
                                                   class="btn btn-wd btn-info">Завершить операцию
                                        </el-button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            @endif
        </div>
    </div>
    <div class="modal fade bd-example-modal-lg show" id="view-photo" role="dialog" aria-labelledby="modal-search"
         style="display: none;">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Фото</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <hr style="margin-top:0">
                    <div class="card border-0">
                        <div class="card-body ">
                            @foreach($operation->images_author as $image)

                                <div class="row attached-photocard">
                                    <div class="col-md-5">
                                        <div class="attached-photo-container">
                                            <a href="{{ $image->url }}" target="_blank">
                                                <img class="attached-photo" src="{{ $image->url }}" alt="фото">
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-md-7" style="margin-top:10px">
                                        <div class="meta-photo-container">
                                            <div class="row">
                                                <label class="col-sm-4 meta-title">Создано</label>
                                                <div class="col-sm-8">
                                                    <span class="meta-content">{{ $image->created_at }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                </div>
            </div>
        </div>
    </div>

    <template>
        <el-button :plain="true" @click="success_delete">Файл успешно удален.</el-button>
    </template>

@endsection

@section('js_footer')
    <script type="text/javascript">
        Vue.component('validation-provider', VeeValidate.ValidationProvider);
        Vue.component('validation-observer', VeeValidate.ValidationObserver);
    </script>
    <!-- component to select materials -->
    <script>
        Vue.component('material-item', {
            template: '\
      <div class="form-row" style="margin-top: 7px;">\
          <div class="col-md-4">\
              <label :class="[material_index !== 1 ? \'show-mobile-label mt-10__mobile\' : \'mt-10__mobile\']" >\
                  Материал <span class="star">*</span>\
              </label>\
              <template>\
                <el-select @change="changeMaterialId" ref="usernameInput" v-model="default_base_id" clearable filterable :remote-method="search" remote size="large" placeholder="Выберите материал">\
                  <el-option\
                    v-for="item in new_materials_filtered"\
                    :label="item.label"\
                    :key="item.base_id"\
                    :value="item.base_id">\
                  </el-option>\
                </el-select>\
              </template>\
          </div>\
           <div class="col-md-1 align-self-end text-center">\
                <button data-toggle="modal" data-target="#material-notes" @click="() => { materialNotes().changeMaterialInput(this,false, true); hideTooltips(); }"\
                        @mouseleave="hideTooltips" type="button"\
                        data-balloon-pos="up" :aria-label="notesLabel"\
                        data-balloon-length="medium"\
                        :disabled="!material_id"\
                        class="btn btn-link btn-xs pd-0 mt-10__mobile mr-1" style="height: 40px;"\
                        :class="material_id && comments.length > 0 ? \'btn-danger\' : \' btn-secondary\'">\
                    <i style="font-size:18px;" class="fa fa-info-circle"></i>\
                </button>\
                <button data-toggle="modal" data-target="#description" @click="() => { getDescription(); hideTooltips(); }"\
                        @mouseleave="hideTooltips" type="button"\
                        data-balloon-pos="up" aria-label="Описание категории материала"\
                        :disabled="!material_id"\
                        class="btn btn-link btn-xs pd-0 mt-10__mobile" style="height: 40px;"\
                        :class="material_id ? \'btn-primary\' : \' btn-secondary\'">\
                    <i style="font-size:18px;" class="fa fa-info-circle"></i>\
                </button>\
              </div>\
          <div class="col-md-1">\
              <label for="" :class="[material_index !== 1 ? \'show-mobile-label mt-10__mobile\' : \'mt-10__mobile\']">\
                  Ед. изм. <span class="star">*</span>\
              </label>\
              <template>\
                <el-select @change="changeMaterialUnit" v-model="default_material_unit" placeholder="Ед. измерения">\
                  <el-option\
                    v-for="item in units"\
                    :key="item.id"\
                    :value="item.id"\
                    :label="item.text">\
                  </el-option>\
                </el-select>\
              </template>\
          </div>\
          <div class="col-md-2">\
              <label for="" :class="[material_index !== 1 ? \'show-mobile-label mt-10__mobile\' : \'mt-10__mobile\']">\
                  Дата <span class="star">*</span>\
              </label>\
              <template>\
              \<el-date-picker\
                        v-model="default_material_date"\
                        type="date"\
                        placeholder="День"\
                        format="dd.MM.yyyy"\
                        value-format="yyyy-MM-dd"\
                        name="planned_date_from"\
                        required\
                        @change="changeMaterialDate"\
                        :picker-options="dateToOptions"\
                >\
                </el-date-picker>\
              </template>\
          </div>\
          <div class="col-md-2">\
              <label for="" :class="[material_index !== 1 ? \'show-mobile-label mt-10__mobile\' : \'mt-10__mobile\']">\
                  Количество <span class="star">*</span>\
              </label>\
              <template>\
                  <el-input-number :min="0" @change="changeMaterialCount" v-model="default_material_count" :precision="3" :step="0.001" :max="10000000" required></el-input-number>\
              </template>\
          </div>\
          <div class="col-md-1">\
                <label for="" :class="[material_index !== 1 ? \'show-mobile-label mt-10__mobile\' : \'mt-10__mobile\']">\
                    Б/У\
                </label>\
                    <template>\
                        <el-checkbox v-model="default_material_used"\
                            border class="d-block"\
                           @can('mat_acc_base_move_to_new') @change="changeUsageValue" @endcan @cannot('mat_acc_base_move_to_new') disabled @endcannot
                ></el-checkbox>\
            </template>\
            </div>\
          <div class="col-md-1 text-center">\
          <button rel="tooltip" type="button" v-on:click="$emit(\'remove\')" class="btn-remove-mobile" data-original-title="Удалить">\
            <i style="font-size:18px;" :class="[material_index !== 1 ? \'fa fa-times remove-stroke-index\' : \'fa fa-times remove-stroke\']" ></i>\
          </button>\
          </div>\
        </div>\
          ',
            props: ['material_id', 'material_unit', 'material_count', 'inputs_length', 'material_index', 'used', 'materials', 'units', 'material_input', 'base_id', 'index'],
            computed: {
                notesLabel() {
                    if (!this.material_id) {
                        return 'Примечания';
                    }
                    if (this.comments && this.comments.length > 0) {
                        const commentsString = this.comments.map(comment => comment.comment).join(', ');
                        if (commentsString.length > 90) {
                            return commentsString.slice(0, 90) + '... см. полный список примечаний в справочнике.';
                        } else {
                            return commentsString;
                        }
                    } else {
                        return 'Вы можете добавить к этому материалу примечания';
                    }
                },
                new_materials_filtered() {
                    return this.materials
                        .filter(el => {
                            const count = materials.material_inputs.filter(input => input.base_id == el.base_id).length;
                            return count < 1 || this.default_base_id == el.base_id;
                        });
                }
            },
            methods: {
                changeMaterialId(value, on_load = false) {
                    if (value) {
                        let mat = this.materials.filter(input => input.base_id == value)[0];
                        let used = (mat.used === undefined ? false : mat.used);
                        this.$emit('update:material_id', mat.id);
                        this.$emit('update:base_id', value);
                        this.getDescription();
                        this.changeUsageValue(used);
                        this.loadComments(mat);
                        this.default_material_used = used;
                        this.default_material_id = mat.id;

                        if (this.default_material_unit == false) {
                            let unit = (mat.unit === undefined ? null : mat.unit);
                            if (!on_load) {
                                this.autoChangeUnit(unit);
                            }
                        }
                    } else {
                        this.changeUsageValue(false);
                        this.default_material_used = false;
                    }
                },
                changeMaterialUnit(value) {
                    this.$emit('update:material_unit', value);
                },
                changeMaterialCount(value) {
                    this.$emit('update:material_count', value);
                },
                changeMaterialDate(value) {
                    this.$emit('update:material_date', value);
                },
                changeUsageValue(value) {
                    this.$emit('update:used', value);
                },
                autoChangeUnit(unit) {
                    this.changeMaterialUnit(unit);
                    this.default_material_unit = unit;
                },
                search(query) {
                    const that = this;

                    if (query !== '') {
                        setTimeout(() => {
                            axios.post('{{ route('building::mat_acc::report_card::get_materials') }}', {
                                q: query,
                                base_id: {{ $operation->object_id_from }}}).then(function (response) {
                                materials.material_inputs[that.inputs_length - 1].materials = response.data
                            })
                        }, 1000);
                    } else {
                        materials.material_inputs[that.inputs_length - 1].materials = []
                    }
                },
                getDescription() {
                    let that = this;

                    if (String(that.default_material_id)) {
                        axios.post('{{ route('building::mat_acc::get_material_category_description') }}', {id: String(that.default_material_id).split('_')[0]}).then(function (response) {
                                that.default_material_description = response.data.message;
                                that.documents = response.data.documents;
                            }).catch((err)=>{});
                    } else {
                        that.default_material_description = 'Нет описания';
                        that.documents = [];
                    }

                    descriptionModal.material = this;
                },
                hideTooltips() {
                    for (let ms = 50; ms <= 1050; ms += 100) {
                        setTimeout(() => {
                            $('[data-balloon-pos]').blur();
                        }, ms);
                    }
                },
                materialNotes() {
                    return materialNotes;
                },
                locationFromName() {
                    if (typeof(vm) !== 'undefined') {
                        return vm.$refs['search_from'] ? vm.$refs['search_from'].query : null;
                    } else if (typeof(this.predefinedLocation) !== 'undefined') {
                        return this.predefinedLocation;
                    }
                    return null;
                },
                materialName() {
                    return this.$refs['usernameInput'] ? this.$refs['usernameInput'].query : null;
                },
                loadComments(mat) {
                    if (typeof(mat) !== 'undefined' && mat.base_id) {
                        axios.get('{{ route('building::mat_acc::report_card::get_base_comments') }}', { params: { base_id: mat.base_id }})
                        .then(response => {
                            this.id = mat.base_id;
                            this.comments = response.data.comments;
                        })
                        .catch(error => console.log(error));
                    }
                }
            },
            data: function () {
                return {
                    default_material_id: materials.material_inputs[this.index].material_id,
                    default_base_id: materials.material_inputs[this.index].base_id,
                    default_material_unit: materials.material_inputs[this.index].material_unit,
                    default_material_count: materials.material_inputs[this.index].material_count,
                    default_material_used: materials.material_inputs[this.index].used,
                    default_material_date: materials.material_inputs[this.index].material_date,
                    default_material_description: 'Нет описания',
                    documents: [],
                    comments: [],
                    id: null,
                    dateToOptions: {
                        firstDayOfWeek: 1,
                    }
                }
            },
            mounted() {
                if (this.default_base_id) {
                    this.changeMaterialId(this.default_base_id, true);
                }
            }
        })

        var materials = new Vue({
            el: '#materials',
            data: {
                options: [],
                selected: '',
                material_unit: '',
                next_mat_id: 1,
                material_inputs: [],
                units: {!! json_encode($operation->materials()->getModel()::$main_units) !!},
                exist_materials: {!! $operation->materialDifference($operation->materials->where('type', 3), $operation->materialsPartFrom) !!}
            },
            created: function () {
                const that = this;

                axios.post('{{ route('building::mat_acc::report_card::get_materials') }}', {base_id: {{ $operation->object_id_from }}, material_ids: {{ $operation->materials->pluck('manual_material_id') }} }).then(function (response) {
                    that.new_materials = response.data;

                    Object.keys(that.exist_materials).map(function (key) {
                        let ex_base_id = typeof (that.exist_materials[key].base_id) === 'object' ? undefined : that.exist_materials[key].base_id;

                        if (!that.inArray(that.new_materials, ex_base_id, that.exist_materials[key].manual_material_id)) {
                            that.new_materials.push({
                                id: that.exist_materials[key].manual_material_id,
                                base_id: ex_base_id,
                                label: that.exist_materials[key].comment_name,
                                used: that.exist_materials[key].used,
                                unit: that.exist_materials[key].unit,
                            })
                        }

                        setTimeout(() => {
                            that.material_inputs.push({
                                id: that.next_mat_id++,
                                material_id: `${that.exist_materials[key].manual_material_id}_${that.exist_materials[key].used ? 1 : 0}`,
                                base_id: ex_base_id,
                                material_unit: that.exist_materials[key].unit,
                                material_count: Number(that.exist_materials[key].count),
                                material_date: (new Date()).toISOString().split('T')[0],
                                used: that.exist_materials[key].used,
                                units: that.units,
                                materials: that.new_materials
                            });
                        }, 200)
                    });
                });
            },
            methods: {
                add_material() {
                    const that = this;

                    axios.post('{{ route('building::mat_acc::report_card::get_materials') }}', {base_id: {{ $operation->object_id_from }} }).then(function (response) {
                        that.new_materials = response.data

                        that.material_inputs.push({
                            id: that.next_mat_id++,
                            material_id: '',
                            base_id: '',
                            material_unit: '',
                            material_label: '',
                            material_count: '',
                            material_date: (new Date()).toISOString().split('T')[0],
                            used: false,
                            units: that.units,
                            materials: that.new_materials
                        });
                    });
                },
                inArray: function (array, base, manual_id) {
                    var length = array.length;
                    for (var i = 0; i < length; i++) {
                        if (array[i].base_id == base && array[i].id == manual_id) return true;
                    }
                    return false;
                }
            }
        })


        var answer = new Vue({
            el: '#answer',
            data: {
                csrf: "{{ csrf_token() }}",
                dialogImageUrl: '',
                dialogVisible: false,
                comment: '',
                request: {author_type: 2},
                is_loading_send: false,
                array_process: [],
                images_ids: [],
                files_ids: []
            },
            computed: {
                inputs_length: function () {
                    return materials.material_inputs.length;
                }
            },
            mounted: function () {
                $('#button_send').removeClass('el-button');
            },
            methods: {
                handleError(error) {
                    let message = '';
                    let errors = error.response.data.errors;
                    for (let key in errors) {
                        message += errors[key][0] + '<br>';
                    }
                    answer.is_loading_send = false;

                    swal({
                        type: 'error',
                        title: "Ошибка",
                        html: message,
                    });
                },
                send() {
                    var that = this;
                    this.is_loading_send = true;

                    axios.post('{{ route('building::mat_acc::write_off::send', $operation->id) }}', {
                        materials: materials.material_inputs,
                        comment: answer.comment,
                        files_ids: answer.files_ids,
                        images_ids: answer.images_ids
                    }).then(function (response) {
                        if (!response.data.message) {
                            window.location = '{{ route('building::mat_acc::operations') }}';
                        } else {
                            answer.is_loading_send = false;
                            that.$nextTick(() => $('#button_send').removeClass('el-button'));

                            answer.$message({
                                showClose: true,
                                message: response.data.message,
                                type: 'error',
                                duration: 10000
                            });
                        }
                    }).catch(error => this.handleError(error));
                },
                part_save() {
                    var that = this;
                    answer.is_loading_send = true;

                    axios.post('{{ route('building::mat_acc::write_off::part_send', $operation->id) }}', {
                        materials: materials.material_inputs,
                        comment: answer.comment,
                        files_ids: answer.files_ids,
                        images_ids: answer.images_ids
                    }).then(function (response) {
                        if (!response.data.message) {
                            window.location.reload();
                        } else {
                            answer.is_loading_send = false;
                            that.$nextTick(() => $('#button_send').removeClass('el-button'));

                            answer.$message({
                                showClose: true,
                                message: response.data.message,
                                type: 'error',
                                duration: 10000
                            });
                        }
                    }).catch(error => this.handleError(error));
                },
                add_files(response, file, fileList) {
                    var that = this;
                    that.files_ids.push(file.response.id);

                    answer.array_process = _.remove(answer.array_process, function (n) {
                        return n != file.uid;
                    });
                    this.$nextTick(() => $('#button_send').removeClass('el-button'));
                },
                remove_files(file, fileList) {
                    var that = this;

                    answer.array_process = _.remove(answer.array_process, function (n) {
                        return n != file.uid;
                    });

                    answer.images_ids = _.remove(answer.images_ids, function (n) {
                        return n != file.response.id;
                    });
                },
                add_images(response, file, fileList) {
                    var that = this;

                    that.images_ids.push(file.response.id);

                    answer.array_process = _.remove(answer.array_process, function (n) {
                        return n != file.uid;
                    });
                    this.$nextTick(() => $('#button_send').removeClass('el-button'));
                },
                remove_images(file, fileList) {
                    var that = this;
                    answer.images_ids = _.remove(answer.images_ids, function (n) {
                        return n != file.response.id;
                    });
                },
                in_process(event, file, fileList) {
                    if (!answer.array_process.includes(file.uid)) {
                        answer.array_process.push(file.uid);
                    }
                },
                beforeSend() {
                    if (this.array_process.length > 0) {
                        this.$message({
                            showClose: true,
                            message: 'Происходит прикрепление документов к операции. Кнопки станут доступны после успешного окончани загрузки. Если это происходит слишком долго - обновите страницу',
                            type: 'error',
                            duration: 5000
                        });
                    }
                },
                if_error(err, file, fileList) {
                    answer.array_process = _.remove(answer.array_process, function (n) {
                        return n != file.uid;
                    });
                },
            }
        })

        var sender_answer = new Vue({
            el: '#sender_answer',
            data: {
                docsList: {!! json_encode($operation->documents_author) !!},
                dialogTableVisible: false,
            },
            methods: {
                url_to_doc: function (index, row) {
                    window.open(row.url, '_blank');
                }
            }
        });
    </script>
    <script type="text/javascript">
        $("body").on('DOMSubtreeModified', ".materials", function () {
            $('.materials').children('.row:first-child').find('label').removeClass('show-mobile-label');
            $('.materials').children('.row:first-child').find('.btn-remove-mobile').children().removeClass('remove-stroke-index').addClass('remove-stroke');
        });
    </script>

    <script>
        var eventHub = new Vue();

        Vue.component('material-attribute', {
            template: '\
        <div style="padding-left:15px;" class="mt-4">\
            <validation-provider :rules="attribute_is_required ? `required` : ``" :vid="\'select-\' + attribute_id"\
                                                                         :ref="\'select-\' + attribute_id" v-slot="v">\
                <label class="d-block">@{{ attribute_name + (attribute_unit ? (comma + attribute_unit) : empty) }}<span v-if="attribute_is_required" class="star">*</span></label>\
                <el-select\
                  v-model="default_parameter"\
                  @change="onChange"\
                  :allow-create="attribute_name.toLowerCase() !== \'эталон\'"\
                  filterable\
                  clearable\
                  :class="v.classes"\
                  :id="\'select-\' + attribute_id"\
                  remote\
                  :remote-method="search"\
                  :loading="loading"\
                  placeholder="">\
                  <el-option\
                    v-for="item in mixedParameters"\
                    :label="item"\
                    :value="item">\
                  </el-option>\
                </el-select>\
                <div class="error-message" style="padding-left: 15px;">@{{ v.errors[0] }}</div>\
             </validation-provider>\
         </div>\
        ',
            props: ['index', 'attribute_id', 'attribute_unit', 'attribute_name', 'attribute_value', 'attribute_is_required'],
            created() {
                eventHub.$on('addEvent', (e) => {
                    this.default_parameter = '';
                });
            },
            mounted: function () {
                const that = this;

                axios.post('{{ route('building::materials::category::get_need_attrs_values') }}', {attribute_ids: this.attribute_id}).then(function (response) {
                    that.parameters = [];
                    that.parameters = response.data;
                })

                if (this.attribute_name.toLowerCase() !== 'эталон') {
                    $(`#select-${this.attribute_id}`).keyup((e) => {
                        this.changeAttributeValue(e.target.value);
                    });
                }
            },
            methods: {
                onChange(value) {
                    this.$emit('update:attribute_value', value);
                    this.default_parameter = value;
                },
                changeAttributeValue(value) {
                    this.$emit('update:attribute_value', value);
                    this.default_parameter = value;
                    this.search(value);
                },
                search(query) {
                    const that = this;

                    clearTimeout(this.searchTimeout);
                    this.searchTimeout = setTimeout(() => {
                        if (query !== '') {
                            axios.post('{{ route('building::materials::category::get_need_attrs_values') }}', {attribute_id: this.attribute_id, q: query }).then(function (response) {
                                that.parameters = response.data;
                            })
                        } else {
                            axios.post('{{ route('building::materials::category::get_need_attrs_values') }}', {attribute_id: this.attribute_id }).then(function (response) {
                                that.parameters = response.data;
                            })
                        }
                    }, 350);
                }
            },
            data: function () {
                return {
                    searchTimeout: null,
                    default_parameter: '',
                    comma: ', ',
                    empty: '',
                    parameters: [],
                    loading: false,
                }
            },
            computed: {
                mixedParameters() {
                    return this.$refs.hasOwnProperty('select')
                    && this.attribute_value
                    && Array.isArray(this.parameters)
                        ? this.parameters.concat([this.attribute_value]) : this.parameters;
                }
            },
            $_veeValidate: {
                value() {
                    return this.default_parameter;
                }
            },
        });

        var descriptionModal = new Vue({
            el: '#description',
            data: {
                material: {
                    default_material_description: 'Нет описания',
                    documents: [],
                },
            },
        });

    </script>
@endsection
