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
@include('building.material_accounting.modules.material_notes')

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

        @include('building.material_accounting.modules.history_composit')

        <!-- information about materials -->
        @include('building.material_accounting.modules.info_about_materials_moving')

        @if($operation->hasAccessTo())

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
                    <div class="col-md-12 col-xl-12">
                        <template>
                            <el-form label-position="top">
                                <validation-observer ref="observer" :key="observer_key">
                                    <label class="d-block">Категория<span class="star">*</span></label>
                                    <validation-provider rules="required" vid="select-category"
                                                         ref="select-category" v-slot="v">
                                        <el-select v-model="category_id" :class="v.classes" @change="getNeedAttributes" placeholder="Выберите категорию материала">
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

                                            <div style="padding-left:15px;" class="col-md-2">
                                                <label class="d-block" v-if="mat_id == 0">Кол-во<span class="star">*</span></label>
                                                <el-input-number :min="0" :precision="3" :step="0.001" :max="10000000"  v-model="material[0].count" style="widht: 90px"></el-input-number>
                                            </div>

                                            <div style="padding-left:15px;" class="col-md-1">
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
                                            <div style="padding-left:15px;" class="col-md-1">
                                                <label class="d-block" v-if="mat_id == 0">
                                                    Б/У
                                                </label>
                                                <el-checkbox v-model="material[0].used"
                                                             border class="d-block"
                                                             @cannot('mat_acc_base_move_to_new') disabled @elsecannot('mat_acc_base_move_to_used') disabled @endcannot></el-checkbox>
                                            </div>
                                            <div style="display: flex; flex-direction: column; justify-content: flex-end; margin-left: 15px">
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
                                        <el-button type="primary" @click="createMaterial">Сохранить</el-button>
                                    </div>
                                </validation-observer>
                            </el-form>
                        </template>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">

                <h5 class="materials-info-title">Подтверждение операции</h5>

                <div id="materials">
                    <div class="card-body">
                        <h6 style="margin-bottom:30px">Номенклатура</h6>
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
                            :comments.sync="material_input.comments"
                            :material_input="material_input"
                            :base_id.sync="material_input.base_id"
                            :used.sync="material_input.used"
                            :materials.sync="material_input.materials"
                            :units.sync="material_input.units"
                            v-on:remove="material_inputs.splice(index, 1)"
                            :inputs_length="material_inputs.length">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 text-right" style="margin-top:25px">
                                <button type="button" v-on:click="add_material" class="btn btn-round btn-sm btn-success btn-outline add-material">
                                    <i class="fa fa-plus"></i>
                                    Добавить материал
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="answer">
                    <div class="row" style="margin-top:25px; margin-bottom:10px">
                        <div class="col-md-12" >
                            <label for="">
                                Комментарий <span class="star">*</span>
                            </label>
                            <textarea v-model="comment" class="form-control textarea-rows"></textarea>
                        </div>
                    </div>
                    <div class="form-group" style="margin-top:30px">
                        <div class="row">
                            <label class="col-sm-2 col-form-label" for="">
                                    Сопроводительные документы
                            </label>
                            <div class="col-sm-4" style="padding-top:0px;">
                                <el-upload
                                  class="upload-demo"
                                  :headers="{ 'X-CSRF-TOKEN': csrf }"
                                  action="{{ route('building::mat_acc::part_upload', [$operation->id, 'operation_material_id' => '0']) }}"
                                  :data="request"
                                  multiple
                                  ref="upload_doc"
                                  :on-success="add_images"
                                  :on-remove="remove_images"
                                  :on-progress="in_process"
                                  :on-error="if_error"
                                  >
                                  <el-button size="small" type="primary">Загрузить</el-button>
                                  <div slot="tip" class="el-upload__tip">pdf/doc файлы не более 100мб</div>
                                </el-upload>
                            </div>
                            @if (!in_array($operation->object_id_to, [76, 192]) and in_array($operation->type, [1, 4]))
                            <label class="col-sm-2 col-form-label" for="">
                                Сертификат соответствия
                            </label>
                            <div class="col-sm-4" style="padding-top:0px;">
                                <el-upload
                                  class="upload-demo"
                                  :headers="{ 'X-CSRF-TOKEN': csrf }"
                                  action="{{ route('building::mat_acc::part_upload', [$operation->id, 'operation_material_id' => '0', 'type' => 'cert']) }}"
                                  :data="request"
                                  multiple
                                  ref="upload_cert"
                                  :on-success="add_images"
                                  :on-remove="remove_images"
                                  :on-progress="in_process"
                                  :on-error="if_error"
                                  >
                                  <el-button size="small" type="primary">Загрузить</el-button>
                                  <div slot="tip" class="el-upload__tip">pdf/doc файлы не более 100мб</div>
                                </el-upload>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div id="drop-area" class="drop-area">
                                <div slot="tip" class="el-upload__tip" style="margin-bottom:15px">Необходимо загрузить фотографии транспорта спереди и сзади.</div>
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
                                <el-button type="primary" v-if="inputs_length" v-on:click="before_submit('part_save')"
                                           :loading="is_loading_send || array_process.length > 0" class="btn-wd"
                                           style="padding: 11px 20px!important; font-weight:400!important; width: 190px;">
                                    Закрыть частично
                                </el-button>
                            </div>
                            <div class="d-inline-block" @click="beforeSend">
                                <el-button id="button_send" type="button" @click="before_submit('send')"
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
        <!-- история поставок модалка -->
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
    <script type="text/javascript">

        $( document ).ready(function() {
            $('.materials row:first-child').addClass('show-mobile-label');
        });

