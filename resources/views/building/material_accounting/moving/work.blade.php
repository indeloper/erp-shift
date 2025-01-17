@extends('layouts.app')

@section('title', 'Материальный учет')

@section('url', '')

@section('css_top')
    <style>
        .el-select {
            width: 100%
        }

        .el-date-editor.el-input {
            width: 100%;
        }

        .margin-top-15 {
            margin-top: 15px;
        }

        .el-input-number {
            width: inherit;
        }

        .el-input {
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

        .el-checkbox .el-checkbox__label {
            text-transform: none !important;
        }
    </style>
@endsection

@section('content')
    @include('building.material_accounting.modules.transfer_notes')
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
            <!-- moving story -->
            @if($operation->hasHistory())
                <div class="card tasks-sidebar__item strpied-tabled-with-hover" style="margin-bottom:30px">
                    <div class="card-body story-collapse-card">
                        <div class="accordions">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                <span class="collapsed story-collapse-card__link" data-target="#collapse2"
                                      data-toggle="collapse">
                                    История перемещения материалов
                                    <b class="caret" style="margin-top:8px"></b>
                                </span>
                                    </h5>
                                </div>
                                <div id="collapse2" class="card-collapse collapse show">
                                    <div class="card-body without-shadow" id="materials-story">
                                        <div>
                                            @include('building.material_accounting.modules.history_composit')
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @include('building.material_accounting.modules.info_about_materials_moving')

            @if($operation->hasAccessFrom() && !$operation->actual_date_from)
                <div class="card">
                    <div class="card-body">

                        <h5 class="materials-info-title">Подтверждение операции (вывоз)</h5>

                        <div id="materials_from">
                            <div class="card-body">
                                <h6 style="margin-bottom:30px">Номенклатура</h6>
                                <div class="materials">
                                    <div
                                        is="material-item-from"
                                        v-for="(material_input, index) in material_inputs"
                                        :key="index + '-' + material_input.base_id"
                                        :index="index"
                                        :material_index="material_input.id"
                                        :material_id.sync="material_input.material_id"
                                        :base_id.sync="material_input.base_id"
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
                                        <button type="button" v-on:click="add_material"
                                                class="btn btn-round btn-sm btn-success btn-outline add-material">
                                            <i class="fa fa-plus"></i>
                                            Добавить материал
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="answer_from">
                            <div class="row" style="margin-top:25px; margin-bottom:10px">
                                <div class="col-md-12">
                                    <label for="">
                                        Комментарий
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
                                            :file-list="fileList"
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
                            <div class="row" style="margin-top:30px">
                                <label class="col-sm-5 col-form-label" for="">
                                    Транспортная накладная
                                </label>
                                <div class="col-sm-7">
                                    <button data-toggle="modal" @click="update_weight()" data-target="#create-ttn"
                                            type="button" name="button" class="btn btn-sm btn-info"
                                            style="font-size: 12px;border-radius: 3px;padding: 8px 15px; font-weight:500">
                                        Сформировать накладную
                                    </button>
                                    <template>
                                        <!-- <div v-if="exist_ttn">
                                            <button onclick="document.getElementById('ttn_form_button').click()" style="font-size: 14px; color: #606266" class="contract-file btn btn-social btn-link btn-facebook">
                                                <i class="el-icon-document" style="font-size:13px; top:-1"></i>
                                                Транспортная накладная
                                            </button>
                                            <button rel="tooltip" @click="exist_ttn = null" type="button" class="btn-danger btn-link btn-xs">
                                                <i class="fa fa-times" style="font-size:14px"></i>
                                            </button>
                                        </div> -->
                                    </template>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div id="drop-area" class="drop-area">
                                        <div slot="tip" class="el-upload__tip">Необходимо загрузить фотографии
                                            транспорта спереди и сзади.
                                        </div>
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
                                        <el-button id="button_send_from" type="button" @click="send"
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

            @if($operation->hasAccessTo() && !$operation->actual_date_to)

                <div class="card">
                    <div class="card-body">

                        <h5 class="materials-info-title">Подтверждение операции (прибытие)</h5>

                        <div id="materials_to">
                            <div class="card-body">
                                <h6 style="margin-bottom:30px">Номенклатура</h6>
                                <div class="materials">
                                    <div
                                        is="material-item-to"
                                        v-for="(material_input, index) in material_inputs"
                                        :key="index + '-' + material_input.id"
                                        :index="index"
                                        :material_index="material_input.id"
                                        :material_id.sync="material_input.material_id"
                                        :base_id.sync="material_input.base_id"
                                        :material_unit.sync="material_input.material_unit"
                                        :material_count.sync="material_input.material_count"
                                        :material_date.sync="material_input.material_date"
                                        :material_input="material_input"
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
                                        <button type="button" v-on:click="add_material"
                                                class="btn btn-round btn-sm btn-success btn-outline add-material">
                                            <i class="fa fa-plus"></i>
                                            Добавить материал
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="answer_to">
                            <div class="row" style="margin-top:25px; margin-bottom:10px">
                                <div class="col-md-12">
                                    <label for="">
                                        Комментарий
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
                                        <div slot="tip" class="el-upload__tip" style="margin-bottom:15px">Необходимо
                                            загрузить фотографии транспорта спереди и сзади.
                                        </div>
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
                                        <el-button type="primary" v-if="inputs_length" v-on:click="before_submit('part_save')"
                                                   :loading="is_loading_send || array_process.length > 0" class="btn-wd"
                                                   style="padding: 11px 20px!important; font-weight:400!important; width: 190px;">
                                            Закрыть частично
                                        </el-button>
                                    </div>
                                    <div class="d-inline-block" @click="beforeSend">
                                        <el-button id="button_send_to" type="button" @click="before_submit('send')"
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

    @include('building.material_accounting.modules.ttn_modal')

@endsection


@section('js_footer')
    <script type="text/javascript">
        Vue.component('validation-provider', VeeValidate.ValidationProvider);
        Vue.component('validation-observer', VeeValidate.ValidationObserver);
    </script>
    <script>

        Vue.component('material-item-from', {
            template: '\
      <div class="form-row mb-10">\
          <div class="col-md-4">\
              <label :class="[material_index !== 1 ? \'show-mobile-label mt-10__mobile\' : \'mt-10__mobile\']">\
                  Материал <span class="star">*</span>\
              </label>\
              <template>\
                <el-select @change="changeMaterialId" ref="usernameInput" v-model="default_base_id" clearable filterable :remote-method="search" @clear="search(``)" remote size="large" placeholder="Выберите материал">\
                  <el-option\
                    v-for="item in new_materials_filtered"\
                    :label="item.label"\
                    :key="`${item.base_id}`"\
                    :value="`${item.base_id}`">\
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
          <div class="col-md-1">\
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
                    <el-checkbox v-model="default_material_used"\
                       border class="d-block"\
                       disabled\
                       @can('mat_acc_base_move_to_new') @change="changeUsageValue" @endcan @cannot('mat_acc_base_move_to_new') disabled @endcannot
                ></el-checkbox>\
            </template>\
            </div>\
          <div class="col-md-1 text-center">\
            <button rel="tooltip" type="button" v-on:click="$emit(\'remove\')" class="btn-remove-mobile" data-original-title="Удалить">\
                <i style="font-size:18px;" :class="[material_index !== 1 ? \'fa fa-times remove-stroke-index\' : \'fa fa-times remove-stroke\']"></i>\
            </button>\
          </div>\
      </div>\
    ',
            props: ['material_id', 'material_unit', 'material_count', 'material_date', 'inputs_length', 'material_index', 'materials', 'units', 'index', 'material_input', 'used', 'base_id'],
            computed: {
                new_materials_filtered() {
                    return this.materials
                        .filter(el => {
                            const count = materials_from.material_inputs.filter(input => input.base_id == el.base_id).length;
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
                changeMaterialId(value, on_load = false) {
                    if (value) {
                        let mat = this.materials.filter(input => input.base_id == value)[0];
                        let used = (mat.used === undefined ? false : mat.used);
                        this.$emit('update:material_id', mat.id);
                        this.$emit('update:base_id', value);
                        this.default_material_id = mat.id;
                        this.getDescription();
                        this.changeUsageValue(used);
                        this.loadComments(mat);
                        this.default_material_used = used;

                        let unit = (mat.unit === undefined ? null : mat.unit);
                        if (!on_load) {
                            this.autoChangeUnit(unit);
                        }
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
                changeMaterialDate(value) {
                    this.$emit('update:material_date', value)
                },
                changeUsageValue(value) {
                    this.$emit('update:used', value);
                },
                autoChangeUnit(unit)
                {
                    this.changeMaterialUnit(unit);
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
                    default_material_id: materials_from.material_inputs[this.index].material_id,
                    default_base_id: materials_from.material_inputs[this.index].base_id.toString(),
                    default_material_unit: materials_from.material_inputs[this.index].material_unit,
                    default_material_count: materials_from.material_inputs[this.index].material_count,
                    default_material_date: materials_from.material_inputs[this.index].material_date,
                    default_material_used: materials_from.material_inputs[this.index].used,
                    default_material_description: 'Нет описания',
                    documents: [],
                    comments: [],
                    id: null,
                    dateToOptions: {
                        firstDayOfWeek: 1,
                    },
                }
            },
            mounted() {
                if (this.default_base_id) {
                    this.changeMaterialId(this.default_base_id, false);
                }
            }
        })

        var materials_from = new Vue({
            el: '#materials_from',
            data: {
                options: [],
                materials_with_comments: [],
                selected: '',
                material_unit: '',
                next_mat_id: 1,
                material_inputs: [],
                units: {!! json_encode($operation->materials()->getModel()::$main_units) !!},
                exist_materials: {!! $operation->materialDifference($operation->materials()->with('manual')->where('type', 7)->get(), $operation->materialsPartFrom) !!}
            },
            created() {
                const that = this;
                this.saveComments();

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

            axios.post('{{ route('building::mat_acc::report_card::get_materials') }}', {base_id: {{ $operation->object_id_from }}}).then(function (response) {
                  that.new_materials = response.data

                  that.material_inputs.push({
                      id: that.next_mat_id++,
                      material_id: '',
                      base_id: '',
                      material_unit: '',
                      material_label: '',
                      material_count: '',
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
        },
        saveComments() {
            {{--this.planned_materials.forEach(mat => {--}}
            {{--    axios.get('{{ route('building::mat_acc::report_card::get_base_comments') }}', { params: { base_id: mat.base_id }})--}}
            {{--    .then(response => {--}}
            {{--        if (response.data.comments.length > 0) {--}}
            {{--            this.materials_with_comments.push({--}}
            {{--                material_name: mat.material_name,--}}
            {{--                comments: response.data.comments.map(comment => comment.comment)--}}
            {{--            });--}}
            {{--        }--}}
            {{--    })--}}
            {{--    .catch(error => console.log(error));--}}
            {{--})--}}
        },
    },
})

        var answer_from = new Vue({
            el: '#answer_from',
            data: {
                csrf: "{{ csrf_token() }}",
                dialogImageUrl: '',
                dialogVisible: false,
                comment: '',
                request: {author_type: 2},
                is_loading_send: false,
                filesCount: 0,
                fileList: [],
                exist_ttn: false,
                array_process: [],
                images_ids: [],
                files_ids: []
            },
            computed: {
                inputs_length: function () {
                    return materials_from.material_inputs.length;
                }
            },
            mounted: function () {
                $('#button_send_from').removeClass('el-button');
            },
            methods: {
                update_weight() {
                    var data = ('{' + '"material_count":[' + materials_from.material_inputs.map(function (item) {
                            return '"' + item.material_count + '"'
                        }) +
                        '],"material_id":[' + materials_from.material_inputs.map(function (item) {
                            return '"' + item.material_id + '"'
                        }) +
                        '],"material_unit":[' + materials_from.material_inputs.map(function (item) {
                            return '"' + item.units[item.material_unit - 1].text + '"'
                        }) + ']' + '}'
                    );

                    axios.post('{{ route('building::mat_acc::materials_count') }}', {materials: data}).then(function (response) {
                        ttn.take.weight = response.data;
                    });
                },
                add_files(response, file, fileList) {
                    var that = this;
                    that.files_ids.push(file.response.id);

                    answer_from.array_process = _.remove(answer_from.array_process, function (n) {
                        return n != file.uid;
                    });
                    this.$nextTick(() => $('#button_send_from').removeClass('el-button'));
                },
                remove_files(file, fileList) {
                    var that = this;

                    answer_from.images_ids = _.remove(answer_from.images_ids, function (n) {
                        return n != file.response.id;
                    });
                },
                add_images(response, file, fileList) {
                    var that = this;

                    that.images_ids.push(file.response.id);

                    answer_from.array_process = _.remove(answer_from.array_process, function (n) {
                        return n != file.uid;
                    });
                    this.$nextTick(() => $('#button_send_from').removeClass('el-button'));
                },
                remove_images(file, fileList) {
                    var that = this;
                    answer_from.images_ids = _.remove(answer_from.images_ids, function (n) {
                        return n != file.response.id;
                    });
                },
                in_process(event, file, fileList) {
                    answer_from.array_process.push(file.uid);
                },
                if_error(err, file, fileList) {
                    answer_from.array_process = _.remove(answer_from.array_process, function (n) {
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
                    answer_from.is_loading_send = false;

                    swal({
                        type: 'error',
                        title: "Ошибка",
                        html: message,
                    });
                },
                send() {
                    var that = this;

                    if (that.inputs_length > 0) {
                        var that = this;
                        that.filesCount = 0;

                        answer_from.$refs.upload.uploadFiles.map(function (file) {
                            if (file.raw.type == 'image/png' || file.raw.type == 'image/jpeg') {
                                that.filesCount++;
                            }
                        });

                        answer_from.$refs.upload_doc.uploadFiles.map(function (file) {
                            if (file.raw.type == 'image/png' || file.raw.type == 'image/jpeg') {
                                that.filesCount++;
                            }
                        });
                    }

                    that.is_loading_send = true;

                    axios.post('{{ route('building::mat_acc::moving::send', $operation->id) }}', {
                        materials: materials_from.material_inputs,
                        comment: answer_from.comment,
                        count_files: that.filesCount,
                        files_ids: answer_from.files_ids,
                        images_ids: answer_from.images_ids,
                        type: 1
                    }).then(function (response) {
                        if (!response.data.message) {
                            window.location = '{{ route('building::mat_acc::operations') }}';
                        } else {
                            answer_from.is_loading_send = false;
                            that.$nextTick(() => $('#button_send_from').removeClass('el-button'));

                            answer_from.$message({
                                showClose: true,
                                message: response.data.message,
                                type: 'error',
                                duration: 10000
                            });
                        }
                    }).catch(function (request) {
                        var errors = Object.values(request.response.data.errors);
                        answer_from.is_loading_send = false;
                        that.$nextTick(() => $('#button_send_from').removeClass('el-button'));

                        errors.forEach(function (error, key) {
                            if (key == 0) {
                                setTimeout(function () {
                                    answer_from.$message({
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
                part_save() {
                    var that = this;
                    that.filesCount = 0;

                    answer_from.$refs.upload.uploadFiles.map(function (file) {
                        if (file.raw.type == 'image/png' || file.raw.type == 'image/jpeg') {
                            that.filesCount++;
                        }
                    });

                    answer_from.$refs.upload_doc.uploadFiles.map(function (file) {
                        if (file.raw.type == 'image/png' || file.raw.type == 'image/jpeg') {
                            that.filesCount++;
                        }
                    });

                    answer_from.is_loading_send = true;
                    axios.post('{{ route('building::mat_acc::moving::part_send', $operation->id) }}', {
                        materials: materials_from.material_inputs,
                        comment: answer_from.comment,
                        type: 8,
                        count_files: that.filesCount,
                        files_ids: answer_from.files_ids,
                        images_ids: answer_from.images_ids
                    }).then(function (response) {
                        if (!response.data.message) {
                            window.location.reload();
                        } else {
                            answer_from.is_loading_send = false;
                            that.$nextTick(() => $('#button_send_from').removeClass('el-button'));

                            answer_from.$message({
                                showClose: true,
                                message: response.data.message,
                                type: 'error',
                                duration: 10000
                            });
                        }
                    }).catch(function (request) {
                        var errors = Object.values(request.response.data.errors);
                        answer_from.is_loading_send = false;
                        that.$nextTick(() => $('#button_send_from').removeClass('el-button'));

                        errors.forEach(function (error, key) {
                            if (key == 0) {
                                setTimeout(function () {
                                    answer_from.$message({
                                        showClose: true,
                                        message: error[0],
                                        type: 'error',
                                        duration: 5000
                                    });
                                }, (key + 1) * 100);
                            }
                        });
                    });
                }
            }
        })

        Vue.component('material-item-to', {
            template: '\
      <div class="row mb-10">\
          <div class="col-md-4">\
              <label :class="[material_index !== 1 ? \'show-mobile-label mt-10__mobile\' : \'mt-10__mobile\']">\
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
              <label :class="[material_index !== 1 ? \'show-mobile-label mt-10__mobile\' : \'mt-10__mobile\']">\
                  Ед. изм.<span class="star">*</span>\
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
                <i style="font-size:18px;" :class="[material_index !== 1 ? \'fa fa-times remove-stroke-index\' : \'fa fa-times remove-stroke\']"></i>\
            </button>\
          </div>\
      </div>\
    ',
            props: ['material_id', 'material_unit', 'material_count', 'material_date','index', 'inputs_length', 'material_index', 'materials', 'units', 'used', 'base_id'],
            computed: {
                new_materials_filtered() {
                    return this.materials
                        .filter(el => {
                            const count = materials_to.material_inputs.filter(input => input.base_id == el.base_id).length;
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
            watch: {
                comments: function () {
                    this.$emit('update:comments', this.comments);
                }
            },
            methods: {
                changeMaterialId(value, on_load = false) {
                    if (value) {
                        let mat = this.materials.filter(input => input.base_id == value)[0];
                        let used = (mat.used === undefined ? false : mat.used);
                        this.$emit('update:material_id', mat.id);
                        this.$emit('update:base_id', value);
                        this.changeUsageValue(used);
                        this.default_material_id = mat.id;
                        this.default_material_used = used;
                        this.getDescription();
                        this.loadComments(mat);

                        if (this.default_material_unit == false) {
                            let unit = (!mat || mat.unit === undefined ? null : mat.unit);
                            if (!on_load) {
                                this.autoChangeUnit(unit)
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
                autoChangeUnit(unit)
                {
                    this.changeMaterialUnit(unit)
                    this.default_material_unit = unit;
                },
                search(query) {
                    const that = this;

                    if (query !== '') {
                        setTimeout(() => {
                            axios.post('{{ route('building::mat_acc::report_card::get_materials') }}', {q: query, base_id: {{ $operation->object_id_to }}}).then(function (response) {
                                materials_to.material_inputs[that.inputs_length - 1].materials = response.data
                            })
                        }, 1000);
                    } else {
                        materials_to.material_inputs[that.inputs_length - 1].materials = []
                    }
                },
                hideTooltips() {
                    for (let ms = 50; ms <= 1050; ms += 100) {
                        setTimeout(() => {
                            $('[data-balloon-pos]').blur();
                        }, ms);
                    }
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
                                this.$emit('update:comments', response.data.comments);
                            })
                            .catch(error => console.log(error));
                    } else {
                        this.id = null;
                        this.comments = [];
                    }
                },
                materialNotes() {
                    return materialNotes;
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
            },
            mounted() {
                if (this.default_base_id) {
                    this.changeMaterialId(this.default_base_id, false);
                }
                // setInterval(() => { this.changeMaterialComments(this.comments.map(comment => comment.comment)); }, 500);
            },
            data: function () {
                return {
                    default_material_id: materials_to.material_inputs[this.index].material_id,
                    default_base_id: materials_to.material_inputs[this.index].base_id,
                    default_material_unit: materials_to.material_inputs[this.index].material_unit,
                    default_material_count: materials_to.material_inputs[this.index].material_count,
                    default_material_date: materials_to.material_inputs[this.index].material_date,
                    default_material_used: materials_to.material_inputs[this.index].used,
                    default_material_description: 'Нет описания',
                    documents: [],
                    comments: [],
                    id: null,
                    dateToOptions: {
                        firstDayOfWeek: 1,
                    },
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
                exist_materials: {!! $operation->materialDifference($operation->materials()->where('type', 6)->with('manual')->get(), $operation->materialsPartTo) !!}
            },
            created: function () {
                const that = this;

                axios.post('{{ route('building::mat_acc::report_card::get_materials') }}', {base_id: {{ $operation->object_id_to }}, material_ids: {{ $operation->materials->pluck('manual_material_id') }} }).then(function (response) {
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

            axios.post('{{ route('building::mat_acc::report_card::get_materials') }}', {base_id: {{ $operation->object_id_to }}}).then(function (response) {
                  that.new_materials = response.data;

                  that.material_inputs.push({
                      id: that.next_mat_id++,
                      material_id: '',
                      base_id: '',
                      material_unit: '',
                      material_label: '',
                      material_count: '',
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

        var answer_to = new Vue({
            el: '#answer_to',
            data: {
                csrf: "{{ csrf_token() }}",
                dialogImageUrl: '',
                dialogVisible: false,
                comment: '',
                request: {author_type: 2},
                is_loading_send: false,
                filesCount: 0,
                array_process: [],
                images_ids: [],
                files_ids: [],
            cert_uploaded: false,},
            computed: {
                inputs_length: function () {
                    return materials_to.material_inputs.length;
                }
            },
            mounted: function () {
                $('#button_send_to').removeClass('el-button');
            },
            methods: {
                send() {
                    var that = this;

                    if (that.inputs_length > 0) {
                        that.filesCount = 0;

                        answer_to.$refs.upload.uploadFiles.map(function (file) {
                            if (file.raw.type == 'image/png' || file.raw.type == 'image/jpeg') {
                                that.filesCount++;
                            }
                        });

                        answer_to.$refs.upload_doc.uploadFiles.map(function (file) {
                            if (file.raw.type == 'image/png' || file.raw.type == 'image/jpeg') {
                                that.filesCount++;
                            }
                        });
                    }

                    that.is_loading_send = true;
                    axios.post('{{ route('building::mat_acc::moving::send', $operation->id) }}', {
                        type: 2,
                        materials: materials_to.material_inputs,
                        comment: answer_to.comment,
                        files_ids: answer_to.files_ids,
                        images_ids: answer_to.images_ids,
                        count_files: that.filesCount
                    })
                        .then(function (response) {
                        if (!response.data.message) {
                                window.location = '{{ route('building::mat_acc::operations') }}';
                        } else {
                            answer_to.is_loading_send = false;
                            that.$nextTick(() => $('#button_send_to').removeClass('el-button'));
                            answer_to.$message({
                                showClose: true,
                                message: response.data.message,
                                type: 'error',
                                duration: 10000
                            });
                        }
                    })
                        .catch(function (request) {
                        var errors = Object.values(request.response.data.errors);
                        answer_to.is_loading_send = false;
                        that.$nextTick(() => $('#button_send_to').removeClass('el-button'));
                        errors.forEach(function (error, key) {
                            if (key == 0) {
                                setTimeout(function () {
                                    answer_from.$message({
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
                add_files(response, file, fileList) {
                    var that = this;
                    that.files_ids.push(file.response.id);

                    answer_to.array_process = _.remove(answer_to.array_process, function (n) {
                        return n != file.uid;
                    });
                    this.$nextTick(() => $('#button_send_to').removeClass('el-button'));
                },
                remove_files(file, fileList) {
                    var that = this;

                    answer_to.array_process = _.remove(answer_to.array_process, function (n) {
                        return n != file.uid;
                    });

                    answer_to.images_ids = _.remove(answer_to.images_ids, function (n) {
                        return n != file.response.id;
                    });
                },
                add_images(response, file, fileList) {
                    var that = this;

                    that.images_ids.push(file.response.id);

                    answer_to.array_process = _.remove(answer_to.array_process, function (n) {
                        return n != file.uid;
                    });
                    if (file.response && file.response.type === 3) {
                        answer_to.cert_uploaded = true;
                    }
                    this.$nextTick(() => $('#button_send').removeClass('el-button'));
                },
                remove_images(file, fileList) {
                    var that = this;

                    answer_to.array_process = _.remove(answer_to.array_process, function(n) {
                        return n != file.uid;
                    });answer_to.images_ids = _.remove(answer_to.images_ids, function (n) {
                        return n != file.response.id;
                    });

                    if (file.response && file.response.type === 3) {
                        answer_to.cert_uploaded = false;
                    }
                },in_process(event, file, fileList) {
                    if (!answer_to.array_process.includes(file.uid)) {
                        answer_to.array_process.push(file.uid);
                    }
                },
                if_error(err, file, fileList) {
                    answer_to.array_process = _.remove(answer_to.array_process, function (n) {
                        return n != file.uid;
                    });
                },
                beforeSend() {
                    if (this.array_process.length > 0) {
                        this.$message({
                            showClose: true,
                            message: 'Происходит прикрепление документов к операции. Кнопки станут доступны после успешного окончани загрузки. Если это происходит слишком долго - обновите страницу',
                            type: 'error',
                            duration: 5000});
                    }
                },
                before_submit(submitType) {
                    {{--if (!answer_to.cert_uploaded && {!!  json_encode(boolval(!in_array($operation->object_id_to, [76, 192]) and in_array($operation->type, [1, 4]))) !!}) {--}}
                        {{--    swal({--}}
                        {{--        title: "Внимание",--}}
                        {{--        html: "Вы не прикрепили сертификат!",--}}
                        {{--        type: 'warning',--}}
                        {{--        showCancelButton: true,--}}
                        {{--        animation: true,--}}
                        {{--        confirmButtonText: "Продолжить без сертификата",--}}
                        {{--        cancelButtonText: "Отмена",--}}
                        {{--        focusCancel: true,--}}
                        {{--    }).then( result => {--}}
                        {{--            if (result.value) {--}}
                        {{--                if (submitType === 'send') {--}}
                        {{--                    this.send();--}}
                        {{--                } else {--}}
                        {{--                    this.part_save();--}}
                        {{--                }--}}
                        {{--            }--}}
                        {{--        }--}}
                        {{--    );--}}
                        {{--} else {--}}
                    if (submitType === 'send') {
                        this.send();
                    } else {
                        this.part_save();
                    }
                },
                // },

                part_save() {
                    var that = this;
                    that.filesCount = 0;
                    answer_to.is_loading_send = true;

                    answer_to.$refs.upload.uploadFiles.map(function (file) {
                        if (file.raw.type == 'image/png' || file.raw.type == 'image/jpeg') {
                            that.filesCount++;
                        }
                    });

                    answer_to.$refs.upload_doc.uploadFiles.map(function (file) {
                        if (file.raw.type == 'image/png' || file.raw.type == 'image/jpeg') {
                            that.filesCount++;
                        }
                    });

                    {{--transferNotes.setMaterialsWithComments(materials_from.materials_with_comments);--}}
                    {{--transferNotes.setObjectFrom(`{!! ($operation->object_from->name_tag) !!}`);--}}
                    {{--transferNotes.setObjectTo(`{!! ($operation->object_to->name_tag) !!}`);--}}
                    {{--transferNotes.setCallback(() => {--}}
                        axios.post('{{ route('building::mat_acc::moving::part_send', $operation->id) }}', {
                            materials: materials_to.material_inputs,
                            comment: answer_to.comment,
                            type: 9,
                            count_files: that.filesCount,
                            files_ids: answer_to.files_ids,
                            images_ids: answer_to.images_ids
                        })
                            .then(function (response) {
                            if (!response.data.message) {
                                window.location.reload();
                            } else {
                                answer_to.is_loading_send = false;
                                that.$nextTick(() => $('#button_send_to').removeClass('el-button'));

                                answer_to.$message({
                                    showClose: true,
                                    message: response.data.message,
                                    type: 'error',
                                    duration: 10000
                                });
                            }
                        })
                            .catch(function (request) {
                            var errors = Object.values(request.response.data.errors);
                            answer_to.is_loading_send = false;
                            that.$nextTick(() => $('#button_send_to').removeClass('el-button'));

                            errors.forEach(function (error, key) {
                                if (key == 0) {
                                    setTimeout(function () {
                                        answer_from.$message({
                                            showClose: true,
                                            message: error[0],
                                            type: 'error',
                                            duration: 5000
                                        });
                                    }, (key + 1) * 100);
                                }
                            });
                        });
                    // });
                    // transferNotes.setMaterialInputs(materials_to.material_inputs);
                    // if (materials_to.material_inputs.length > 0) {
                    //     $('#transfer-notes').modal('show');
                    // }
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


        let materials_create_from = new Vue({
            el: '#add_new_material_via_category_from',
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
                        that.attrs_all = response.data.attrs;
                        that.attrs_all = that.attrs_all.reverse();

                        that.attrs_all.forEach(function (attribute) {
                            that.need_attributes.push({
                                id: attribute.id,
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
                        if(array[i].id == element.id && (element.used === undefined || array[i].used == element.used)) return true;
                    }
                    return false;
                },
            }
        })

        let materials_create_to = new Vue({
            el: '#add_new_material_via_category_to',
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
                        that.attrs_all = response.data.attrs;
                        that.attrs_all = that.attrs_all.reverse();

                        that.attrs_all.forEach(function (attribute) {
                            that.need_attributes.push({
                                id: attribute.id,
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
