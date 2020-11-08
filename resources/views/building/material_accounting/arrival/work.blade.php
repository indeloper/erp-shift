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
        @include('building.material_accounting.modules.info_about_materials')

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
                    <div class="col-md-7 col-xl-7">
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
                                        <el-button type="primary" @click="createMaterial">Добавить</el-button>
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

        $( document ).ready(function() {
            $('.materials row:first-child').addClass('show-mobile-label');
        });

Vue.component('material-item', {
  template: '\
      <div class="row" style="margin-top: 7px;">\
          <div class="col-md-4">\
              <label :class="[material_index !== 1 ? \'show-mobile-label mt-10__mobile\' : \'mt-10__mobile\']">\
                  Материал <span class="star">*</span>\
              </label>\
              <template>\
                <el-select @change="changeMaterialId" ref="usernameInput" v-model="default_material_id" clearable filterable @clear="search(\'\')" :remote-method="search" remote size="large" placeholder="Выберите материал">\
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

  props: ['material_id', 'material_unit', 'material_count','used', 'material_date', 'inputs_length', 'material_index', 'materials', 'units'],
  methods: {
      changeMaterialId(value) {
          this.$emit('update:material_id', value);

          let mat = this.materials.filter(input => input.id == value)[0];

          let unit = (mat.unit === undefined ? null : mat.unit);
          this.autoChangeUnit(unit)
          this.getDescription();
      },
      changeMaterialUnit(value) {
          this.$emit('update:material_unit', value)
      },
      changeMaterialCount(value) {
          this.$emit('update:material_count', value)
      },
      changeMaterialDate(value) {
          this.$emit('update:material_date', value)
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
  },
  data: function () {
      return {
          default_material_id: materials.material_inputs[this.inputs_length - 1].material_id,
          default_material_unit: materials.material_inputs[this.inputs_length - 1].material_unit,
          default_material_count: materials.material_inputs[this.inputs_length - 1].material_count,
          default_material_date: materials.material_inputs[this.inputs_length - 1].material_date,
          dateToOptions: {
              firstDayOfWeek: 1,
          },
          default_material_description: 'Нет описания',
          documents: [],
          default_material_used: materials.material_inputs[this.inputs_length - 1].used,
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
                      material_unit: '',
                      material_label: '',
                      material_count: '',
                      used: false,
                      material_date: (new Date()).toISOString().split('T')[0],
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
