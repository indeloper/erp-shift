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

    @include('building.material_accounting.modules.mass_add_materials')

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
            <div class="card strpied-tabled-with-hover">
                <div class="card-body" id="base" v-cloak>
                    <h5 style="margin-bottom:30px;font-size:19px">Списание материала</h5>
                    <div class="row">
                        <div class="col-md-4 mt-20__mobile">
                            <label for="">
                                Ответственный сотрудник <span class="star">*</span>
                            </label>
                            <template>
                                <el-select v-model="responsible_user" clearable filterable
                                           :remote-method="search_responsible_users" remote name="responsible_user_id"
                                           placeholder="Поиск сотрудника">
                                    <el-option
                                        v-for="item in responsible_users"
                                        :key="item.code"
                                        :label="item.label"
                                        :value="item.code">
                                    </el-option>
                                </el-select>
                            </template>
                        </div>
                        <div class="col-md-5">
                            <label for="">Место списания <span class="star">*</span></label>
                            <template>
                                <el-select v-model="selected" @change="clean_materials" ref="search_from" clearable filterable
                                           :remote-method="search" remote name="object_id" placeholder="Поиск">
                                    <el-option
                                        v-for="item in options"
                                        :key="item.code"
                                        :label="item.label"
                                        :value="item.code">
                                    </el-option>
                                </el-select>
                            </template>
                        </div>
                        <div class="col-md-3 mt-20__mobile">
                            <label for="">
                                Дата операции <span class="star">*</span>
                            </label>
                            <template>
                                <div class="block">
                                    <el-date-picker
                                        @change="clean_materials"
                                        v-model="operation_date"
                                        type="date"
                                        format="dd.MM.yyyy"
                                        value-format="dd.MM.yyyy"
                                        placeholder="Выберите день"
                                        name="planned_date_from"
                                        :picker-options="{firstDayOfWeek: 1}"
                                    >
                                    </el-date-picker>
                                </div>
                            </template>
                        </div>
                    </div>
                    @can('mat_acc_write_off_draft_create')
                        @cannot('mat_acc_write_off_create')
                            <div class="row" style="margin-bottom: 10px">
                                <div class="col-md-12 mt-20__mobile">
                                    <label for="">
                                        Руководитель проектов для согласования <span class="star">*</span>
                                    </label>
                                    <template>
                                        <el-select v-model="responsible_RP" clearable filterable
                                                   :remote-method="search_RP" remote name="responsible_RP"
                                                   placeholder="Поиск руководителя проектов">
                                            <el-option
                                                v-for="item in responsible_RPs"
                                                :key="item.code"
                                                :label="item.label"
                                                :value="item.code">
                                            </el-option>
                                        </el-select>
                                    </template>
                                </div>
                            </div>
                        @endcannot
                    @endcan
                    <div class="row">
                        <div class="col-md-6 mt-20">
                            <label for="">
                                Основание <span class="star">*</span>
                            </label>
                            <template>
                                <el-select v-model="reason" placeholder="Основание">
                                    <el-option
                                        v-for="item in reasons"
                                        :key="item.id"
                                        :label="item.text"
                                        :value="item.text">
                                    </el-option>
                                </el-select>
                            </template>
                        </div>
                    </div>
                    <div class="row" style="margin-bottom:10px">
                        <div class="col-md-12 mt-20">
                            <label for="">
                                Комментарий <span class="star">*</span>
                            </label>
                            <textarea v-model="comment" class="form-control textarea-rows" required></textarea>
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
                                    action="{{ route('building::mat_acc::upload', '0') }}"
                                    :data="request"
                                    :on-success="add_files"
                                    :on-remove="remove_files"
                                    multiple>
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
                                    action="{{ route('building::mat_acc::upload', 0) }}"
                                    list-type="picture-card"
                                    :data="request"
                                    :on-success="add_images"
                                    :on-remove="remove_images"
                                    multiple>
                                    <i class="el-icon-plus"></i>
                                </el-upload>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card" id="materials" v-cloak>
                <div class="card-body">
                    <h6 style="display:inline-block; width: 50%; margin-bottom:30px">Материалы</h6>
                    <div class="float-right">
                        <el-tooltip :disabled="!is_loading_mats" content="Ищем и готовим материалы..." placement="top">
                            <span>
                                <el-button data-toggle="modal" data-target="#mass_add_materials_modal" type="primary" name="button"
                                        :loading="is_loading_mats"
                                        style="font-size: 13px;border-radius: 3px;padding: 8px 15px; font-weight:400">
                                    Выбрать массово
                                </el-button>
                            </span>
                        </el-tooltip>
                    </div>
                    <br>
                    <div class="materials"
                         v-if="material_inputs.length > 0 ? (new_materials ? new_materials.length > 0 : false) : false">
                        <div
                            is="material-item"
                            v-for="(material_input, index) in material_inputs"
                            :key="index + '-' + material_input.id"
                            :index="index"
                            :material_index="material_input.id"
                            :material_id.sync="material_input.material_id"
                            :material_unit.sync="material_input.material_unit"
                            :material_count.sync="material_input.material_count"
                            :material_input="material_input"
                            :base_id.sync="material_input.base_id"
                            :used.sync="material_input.used"
                            :materials.sync="material_input.materials"
                            :units.sync="material_input.units"
                            v-on:remove="material_inputs.splice(index, 1)"
                            :inputs_length="material_inputs.length">
                        </div>
                    </div>
                    <div v-else>
                        @{{ !selected ? 'Выберите место и дату списания.' : (!operation_date ? 'Выберите дату списания' : 'На объекте отсутствуют материалы для списания.') }}
                    </div>
                    <div class="row"
                         v-if="material_inputs.length > 0 ? (new_materials ? new_materials.length > 0 : false) : false">
                        <div class="col-md-12 text-right" style="margin-top:25px">
                            <button type="button" v-on:click="add_material"
                                    class="btn btn-round btn-sm btn-success btn-outline add-material">
                                <i class="fa fa-plus"></i>
                                Добавить материал
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row" style="margin-top:20px">
                        <div class="col-md-12 mobile-btn-align">
                            @can('mat_acc_write_off_draft_create')
                                <el-button type="warning" @click="send_draft" :loading="is_loading_draft">Сохранить
                                    черновик
                                </el-button>
                            @endcan
                            @can('mat_acc_write_off_create')
                                <el-button type="primary" v-on:click="check_conflict" :loading="is_loading_send"
                                           style="vertical-align: top; padding: 11px 20px!important; font-weight:400!important">
                                    Создать
                                </el-button>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js_footer')
    <script type="text/javascript">
        Vue.component('validation-provider', VeeValidate.ValidationProvider);
        Vue.component('validation-observer', VeeValidate.ValidationObserver);
    </script>
    <script type="text/javascript">
        var vm = new Vue({
            el: '#base',
            data: {
                responsible_users: [],
                responsible_RPs: [],
                options: [],
                responsible_user: '',
                responsible_RP: '',
                selected: '',
                operation_date: '',
                comment: '',
                reason: '',
                reasons: [
                    {id: 1, text: 'Производство работ'},
                    {id: 2, text: 'Продажа'},
                    {id: 3, text: 'Другое'},
                ],
                csrf: "{{ csrf_token() }}",
                request: {author_type: 1},
                files_ids: [],
                images_ids: []
            },
            mounted: function () {
                axios.post('{{ route('building::mat_acc::get_users') }}', {
                    responsible_user: "{{ Request::get('resp')?? '' }}"
                }).then(response => vm.responsible_users = response.data).then(function () {
                    if (vm.responsible_users.some(el => {
                        return el.code === '{!! Request::get('resp')?? ''  !!}'
                    })) {
                        vm.responsible_user = "{{ Request::get('resp')?? '' }}";
                    }
                });

                axios.post('{{ route('building::mat_acc::get_RPs') }}')
                    .then(response => vm.responsible_RPs = response.data);

                axios.post('{{ route('building::mat_acc::report_card::get_objects') }}', {
                    object_id: '{{ Request::get('obj') ?? Session::get('object_id')[0] ?? '' }}',
                }).then(response => vm.options = response.data
                ).then(function () {
                    if (vm.options.some(el => {
                        return el.code === '{{ Request::get('obj') ?? Session::get('object_id')[0] ?? '' }}'
                    })) {
                        vm.selected = '{{ Request::get('obj') ?? Session::get('object_id')[0] ?? '' }}';
                    }
                }).then(function () {
                    if (vm.selected != false) {
                        vm.clean_materials();
                    }
                });
            },
            methods: {
                search(query) {
                    if (query !== '') {
                        setTimeout(() => {
                            axios.post('{{ route('building::mat_acc::report_card::get_objects') }}', {q: query}).then(function (response) {
                                vm.options = response.data;
                            })
                        }, 200);
                    } else {
                        axios.post('{{ route('building::mat_acc::report_card::get_objects') }}').then(function (response) {
                            vm.options = response.data;
                        });
                    }
                },
                search_responsible_users(query) {
                    if (query !== '') {
                        setTimeout(() => {
                            axios.post('{{ route('building::mat_acc::get_users') }}', {q: query}).then(function (response) {
                                vm.responsible_users = response.data;
                            })
                        }, 200);
                    } else {
                        axios.post('{{ route('building::mat_acc::get_users') }}').then(function (response) {
                            vm.responsible_users = response.data;
                        });
                    }
                },
                search_RP(query) {
                    if (query !== '') {
                        setTimeout(() => {
                            axios.post('{{ route('building::mat_acc::get_RPs') }}', {q: query}).then(function (response) {
                                vm.responsible_RPs = response.data;
                            })
                        }, 200);
                    } else {
                        axios.post('{{ route('building::mat_acc::get_RPs') }}').then(response => vm.responsible_RPs = response.data);
                    }
                },
                add_files(response, file, fileList) {
                    var that = this;
                    that.files_ids = [];

                    fileList.forEach(function (element) {
                        that.files_ids.push(element.response.id);
                    });
                },
                remove_files(file, fileList) {
                    var that = this;
                    that.files_ids = [];

                    fileList.forEach(function (element) {
                        that.files_ids.push(element.response.id);
                    });
                },
                add_images(response, file, fileList) {
                    var that = this;
                    that.images_ids = [];

                    fileList.forEach(function (element) {
                        that.images_ids.push(element.response.id);
                    });
                },
                remove_images(file, fileList) {
                    var that = this;
                    that.images_ids = [];

                    fileList.forEach(function (element) {
                        that.images_ids.push(element.response.id);
                    });
                },
                clean_materials() {
                    if (vm.selected && vm.operation_date) {
                        mass_add_materials.findAllMaterials(vm.selected);

                        axios.post('{{ route('building::mat_acc::report_card::get_materials') }}', {base_id: vm.selected ? vm.selected : -1}).then(function (response) {
                            materials.new_materials = response.data;
                            materials.material_inputs = [];
                            materials.material_inputs.push({
                                id: materials.next_mat_id++,
                                base_id: '',
                                material_id: '',
                                material_unit: '',
                                material_count: Number(0),
                                used: false,
                                units: materials.units,
                                materials: materials.new_materials
                            });
                        });
                    }
                }
            }
        })

        Vue.component('material-item', {
            template: '\
          <div class="form-row" style="margin-top: 7px;">\
              <div class="col-md-5">\
                  <label :class="[material_index !== 1 ? \'show-mobile-label mt-10__mobile\' : \'mt-10__mobile\']" >\
                      Материал <span class="star">*</span>\
                  </label>\
                  <template>\
                    <el-select @change="changeMaterialId" ref="usernameInput" v-model="default_base_id" clearable filterable @clear="search(``)" :remote-method="search" remote size="large" placeholder="Выберите материал">\
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
                <button data-toggle="modal" data-target="#material-notes" @click="() => { materialNotes().changeMaterialInput(this, false, true); hideTooltips(); }"\
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
              <div :class="[inputs_length === 1 ? \'col-md-2\' : \'col-md-2\']">\
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
                <div class="col-md-1 text-center" v-if="inputs_length > 1">\
                  <button rel="tooltip" type="button" v-on:click="$emit(\'remove\')" class="btn-remove-mobile" data-original-title="Удалить">\
                      <i style="font-size:18px;" :class="[material_index !== 1 ? \'fa fa-times remove-stroke-index\' : \'fa fa-times remove-stroke\']" ></i>\
                  </button>\
                </div>\
            </div>\
          ',
            props: ['material_id', 'material_unit', 'material_count', 'used', 'inputs_length', 'material_index', 'materials', 'units', 'index', 'material_input', 'base_id'],
            computed: {
                new_materials_filtered() {
                    return this.materials
                        .filter(el => {
                            const count = materials.material_inputs.filter(input => input.base_id == el.base_id).length;
                            return count < 1 || this.default_base_id == el.base_id;
                        });
                    },
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
                    }
            },
            methods: {
                changeMaterialId(value) {
                    if (value) {
                        let mat = this.materials.filter(input => input.base_id == value)[0];
                        let used = (mat.used === undefined ? false : mat.used);
                        this.$emit('update:material_id', mat.id);
                        this.$emit('update:base_id', value);
                        this.getDescription();
                        this.changeUsageValue(used);
                        this.default_material_used = used;
                        this.default_material_id = mat.id;
                        this.loadComments(mat);

                        if (this.default_material_unit == false) {
                            let unit = (mat.unit === undefined ? null : mat.unit);
                            this.autoChangeUnit(unit)
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
                changeUsageValue(value) {
                    this.$emit('update:used', value);
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
                autoChangeUnit(unit)
                {
                    this.changeMaterialUnit(unit)
                    this.default_material_unit = unit;
                },
                search(query) {
                    const that = this;

                    axios.post('{{ route('building::mat_acc::report_card::get_materials') }}', {
                        q: query,
                        base_id: vm.selected
                    }).then(function (response) {
                        materials.material_inputs[that.index].materials = response.data
                    })
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
                    default_material_id: this.material_id ? this.material_id : materials.material_inputs[this.index].material_id,
                    default_base_id: materials.material_inputs[this.index].base_id,
                    default_material_unit: this.material_unit ? this.material_unit : materials.material_inputs[this.index].material_unit,
                    default_material_count: this.material_count ? this.material_count : materials.material_inputs[this.index].material_count,
                    default_material_used: (this.used !== null && this.used !== undefined) ? this.used : materials.material_inputs[this.index].used,
                    default_material_description: 'Нет описания',
                    documents: [],
                    comments: [],
                    id: null
                }
            },
            mounted() {
                if (this.default_base_id) {
                    this.changeMaterialId(this.default_base_id);
                }
            }
        });

        var materials = new Vue({
            el: '#materials',
            data: {
                options: [],
                new_materials: [],
                material_unit: '',
                next_mat_id: 1,
                material_inputs: [],
                units: {!! json_encode($units) !!},
                exist_materials: [],
                is_loading_send: false,
                is_loading_draft: false,
                is_loading_mats: false,
            },
            computed: {
                selected() {
                    return vm.selected;
                },
                operation_date() {
                    return vm.operation_date;
                },
                show_tooltip() {
                    return !this.is_loading_mats;
                }
            },
            methods: {
                add_material() {
                    const that = this;

                    axios.post('{{ route('building::mat_acc::report_card::get_materials') }}', {base_id: vm.selected}).then(function (response) {
                        that.new_materials = response.data;

                        that.material_inputs.push({
                            id: that.next_mat_id++,
                            material_id: '',
                            base_id: '',
                            material_unit: '',
                            material_label: '',
                            material_count: '',
                            used: false,
                            units: that.units,
                            materials: that.new_materials
                        });
                    });
                },
                check_conflict() {
                    materials.is_loading_send = true;
                    axios.post('{{ route('building::mat_acc::suggest_solution') }}', {
                        materials: materials.material_inputs,
                        planned_date_to: vm.operation_date,
                        object_id: vm.selected,
                    }).then(function (response) {
                        if (response.data) {
                            let message = '';
                            let errMessage = '';
                            if (!response.data.failure && !response.data.transform) {
                                materials.send();
                                return;
                            }

                            for (let solutionKey in response.data.solutions) {
                                if (response.data.solutions[solutionKey].status === 'transform') {
                                    message += response.data.solutions[solutionKey].message + ' <br>';
                                } else if (response.data.solutions[solutionKey].status === 'failure') {
                                    errMessage += response.data.solutions[solutionKey].message + ' <br>';
                                }
                            }

                            let title = (response.data.failure ? 'Ошибка создания операции' : 'Произвести автоматическое преобразование?')
                            swal({
                                title: title,
                                html: response.data.failure ? errMessage : message,
                                type: 'warning',
                                showCancelButton: (!response.data.failure),
                                cancelButtonText: 'Нет',
                                confirmButtonText: 'Ок'
                            }).then((result) => {
                                if (!response.data.failure) {
                                    if (result.value) {
                                        axios.post('{{ route('building::mat_acc::do_solutions') }}', {'solutions': response.data.solutions})
                                            .then(function (done_response) {
                                                if (done_response.data.status) {
                                                    materials.send();
                                                    return;
                                                }
                                            });
                                    } else {
                                        materials.is_loading_send = false;
                                    }
                                } else {
                                    materials.is_loading_send = false;
                                }
                            })
                        }
                    });
                },
                send() {
                    materials.is_loading_draft = true;
                    filesCount = vm.files_ids.length + vm.images_ids.length;
                    axios.post('{{ route('building::mat_acc::write_off::store') }}', {
                        materials: materials.material_inputs,
                        planned_date_to: vm.operation_date,
                        responsible_user_id: vm.responsible_user,
                        object_id: vm.selected,
                        comment: vm.comment,
                        reason: vm.reason,
                        count_files: filesCount,
                        parent_id: {{ Request::get('parent_id')?? 0 }},
                        files_ids: vm.files_ids,
                        images_ids: vm.images_ids
                    }).then(function (response) {
                        if (!response.data.message) {
                            window.location = '{{ route('building::mat_acc::operations') }}';
                        } else {
                            materials.is_loading_send = false;
                            materials.is_loading_draft = false;
                            materials.$message({
                                showClose: true,
                                message: response.data.message,
                                type: 'error',
                                duration: 10000
                            });
                        }
                    }).catch(function (request) {
                        var errors = Object.values(request.response.data.errors);
                        materials.is_loading_send = false;
                        materials.is_loading_draft = false;

                        errors.forEach(function (error, key) {
                            if (key == 0) {
                                setTimeout(function () {
                                    materials.$message({
                                        showClose: true,
                                        message: error[0],
                                        type: 'error',
                                        duration: 5000
                                    });
                                }, (key + 1) * 100);
                            }
                        });
                    });
                },
                send_draft() {
                    materials.is_loading_draft = true;
                    filesCount = vm.files_ids.length + vm.images_ids.length;
                    axios.post('{{ route('building::mat_acc::write_off::store') }}', {
                        materials: materials.material_inputs,
                        planned_date_to: vm.operation_date,
                        responsible_user_id: vm.responsible_user,
                        responsible_RP: vm.responsible_RP,
                        object_id: vm.selected,
                        comment: vm.comment,
                        reason: vm.reason, is_draft: true,
                        count_files: filesCount,
                        files_ids: vm.files_ids,
                        images_ids: vm.images_ids
                    }).then(function (response) {
                        if (!response.data.message) {
                            window.location = '{{ route('building::mat_acc::operations') }}';
                        } else {
                            materials.is_loading_draft = false;

                            materials.$message({
                                showClose: true,
                                message: response.data.message,
                                type: 'error',
                                duration: 10000
                            });
                        }
                    }).catch(function (request) {
                        var errors = Object.values(request.response.data.errors);
                        materials.is_loading_draft = false;

                        errors.forEach(function (error, key) {
                            if (key == 0) {
                                setTimeout(function () {
                                    materials.$message({
                                        showClose: true,
                                        message: error[0],
                                        type: 'error',
                                        duration: 5000
                                    });
                                }, (key + 1) * 100);
                            }
                        });
                    });
                },
                inArray: function (array, element) { //this one is used in mass_add_materials
                    var length = array.length;
                    for (var i = 0; i < length; i++) {
                        if (array[i].base_id == element) return true;
                    }
                    return false;
                }
            }
        })


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
                            axios.post('{{ route('building::materials::category::get_need_attrs_values') }}', {
                                attribute_ids: this.attribute_id,
                                q: query
                            }).then(function (response) {
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