Vue.component('material-item', {
  template: '\
      <div class="form-row" style="margin-top: 7px;">\
          <div class="col-md-4">\
              <label :class="[material_index !== 1 ? \'show-mobile-label mt-10__mobile\' : \'mt-10__mobile\']">\
                  Материал <span class="star">*</span>\
              </label>\
              <template>\
                <el-select @change="changeMaterialId" ref="usernameInput" v-model="default_base_material_id" clearable filterable @clear="search(\'\')" :remote-method="search" remote size="large" placeholder="Выберите материал">\
                  <el-option\
                    v-for="item in new_materials_filtered"\
                    :label="item.label"\
                    :key="`${item.base_id}_${item.id}`"\
                    :value="`${item.base_id}_${item.id}`">\
                  </el-option>\
                </el-select>\
              </template>\
          </div>\
          <div class="col-md-1 align-self-end text-center">\
                <button data-toggle="modal" data-target="#material-notes" @click="() => { materialNotes().changeMaterialInput(this, true); hideTooltips(); }"\
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
                  Количество <span class="star">*</span>\
              </label>\
              <template>\
                  <el-input-number @change="changeMaterialCount" :min="0" v-model="default_material_count" :precision="3" :step="0.001" :max="10000000" required></el-input-number>\
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
                        :picker-options="dateToOptions"\
                        required\
                        @change="changeMaterialDate"\
                >\
                </el-date-picker>\
              </template>\
          </div>\
          <div class="col-md-1">\
              <label for="" :class="[material_index !== 1 ? \'show-mobile-label mt-10__mobile\' : \'mt-10__mobile\']">\
                  Б/У\
              </label>\
              <template>\
                  <el-checkbox v-model="default_material_used" \
                    border class="d-block"\
                    @can('mat_acc_base_move_to_new') @change="changeUsageValue" @endcan @cannot('mat_acc_base_move_to_new') disabled @endcannot
                  ></el-checkbox> \
              </template>\
          </div>\
          <div class="col-md-1 text-center">\
            <button rel="tooltip" type="button" v-on:click="$emit(\'remove\')" class="btn-remove-mobile" data-original-title="Удалить">\
                <i style="font-size:18px;" class="fa fa-times" :class="[material_index !== 1 ? \'fa fa-times remove-stroke-index\' : \'fa fa-times remove-stroke\']"></i>\
            </button>\
          </div>\
      </div>\
    ',
  props: ['material_id', 'material_unit', 'material_count','used', 'material_date', 'inputs_length', 'material_index', 'materials', 'units', 'material_input', 'base_id', 'index'],
  computed: {
      new_materials_filtered() {
          return this.materials
              .filter(el => {
                  const count = materials.material_inputs.filter(input => input.material_id == el.id && input.base_id == el.base_id).length;
                  return count < 1 || parseInt(this.default_base_material_id.split('_')[0]) === el.base_id && parseInt(this.default_base_material_id.split('_')[1]) == el.id;
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
      changeMaterialId(value, on_load = false) {
          base_id = value.split('_')[0];
          material_id = parseInt(value.split('_')[1]);
          if (base_id !== 'undefined') {
              mat = this.materials.filter(input => input.base_id == parseInt(base_id))[0];
          } else {
              mat = this.materials.filter(input => input.id == material_id)[0];
          }
          let used = (!mat || mat.used === undefined ? false : mat.used);
          this.$emit('update:material_id', mat.id);
          this.$emit('update:base_id', base_id);
          this.default_material_used = used;
          this.default_material_id = mat.id;
          this.loadComments(mat);
          this.changeUsageValue(used)

          let unit = (!mat || mat.unit === undefined ? null : mat.unit);
          if (!on_load) {
              this.autoChangeUnit(unit)
          }
          this.getDescription();
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
          console.log(value);
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
              axios.post('{{ route('building::mat_acc::report_card::get_materials') }}', {q: query}).then(function (response) {
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
        if (typeof(mat) !== 'undefined' && this.material_input.base_id) {
            axios.get('{{ route('building::mat_acc::report_card::get_base_comments') }}', { params: { base_id: this.material_input.base_id }})
            .then(response => {
                this.id = this.material_input.base_id;
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
          default_base_material_id: this.material_id ? this.base_id + '_' + this.material_id: '',
          default_material_unit: materials.material_inputs[this.index].material_unit,
          default_material_count: materials.material_inputs[this.index].material_count,
          default_material_date: materials.material_inputs[this.index].material_date,
          dateToOptions: {
              firstDayOfWeek: 1,
          },
          default_material_description: 'Нет описания',
          documents: [],
          default_material_used: materials.material_inputs[this.index].used,
          predefinedLocation: {!! json_encode($operation->object_to->name_tag) !!},
          comments: [],
          id: null,
      }
  },
  mounted() {
        if (this.default_base_material_id) {
            this.changeMaterialId(this.default_base_material_id, true);
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
        exist_materials: {!! $operation->materialDifference($operation->materials->where('type', 3), $operation->materialsPartTo) !!}
    },
    mounted: function () {
        const that = this;
        axios.post('{{ route('building::mat_acc::report_card::get_materials') }}', {base_id: {{ $operation->object_id_to }}, with_etc: true}).then(function (response) {
            that.new_materials = response.data;

            Object.keys(that.exist_materials).map(function(key) {
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
                        material_id: that.exist_materials[key].manual_material_id,
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

            axios.post('{{ route('building::mat_acc::report_card::get_materials') }}').then(function (response) {
                  that.new_materials = response.data

                  that.material_inputs.push({
                      id: that.next_mat_id++,
                      material_id: '',
                      base_id: '',
                      material_unit: '',
                      material_label: '',
                      material_count: '',
                      comments: [],
                      used: false,
                      material_date: (new Date()).toISOString().split('T')[0],
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
        files_ids: [],
        cert_uploaded: false,
    },
    computed: {
      inputs_length: function () {
        return materials.material_inputs.length;
      }
    },
    mounted: function() {
        $('#button_send').removeClass('el-button');
    },
    methods: {
        send() {
            let that = this;
            this.is_loading_send = true;
            axios.post('{{ route('building::mat_acc::arrival::send', $operation->id) }}', {
                materials: materials.material_inputs,
                comment: answer.comment,
                files_ids: answer.files_ids,
                images_ids: answer.images_ids
            }).then(function (response) {
                if (!response.data.message) {
                    window.location = '{{ route('building::mat_acc::operations') }}';
                } else {
                    that.is_loading_send = false;
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

            answer.array_process = _.remove(answer.array_process, function(n) {
              return n != file.uid;
            });
            this.$nextTick(() => $('#button_send').removeClass('el-button'));
        },
        remove_files(file, fileList) {
            var that = this;

            answer.array_process = _.remove(answer.array_process, function(n) {
                return n != file.uid;
            });

            answer.images_ids = _.remove(answer.images_ids, function(n) {
              return n != file.response.id;
            });
        },
        add_images(response, file, fileList) {
            var that = this;

            that.images_ids.push(file.response.id);

            answer.array_process = _.remove(answer.array_process, function(n) {
              return n != file.uid;
            });

            if (file.response && file.response.type === 3) {
                answer.cert_uploaded = true;
            }
            this.$nextTick(() => $('#button_send').removeClass('el-button'));
        },
        remove_images(file, fileList) {
            var that = this;

            answer.array_process = _.remove(answer.array_process, function(n) {
                return n != file.uid;
            });

            answer.images_ids = _.remove(answer.images_ids, function(n) {
                return n != file.response.id;
            });

            if (file.response && file.response.type === 3) {
                answer.cert_uploaded = false;
            }
        },
        in_process(event, file, fileList) {
            if (!answer.array_process.includes(file.uid)) {
                answer.array_process.push(file.uid);
            }
        },
        if_error(err, file, fileList) {
            answer.array_process = _.remove(answer.array_process, function(n) {
                return n != file.uid;
            });
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
        before_submit(submitType) {
            if (!answer.cert_uploaded && {!!  json_encode(boolval(!in_array($operation->object_id_to, [76, 192]) and in_array($operation->type, [1, 4]))) !!}) {
                swal({
                    title: "Внимание",
                    html: "Вы не прикрепили сертификат!",
                    type: 'warning',
                    showCancelButton: true,
                    animation: true,
                    confirmButtonText: "Продолжить без сертификата",
                    cancelButtonText: "Отмена",
                    focusCancel: true,
                }).then( result => {
                        if (result.value) {
                            if (submitType === 'send') {
                                this.send();
                            } else {
                                this.part_save();
                            }
                        }
                    }
                );
            } else {
                if (submitType === 'send') {
                    this.send();
                } else {
                    this.part_save();
                }
            }
        },
        part_save() {
            var that = this;
            answer.is_loading_send = true;
            axios.post('{{ route('building::mat_acc::arrival::part_send', $operation->id) }}', {
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
        <div style="padding-left:15px;" class="col-md-2">\
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
                    materials_create.createMaterial();
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
                row_key: 1,
                parameters: [],
                loading: false,
                materials: [],
                attrs_all: [],
                observer_key: 1,
                unit_show: 0,
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
                        that.unit_show = response.data.unit_show;
                        that.need_attributes.push(one_material);
                        materials_create.row_key += 1;
                    });
                },
                deleteMaterial(id) {
                    this.need_attributes.splice(id, 1);
                },
                addMaterial() {
                    let one_material = [];
                    this.attrs_all.forEach(function (attribute) {
                        let prev_etalon = materials_create.need_attributes[materials_create.need_attributes.length - 1].find(attr => attr.attr_id === 'etalon').value;
                        one_material.push({
                            key: materials_create.row_key,
                            id: attribute.id,
                            attr_id: attribute.id,
                            category_id: attribute.category_id,
                            name: attribute.name,
                            unit: materials_create.unit_show,
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
                    materials_create.row_key += 1;
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
                            let select_materials = materials.new_materials;
                            for (const new_material of new_materials)
                            {
                                if (!that.inArray(select_materials, {
                                    id: new_material.id,
                                    used: new_material.used
                                })) {
                                    select_materials.push({id: new_material.id, label: new_material.name});
                                }

                                if (Object.values(materials.material_inputs)[Object.values(materials.material_inputs)["length"] - 1].material_id == '') {
                                    materials.material_inputs.splice(Object.values(materials.material_inputs)["length"] - 1, 1);
                                }

                                let unit_id = materials.units.filter(item => item.text == new_material.category.category_unit)[0].id

                                materials.material_inputs.push({
                                    id: materials.next_mat_id++,
                                    material_id: new_material.id,
                                    material_unit: new_material.unit,
                                    used: new_material.used,
                                    material_count: new_material.count,
                                    units: materials.units,
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
