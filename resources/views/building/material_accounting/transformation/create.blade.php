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
                <h5 style="margin-bottom:30px;font-size:19px">Преобразование материала</h5>
                <div class="row">
                    <div class="col-md-4 mt-20__mobile">
                        <label for="">
                            Ответственный сотрудник <span class="star">*</span>
                        </label>
                        <template>
                            <el-select v-model="responsible_user" clearable filterable :remote-method="search_responsible_users" remote name="responsible_user_id" placeholder="Поиск сотрудника">
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
                        <label for="">Место преобразования <span class="star">*</span></label>
                        <template>
                          <el-select v-model="selected" @change="clear_materials" clearable filterable :remote-method="search" remote name="object_id" placeholder="Поиск">
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
                @can('mat_acc_transformation_draft_create')
                    @cannot('mat_acc_transformation_create')
                        <div class="row" style="margin-bottom: 10px">
                            <div class="col-md-12 mt-20__mobile">
                                <label for="">
                                    Руководитель проектов для согласования <span class="star">*</span>
                                </label>
                                <template>
                                    <el-select v-model="responsible_RP" clearable filterable :remote-method="search_RP" remote name="responsible_RP" placeholder="Поиск руководителя проектов">
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
                    <div class="col-md-6 mt-20" >
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
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="card" id="materials_from" v-cloak>
                    <div class="card-body">
                        <h6 style="display:inline-block; width: 50%; margin-bottom:30px" >Материалы</h6>
                            <button data-toggle="modal" data-target="#mass_add_materials_modal" type="button" name="button" class="btn btn-sm btn-info float-right clearfix" style="font-size: 12px;border-radius: 3px;padding: 8px 15px; font-weight:500">
                            Выбрать массово
                        </button>
                        <div class="materials"
                                 v-if="material_inputs.length > 0 ? (new_materials ? new_materials.length > 0 : false) : false">
                                <div
                                    is="material-item-from"
                                    v-for="(material_input, index) in material_inputs"
                                    :key="index + '-' + material_input.id"
                                    :index="index"
                                    :material_index="material_input.id"
                                    :material_id.sync="material_input.material_id"
                                    :material_unit.sync="material_input.material_unit"
                                    :material_count.sync="material_input.material_count"
                                    :used.sync="material_input.used"
                                    :materials.sync="material_input.materials"
                                    :units.sync="material_input.units"
                                    v-on:remove="material_inputs.splice(index, 1)"
                                    :inputs_length="material_inputs.length">
                                </div>
                            </div>
                            <div v-else>
                                @{{ !selected ? 'Выберите место преобразования.' : 'На объекте отсутствуют материалы для преобразования.' }}
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
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h6>После преобразования</h6>
                            <div class="card-header accordion-card-header px-0 mb-3">
                                <h4 class="card-title accordion-card-title">
                                    <a data-target="#base2" href="#" data-toggle="collapse" class="accordion-title">
                                        Поиск материалов
                                        <b class="caret accordion-caret"></b>
                                    </a>
                                </h4>
                            </div>
                            <div class="card-body card-collapse collapse px-0" id="base2">
                                <div class="row" id="add_new_material_via_category_2" v-cloak>
                                    <div class="col-md-7 col-xl-7">
                                        <template>
                                            <el-form label-position="top">
                                                <validation-observer ref="observer" :key="observer_key">
                                                    <label class="d-block">Категория<span class="star">*</span></label>
                                                    <validation-provider rules="required" vid="select-category"
                                                                         ref="select-category" v-slot="v">
                                                        <el-select v-model="category_id" :class="v.classes"
                                                                   @change="getNeedAttributes"
                                                                   placeholder="Выберите категорию материала">
                                                            <el-option
                                                                v-for="item in categories"
                                                                :key="item.id"
                                                                id="select-category"
                                                                :label="item.name"
                                                                :value="item.id">
                                                            </el-option>
                                                        </el-select>
                                                        <div class="error-message">@{{ v.errors[0] }}</div>
                                                    </validation-provider>
                                                    <div class="row"
                                                         v-if="need_attributes.length < 4 && i === 1 || need_attributes.length >= 4 && i % 2 === 1"
                                                         v-for="i in need_attributes.length">
                                                        <template v-for="(attribute, index) in need_attributes">
                                                            <div
                                                                is="material-attribute"
                                                                v-if="need_attributes.length >= 4 && (index === i || index === i - 1)  || need_attributes.length < 4"
                                                                :key="attribute.id"
                                                                :id="'select-' + attribute.id"
                                                                :index="index"
                                                                :attribute_id.sync="attribute.id"
                                                                :attribute_unit.sync="attribute.unit"
                                                                :attribute_name.sync="attribute.name"
                                                                :attribute_value.sync="attribute.value"
                                                                :attribute_is_required.sync="attribute.is_required"
                                                                :category_id.sync="attribute.category_id"
                                                            >
                                                            </div>
                                                        </template>
                                                    </div>
                                                    <div class="row text-center mt-4" style="padding-left: 15px">
                                                        <el-button type="primary" @click="createMaterial">Добавить
                                                        </el-button>
                                                    </div>
                                                </validation-observer>
                                            </el-form>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            <div id="materials_to" v-cloak>
                                <div class="materials">
                                    <div
                                        is="material-item-to"
                                        v-for="(material_input, index) in material_inputs"
                                        :key="index + '-' + material_input.id"
                                        :index="index"
                                        :material_index="material_input.id"
                                        :material_id.sync="material_input.material_id"
                                        :material_unit.sync="material_input.material_unit"
                                        :material_count.sync="material_input.material_count"
                                        :used.sync="material_input.used"
                                        :materials.sync="material_input.materials"
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
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 col-xl-10 ml-auto mr-auto">
            <div class="card strpied-tabled-with-hover">
                <div class="card-body">
                    <div class="row" id="send" v-cloak>
                        <div class="col-md-12 mobile-btn-align">
                            @can('mat_acc_transformation_draft_create')
                                <el-button type="warning" @click="send_draft" :loading="is_loading_draft">Сохранить
                                    черновик
                                </el-button>
                            @endcan
                            @can('mat_acc_transformation_create')
                                <el-button type="primary" @click="check_conflict" :loading="is_loading_send">Создать операцию
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
                    {id: 2, text: 'Переработка'},
                    {id: 3, text: 'Сборка'},
                    {id: 4, text: 'Другое'},
                ]
            },
            mounted: function () {
                axios.post('{{ route('building::mat_acc::get_users') }}', {
                    responsible_user: "{{ Request::get('resp')?? '' }}"
                }).then(response => vm.responsible_users = response.data
                ).then(function () {
                    if (vm.responsible_users.some(el => {
                        return el.code === '{!! Request::get('resp')?? ''  !!}'
                    })) {
                        vm.responsible_user = "{{ Request::get('resp')?? '' }}";
                    }
                });

                axios.post('{{ route('building::mat_acc::get_RPs') }}')
                    .then(response => vm.responsible_RPs = response.data);

                axios.post('{{ route('building::mat_acc::report_card::get_objects') }}', {
                    selected: '{{ Request::get('obj') ?? Session::get('object_id')[0] ?? '' }}',
                }).then(response => vm.options = response.data
                ).then(function () {
                    if (vm.options.some(el => {
                        return el.code === '{{ Request::get('obj') ?? Session::get('object_id')[0] ?? '' }}'
                    })) {
                        vm.selected = '{{ Request::get('obj') ?? Session::get('object_id')[0] ?? '' }}';
                    }
                }).then(function () {
                    if (vm.selected != false) {
                        vm.clear_materials_from();
                    }
                    if (materials_to.material_inputs[0]) {
                        vm.clear_materials_to();
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
                clear_materials() {
                    this.clear_materials_from();
                    this.clear_materials_to();
                },
                clear_materials_from() {
                    mass_add_materials.findAllMaterials(vm.selected);

            axios.post('{{ route('building::mat_acc::report_card::get_materials') }}', {base_id: vm.selected ? vm.selected : -1}).then(function (response) {
                materials_from.new_materials = response.data;
                materials_from.material_inputs = [];
                materials_from.material_inputs.push({
                    id: materials_from.next_mat_id++,
                    material_id: '',
                    material_unit: '',
                    material_count: Number(0),
                    used: false,
                            units: materials_from.units,
                            materials: materials_from.new_materials
                        });
                    });
                },
                clear_materials_to() {
                    materials_to.material_inputs = [];
                    axios.post('{{ route('building::mat_acc::report_card::get_materials') }}', {}).then(function (response) {
                        materials_to.new_materials = response.data;
                        materials_to.material_inputs.push({
                            id: materials_to.next_mat_id++,
                            material_id: '',
                            material_unit: '',
                            material_count: Number(0),
                            units: materials_to.units,
                            materials: materials_to.new_materials
                        });
                    });
                }
            }
        })

        Vue.component('material-item-from', {
            template: '\
      <div class="row">\
        <div class="col-10 mb-10">\
              <label>\
                  Материал <span class="star">*</span>\
              </label>\
              <template>\
                <el-select @change="changeMaterialId" v-model="default_material_id" clearable filterable :remote-method="search" @clear="search(``)" remote size="large" placeholder="Выберите материал">\
                  <el-option\
                    v-for="item in new_materials_filtered"\
                    :key="`${item.id}_${item.used ? 1 : 0}`"\
                    :value="`${item.id}_${item.used ? 1 : 0}`"\
                    :label="item.label">\
                  </el-option>\
                </el-select>\
              </template>\
          </div>\
          <div class="col-2 align-self-end mb-10">\
            <button data-toggle="modal" data-target="#description" @click="getDescription" title="Примечание" type="button" name="button" class="btn btn-sm btn-outline btn-primary mt-10__mobile btn-block mb-0" style="height: 40px;">\
              <i style="font-size:18px;" class="fa fa-info-circle"></i>\
            </button>\
          </div>\
          <div class="col-md-4">\
              <label for="">\
                  Ед. измерения <span class="star">*</span>\
              </label>\
              <template>\
                <el-select @change="changeMaterialUnit" v-model="default_material_unit" placeholder="Ед. измерения">\
                  <el-option\
                    v-for="item in units"\
                    :key="item.id"\
                    :label="item.text"\
                    :value="item.id">\
                  </el-option>\
                </el-select>\
              </template>\
          </div>\
          <div class="col-md-5">\
              <label for="">\
                  Количество <span class="star">*</span>\
              </label>\
              <template>\
                  <el-input-number :min="0" @change="changeMaterialCount" v-model="default_material_count" :precision="3" :step="0.001" :max="10000000" required></el-input-number>\
              </template>\
          </div>\
          <div class="col-md-2">\
            <label for="">\
                Б/У\
            </label>\
            <template>\
                <el-checkbox v-model="default_material_used"\
                   border class="d-block" disabled\
                @canany(['mat_acc_base_move_to_new', 'mat_acc_base_move_to_used']) @change="changeUsageValue" @endcanany @cannot('mat_acc_base_move_to_new') disabled @elsecannot('mat_acc_base_move_to_used') disabled @endcannot
                ></el-checkbox>\
            </template>\
          </div>\
        <div class="col-md-12">\
            <div class="text-center" v-if="inputs_length > 1">\
              <button rel="tooltip" type="button" v-on:click="$emit(\'remove\')" class="btn-remove-big" data-original-title="Удалить">\
                  <i class="fa fa-times remove-stroke"></i>\
              </button>\
          </div>\
        </div>\
      </div>\
    ',
  props: ['material_id', 'material_unit', 'material_count', 'inputs_length', 'material_index', 'materials', 'units', 'index', 'used'],
    computed: {
        new_materials_filtered() {
            return this.materials
                .filter(el => {
                    const count = materials_from.material_inputs.filter(input => input.material_id == el.id && input.used == el.used).length;
                    return count < 1 || String(this.default_material_id).split('_')[0] === el.id && String(this.default_material_id).split('_')[1] == el.used;
                });
        }
    },
    methods: {
      changeMaterialId(value) {
            this.$emit('update:material_id', value.split('_')[0]);
          this.getDescription();
          if (value) {
              let mat = this.materials.filter(input => input.id == value.split('_')[0]
                  && input.used == value.split('_')[1])[0];
              let used = (mat.used === undefined ? false : mat.used);
              this.changeUsageValue(used);
              this.default_material_used = used;

              let unit = (mat.unit === undefined ? null : mat.unit);
              this.autoChangeUnit(unit)
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
                    axios.post('{{ route('building::mat_acc::report_card::get_materials') }}', {q: query, base_id: vm.selected ? vm.selected : -1}).then(function (response) {
                        materials_from.material_inputs[that.index].materials = response.data;
                    })
                }
            },
            data: function () {
                return {
                    default_material_id: materials_from.material_inputs[this.inputs_length - 1].material_id,
                    default_material_unit: materials_from.material_inputs[this.inputs_length - 1].material_unit,
                    default_material_count: materials_from.material_inputs[this.inputs_length - 1].material_count,
                    default_material_used: materials_from.material_inputs[this.inputs_length - 1].used,
                    default_material_description: 'Нет описания',
                    documents: [],
                }
            }
        })

        var materials_from = new Vue({
            el: '#materials_from',
            data: {
                options: [],
                material_unit: '',
                next_mat_id: 1,
                material_inputs: [],
                new_materials: [],
                units: {!! json_encode($units) !!},
                exist_materials: []
            },
            computed: {
                selected() {
                    return vm.selected;
                },
            },
            methods: {
                add_material() {
                    const that = this;

                    axios.post('{{ route('building::mat_acc::report_card::get_materials') }}', {base_id: vm.selected}).then(function (response) {
                        that.new_materials = response.data;

                        that.material_inputs.push({
                            id: that.next_mat_id++,
                            material_id: '',
                            material_unit: '',
                            material_label: '',
                            material_count: '',
                            used: false,
                            units: that.units,
                            materials: that.new_materials
                        });
                    });
                },
                inArray: function (array, element) {
                    var length = array.length;
                    for (var i = 0; i < length; i++) {
                        if (array[i].id == element.id && (element.used === undefined || array[i].used == element.used)) return true;
                    }
                    return false;
                }
            }
        })

        Vue.component('material-item-to', {
            template: '\
      <div class="row">\
            <div class="col-md-12 mb-10">\
              <label>\
                  Материал <span class="star">*</span>\
              </label>\
              <template>\
                <el-select @change="changeMaterialId" v-model="default_material_id" clearable filterable :remote-method="search" @clear="search(``)" remote size="large" placeholder="Выберите материал">\
                  <el-option\
                    v-for="item in materials"\
                    :key="item.id"\
                    :value="item.id"\
                    :label="item.label">\
                  </el-option>\
                </el-select>\
              </template>\
          </div>\
          <div class="col-md-4">\
              <label for="">\
                  Ед. измерения <span class="star">*</span>\
              </label>\
              <template>\
                <el-select @change="changeMaterialUnit" v-model="default_material_unit" placeholder="Ед. измерения">\
                  <el-option\
                    v-for="item in units"\
                    :key="item.id"\
                    :label="item.text"\
                    :value="item.id">\
                  </el-option>\
                </el-select>\
              </template>\
          </div>\
          <div class="col-md-5">\
              <label for="">\
                  Количество <span class="star">*</span>\
              </label>\
              <template>\
                  <el-input-number :min="0" @change="changeMaterialCount" v-model="default_material_count" :precision="3" :step="0.001" :max="10000000" required></el-input-number>\
              </template>\
          </div>\
          <div class="col-md-2">\
            <label for="">\
                Б/У\
            </label>\
            <template>\
                <el-checkbox v-model="default_material_used"\
                    border\
                    class="d-block"\
                @canany(['mat_acc_base_move_to_new', 'mat_acc_base_move_to_used']) @change="changeUsageValue" @endcanany @cannot('mat_acc_base_move_to_new') disabled @elsecannot('mat_acc_base_move_to_used') disabled @endcannot
                ></el-checkbox>\
            </template>\
          </div>\
        <div class="col-md-12">\
            <div class="text-center" v-if="inputs_length > 1">\
              <button rel="tooltip" type="button" v-on:click="$emit(\'remove\')" class="btn-remove-big" data-original-title="Удалить">\
                  <i class="fa fa-times remove-stroke"></i>\
              </button>\
          </div>\
        </div>\
      </div>\
    ',
            props: ['material_id', 'material_unit', 'material_count', 'inputs_length', 'material_index', 'materials', 'units', 'index'],
            methods: {
                changeMaterialId(value) {
                    this.$emit('update:material_id', value);
                    let mat = this.materials.filter(input => input.id == value)[0];

                    let unit = (mat.unit === undefined ? null : mat.unit);
                    this.autoChangeUnit(unit)
                },
                changeMaterialUnit(value) {
                    this.$emit('update:material_unit', value)
                },
                changeMaterialCount(value) {
                    this.$emit('update:material_count', value)
                },
                changeUsageValue(value) {
                    this.$emit('update:used', value)
                },
                autoChangeUnit(unit)
                {
                    this.changeMaterialUnit(unit)
                    this.default_material_unit = unit;
                },
                search(query) {
                    const that = this;
                    axios.post('{{ route('building::mat_acc::report_card::get_materials') }}', {q: query}).then(function (response) {
                        materials_to.material_inputs[that.index].materials = response.data
                    })
                },
            },
            data: function () {
                return {
                    default_material_id: materials_to.material_inputs[this.inputs_length - 1].material_id,
                    default_material_unit: materials_to.material_inputs[this.inputs_length - 1].material_unit,
                    default_material_count: materials_to.material_inputs[this.inputs_length - 1].material_count,
                    default_material_used: materials_to.material_inputs[this.inputs_length - 1].used,
                }
            }
        })

        var materials_to = new Vue({
            el: '#materials_to',
            data: {
                options: [],
                material_unit: '',
                next_mat_id: 2,
                material_inputs: [],
                units: {!! json_encode($units) !!},
                exist_materials: [],
                default_materials: [],
                new_materials: []
            },
            mounted: function () {
                const that = this;

                axios.post('{{ route('building::mat_acc::report_card::get_materials') }}', {base_id: vm.selected}).then(function (response) {
                    that.new_materials = response.data;

                    that.material_inputs.push({
                        id: that.next_mat_id++,
                        material_id: '',
                        material_unit: '',
                        material_count: Number(0),
                        used: false,
                        units: that.units,
                        materials: that.new_materials
                    });
                }).then(function () {
                    if (vm.selected) {
                        vm.clear_materials_to();
                    }
                });
            },
            methods: {
                add_material() {
                    const that = this;

                    axios.post('{{ route('building::mat_acc::report_card::get_materials') }}', {base_id: vm.selected}).then(function (response) {
                        that.new_materials = response.data

                        that.material_inputs.push({
                            id: that.next_mat_id++,
                            material_id: '',
                            material_unit: '',
                            material_label: '',
                            material_count: '',
                            used: false,
                            units: that.units,
                            materials: that.new_materials
                        });

                        axios.post('{{ route('building::mat_acc::report_card::get_materials') }}', {q: ''}).then(function (response) {
                            that.material_inputs[that.material_inputs.length - 1].materials = response.data;
                        })
                    });
                },
                inArray: function (array, element) {
                    var length = array.length;
                    for (var i = 0; i < length; i++) {
                        if (array[i].id == element.id && (element.used === undefined || array[i].used == element.used)) return true;
                    }
                    return false;
                }
            }
        })

        var send = new Vue({
            el: '#send',
            data: {
                is_loading_send: false,
                is_loading_draft: false,
            },
            methods: {
                check_conflict() {
                    send.is_loading_send = true;
                    axios.post('{{ route('building::mat_acc::suggest_solution') }}', {
                        materials: materials_from.material_inputs,
                        planned_date_to: vm.operation_date,
                        object_id: vm.selected,
                    }).then(function (response) {
                        if (response.data) {
                            let message = '';
                            let errMessage = '';
                            if (!response.data.failure && !response.data.transform) {
                                send.send();
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
                                                    send.send();
                                                    return;
                                                }
                                            });
                                    } else {
                                        send.is_loading_send = false;
                                    }
                                } else {
                                    send.is_loading_send = false;
                                }
                            })
                        }
                    });
                },
                send() {
                    send.is_loading_send = true;
                    axios.post('{{ route('building::mat_acc::transformation::store') }}', {
                        materials_from: materials_from.material_inputs,
                        materials_to: materials_to.material_inputs,
                        planned_date_to: vm.operation_date,
                        responsible_user_id: vm.responsible_user,
                        object_id: vm.selected,
                        comment: vm.comment,
                        reason: vm.reason,
                        parent_id: {{ Request::get('parent_id')?? 0 }}
                    }).then(function (response) {
                        if (!response.data.message) {
                            window.location = '{{ route('building::mat_acc::operations') }}';
                        } else {
                            send.is_loading_send = false;

                            send.$message({
                                showClose: true,
                                message: response.data.message,
                                type: 'error',
                                duration: 10000
                            });
                        }
                    }).catch(function (request) {
                        send.is_loading_send = false;

                        var errors = Object.values(request.response.data.errors);

                        errors.forEach(function (error, key) {
                            if (key == 0) {
                                setTimeout(function () {
                                    send.$message({
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
                    send.is_loading_draft = true;
                    axios.post('{{ route('building::mat_acc::transformation::store') }}', {
                        materials_from: materials_from.material_inputs,
                        materials_to: materials_to.material_inputs,
                        planned_date_to: vm.operation_date,
                        responsible_user_id: vm.responsible_user,
                        responsible_RP: vm.responsible_RP,
                        object_id: vm.selected,
                        comment: vm.comment,
                        reason: vm.reason,
                        is_draft: true
                    }).then(function (response) {
                        if (!response.data.message) {
                            window.location = '{{ route('building::mat_acc::operations') }}';
                        } else {
                            send.is_loading_draft = false;
                            send.$message({
                                showClose: true,
                                message: response.data.message,
                                type: 'error',
                                duration: 10000
                            });
                        }
                    }).catch(function (request) {
                        send.is_loading_draft = false;
                        var errors = Object.values(request.response.data.errors);

                        errors.forEach(function (error, key) {
                            if (key == 0) {
                                setTimeout(function () {
                                    send.$message({
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
                      @keydown.native.enter="keyHandler"\
                      :loading="loading"\
                      placeholder="">\
                      <el-option\
                        v-for="(item, index) in mixedParameters"\
                        :key="index"\
                        :label="item.name"\
                        :value="item.id">\
                      </el-option>\
                    </el-select>\
            <div class="error-message" style="padding-left: 15px;">@{{ v.errors[0] }}</div>\
             </validation-provider>\
         </div>\
        ',
            props: ['index', 'attribute_id', 'attribute_unit', 'attribute_name', 'attribute_value', 'attribute_is_required', 'category_id'],
            created() {
                eventHub.$on('addEvent', (e) => {
                    this.default_parameter = '';
                });
            },
            mounted: function () {
                const that = this;

                axios.post('{{ route('building::materials::category::get_need_attrs_values') }}', {attribute_id: this.attribute_id, category_id: this.category_id}).then(function (response) {
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
                keyHandler() {
                    materials_create_2.createMaterial();
                },
                search(query) {
                    const that = this;

                    clearTimeout(this.searchTimeout);
                    this.searchTimeout = setTimeout(() => {
                        if (query !== '') {
                            axios.post('{{ route('building::materials::category::get_need_attrs_values') }}', {attribute_id: this.attribute_id, q: query, category_id: this.category_id }).then(function (response) {
                                that.parameters = response.data;
                            })
                        } else {
                            axios.post('{{ route('building::materials::category::get_need_attrs_values') }}', {attribute_id: this.attribute_id, category_id: this.category_id }).then(function (response) {
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

        let materials_create_2 = new Vue({
            el: '#add_new_material_via_category_2',
            data: {
                categories: {!! $categories !!},
                category_id: '',
                need_attributes: [],
                parameters: [],
                loading: false,
                materials: [],
                attrs_all: [],
                observer_key: 1,
            },
            methods: {
                getNeedAttributes() {
                    let that = this;
                    that.need_attributes = [];
                    axios.post('{{ route('building::materials::category::get_need_attrs') }}', {category_id: that.category_id}).then(function (response) {
                        that.attrs_all = response.data;
                        that.attrs_all = that.attrs_all.reverse();

                        that.attrs_all.forEach(function (attribute) {
                            that.need_attributes.push({
                                id: attribute.id,
                                attr_id: attribute.id,
                                category_id: attribute.category_id,
                                name: attribute.name,
                                unit: attribute.unit,
                                value: '',
                                is_required: attribute.is_required,
                                from: attribute.from,
                                to: attribute.to,
                                step: attribute.step,
                            });
                        });
                    });
                },
                createMaterial() {
                    let that = this;

                    this.$refs.observer.validate().then(success => {
                        if (!success) {
                            return;
                        }
                        axios.post('{{ route('building::mat_acc::attach_material') }}', {
                            attributes: that.need_attributes,
                            category_id: that.category_id
                        }).then(function (response) {
                            let new_material = response.data;
                            let select_materials = materials_to.new_materials;

                            if (!that.inArray(select_materials, {id: new_material.id, used: new_material.used})) {
                                select_materials.push({id: new_material.id, label: new_material.name});
                            }

                            if (Object.values(materials_to.material_inputs)[Object.values(materials_to.material_inputs)["length"] - 1].material_id == '') {
                                materials_to.material_inputs.splice(Object.values(materials_to.material_inputs)["length"] - 1, 1);
                            }

                            let unit_id = materials_to.units.filter(item => item.text == new_material.category.category_unit)[0].id

                            materials_to.material_inputs.push({
                                id: materials_to.next_mat_id++,
                                material_id: new_material.id,
                                material_unit: unit_id,
                                used: false,
                                material_count: '',
                                units: materials_to.units,
                                materials: select_materials
                            });

                            //TODO complete
                            eventHub.$emit('addEvent', '');
                            that.need_attributes.map(el => el.value = '');
                            that.observer_key += 1;
                            that.$nextTick(() => {
                                that.$refs.observer.reset();
                            });
                        }).catch(error => this.handleError(error));
                    });
                },
                handleError(error) {
                    let message = '';
                    let errors = error.response.data.errors;
                    for (let key in errors) {
                        message += errors[key][0] + '<br>';
                    }

                    swal({
                        type: 'error',
                        title: "Ошибка",
                        html: message,
                    });
                },
                search(query) {
                    if (query !== '') {
                        this.loading = true;

                        setTimeout(() => {
                            axios.post('{{ route('building::materials::category::get_need_attrs') }}', {
                                category_id: that.category_id,
                                q: query
                            }).then(function (response) {
                                that.need_attributes = response.data;
                            });

                            this.loading = false;
                        }, 200);
                    } else {
                        axios.post('{{ route('building::materials::category::get_need_attrs') }}', {category_id: that.category_id}).then(function (response) {
                            that.need_attributes = response.data;
                        });
                    }
                },
                inArray: function (array, element) {
                    var length = array.length;
                    for (var i = 0; i < length; i++) {
                        if (array[i].id == element.id && (element.used === undefined || array[i].used == element.used)) return true;
                    }
                    return false;
                },
            }
        })
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
