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
                <h5 style="margin-bottom:30px;font-size:19px">Преобразование материала</h5>
                <div class="row">
                    <div class="col-md-4 mt-20__mobile">
                        <label for="">
                            Ответственный сотрудник <span class="star">*</span>
                        </label>
                        <template>
                            <el-select v-model="responsible_user" clearable filterable :remote-method="search_responsible_users" @if($edit_restrict) disabled @endif remote name="responsible_user_id" placeholder="Поиск сотрудника">
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
                            <el-select v-model="selected" @change="clean_materials" ref="search_from" clearable filterable :remote-method="search"  @if($edit_restrict) disabled @endif remote name="object_id" placeholder="Поиск">
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
                                @if($edit_restrict) disabled @endif
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
                @if($operation->responsible_RP)
                    @can('mat_acc_transformation_draft_create')
                        @cannot('mat_acc_transformation_create')
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
                    <div class="col-md-6 mt-20" >
                        <label for="">
                            Основание <span class="star">*</span>
                        </label>
                        <template>
                          <el-select v-model="reason" @if($edit_restrict) disabled @endif placeholder="Основание">
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
                        <textarea v-model="comment" class="form-control textarea-rows" @if($edit_restrict) disabled @endif required></textarea>

                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="card" id="materials_from" v-cloak>
                    <div class="card-body">
                        <h6 style="margin-bottom:30px">До преобразования</h6>
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
                        <div class="clearfix"></div>
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
                            @{{ !selected ? 'Выберите место преобразования.' : 'На объекте отсутствую материалы для преобразования.' }}
                        </div>
                        <div class="row" v-if="material_inputs.length > 0 ? (new_materials ? new_materials.length > 0 : false) : false">
                            <div class="col-md-12 text-right" style="margin-top:25px">
                                <button type="button" v-on:click="add_material" @if($edit_restrict) disabled @endif class="btn btn-round btn-sm btn-success btn-outline add-material">
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
                        <h6 style="margin-bottom:30px">После преобразования</h6>
                        <div class="card-header accordion-card-header px-0 mb-3">
                            <h4 class="card-title accordion-card-title">
                                <a data-target="#base2" href="#" data-toggle="collapse" class="accordion-title">
                                    Поиск материалов
                                    <b class="caret accordion-caret"></b>
                                </a>
                            </h4>
                        </div>
                        <div class="card-body card-collapse collapse px-0" id="base2">
                            <div class="row" id="add_new_material_via_category_2">
                                <div class="col-md-12 col-xl-12">
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
                                                <div v-for="(material, mat_id) in need_attributes" v-bind:key="material[0].key">
                                                    <hr v-if="need_attributes.length > 1 && mat_id != 0">
                                                    <div class="row"
                                                         v-if="material.length < 4 && i === 1 || material.length >= 4 && i % 2 === 1"
                                                         v-for="i in material.length">
                                                        <template v-for="(attribute, index) in material">
                                                            <div
                                                                is="material-attribute"
                                                                v-if="material.length >= 4 && (index === i || index === i - 1)  || material.length < 4"
                                                                :key="material[0].key + '-' + attribute.id"
                                                                :id="'select-' + material[0].key + '-' + attribute.id"
                                                                :index="index"
                                                                :mat_id="mat_id"
                                                                :attribute_id.sync="attribute.id"
                                                                :attribute_unit.sync="attribute.unit"
                                                                :attribute_name.sync="attribute.name"
                                                                :attribute_value.sync="attribute.value"
                                                                :attribute_is_required.sync="attribute.is_required"
                                                                :category_id.sync="attribute.category_id"
                                                            >
                                                            </div>
                                                        </template>

                                                        <div style="padding-left:15px;">
                                                            <label class="d-block" v-if="mat_id == 0">Кол-во<span class="star">*</span></label>
                                                            <el-input-number :min="0" :precision="3" :step="0.001" :max="10000000"  v-model="material[0].count" style="widht: 90px"></el-input-number>
                                                        </div>

                                                        <div style="padding-left:15px;">
                                                            <label class="d-block" v-if="mat_id == 0">Ед. изм.<span class="star">*</span></label>
                                                            <el-select v-model="material[0].unit" style="widht: 90px">
                                                                @foreach($operation->materials()->getModel()::$main_units as $unit)
                                                                    <el-option
                                                                        value="{{ $unit['id'] }}"
                                                                        key="{{ $unit['id'] }}"
                                                                        label="{{ $unit['text'] }}"
                                                                    ></el-option>
                                                                @endforeach
                                                            </el-select>
                                                        </div>
                                                        <div style="padding-left:15px;">
                                                            <label class="d-block" v-if="mat_id == 0">
                                                                Б/У
                                                            </label>
                                                            <el-checkbox v-model="material[0].used"
                                                                         border class="d-block"
                                                                         @cannot('mat_acc_base_move_to_new') disabled @elsecannot('mat_acc_base_move_to_used') disabled @endcannot></el-checkbox>
                                                        </div>
                                                        <div style="display: flex; flex-direction: column; justify-content: flex-end; margin-left: 20px">
                                                            <div class="row">
                                                                <button rel="tooltip" type="button" v-if="need_attributes.length > 1" @click="deleteMaterial(mat_id)" class="btn-remove-mobile" data-original-title="Удалить" style="margin-bottom: 5px">
                                                                    <i style="font-size:18px;" class="fa fa-times remove-stroke-index"></i>
                                                                </button>
                                                                <button type="button" v-if="mat_id == need_attributes.length - 1" @click="addMaterial" class="btn-round btn-sm btn-success btn-outline" style="margin-bottom: 8px">
                                                                    <i class="fa fa-plus"></i>
                                                                    Добавить
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row text-center float-right" style="padding-right: 30px">
                                                    <el-button type="primary" @if($edit_restrict) disabled @endif @click="createMaterial">Сохранить</el-button>
                                                </div>
                                            </validation-observer>
                                        </el-form>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div id="materials_to">
                            <div class="materials">
                                <div
                                    is="material-item-to"
                                    v-for="(material_input, index) in material_inputs"
                                    :key="index + '-' + material_input.id"
                                    :index="index"
                                    :material_index="material_input.id"
                                    :material.sync="material_input.code"
                                    :material_id.sync="material_input.material_id"
                                    :material_unit.sync="material_input.material_unit"
                                    :material_count.sync="material_input.material_count"
                                    :base_id.sync="material_input.base_id"
                                    :comments.sync="material_input.comments"
                                    :used.sync="material_input.used"
                                    :materials.sync="material_input.materials"
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
                <div class="row" id="send">
                    <div class="col-md-6 text-left">
                        @if($operation->status != 5 && $operation->status != 8)
                            <a href="{{ $operation->url }}" class="btn btn-wd btn-default">Назад</a>
                        @else
                            <a href="{{ route('building::mat_acc::operations')  }}" class="btn btn-wd btn-default">Назад</a>
                        @endif
                        @if($operation->status == 5 and ($operation->isAuthor() or Gate::check('mat_acc_transformation_create')))
                            <button type="button" @click="close_operation" @if($edit_restrict) disabled @endif class="btn btn-wd btn-danger">Отмена операции</button>
                        @endif
                    </div>
                    <div class="col-md-6 text-right">
                        @if($operation->status == 5)
                            @if(Gate::check('mat_acc_transformation_create') || Gate::check('mat_acc_transformation_draft_create'))
                                <el-button type="primary" v-on:click="check_conflict" @if($edit_restrict) disabled @endif :loading="is_loading_send">@if(Gate::check('mat_acc_transformation_create'))Сохранить @elseif(Gate::check('mat_acc_transformation_draft_create'))Обновить черновик @endif</el-button>
                            @endif
                        @else
                            @can('mat_acc_transformation_create')
                                <el-button type="primary" v-on:click="check_conflict" @if($edit_restrict) disabled @endif :loading="is_loading_send">Сохранить</el-button>
                            @endcan
                        @endif
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
                init: true,
                options: [],
                responsible_user: "{{ $operation->responsible_user->user->id }}",
                responsible_RP: "{{ $operation->responsible_RP ?? 'old_draft' }}",
                selected: "{{ $operation->object_id_to }}",
                operation_date: "{!! \Carbon\Carbon::parse($operation->planned_date_to)->format('d.m.Y') !!}",
                comment: {!! json_encode($operation->comment_author)!!},
                reason: '{{ $operation->reason }}',
                reasons: [
                    {id: 1, text: 'Производство работ'},
                    {id: 2, text: 'Переработка'},
                    {id: 3, text: 'Сборка'},
                    {id: 4, text: 'Другое'},
                ]
            },
            mounted: function () {
                axios.post('{{ route('building::mat_acc::get_users') }}', {responsible_user_id: "{{ $operation->responsible_user->user->id }}"}).then(response => vm.responsible_users = response.data);
                axios.post('{{ route('building::mat_acc::get_RPs') }}', {responsible_RP: "{{ $operation->responsible_RP }}"}).then(response => vm.responsible_RPs = response.data);
                axios.post('{{ route('building::mat_acc::report_card::get_objects') }}', {object_id: "{{ $operation->object_id_to }}"}).then(response => vm.options = response.data)

                setTimeout(() => {
                    mass_add_materials.findAllMaterials(vm.selected);
                }, 500)
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
                        axios.post('{{ route('building::mat_acc::report_card::get_objects') }}', {object_id: "{{ $operation->object_id_to }}"}).then(response => vm.options = response.data)
                    }
                },
                clean_materials() {
                    mass_add_materials.findAllMaterials(vm.selected);

                    axios.post('{{ route('building::mat_acc::report_card::get_materials') }}', {base_id: this.selected ? this.selected : -1}).then(function (response) {
                        materials_from.new_materials = response.data;
                        if (!this.init) {
                            materials_from.material_inputs = [];
                            materials_from.material_inputs.push({
                                id: materials_from.next_mat_id++,
                                material_id: '',
                                material_unit: '',
                                used: false,
                                material_count: Number(0),
                                units: materials_from.units,
                                materials: materials_from.new_materials
                            });
                        } else {
                            this.init = false;
                            materials_from.init = false;
                        }
                    });
                },
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
            }
        })

        Vue.component('material-item-from', {
            template: '\
      <div class="form-row">\
        <div class="col-10 mb-10">\
              <label>\
                  Материал <span class="star">*</span>\
              </label>\
              <template>\
                <el-select @change="changeMaterialId" ref="usernameInput" v-model="default_material_base_id" @clear="search(``)" clearable filterable @if($edit_restrict) disabled @endif :remote-method="search" remote size="large" placeholder="Выберите материал">\
                  <el-option\
                    v-for="item in new_materials_filtered"\
                    :label="item.label"\
                    :key="`${item.base_id}_${item.id}_${item.used ? 1 : 0}`"\
                    :value="`${item.base_id}_${item.id}_${item.used ? 1 : 0}`">\
                  </el-option>\
                </el-select>\
              </template>\
          </div>\
          <div class="col-2 align-self-end mb-10 text-center">\
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
          <div class="col-md-4">\
              <label for="">\
                  Ед. измерения <span class="star">*</span>\
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
          <div class="col-md-5">\
              <label>\
                  Количество <span class="star">*</span>\
              </label>\
              <template>\
                  <el-input-number :min="0" @change="changeMaterialCount" v-model="default_material_count" :precision="3" @if($edit_restrict) disabled @endif :step="0.001" :max="10000000" required></el-input-number>\
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
                <button type="button" v-on:click="$emit(\'remove\')"  @if($edit_restrict) disabled @endif class="btn-remove-big">\
                    <i style="font-size:18px;" class="fa fa-times remove-stroke"></i>\
                </button>\
              </div>\
          </div>\
          </div>\
          ',
            props: ['material_id', 'material_unit', 'material_count', 'used', 'inputs_length', 'material_index', 'used', 'materials', 'units', 'index', 'material_input', 'base_id'],
            computed: {
                new_materials_filtered() {
                    return this.materials
                        .filter(el => {
                            const count = materials_from.material_inputs.filter(input => input.base_id == el.base_id && input.id == el.id && input.used == el.used).length;
                            return count < 1 || this.default_material_base_id == (el.base_id + '_' + el.id + '_' + (el.used ? 1 : 0));
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
            mounted: function () {
                if (this.default_material_base_id) {
                    this.changeMaterialId(this.default_material_base_id);
                }
            },
            methods: {
                changeMaterialId(value) {
                    if (value) {
                        let [base_id, manual_id, mat_used] = value.split('_');
                        let mat = this.materials.filter(input => String(input.base_id) == base_id && input.id == manual_id && (input.used ? 1 : 0) == mat_used)[0];
                        let used = (mat.used === undefined ? false : mat.used);
                        this.$emit('update:material_id', mat.id);
                        this.$emit('update:base_id', base_id);
                        this.default_material_id = mat.id;
                        this.default_material_used = used;
                        this.loadComments(mat);
                        this.changeUsageValue(used);
                        this.getDescription();
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
                autoChangeUnit(unit) {
                    this.changeMaterialUnit(unit);
                    this.default_material_unit = unit;
                },
                search(query) {
                    const that = this;

                    axios.post('{{ route('building::mat_acc::report_card::get_materials') }}', {
                        q: query,
                        base_id: vm.selected
                    }).then(function (response) {
                        materials_from.material_inputs[that.index].materials = response.data
                    });
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
                    default_material_id: materials_from.material_inputs[this.index].material_id,
                    default_base_id: materials_from.material_inputs[this.index].base_id,
                    default_material_base_id: materials_from.material_inputs[this.index].base_id + '_' + materials_from.material_inputs[this.index].material_id,
                    default_material_unit: materials_from.material_inputs[this.index].material_unit,
                    default_material_count: materials_from.material_inputs[this.index].material_count,
                    default_material_used: materials_from.material_inputs[this.index].used,
                    default_material_description: 'Нет описания',
                    documents: [],
                    comments: [],
                    id: null
                }
            },
        })

        var materials_from = new Vue({
            el: '#materials_from',
            data: {
                options: [],
                selected: '',
                init: true,
                new_materials: [],
                material_unit: '',
                next_mat_id: 1,
                material_inputs: [],
                units: {!! json_encode($operation->materials()->getModel()::$main_units) !!},
                exist_materials: {!! $operation->materials->where('type', 7)->where('count', '>', 0) !!},
                is_loading_mats: false,
            },
            created: function () {
                const that = this;

                axios.post('{{ route('building::mat_acc::report_card::get_materials') }}', {base_id: vm.selected, material_ids: {{ $operation->materials->pluck('manual_material_id') }} }).then(function (response) {
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
                                material_unit: that.exist_materials[key].unit,
                                base_id: ex_base_id,
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
                inArray: function (array, base, manual_id) {
                    var length = array.length;
                    for (var i = 0; i < length; i++) {
                        if (array[i].base_id == base && array[i].id == manual_id) return true;
                    }
                    return false;
                }
            }
        })


        Vue.component('material-item-to', {
            template: '\
      <div class="form-row">\
        <div class="col-md-10 mb-10">\
              <label>\
                  Материал <span class="star">*</span>\
              </label>\
              <template>\
                <el-select @change="changeMaterialId" ref="usernameInput" @if($edit_restrict) disabled @endif v-model="default_material_base_id" clearable filterable :remote-method="search" @clear="search(``)" remote size="large" placeholder="Выберите материал">\
                  <el-option\
                    v-for="item in new_materials_filtered"\
                    :label="item.label"\
                    :key="`${item.base_id}_${item.id}_${item.used ? 1 : 0}`"\
                    :value="`${item.base_id}_${item.id}_${item.used ? 1 : 0}`">\
                  </el-option>\
                </el-select>\
              </template>\
          </div>\
          <div class="col-2 align-self-end mb-10 text-center">\
            <button data-toggle="modal" data-target="#material-notes" @click="() => { materialNotes().changeMaterialInput(this); hideTooltips(); }"\
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
          <div class="col-md-4">\
              <label>\
                  Ед. измерения <span class="star">*</span>\
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
          <div class="col-md-5">\
              <label>\
                  Количество <span class="star">*</span>\
              </label>\
              <template>\
                  <el-input-number :min="0" @change="changeMaterialCount" v-model="default_material_count" @if($edit_restrict) disabled @endif :precision="3" :step="0.001" :max="10000000" required></el-input-number>\
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
                <button type="button" v-on:click="$emit(\'remove\')" class="btn-remove-big">\
                    <i style="font-size:18px;" class="fa fa-times remove-stroke" ></i>\
                </button>\
              </div>\
          </div>\
      </div>\
    ',
            props: ['material_id', 'material_unit', 'material_count', 'inputs_length', 'material_index', 'used', 'materials', 'units', 'index', 'base_id'],
            computed: {
                new_materials_filtered() {
                    return this.materials
                        .filter(el => {
                            const count = materials_to.material_inputs.filter(input => input.base_id == el.base_id && input.id == el.id && input.used == el.used).length;
                            return count < 1 || this.default_material_base_id == (el.base_id + '_' + el.id + '_' + (el.used ? 1 : 0));
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
            watch: {
                comments: function () {
                    this.$emit('update:comments', this.comments);
                }
            },
            methods: {
                changeMaterialId(value) {
                    let [base_id, manual_id, mat_used] = value.split('_');
                    let mat = this.materials.filter(input => String(input.base_id) == String(base_id) && input.id == manual_id && (input.used ? 1 : 0) == mat_used)[0];
                    let used = (!mat || mat.used === undefined ? false : mat.used);
                    this.$emit('update:material_id', mat.id);
                    this.$emit('update:base_id', base_id);
                    this.default_material_used = used;
                    this.default_material_id = mat.id;
                    this.loadComments(mat);
                    this.changeUsageValue(used)
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
                    default_material_id: materials_to.material_inputs[this.index].material_id,
                    default_base_id: materials_to.material_inputs[this.index].base_id,
                    default_material_base_id: materials_to.material_inputs[this.index].base_id + '_' + materials_to.material_inputs[this.index].material_id,
                    default_material_unit: materials_to.material_inputs[this.index].material_unit,
                    default_material_count: materials_to.material_inputs[this.index].material_count,
                    default_material_used: materials_to.material_inputs[this.index].used,
                    documents: [],
                    comments: [],
                    id: null,
                }
            },
            mounted() {
                if (this.default_material_base_id) {
                    this.changeMaterialId(this.default_material_base_id);
                }
            }
        })

        var materials_to = new Vue({
            el: '#materials_to',
            data: {
                options: [],
                selected: '',
                material_unit: '',
                next_mat_id: 1,
                material_inputs: [],
                units: {!! json_encode($operation->materials()->getModel()::$main_units) !!},
                exist_materials: {!! $operation->materials->where('type', 6)->where('count', '>', 0) !!},
                default_materials: [],
                new_materials: [],
            },
            created: function () {
                const that = this;

                axios.post('{{ route('building::mat_acc::report_card::get_materials') }}',).then(function (response) {
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
                    console.log(1);
                    axios.post('{{ route('building::mat_acc::report_card::get_materials') }}').then(function (response) {
                        that.new_materials = response.data

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
                inArray: function (array, base, manual_id) {
                    var length = array.length;
                    for (var i = 0; i < length; i++) {
                        if (array[i].base_id == base && array[i].id == manual_id) return true;
                    }
                    return false;
                }
            }
        })

        var send = new Vue({
            el: '#send',
            data: {
                is_loading_send: false,
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
                    axios.post('{{ route('building::mat_acc::transformation::update', $operation->id) }}', {
                        materials_from: materials_from.material_inputs,
                        materials_to: materials_to.material_inputs,
                        planned_date_to: vm.operation_date,
                        responsible_user_id: vm.responsible_user,
                        object_id: vm.selected,
                        comment: vm.comment,
                        reason: vm.reason,
                        responsible_RP: vm.responsible_RP,
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
                close_operation: function () {
                    send.$confirm('Это действие приведет к отмене операции.', 'Внимание', {
                        confirmButtonText: 'Подтвердить',
                        cancelButtonText: 'Назад',
                        type: 'warning'
                    }).then(() => {
                        axios.post('{{ route('building::mat_acc::close_operation', $operation->id) }}').then(function (response) {
                            if (response.data) {
                                send.$message({
                                    type: 'success',
                                    message: 'Операция отменена'
                                });

                                window.location = '{{ route('building::mat_acc::operations') }}';
                            } else {
                                send.$message({
                                    message: 'Нельзя отменить операцию.',
                                    type: 'error'
                                });
                            }
                        })
                    });
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
        <div style="padding-left:15px;">\
            <validation-provider :rules="attribute_is_required ? `required` : ``" :vid="id"\
                                                                 :ref="id" v-slot="v">\
                <label class="d-block" v-if="mat_id == 0">@{{ attribute_name }}<span v-if="attribute_is_required" class="star">*</span></label>\
                <el-select\
                  v-model="default_parameter"\
                  @change="onChange"\
                  :allow-create="attribute_name.toLowerCase() !== \'эталон\'"\
                  filterable\
                  clearable\
                  :class="v.classes"\
                  :id="id"\
                  remote\
                  :remote-method="search"\
                  :loading="loading"\
                  @keydown.native.enter="keyHandler"\
                  placeholder="">\
                  <el-option\
                    v-for="item in mixedParameters"\
                    :key="item.id"\
                    :label="item.name"\
                    :value="item.id">\
                  </el-option>\
                </el-select>\
                <div class="error-message" style="padding-left: 15px;">@{{ v.errors[0] }}</div>\
             </validation-provider>\
         </div>\
        ',
            props: ['index', 'attribute_id', 'attribute_unit', 'attribute_name', 'attribute_value', 'attribute_is_required', 'category_id', 'id', 'mat_id'],
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
                }).then(() => {
                    if (that.attribute_id == 'etalon') {
                        that.default_parameter = that.attribute_value;
                    }
                });

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
                row_key: 1,
                parameters: [],
                loading: false,
                materials: [],
                attrs_all: [],
                observer_key: 1,
                unit_show: '0',
            },
            methods: {
                getNeedAttributes() {
                    let that = this;
                    that.need_attributes = [];
                    axios.post('{{ route('building::materials::category::get_need_attrs') }}', {category_id: that.category_id}).then(function (response) {
                        that.attrs_all = response.data.attrs.reverse();

                        let one_material = [];
                        that.attrs_all.forEach(function (attribute) {
                            one_material.push({
                                key: that.row_key,
                                id: attribute.id,
                                attr_id: attribute.id,
                                category_id: attribute.category_id,
                                name: attribute.name,
                                unit: response.data.unit_show,
                                value: '',
                                is_required: attribute.is_required,
                                from: attribute.from,
                                to: attribute.to,
                                step: attribute.step,
                                count: 0,
                                used: 0,
                            });
                        });
                        materials_create_2.unit_show = response.data.unit_show;
                        that.need_attributes.push(one_material);
                        materials_create_2.row_key += 1;
                    });
                },
                deleteMaterial(id) {
                    this.need_attributes.splice(id, 1);
                },
                addMaterial() {
                    let one_material = [];
                    this.attrs_all.forEach(function (attribute) {
                        let prev_etalon = materials_create_2.need_attributes[materials_create_2.need_attributes.length - 1].find(attr => attr.attr_id === 'etalon').value;
                        one_material.push({
                            key: materials_create_2.row_key,
                            id: attribute.id,
                            attr_id: attribute.id,
                            category_id: attribute.category_id,
                            name: attribute.name,
                            unit: materials_create_2.unit_show,
                            value: (attribute.id === 'etalon' ? prev_etalon : ''),
                            is_required: attribute.is_required,
                            from: attribute.from,
                            to: attribute.to,
                            step: attribute.step,
                            count: 0,
                            used: false,
                        });
                    });
                    this.need_attributes.push(one_material);
                    materials_create_2.row_key += 1;
                },
                createMaterial() {
                    let that = this;

                    this.$refs.observer.validate().then(success => {
                        if (!success) {
                            return;
                        }
                        axios.post('{{ route('building::mat_acc::attach_material') }}', {
                            materials: that.need_attributes,
                            category_id: that.category_id
                        }).then(function (response) {
                            let new_materials = response.data;
                            let select_materials = materials_to.new_materials;
                            for (const new_material of new_materials)
                            {
                                if (!that.inArray(select_materials, {
                                    id: new_material.id,
                                    used: new_material.used
                                })) {
                                    select_materials.push({id: new_material.id, label: new_material.name});
                                }

                                if (Object.values(materials_to.material_inputs)[Object.values(materials_to.material_inputs)["length"] - 1].material_id == '') {
                                    materials_to.material_inputs.splice(Object.values(materials_to.material_inputs)["length"] - 1, 1);
                                }

                                let unit_id = materials_to.units.filter(item => item.text == new_material.category.category_unit)[0].id

                                materials_to.material_inputs.push({
                                    id: materials_to.next_mat_id++,
                                    material_id: new_material.id,
                                    material_unit: new_material.unit,
                                    used: new_material.used,
                                    material_count: new_material.count,
                                    units: materials_to.units,
                                    materials: select_materials
                                });
                            }
                            //TODO complete
                            eventHub.$emit('addEvent', '');
                            that.observer_key += 1;
                            that.need_attributes = [];
                            that.getNeedAttributes();
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
                {{--search(query) {--}}
                    {{--    if (query !== '') {--}}
                    {{--        this.loading = true;--}}

                    {{--        setTimeout(() => {--}}
                    {{--            axios.post('{{ route('building::materials::category::get_need_attrs') }}', {--}}
                    {{--                category_id: that.category_id,--}}
                    {{--                q: query--}}
                    {{--            }).then(function (response) {--}}
                    {{--                that.need_attributes = response.data;--}}
                    {{--            });--}}

                    {{--            this.loading = false;--}}
                    {{--        }, 200);--}}
                    {{--    } else {--}}
                    {{--        axios.post('{{ route('building::materials::category::get_need_attrs') }}', {category_id: that.category_id}).then(function (response) {--}}
                    {{--            that.need_attributes = response.data;--}}
                    {{--        });--}}
                    {{--    }--}}
                    {{--},--}}
                inArray: function (array, element) {
                    var length = array.length;
                    for (var i = 0; i < length; i++) {
                        if (array[i].base_id == element) return true;
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
