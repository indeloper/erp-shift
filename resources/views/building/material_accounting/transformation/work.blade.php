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
            <!-- transformation story -->
            @if($operation->materialsPartTo()->count() || $operation->materialsPartFrom()->count())
                <div class="card tasks-sidebar__item strpied-tabled-with-hover" style="margin-bottom:30px">
                    <div class="card-body story-collapse-card">
                        <div class="accordions">
                            <div class="card" style="margin-bottom:0">
                                <div class="card-header">
                                    <h5 class="card-title">
                                <span class="collapsed story-collapse-card__link" data-target="#collapse2"
                                      data-toggle="collapse">
                                    История трансформации материалов
                                    <b class="caret" style="margin-top:8px"></b>
                                </span>
                                    </h5>
                                </div>
                                <div id="collapse2" class="card-collapse collapse show">
                                    <div class="card-body without-shadow">
                                        <div>
                                            @include('building.material_accounting.modules.history_composit')

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @include('building.material_accounting.modules.info_about_materials_transformation')

        @if($operation->hasAccessTo())

        <div class="card">
            <div class="card-body">
                <h5 class="materials-info-title">Подтверждение операции</h5>
                <div class="row">
                    <div class="col-md-6" id="materials_from">
                        <div class="card">
                                <div class="card-body">
                                    <h6 style="margin-bottom:30px">До преобразования</h6>
                                    <div class="materials">
                                        <div
                                        is="material-item-from"
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
                                            <div class="row" id="add_new_material_via_category_2">
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
                                    </div>
                                    <div id="materials_to">
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
                                                :material_date.sync="material_input.material_date"
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
                        <label class="col-sm-5 col-form-label" for="">
                            Сопроводительные документы
                        </label>
                        <div class="col-sm-7" style="padding-top:0px;">
                            <el-upload
                              class="upload-demo"
                              :headers="{ 'X-CSRF-TOKEN': csrf }"
                              action="{{ route('building::mat_acc::part_upload', [$operation->id, 'operation_material_id' => 'replace_this']) }}"
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
                            <div slot="tip" class="el-upload__tip" style="margin-bottom:15px">Необходимо загрузить фотографии транспорта спереди и сзади.</div>
                            <el-upload
                              :headers="{ 'X-CSRF-TOKEN': csrf }"
                              action="{{ route('building::mat_acc::part_upload', [$operation->id, 'operation_material_id' => 'replace_this']) }}"
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
            @endif
        </div>
    </div>
@endsection

