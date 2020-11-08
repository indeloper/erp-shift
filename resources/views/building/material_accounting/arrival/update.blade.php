@extends('layouts.app')

@section('title', 'Материальный учет')

@section('url', '')

@section('content')

@section('css_top')
<style>
    .el-select {width: 100%}
    .el-date-editor.el-input {width: inherit;}
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
        <form action="{{ route('building::mat_acc::arrival::store') }}" method="post">
            @csrf
            <div class="card strpied-tabled-with-hover">
                <div class="card-body" id="base">
                    <h5 style="margin-bottom:30px;font-size:19px">
                        Поступление материала
                    </h5>
                    <div class="row" style="margin-bottom: 10px">
                        <div class="col-md-6 mt-20__mobile">
                            <label for="">
                                Ответственный сотрудник <span class="star">*</span>
                            </label>
                            <template>
                                <el-select v-model="responsible_user" clearable filterable :remote-method="search_responsible_users" remote name="responsible_user_id" @if($edit_restrict) disabled @endif placeholder="Поиск сотрудника">
                                    <el-option
                                        v-for="item in responsible_users"
                                        :key="item.code"
                                        :label="item.label"
                                        :value="item.code">
                                    </el-option>
                                </el-select>
                            </template>
                        </div>
                        <div class="col-md-6 mt-20__mobile">
                            <label for="">
                                Место поступления <span class="star">*</span>
                            </label>
                            <template>
                                <el-select v-model="selected" clearable filterable :remote-method="search" @if($edit_restrict) disabled @endif remote name="object_id" placeholder="Поиск объекта">
                                    <el-option
                                            v-for="item in options"
                                            :key="item.code"
                                            :label="item.label"
                                            :value="item.code">
                                    </el-option>
                                </el-select>
                            </template>
                        </div>
                    </div>
                    @if($operation->responsible_RP)
                        @can('mat_acc_arrival_draft_create')
                            @cannot('mat_acc_arrival_create')
                                <div class="row" style="margin-bottom: 10px">
                                    <div class="col-md-12 mt-20__mobile">
                                        <label for="">
                                            Руководитель проектов для согласования <span class="star">*</span>
                                        </label>
                                        <template>
                                            <el-select v-model="responsible_RP" disabled clearable filterable name="responsible_RP" @if($edit_restrict) disabled @endif placeholder="Поиск руководителя проектов">
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
                    @endif
                    <div class="row">
                        <div class="col-md-4 mt-20__mobile">
                            <label for="">
                                Поставщик <span class="star">*</span>
                            </label>
                            <template>
                                <el-select v-model="supplier" clearable filterable :remote-method="search_suppliers" @if($edit_restrict) disabled @endif remote name="supplier_id" placeholder="Поиск поставщика">
                                    <el-option
                                        v-for="item in suppliers"
                                        :key="item.code"
                                        :label="item.label"
                                        :value="item.code">
                                    </el-option>
                                </el-select>
                            </template>
                        </div>
                        <div class="col-md-5 mt-20__mobile">
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
                        <div class="col-md-3 mt-20__mobile">
                            <div class="row">
                                <div class="col-md-6 mt-20__mobile">
                                    <label for="">
                                        Дата начала <span class="star">*</span>
                                    </label>
                                    <template>
                                        <div class="block">
                                            <el-date-picker
                                                @if($edit_restrict) disabled @endif
                                                    v-model="operation_date_from"
                                                    type="date"
                                                    placeholder="День"
                                                    format="dd.MM.yyyy"
                                                    value-format="dd.MM.yyyy"
                                                    name="planned_date_from"
                                                    :picker-options="{firstDayOfWeek: 1}"
                                            >
                                            </el-date-picker>
                                        </div>
                                    </template>
                                </div>
                                <div class="col-md-6 mt-20__mobile">
                                    <label for="">
                                        Дата окончания <span class="star">*</span>
                                    </label>
                                    <template>
                                        <div class="block">
                                            <el-date-picker
                                                @if($edit_restrict) disabled @endif
                                                    v-model="operation_date"
                                                    type="date"
                                                    placeholder="День"
                                                    format="dd.MM.yyyy"
                                                    value-format="dd.MM.yyyy"
                                                    name="planned_date_to"
                                                    :picker-options="{firstDayOfWeek: 1}"
                                            >
                                            </el-date-picker>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card strpied-tabled-with-hover">
                <div class="card-header accordion-card-header">
                    <h4 class="card-title accordion-card-title">
                        <a data-target="#base1" href="#" data-toggle="collapse" class="accordion-title">
                            Поиск материалов
                            <b class="caret accordion-caret"></b>
                        </a>
                    </h4>
                </div>
                <div class="card-body card-collapse collapse show" id="base1">
                    <div class="row" id="add_new_material_via_category">
                        <div class="col-md-7 col-xl-7">
                            <template>
                                <el-form label-position="top">
                                    <validation-observer ref="observer" :key="observer_key">
                                        <label class="d-block">Категория<span class="star">*</span></label>
                                        <validation-provider rules="required" vid="select-category"
                                                             ref="select-category" v-slot="v">
                                            <el-select v-model="category_id" :class="v.classes" @change="getNeedAttributes" @if($edit_restrict) disabled @endif placeholder="Выберите категорию материала">
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
                                        <div class="row" v-if="need_attributes.length < 4 && i === 1 || need_attributes.length >= 4 && i % 2 === 1" v-for="i in need_attributes.length">
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
                                            <el-button type="primary" @if($edit_restrict) disabled @endif @click="createMaterial">Добавить</el-button>
                                        </div>
                                    </validation-observer>
                                </el-form>
                            </template>
                        </div>
                    </div>
                    <div>

                    </div>
                </div>
            </div>
            <div class="card" id="materials">
                <div class="card-body">
                    <h6 style="margin-bottom:30px">Материалы</h6>
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
                        :materials.sync="material_input.materials"
                        :used.sync="material_input.used"
                        :units.sync="material_input.units"
                        v-on:remove="material_inputs.splice(index, 1)"
                        :inputs_length="material_inputs.length">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 text-right" style="margin-top:25px">
                            <button type="button" v-on:click="add_material" @if($edit_restrict) disabled @endif class="btn btn-round btn-sm btn-success btn-outline add-material">
                                <i class="fa fa-plus"></i>
                                Добавить материал
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-footer" >
                    <div class="row" style="margin-top:20px">
                        <div class="col-md-6 text-left">
                            @if($operation->status != 5 && $operation->status != 8)
                                <a href="{{ $operation->url }}" class="btn btn-wd btn-default">Назад</a>
                            @else
                                <a href="{{ route('building::mat_acc::operations')  }}" class="btn btn-wd btn-default">Назад</a>
                            @endif
                            @if($operation->status == 5 and ($operation->isAuthor() or Gate::check('mat_acc_arrival_create')))
                                <button type="button" @click="close_operation" @if($edit_restrict) disabled @endif class="btn btn-wd btn-danger">Отмена операции</button>
                            @endif
                        </div>
                        <div class="col-md-6 text-right">
                            @if($operation->status == 5)
                                @if(Gate::check('mat_acc_arrival_create') || Gate::check('mat_acc_arrival_draft_create'))
                                    <el-button type="primary" v-on:click="send" @if($edit_restrict) disabled @endif :loading="is_loading_draft">@if(Gate::check('mat_acc_arrival_create'))Сохранить @elseif(Gate::check('mat_acc_arrival_draft_create'))Обновить черновик @endif</el-button>
                                @endif
                            @else
                                @can('mat_acc_arrival_create')
                                    <el-button type="primary" v-on:click="send" @if($edit_restrict) disabled @endif :loading="is_loading_send">Сохранить</el-button>
                                @endcan
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('js_footer')
    <script type="text/javascript">
        Vue.component('validation-provider', VeeValidate.ValidationProvider);
        Vue.component('validation-observer', VeeValidate.ValidationObserver);
    </script>

    <script>

    var vm = new Vue({
        el: '#base',
        data: {
            responsible_users: [],
            responsible_RPs: [],
            suppliers: [],
            contracts: [],
            contract: '{{ $operation->contract_id }}',
            options: [],
            responsible_user: "{{ $operation->responsible_user->user->id }}",
            responsible_RP: "{{ $operation->responsible_RP ?? 'old_draft' }}",
            supplier: "{{ $operation->supplier_id }}",
            selected: "{{ $operation->object_id_to }}",
            operation_date: "{!! \Carbon\Carbon::parse($operation->planned_date_to)->format('d.m.Y') !!}",
            operation_date_from: "{!! \Carbon\Carbon::parse($operation->planned_date_from)->format('d.m.Y') !!}",
        },
        watch: {
            selected:
                function (val) {
                    if (['76', '192'].includes(val)) {
                        vm.contracts = [{label: "На базе нет договоров, поле не обязательно."}];
                    } else {
                        vm.search_contracts();
                    }
                    vm.contract = null;
                }
        },
        created: function () {
            axios.post('{{ route('building::mat_acc::get_users') }}', {responsible_user_id: "{{ $operation->responsible_user->user->id }}"}).then(response => vm.responsible_users = response.data);
            axios.post('{{ route('building::mat_acc::get_RPs') }}', {responsible_RP: "{{ $operation->responsible_RP }}"}).then(response => vm.responsible_RPs = response.data);
            axios.post('{{ route('building::mat_acc::report_card::get_suppliers') }}', {supplier_id: "{{ $operation->supplier_id }}"}).then(response => vm.suppliers = response.data);
            axios.post('{{ route('building::mat_acc::report_card::get_objects') }}', {object_id: "{{ $operation->object_id_to }}"}).then(response => vm.options = response.data)
            axios.post('{{ route('contracts::get_contracts') }}', {object_id: this.selected, from_mat_acc: true}).then(response => vm.contracts = response.data);
        },
        methods: {
            search_responsible_users(query) {
                if (query !== '') {
                    setTimeout(() => {
                        axios.post('{{ route('building::mat_acc::get_users') }}', {q: query}).then(function (response) {
                            vm.responsible_users = response.data;
                        })
                    }, 200);
                } else {
                    axios.post('{{ route('building::mat_acc::get_users') }}', {responsible_user_id: "{{ $operation->responsible_user->user->id }}"}).then(response => vm.responsible_users = response.data);
                }
            },
            search_contracts(query) {
                if (query !== '') {
                    setTimeout(() => {
                        axios.post('{{ route('contracts::get_contracts') }}', {q: query, object_id: this.selected, from_mat_acc: true}).then(function (response) {
                            if (response.data.length > 0) {
                                vm.contracts = response.data;
                            } else {
                                vm.contracts = [{label: "На данном объекте отсутствуют договоры"}];
                            }
                        })
                    }, 200);
                } else {
                    axios.post('{{ route('contracts::get_contracts') }}', {object_id: this.selected, from_mat_acc: true}).then(response => vm.contracts = response.data);
                }
            },
            search_suppliers(query) {
                if (query !== '') {
                    setTimeout(() => {
                        axios.post('{{ route('building::mat_acc::report_card::get_suppliers') }}', {q: query}).then(function (response) {
                            vm.suppliers = response.data;
                        })
                    }, 200);
                } else {
                    axios.post('{{ route('building::mat_acc::report_card::get_suppliers') }}', {supplier_id: "{{ $operation->supplier_id }}"}).then(response => vm.suppliers = response.data);
                }
            },
            search(query) {
                if (query !== '') {
                  setTimeout(() => {
                    axios.post('{{ route('building::mat_acc::report_card::get_objects') }}', {q: query}).then(function (response) {
                        vm.options = response.data;
                    })
                  }, 200);
                } else {
                    axios.post('{{ route('building::mat_acc::report_card::get_objects') }}', {object_id: "{{ $operation->object_id_to }}"}).then(response => vm.options = response.data)
              }
            },
        }
    });

    Vue.component('material-item', {
      template: '\
          <div class="row" style="margin-top: 7px;">\
              <div class="col-md-5">\
                  <label :class="[material_index !== 1 ? \'show-mobile-label mt-10__mobile\' : \'mt-10__mobile\']">\
                      Материал <span class="star">*</span>\
                  </label>\
                  <template>\
                    <el-select @change="changeMaterialId" ref="usernameInput" v-model="default_material_id" @if($edit_restrict) disabled @endif clearable @clear="search(\'\')" filterable :remote-method="search" remote size="large" placeholder="Выберите материал">\
                      <el-option\
                        v-for="item in materials"\
                        :label="item.label"\
                        :key="item.id"\
                        :value="item.id">\
                      </el-option>\
                    </el-select>\
                  </template>\
              </div>\
              <div class="col-md-1 align-self-end">\
               <button data-toggle="modal" data-target="#description" @click="getDescription" title="Примечание" type="button" name="button" class="btn btn-sm btn-primary btn-outline mt-10__mobile btn-block" style="height: 40px;">\
                  <i style="font-size:18px;" class="fa fa-info-circle"></i>\
                </button>\
                </div>\
              <div :class="[inputs_length === 1 ? \'col-md-3\' : \'col-md-2\']">\
                  <label for="" :class="[material_index !== 1 ? \'show-mobile-label mt-10__mobile\' : \'mt-10__mobile\']">\
                      Ед. изм. <span class="star">*</span>\
                  </label>\
                  <template>\
                    <el-select @change="changeMaterialUnit" v-model="default_material_unit" @if($edit_restrict) disabled @endif placeholder="Ед. измерения">\
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
                      <el-input-number @change="changeMaterialCount" :min="0" v-model="default_material_count" @if($edit_restrict) disabled @endif :precision="3" :step="0.001" :max="10000000" required></el-input-number>\
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
                    ></el-checkbox> \
                </template>\
              </div>\
              <div class="col-md-1 text-center" v-if="inputs_length > 1">\
                <button rel="tooltip" type="button" v-on:click="$emit(\'remove\')"  class="btn-remove-mobile" data-original-title="Удалить">\
                    <i style="font-size:18px;" class="fa fa-times" :class="[material_index !== 1 ? \'fa fa-times remove-stroke-index\' : \'fa fa-times remove-stroke\']"></i>\
                </button>\
              </div>\
          </div>\
        ',
      props: ['material_id', 'material_unit', 'material_count', 'inputs_length', 'used', 'material_index', 'materials', 'units'],
      methods: {
          changeMaterialId(value) {
              this.$emit('update:material_id', value);
              this.getDescription();
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
          getDescription() {
                let that = this;

                if (String(that.default_material_id)) {
                    axios.post('{{ route('building::mat_acc::get_material_category_description') }}', {id: that.default_material_id}).then(function (response) {
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
          autoChangeUnit(unit)
          {
              this.changeMaterialUnit(unit)
              this.default_material_unit = unit;
          },
          search(query) {
              const that = this;

              if (query !== '') {
                setTimeout(() => {
                  axios.post('{{ route('building::mat_acc::report_card::get_materials') }}', {q: query}).then(function (response) {
                      materials.material_inputs[that.inputs_length - 1].materials = response.data
                  })
              }, 1000);
              } else {
                  materials.material_inputs[that.inputs_length - 1].materials = []
              }
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
    });

    var materials = new Vue({
        el: '#materials',
        data: {
            options: [],
            selected: '',
            material_unit: '',
            next_mat_id: 1,
            material_inputs: [],
            units: {!! json_encode($operation->materials()->getModel()::$main_units) !!},
            exist_materials: {!! $operation->materials->where('type', 3) !!},
            is_loading_send: false,
        },
        mounted: function () {
            const that = this;

            axios.post('{{ route('building::mat_acc::report_card::get_materials') }}', ).then(function (response) {
                that.new_materials = response.data;

                Object.keys(that.exist_materials).map(function(key) {
                    if (!that.inArray(that.new_materials, {id: that.exist_materials[key].manual_material_id})) {
                        that.new_materials.push({id: that.exist_materials[key].manual_material_id, label: that.exist_materials[key].manual.name})
                    }

                    setTimeout(() => {
                        that.material_inputs.push({
                            id: that.next_mat_id++,
                            material_id: that.exist_materials[key].manual_material_id,
                            material_unit: that.exist_materials[key].unit,
                            material_count: Number(that.exist_materials[key].count),
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

                axios.post('{{ route('building::mat_acc::report_card::get_materials') }}').then(function (response) {
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
                    if(array[i].id == element.id) return true;
                }
                return false;
            },
            send() {
                materials.is_loading_send = true;
                axios.post('{{ route('building::mat_acc::arrival::update', $operation->id) }}', {
                    materials: materials.material_inputs,
                    planned_date_from: vm.operation_date_from,
                    planned_date_to: vm.operation_date,
                    responsible_user_id: vm.responsible_user,
                    contract_id: vm.contract,
                    responsible_RP: vm.responsible_RP,
                    supplier_id: vm.supplier,
                    object_id: vm.selected
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
            close_operation: function () {
                materials.$confirm('Это действие приведет к отмене операции.', 'Внимание', {
                    confirmButtonText: 'Подтвердить',
                    cancelButtonText: 'Назад',
                    type: 'warning'
                }).then(() => {
                    axios.post('{{ route('building::mat_acc::close_operation', $operation->id) }}').then(function (response) {
                        if (response.data) {
                            materials.$message({
                                type: 'success',
                                message: 'Операция отменена'
                            });

                            window.location = '{{ route('building::mat_acc::operations') }}';
                        } else {
                            materials.$message({
                              message: 'Нельзя отменить операцию.',
                              type: 'error'
                            });
                        }
                    })
                });
            }
        }
    })

    </script>
    <script type="text/javascript">
        $("body").on('DOMSubtreeModified', ".materials", function() {
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
                      @keydown.native.enter="keyHandler"\
                      :remote-method="search"\
                      :loading="loading"\
                      placeholder="">\
                      <el-option\
                        v-for="item in mixedParameters"\
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
            keyHandler() {
                materials_create.createMaterial();
            },
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
            value () {
                return this.default_parameter;
            }
        },
    });


    let materials_create = new Vue({
        el: '#add_new_material_via_category',
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
                axios.post('{{ route('building::materials::category::get_need_attrs') }}', { category_id: that.category_id }).then(function (response) {
                    that.attrs_all = response.data;
                    that.attrs_all = that.attrs_all.reverse();

                    that.attrs_all.forEach(function(attribute) {
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
                    axios.post('{{ route('building::mat_acc::attach_material') }}', { attributes: that.need_attributes, category_id: that.category_id }).then(function (response) {
                        let new_material = response.data;
                        let select_materials = materials.new_materials;

                        if (!that.inArray(select_materials, {id: new_material.id})) {
                            select_materials.push({id: new_material.id, label: new_material.name});
                        }

                        if (Object.values(materials.material_inputs)[Object.values(materials.material_inputs)["length"] - 1].material_id == '') {
                            materials.material_inputs.splice(Object.values(materials.material_inputs)["length"] - 1, 1);
                        }

                        let unit_id = materials.units.filter(item => item.text == new_material.category.category_unit)[0].id

                        materials.material_inputs.push({
                            id: materials.next_mat_id++,
                            material_id: new_material.id,
                            material_unit: unit_id,
                            material_count: '',
                            used: false,
                            units: materials.units,
                            materials: select_materials
                        });

                        //TODO complete
                        eventHub.$emit('addEvent', '');
                        that.need_attributes.map(el => el.value = '');
                        that.observer_key += 1;
                        that.$nextTick(() => {
                            that.$refs.observer.reset();
                        });
                    })
                });
            },
            search(query) {
               if (query !== '') {
                 this.loading = true;

                 setTimeout(() => {
                     axios.post('{{ route('building::materials::category::get_need_attrs') }}', { category_id: that.category_id, q: query }).then(function (response) {
                         that.need_attributes = response.data;
                     });

                     this.loading = false;
                 }, 200);
               } else {
                   axios.post('{{ route('building::materials::category::get_need_attrs') }}', { category_id: that.category_id }).then(function (response) {
                       that.need_attributes = response.data;
                   });
               }
           },
           inArray: function(array, element) {
               var length = array.length;
               for(var i = 0; i < length; i++) {
                   if(array[i].id == element.id) return true;
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
