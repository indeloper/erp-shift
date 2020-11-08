@extends('layouts.app')

@section('title', 'Материальный учет')

@section('url', '')

@section('content')

@section('css_top')
<style>
    .el-select {width: 100%}
    .el-date-editor.el-input {width: 100%;}
    .margin-top-15 {
        margin-top: 15px;
    }
    .el-input-number {
        width: inherit;
    }
    .card-header.accordion-card-header{
        border-bottom: 1px solid
        #DDDDDD !important;
    }
    .card-title.accordion-card-title{
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
        border-top: 4px solid\9;
        border-right: 4px solid
        transparent;
        border-left: 4px solid
        transparent;
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
                <h5 style="margin-bottom:30px;font-size:19px">Перемещение материала</h5>
                <div class="row">
                    <div class="col-md-4 mt-20__mobile">
                        <label for="">
                            Ответственный сотрудник <span class="star">*</span>
                        </label>
                        <template>
                            <el-select v-model="from_responsible_user" clearable filterable :remote-method="search_from_responsible_users" remote name="from_responsible_user_id" placeholder="Поиск сотрудника">
                                <el-option
                                    v-for="item in from_responsible_users"
                                    :key="item.code"
                                    :label="item.label"
                                    :value="item.code">
                                </el-option>
                            </el-select>
                        </template>
                    </div>
                    <div class="col-md-5">
                        <label for="">
                            Место отправки <span class="star">*</span>
                        </label>
                        <template>
                          <el-select v-model="object_id_from" @change="clean_materials" clearable filterable :remote-method="search_from" remote placeholder="Поиск">
                            <el-option
                              v-for="item in options_from"
                              :key="item.code"
                              :label="item.label"
                              :value="item.code">
                            </el-option>
                          </el-select>
                        </template>
                    </div>
                    <div class="col-md-3">
                        <label for="">
                            Дата отправки <span class="star">*</span>
                        </label>
                        <template>
                          <div class="block">
                            <el-date-picker
                                @change="clean_materials"
                              v-model="operation_date_from"
                              type="date"
                              format="dd.MM.yyyy"
                              value-format="dd.MM.yyyy"
                              placeholder="Дата начала"
                              :picker-options="dateFromOptions"
                              >
                            </el-date-picker>
                          </div>
                        </template>
                    </div>
                </div>
                <div class="row" style="margin-top:15px">
                    <div class="col-md-4 mt-20__mobile">
                        <label for="">
                            Ответственный сотрудник <span class="star">*</span>
                        </label>
                        <template>
                            <el-select v-model="to_responsible_user" clearable filterable :remote-method="search_to_responsible_users" remote name="to_responsible_user_id" placeholder="Поиск сотрудника">
                                <el-option
                                    v-for="item in to_responsible_users"
                                    :key="item.code"
                                    :label="item.label"
                                    :value="item.code">
                                </el-option>
                            </el-select>
                        </template>
                    </div>
                    <div class="col-md-5">
                        <label for="">
                            Место получения <span class="star">*</span>
                        </label>
                        <template>
                          <el-select v-model="object_id_to" clearable filterable :remote-method="search_to" remote name="object_id_to" placeholder="Поиск">
                            <el-option
                              v-for="item in options_to"
                              :key="item.code"
                              :label="item.label"
                              :value="item.code">
                            </el-option>
                          </el-select>
                        </template>
                    </div>
                    <div class="col-md-3" >
                        <label for="">
                            Дата получения <span class="star">*</span>
                        </label>
                        <template>
                          <div class="block">
                            <el-date-picker
                              v-model="operation_date_to"
                              type="date"
                              format="dd.MM.yyyy"
                              value-format="dd.MM.yyyy"
                              placeholder="Дата начала"
                              :picker-options="dateToOptions"
                              >
                            </el-date-picker>
                          </div>
                        </template>
                    </div>
                </div>
                <div class="row" style="margin-top:15px">
                    <div class="col-md-4 mt-20__mobile">
                        <label for="">
                            Договор <span class="star">*</span>
                            <el-tooltip placement="right" effect="light">
                                <div slot="content">Договор, по которому ведутся работы.<br/>Из него будет браться дата сдачи КС, сертификаты соответствия надо будет загрузить до этой даты.<br/>Для дополнительной фильтрации договоров нужно выбрать объект</div>
                                <i class="fa fa-info-circle" style="color: #447DF7">
                                </i>
                            </el-tooltip>
                        </label>
                        <template>
                            <el-select v-model="contract" clearable filterable :remote-method="search_contracts" remote name="contract_id" placeholder="Поиск договора">
                                <el-option
                                    v-for="item in contracts"
                                    :key="item.code"
                                    :label="item.label"
                                    :value="item.code">
                                </el-option>
                            </el-select>
                        </template>
                    </div>
                </div>
                @can('mat_acc_moving_draft_create')
                    @cannot('mat_acc_moving_create')
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
            </div>
        </div>
        <div class="card" id="materials" v-cloak>
            <div class="card-body">
                <h6 style="display:inline-block; width: 50%; margin-bottom:30px" >Материалы</h6>
                <button data-toggle="modal" data-target="#mass_add_materials_modal" type="button" name="button" class="btn btn-sm btn-info float-right clearfix" style="font-size: 12px;border-radius: 3px;padding: 8px 15px; font-weight:500">
                    Выбрать массово
                </button>
                <div class="materials" v-if="material_inputs.length > 0 ? (new_materials ? new_materials.length > 0 : false) : false">
                    <div
                        is="material-item"
                        v-for="(material_input, index) in material_inputs"
                        :key="index + '-' + material_input.id"
                        :index="index"
                        :material_index="material_input.id"
                        :material_id.sync="material_input.material_id"
                        :material_unit.sync="material_input.material_unit"
                        :material_count.sync="material_input.material_count"
                        :materials.sync="material_input.materials"
                        :used.sync="material_input.used"
                        :units.sync="material_input.units"
                        v-on:remove="material_inputs.splice(index, 1)"
                        :inputs_length="material_inputs.length">
                    </div>
                </div>
                <div v-else>
                    @{{ !object_id_from ? 'Выберите место отправки.' : 'На объекте отсутствуют материалы для перемещения.' }}
                </div>
                <div class="row" v-if="material_inputs.length > 0 ? (new_materials ? new_materials.length > 0 : false) : false">
                    <div class="col-md-12 text-right" style="margin-top:25px">
                        <button type="button" v-on:click="add_material" class="btn btn-round btn-sm btn-success btn-outline add-material">
                            <i class="fa fa-plus"></i>
                            Добавить материал
                        </button>
                    </div>
                </div>

                <div class="row"  style="margin-top:20px">
                    <div class="col-md-12 mobile-btn-align">
                        @can('mat_acc_moving_draft_create')
                            <el-button type="warning" @click="send_draft" :loading="is_loading_draft">Сохранить черновик</el-button>
                        @endcan
                        @can('mat_acc_moving_create')
                            <el-button type="primary" @click="check_conflict" name="button" :loading="is_loading_send">Создать</el-button>
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

var vm = new Vue({
    el: '#base',
    data: {
        from_responsible_users: [],
        to_responsible_users: [],
        responsible_RPs: [],
        options_from: [],
        options_to: [],
        contracts: [],
        contract: '',
        from_responsible_user: '',
        to_responsible_user: '',
        responsible_RP: '',
        object_id_from: '',
        object_id_to: '',
        operation_date_from: '',
        operation_date_to: '',
        dateFromOptions: {
            firstDayOfWeek: 1,
            // disabledDate: date => date < moment().startOf('date') || (vm.operation_date_to ? (date > moment(vm.operation_date_to, "DD.MM.YYYY")) : false),
        },
        dateToOptions: {
            firstDayOfWeek: 1,
            // disabledDate: date => date < moment().startOf('date') || (vm.operation_date_from ? (date < moment(vm.operation_date_from, "DD.MM.YYYY")) : false),
        }
    },
    watch: {
        object_id_to:
            function (val) {
                if (['76', '192'].includes(val)) {
                    vm.contracts = [{label: "На базе нет договоров, поле не обязательно."}];
                } else {
                    vm.search_contracts();
                }
                vm.contract = null;
            }
    },
    created() { this.search_contracts(); },
    mounted: function () {
        axios.post('{{ route('building::mat_acc::get_users') }}', {
            from_responsible_user: "{{ Request::get('from_resp')?? '' }}",
            to_responsible_user: "{{ Request::get('to_resp')?? '' }}"
        }).then(function (response) {
            vm.from_responsible_users = response.data;
            vm.to_responsible_users = response.data;
        }).then(function () {
            if (vm.from_responsible_users.some(el => { return el.code === '{!! Request::get('from_resp')?? ''  !!}'})) {
                vm.from_responsible_user = "{{ Request::get('from_resp')?? '' }}";
            }
            if (vm.to_responsible_users.some(el => { return el.code === '{!! Request::get('to_resp') ?? ''  !!}'})) {
                vm.to_responsible_user = "{{ Request::get('to_resp')?? '' }}";
            }
        });

        axios.post('{{ route('building::mat_acc::get_RPs') }}')
            .then(response => vm.responsible_RPs = response.data);

        axios.post('{{ route('building::mat_acc::report_card::get_objects') }}', {
            object_id: '{{ Request::get('obj') ?? Session::get('object_id')[0] ?? '' }}',
            one_more_object_id: '{{ Request::get('to_obj')?? '' }}',
        }).then(function (response) {
            vm.options_from = response.data;
            vm.options_to = response.data;
        }).then(function () {
            if (vm.options_from.some(el => { return el.code === '{{ Request::get('obj') ?? Session::get('object_id')[0] ?? '' }}'})) {
                vm.object_id_from = '{{ Request::get('obj') ?? Session::get('object_id')[0] ?? '' }}';
            }
            if (vm.options_to.some(el => { return el.code === '{!! Request::get('to_obj')?? ''   !!}'})) {
                vm.object_id_to = "{{ Request::get('to_obj')?? '' }}";
            }
        }).then(function() {
            if (vm.object_id_from != false) {
                vm.clean_materials();
            }
        });
    },
    methods: {
        search_from(query) {
            if (query !== '') {
              setTimeout(() => {
                axios.post('{{ route('building::mat_acc::report_card::get_objects') }}', {q: query}).then(function (response) {
                    vm.options_from = response.data;
                })
              }, 200);
            } else {
                axios.post('{{ route('building::mat_acc::report_card::get_objects') }}').then(function (response) {
                    vm.options_from = response.data;
                });
          }
        },
        search_to(query) {
            if (query !== '') {
                setTimeout(() => {
                    axios.post('{{ route('building::mat_acc::report_card::get_objects') }}', {q: query}).then(function (response) {
                        vm.options_to = response.data;
                    })
                }, 200);
              } else {
                axios.post('{{ route('building::mat_acc::report_card::get_objects') }}').then(function (response) {
                    vm.options_to = response.data;
                });
            }
        },
        search_contracts(query) {
            if (query !== '') {
                setTimeout(() => {
                    axios.post('{{ route('contracts::get_contracts') }}', {q: query, object_id: this.object_id_to, from_mat_acc: true}).then(function (response) {
                        if (response.data.length > 0) {
                            vm.contracts = response.data;
                        } else {
                            vm.contracts = [{label: "На данном объекте отсутствуют договоры"}];
                        }
                    })
                }, 200);
            } else {
                axios.post('{{ route('contracts::get_contracts') }}', {object_id: this.object_id_to, from_mat_acc: true}).then(response => vm.contracts = response.data);
            }
        },
        clean_materials() {
            mass_add_materials.findAllMaterials(vm.object_id_from);


            axios.post('{{ route('building::mat_acc::report_card::get_materials') }}', {base_id: vm.object_id_from ? vm.object_id_from : -1, date: vm.operation_date_from }).then(function (response) {

                materials.new_materials = response.data;
                materials.material_inputs = [];
                materials.material_inputs.push({
                    id: materials.next_mat_id++,
                    material_id: '',
                    material_unit: '',
                    material_count: Number(0),
                    units: materials.units,
                    materials: materials.new_materials
                });
            });
        },
        search_from_responsible_users(query) {
            if (query !== '') {
                setTimeout(() => {
                    axios.post('{{ route('building::mat_acc::get_users') }}', {q: query}).then(function (response) {
                        vm.from_responsible_users = response.data;
                    })
                }, 200);
            } else {
                axios.post('{{ route('building::mat_acc::get_users') }}').then(function (response) {
                    vm.from_responsible_users = response.data;
                });
            }
        },
        search_to_responsible_users(query) {
            if (query !== '') {
                setTimeout(() => {
                    axios.post('{{ route('building::mat_acc::get_users') }}', {q: query}).then(function (response) {
                        vm.to_responsible_users = response.data;
                    })
                }, 200);
            } else {
                axios.post('{{ route('building::mat_acc::get_users') }}').then(function (response) {
                    vm.to_responsible_users = response.data;
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
    }
})

Vue.component('material-item', {
  template: '\
      <div class="row mb-10">\
          <div class="col-md-5">\
              <label :class="[material_index !== 1 ? \'show-mobile-label mt-10__mobile\' : \'mt-10__mobile\']">\
                  Материал <span class="star">*</span>\
              </label>\
              <template>\
                <el-select @change="changeMaterialId" ref="usernameInput" v-model="default_material_id" clearable filterable @clear="search(``)" :remote-method="search" remote size="large" placeholder="Выберите материал">\
                  <el-option\
                    v-for="item in new_materials_filtered"\
                    :label="item.label"\
                    :key="`${item.id}_${item.used ? 1 : 0}`"\
                    :value="`${item.id}_${item.used ? 1 : 0}`">\
                  </el-option>\
                </el-select>\
              </template>\
          </div>\
          <div class="col-md-1 align-self-end">\
             <button data-toggle="modal" data-target="#description" @click="getDescription" title="Примечание" type="button" name="button" class="btn btn-sm btn-primary btn-outline mt-10__mobile btn-block" style="height: 40px;">\
                <i style="font-size:18px;" class="fa fa-info-circle"></i>\
             </button>\
          </div>\
          <div :class="[inputs_length === 1 ? \'col-md-2\' : \'col-md-2\']">\
              <label :class="[material_index !== 1 ? \'show-mobile-label mt-10__mobile\' : \'mt-10__mobile\']">\
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
              <label :class="[material_index !== 1 ? \'show-mobile-label mt-10__mobile\' : \'mt-10__mobile\']">\
                  Количество <span class="star">*</span>\
              </label>\
              <template>\
                  <el-input-number @change="changeMaterialCount" :min="0" v-model="default_material_count" :precision="3" :step="0.001" :max="10000000" required></el-input-number>\
              </template>\
          </div>\
          <div class="col-md-1">\
                    <label for="" :class="[material_index !== 1 ? \'show-mobile-label mt-10__mobile\' : \'mt-10__mobile\']">\
                        Б/У\
                    </label>\
                    <template>\
                        <el-checkbox v-model="default_material_used" \
                        border class="d-block"\
                        @canany(['mat_acc_base_move_to_new', 'mat_acc_base_move_to_used']) @change="changeUsageValue" @endcanany @cannot('mat_acc_base_move_to_new') disabled @elsecannot('mat_acc_base_move_to_used') disabled @endcannot
      ></el-checkbox>\
    </template>\
        </div>\
        <div class="col-md-1 text-center" v-if="inputs_length > 1">\
            <button rel="tooltip" type="button" v-on:click="$emit(\'remove\')" class="btn-remove-mobile" data-original-title="Удалить">\
                <i style="font-size:18px;" :class="[material_index !== 1 ? \'fa fa-times remove-stroke-index\' : \'fa fa-times remove-stroke\']"></i>\
            </button>\
    </div>\
</div>\
',
  props: ['material_id', 'material_unit', 'material_count', 'inputs_length', 'material_index', 'materials', 'units', 'index', 'used'],
    computed: {
        new_materials_filtered() {
            return this.materials
                .filter(el => {
                    const count = materials.material_inputs.filter(input => input.material_id == el.id && input.used == el.used).length;
                    return count < 1 || String(this.default_material_id).split('_')[0] == el.id && String(this.default_material_id).split('_')[1] == el.used;
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
          this.$emit('update:material_unit', value)
      },
      changeMaterialCount(value) {
          this.$emit('update:material_count', value)
      },
        changeUsageValue(value) {
            this.$emit('update:used', value)
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
            this.changeMaterialUnit(unit);
            this.default_material_unit = unit;
        },
      search(query) {
          const that = this;
          axios.post('{{ route('building::mat_acc::report_card::get_materials') }}', {q: query, base_id: vm.object_id_from, date: vm.operation_date_from }).then(function (response) {
              materials.material_inputs[that.index].materials = response.data
          })
      }
  },
  data: function () {
      return {
          default_material_id: materials.material_inputs[this.inputs_length - 1].material_id,
          default_material_unit: materials.material_inputs[this.inputs_length - 1].material_unit,
          default_material_count: materials.material_inputs[this.inputs_length - 1].material_count,
          default_material_used: materials.material_inputs[this.inputs_length - 1].used,
          default_material_description: 'Нет описания',
          documents: [],
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
        new_materials: [],
        material_inputs: [],
        units: {!! json_encode($units) !!},
        exist_materials: [],
        is_loading_send: false,
        is_loading_draft: false,
    },
    computed: {
        object_id_from() {
            return vm.object_id_from;
        },
    },
    methods: {
        add_material() {
            const that = this;

            axios.post('{{ route('building::mat_acc::report_card::get_materials') }}', {base_id: vm.object_id_from, date: vm.operation_date_from }).then(function (response) {
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
            });
        },
        inArray: function(array, element) {
            var length = array.length;
            for(var i = 0; i < length; i++) {
                if (array[i].id == element.id && (element.used === undefined || array[i].used == element.used)) return true;
            }
            return false;
        },
        check_conflict() {
            materials.is_loading_send = true;
            axios.post('{{ route('building::mat_acc::suggest_solution') }}', {
                materials: materials.material_inputs,
                planned_date_to: vm.operation_date_from,
                object_id: vm.object_id_from,
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
            materials.is_loading_send = true;
            axios.post('{{ route('building::mat_acc::moving::store') }}',
            {
                materials: materials.material_inputs,
                planned_date_from: vm.operation_date_from,
                planned_date_to: vm.operation_date_to,
                from_responsible_user: vm.from_responsible_user,
                to_responsible_user: vm.to_responsible_user,
                contract_id: vm.contract,
                object_id_from: vm.object_id_from,
                object_id_to: vm.object_id_to,
                parent_id: {{ Request::get('parent_id')?? 0 }}

            }).then(function (response) {
                if (!response.data.message) {
                    window.location = '{{ route('building::mat_acc::operations') }}';
                } else {
                    materials.is_loading_send = false;
                    materials.$message({
                      showClose: true,
                      message: response.data.message,
                      type: 'error',
                      duration: 10000
                    });
                }
            }).catch(function (request) {
                materials.is_loading_send = false;
                var errors = Object.values(request.response.data.errors);

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
            axios.post('{{ route('building::mat_acc::moving::store') }}',
            {
                materials: materials.material_inputs,
                planned_date_from: vm.operation_date_from,
                planned_date_to: vm.operation_date_to,
                from_responsible_user: vm.from_responsible_user,
                to_responsible_user: vm.to_responsible_user,
                responsible_RP: vm.responsible_RP,
                contract_id: vm.contract,
                object_id_from: vm.object_id_from,
                object_id_to: vm.object_id_to,
                is_draft: true

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
                materials.is_loading_draft = false;
                var errors = Object.values(request.response.data.errors);

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
    <script type="text/javascript">
        $("body").on('DOMSubtreeModified', ".materials", function() {
            $('.materials').children('.row:first-child').find('label').removeClass('show-mobile-label');
            $('.materials').children('.row:first-child').find('.btn-remove-mobile').children().removeClass('remove-stroke-index').addClass('remove-stroke');
        });
    </script>


@endsection