@section('js_footer')
    <script type="text/javascript">
        Vue.component('validation-provider', VeeValidate.ValidationProvider);
        Vue.component('validation-observer', VeeValidate.ValidationObserver);
    </script>
    <script>

        Vue.component('material-item-from', {
            template: '\
      <div class="row">\
        <div class="col-10 mb-10">\
              <label>\
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
          <div class="col-2 align-self-end mb-10">\
            <button data-toggle="modal" data-target="#description" @click="getDescription" title="Примечание" type="button" name="button" class="btn btn-sm btn-outline btn-primary mt-10__mobile btn-block mb-0" style="height: 40px;">\
              <i style="font-size:18px;" class="fa fa-info-circle"></i>\
            </button>\
          </div>\
          <div class="col-md-4">\
              <label for="">\
                  Ед. измерения <span class="span">*</span>\
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
          <div class="col-md-4">\
              <label>\
                  Количество <span class="span">*</span>\
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
          <div class="col-md-12  mb-10">\
              <label>\
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
          <div class="col-md-12">\
              <div class="text-center">\
                <button type="button" v-on:click="$emit(\'remove\')" class="btn-remove-big">\
                    <i style="font-size:18px;" class="fa fa-times remove-stroke"></i>\
                </button>\
              </div>\
          </div>\
      </div>\
    ',
  props: ['material_id', 'material_unit', 'material_count','used', 'material_date', 'inputs_length', 'material_index', 'materials', 'units', 'index', 'used'],
    computed: {
        new_materials_filtered() {
            return this.materials
                .filter(el => {
                    const count = materials_from.material_inputs.filter(input => input.material_id == el.id && input.used == el.used).length;
                    return count < 1 || String(this.default_material_id).split('_')[0] === el.id && String(this.default_material_used).split('_')[1] == el.used;
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
      changeMaterialDate(value) {
          this.$emit('update:material_date', value);
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
          axios.post('{{ route('building::mat_acc::report_card::get_materials') }}', {q: query, base_id: {{ $operation->object_id_from }}}).then(function (response) {
              materials_from.material_inputs[that.index].materials = response.data
          })
      },
      getDescription() {
            let that = this;

            if (String(that.default_material_id)) {
                axios.post('{{ route('building::mat_acc::get_material_category_description') }}', {id: String(that.default_material_id.split('_')[0])}).then(function (response) {
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
          default_material_id: materials_from.material_inputs[this.inputs_length - 1].material_id,
          default_material_unit: materials_from.material_inputs[this.inputs_length - 1].material_unit,
          default_material_count: materials_from.material_inputs[this.inputs_length - 1].material_count,
          default_material_used: materials_from.material_inputs[this.inputs_length - 1].used,
          default_material_date: materials_from.material_inputs[this.inputs_length - 1].material_date,
          default_material_description: 'Нет описания',
        documents: [],
          dateToOptions: {
              firstDayOfWeek: 1,
          },
      }
  }
})


        var materials_from = new Vue({
            el: '#materials_from',
            data: {
                options: [],
                selected: '',
                material_unit: '',
                next_mat_id: 1,
                material_inputs: [],
                units: {!! json_encode($operation->materials()->getModel()::$main_units) !!},
                exist_materials: {!! $operation->materialDifference($operation->materials->where('type', 7), $operation->materialsPartFrom) !!}
            },
            mounted: function () {
                const that = this;

                axios.post('{{ route('building::mat_acc::report_card::get_materials') }}', {base_id: {{ $operation->object_id_from }}, material_ids: {{ $operation->materials->pluck('manual_material_id') }} }).then(function (response) {
                    that.new_materials = response.data;

                    Object.keys(that.exist_materials).map(function (key) {
                        if (!that.inArray(that.new_materials, {id: that.exist_materials[key].manual_material_id, used: that.exist_materials[key].used})) {
                            that.new_materials.push({
                                id: that.exist_materials[key].manual_material_id,
                                label: that.exist_materials[key].manual.name
                            })
                        }

                setTimeout(() => {
                    that.material_inputs.push({
                        id: that.next_mat_id++,
                        material_id: `${that.exist_materials[key].manual_material_id}_${that.exist_materials[key].used ? 1 : 0}`,
                        material_unit: that.exist_materials[key].unit,
                        material_count: Number(that.exist_materials[key].count),
                        used: that.exist_materials[key].used,
                        material_date: (new Date()).toISOString().split('T')[0],
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

                    axios.post('{{ route('building::mat_acc::report_card::get_materials') }}', {base_id: {{ $operation->object_id_from }}}).then(function (response) {
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
                <el-select @change="changeMaterialId" ref="usernameInput" v-model="default_material_id" clearable filterable @clear="search(``)" :remote-method="search" remote size="large" placeholder="Выберите материал">\
                  <el-option\
                    v-for="item in materials"\
                    :label="item.label"\
                    :key="item.id"\
                    :value="item.id">\
                  </el-option>\
                </el-select>\
              </template>\
          </div>\
          <div class="col-md-4">\
              <label>\
                  Ед. измерения <span class="star">*</span>\
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
          <div class="col-md-4">\
              <label>\
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
            <div class="text-center">\
                <button type="button" v-on:click="$emit(\'remove\')" class="btn-remove-big">\
                    <i style="font-size:18px;" class="fa fa-times remove-stroke" ></i>\
                </button>\
              </div>\
          </div>\
      </div>\
    ',
  props: ['material_id', 'material_unit', 'material_count', 'used', 'material_date','used', 'inputs_length', 'material_index', 'materials', 'units', 'index'],
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
      changeMaterialDate(value) {
          this.$emit('update:material_date', value);
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
              materials_to.material_inputs[that.index].materials = response.data;
          })
      },
  },
  data: function () {
      return {
          default_material_id: materials_to.material_inputs[this.inputs_length - 1].material_id,
          default_material_unit: materials_to.material_inputs[this.inputs_length - 1].material_unit,
          default_material_count: materials_to.material_inputs[this.inputs_length - 1].material_count,
          default_material_date: materials_to.material_inputs[this.inputs_length - 1].material_date,
          dateToOptions: {
              firstDayOfWeek: 1,
          },
          default_material_used: materials_to.material_inputs[this.inputs_length - 1].used,
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
                exist_materials: {!! $operation->materialDifference($operation->materials->where('type', 6), $operation->materialsPartTo) !!}
            },
            mounted: function () {
                const that = this;

                axios.post('{{ route('building::mat_acc::report_card::get_materials') }}',).then(function (response) {
                    that.new_materials = response.data;

                    Object.keys(that.exist_materials).map(function (key) {
                        if (!that.inArray(that.new_materials, {id: that.exist_materials[key].manual_material_id, used: that.exist_materials[key].used})) {
                            that.new_materials.push({
                                id: that.exist_materials[key].manual_material_id,
                                label: that.exist_materials[key].manual.name
                            })
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
                      material_date: (new Date()).toISOString().split('T')[0],
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
                    return materials_from.material_inputs.length && materials_to.material_inputs.length;
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

                    axios.post('{{ route('building::mat_acc::transformation::send', $operation->id) }}', {
                        materials_to: materials_to.material_inputs,
                        materials_from: materials_from.material_inputs,
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
                    axios.post('{{ route('building::mat_acc::transformation::part_send', $operation->id) }}', {
                        materials_to: materials_to.material_inputs,
                        materials_from: materials_from.material_inputs,
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

                            if (!that.inArray(select_materials, {id: new_material.id, used: new_material.id})) {
                                select_materials.push({id: new_material.id, label: new_material.name});
                            }

                        if (Object.values(materials_to.material_inputs)[Object.values(materials_to.material_inputs)["length"] - 1].material_id == '') {
                            materials_to.material_inputs.splice(Object.values(materials_to.material_inputs)["length"] - 1, 1);
                        }
                        materials_to.material_inputs.push({
                            id: materials_to.next_mat_id++,
                            material_id: new_material.id,
                            material_unit: '',
                            material_count: '',
                            units: materials_to.units,
                            material_date: (new Date()).toISOString().split('T')[0],
                            materials: select_materials,
                            used: false
                        });

                            if (Object.values(materials_to.material_inputs)[Object.values(materials_to.material_inputs)["length"] - 1].material_id == '') {
                                materials_to.material_inputs.splice(Object.values(materials_to.material_inputs)["length"] - 1, 1);
                            }
                            materials_to.material_inputs.push({
                                id: materials_to.next_mat_id++,
                                material_id: new_material.id,
                                material_unit: '',
                                material_count: '',
                                used: false,
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
                        })
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
