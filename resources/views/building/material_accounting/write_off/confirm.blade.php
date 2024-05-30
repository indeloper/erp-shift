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
                        <div class="row" id="author_answer">
                            <div class="col-md-12">
                                <label class="mb-20">Комментарий автора</label>
                                <blockquote>
                                    <p style="font-size:14px">
                                        {{ $operation->comment_author }}
                                    </p>
                                    <div class="row">
                                        <div class="col-md-7">
                                            <a href="#" data-toggle="modal" data-target="#view-author-photo"
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

            <div class="card strpied-tabled-with-hover">
                <div class="card-body">
                    <div class="row" id="sender_answer">
                        <div class="col-md-12 iphone-quote-container">
                            <label class="mb-20">Комментарий исполнителя</label>
                            <blockquote>
                                @if($operation->comment_from)
                                    <p style="font-size:14px">
                                        {{ $operation->comment_from }}
                                    </p>
                                @endif
                                <div class="row">
                                    <div class="col-md-7">
                                        <a href="#" data-toggle="modal" data-target="#view-photo"
                                           class="table-link blockquote-link">
                                            <i class="fa fa-picture-o "></i>
                                            Фото
                                        </a>
                                        <el-button type="text" @click="dialogTableVisible = true" class="iphone-quote">
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
                                        <small>{{ $operation->sender->full_name }}</small>
                                    </div>
                                </div>

                                <div class="clearfix"></div>
                            </blockquote>
                        </div>
                    </div>
                </div>
            </div>

            @if($operation->isAuthor())
                <div class="card">
                    <div class="card-body">
                        <h5 class="materials-info-title">Подтверждение операции</h5>
                        <ol>
                            <li>
                                Проверьте количество отправленного и полученного материала в блоке <i>"Сведения о материалах"</i>.
                            </li>
                            <li>
                                Для того, чтобы внести корректировки, отредактируйте записи в разделе <i>"История перемещения материалов"</i>.
                            </li>
                        </ol>
                        <div id="answer">
                            <div class="card-footer">
                                <div class="row" style="margin-top:20px">
                                    <div class="col-md-12 text-center">
                                        <button type="button" @click="send" class="btn btn-wd btn-info">Подтвердить
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            @endif
        </div>
    </div>

    <template>
        <el-button :plain="true" @click="success_delete">Файл успешно удален.</el-button>
    </template>

    <!-- фото -->
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
                            @foreach($operation->materialFiles->where('type', 2) as $image)

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
                                            <!-- <div class="row">
                                                <label class="col-sm-4 meta-title">Геометка</label>
                                                <div class="col-sm-8">
                                                    <a href="https://yandex.ru/maps/2/saint-petersburg/?ll=30.397923%2C59.808618&mode=whatshere&whatshere%5Bpoint%5D=30.395531%2C59.807651&whatshere%5Bzoom%5D=16.9&z=16.9" class="table-link" target="_blank">
                                                        <span class="meta-content">Шушары, Поселковая улица, 12В</span>
                                                    </a>
                                                </div>
                                            </div> -->
                                            <!-- <div class="row">
                                                <label class="col-sm-4 meta-title">Разрешение</label>
                                                <div class="col-sm-8">
                                                    <span class="meta-content">3264 x 2448</span>
                                                </div>
                                            </div> -->
                                            <!-- <div class="row">
                                                <label class="col-sm-4 meta-title">Размер файла</label>
                                                <div class="col-sm-8">
                                                    <span class="meta-content">2,79 мб</span>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <label class="col-sm-4 meta-title">Устройство</label>
                                                <div class="col-sm-8">
                                                    <span class="meta-content">iphone 5s</span>
                                                </div>
                                            </div> -->
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

    <div class="modal fade bd-example-modal-lg show" id="view-author-photo" role="dialog" aria-labelledby="modal-search"
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
        <el-button :plain="true" @click="noSuchMaterials">Файл успешно удален.</el-button>
    </template>

@endsection


@section('js_footer')
    <script type="text/javascript">
        Vue.component('validation-provider', VeeValidate.ValidationProvider);
        Vue.component('validation-observer', VeeValidate.ValidationObserver);
    </script>
    <script type="text/javascript">

{{--        Vue.component('material-item', {--}}
{{--            template: '\--}}
{{--          <div class="form-row" style="margin-top: 7px;">\--}}
{{--              <div class="col-md-5">\--}}
{{--                  <label :class="[material_index !== 1 ? \'show-mobile-label mt-10__mobile\' : \'mt-10__mobile\']">\--}}
{{--                      Материал <span class="star">*</span>\--}}
{{--                  </label>\--}}
{{--                  <template>\--}}
{{--                    <el-select @change="changeMaterialId" ref="usernameInput" v-model="default_material_id" clearable filterable :remote-method="search" remote size="large" placeholder="Выберите материал">\--}}
{{--                      <el-option\--}}
{{--                        v-for="item in new_materials_filtered"\--}}
{{--                        :label="item.label"\--}}
{{--                        :key="`${item.id}_${item.used ? 1 : 0}`"\--}}
{{--                        :value="`${item.id}_${item.used ? 1 : 0}`">\--}}
{{--                      </el-option>\--}}
{{--                    </el-select>\--}}
{{--                  </template>\--}}
{{--              </div>\--}}
{{--              <div class="col-md-1 align-self-end text-center">\--}}
{{--                <button data-toggle="modal" data-target="#material-notes" @click="() => { materialNotes().changeMaterialInput(this); hideTooltips(); }"\--}}
{{--                        @mouseleave="hideTooltips" type="button"\--}}
{{--                        data-balloon-pos="up" :aria-label="notesLabel"\--}}
{{--                        data-balloon-length="medium"\--}}
{{--                        :disabled="!material_id"\--}}
{{--                        class="btn btn-link btn-xs pd-0 mt-10__mobile mr-1" style="height: 40px;"\--}}
{{--                        :class="material_id && comments.length > 0 ? \'btn-danger\' : \' btn-secondary\'">\--}}
{{--                    <i style="font-size:18px;" class="fa fa-info-circle"></i>\--}}
{{--                </button>\--}}
{{--                <button data-toggle="modal" data-target="#description" @click="() => { getDescription(); hideTooltips(); }"\--}}
{{--                        @mouseleave="hideTooltips" type="button"\--}}
{{--                        data-balloon-pos="up" aria-label="Описание категории материала"\--}}
{{--                        :disabled="!material_id"\--}}
{{--                        class="btn btn-link btn-xs pd-0 mt-10__mobile" style="height: 40px;"\--}}
{{--                        :class="material_id ? \'btn-primary\' : \' btn-secondary\'">\--}}
{{--                    <i style="font-size:18px;" class="fa fa-info-circle"></i>\--}}
{{--                </button>\--}}
{{--              </div>\--}}
{{--              <div class="col-md-2">\--}}
{{--                  <label for="" :class="[material_index !== 1 ? \'show-mobile-label mt-10__mobile\' : \'mt-10__mobile\']">\--}}
{{--                      Ед. изм. <span class="star">*</span>\--}}
{{--                  </label>\--}}
{{--                  <template>\--}}
{{--                    <el-select @change="changeMaterialUnit" v-model="default_material_unit" placeholder="Ед. измерения">\--}}
{{--                      <el-option\--}}
{{--                        v-for="item in units"\--}}
{{--                        :key="item.id"\--}}
{{--                        :value="item.id"\--}}
{{--                        :label="item.text">\--}}
{{--                      </el-option>\--}}
{{--                    </el-select>\--}}
{{--                  </template>\--}}
{{--              </div>\--}}
{{--              <div class="col-md-2">\--}}
{{--                  <label for="" :class="[material_index !== 1 ? \'show-mobile-label mt-10__mobile\' : \'mt-10__mobile\']">\--}}
{{--                      Количество <span class="star">*</span>\--}}
{{--                  </label>\--}}
{{--                  <template>\--}}
{{--                      <el-input-number :min="0" @change="changeMaterialCount" v-model="default_material_count" :precision="3" :step="0.001" :max="10000000" required></el-input-number>\--}}
{{--                  </template>\--}}
{{--              </div>\--}}
{{--              <div class="col-md-1">\--}}
{{--                <label for="" :class="[material_index !== 1 ? \'show-mobile-label mt-10__mobile\' : \'mt-10__mobile\']">\--}}
{{--                    Б/У\--}}
{{--                </label>\--}}
{{--                    <template>\--}}
{{--                        <el-checkbox v-model="default_material_used"\--}}
{{--                           border class="d-block"\--}}
{{--                           @can('mat_acc_base_move_to_new') @change="changeUsageValue" @endcan @cannot('mat_acc_base_move_to_new') disabled @endcannot--}}
{{--                ></el-checkbox>\--}}
{{--           </template>\--}}
{{--          </div>\--}}
{{--              <div class="col-md-1 text-center" v-if="inputs_length > 1">\--}}
{{--                <button rel="tooltip" type="button" v-on:click="$emit(\'remove\')" class="btn-remove-mobile" data-original-title="Удалить">\--}}
{{--                    <i style="font-size:18px;" :class="[material_index !== 1 ? \'fa fa-times remove-stroke-index\' : \'fa fa-times remove-stroke\']"></i>\--}}
{{--                </button>\--}}
{{--              </div>\--}}
{{--          </div>\--}}
{{--        ',--}}
{{--            props: ['material_id', 'material_unit', 'material_count', 'inputs_length', 'material_index', 'used', 'materials', 'units', 'material_input'],--}}
{{--            computed: {--}}
{{--                notesLabel() {--}}
{{--                    if (!this.material_id) {--}}
{{--                        return 'Примечания';--}}
{{--                    }--}}
{{--                    if (this.comments && this.comments.length > 0) {--}}
{{--                        const commentsString = this.comments.map(comment => comment.comment).join(', ');--}}
{{--                        if (commentsString.length > 90) {--}}
{{--                            return commentsString.slice(0, 90) + '... см. полный список примечаний в справочнике.';--}}
{{--                        } else {--}}
{{--                            return commentsString;--}}
{{--                        }--}}
{{--                    } else {--}}
{{--                        return 'Вы можете добавить к этому материалу примечания';--}}
{{--                    }--}}
{{--                },--}}
{{--                new_materials_filtered() {--}}
{{--                    return this.materials--}}
{{--                            .filter(el => {--}}
{{--                                const count = materials.material_inputs.filter(input => input.material_id == el.id && input.used == el.used).length;--}}
{{--                                return count < 1 || String(this.default_material_id).split('_')[0] === el.id && String(this.default_material_id).split('_')[1] == el.used;--}}
{{--                            });--}}
{{--                }--}}
{{--            },--}}
{{--            methods: {--}}
{{--                changeMaterialId(value) {--}}
{{--                    this.$emit('update:material_id', value.split('_')[0]);--}}
{{--                    if (value) {--}}
{{--                        let mat = this.materials.filter(input => input.id == value.split('_')[0]--}}
{{--                            && input.used == value.split('_')[1])[0];--}}
{{--                        let used = (!mat || mat.used === undefined ? false : mat.used);--}}
{{--                        this.changeUsageValue(used);--}}
{{--                        this.default_material_used = used;--}}
{{--                        this.loadComments(mat);--}}
{{--                        this.getDescription();--}}

{{--                        let unit = (!mat || mat.unit === undefined ? null : mat.unit);--}}
{{--                        this.autoChangeUnit(unit)--}}
{{--                    } else {--}}
{{--                        this.changeUsageValue(false);--}}
{{--                        this.default_material_used = false;--}}
{{--                    }--}}
{{--                },--}}
{{--                changeMaterialUnit(value) {--}}
{{--                    this.$emit('update:material_unit', value);--}}
{{--                },--}}
{{--                changeMaterialCount(value) {--}}
{{--                    this.$emit('update:material_count', value);--}}
{{--                },--}}
{{--                changeUsageValue(value) {--}}
{{--                    this.$emit('update:used', value);--}}
{{--                },--}}
{{--                autoChangeUnit(unit)--}}
{{--                {--}}
{{--                    this.changeMaterialUnit(unit);--}}
{{--                    this.default_material_unit = unit;--}}
{{--                },--}}
{{--                search(query) {--}}
{{--                    const that = this;--}}
{{--                    console.log(query);--}}

{{--                    if (query !== '') {--}}
{{--                        setTimeout(() => {--}}
{{--                            axios.post('{{ route('building::mat_acc::report_card::get_materials') }}', {--}}
{{--                                q: query,--}}
{{--                                base_id: {{ $operation->object_id_from }}}).then(function (response) {--}}
{{--                                materials.material_inputs[that.inputs_length - 1].materials = response.data--}}
{{--                            })--}}
{{--                        }, 1000);--}}
{{--                    } else {--}}
{{--                        materials.material_inputs[that.inputs_length - 1].materials = []--}}
{{--                    }--}}
{{--                },--}}
{{--                getDescription() {--}}
{{--                    let that = this;--}}

{{--                    if (String(that.default_material_id)) {--}}
{{--                        axios.post('{{ route('building::mat_acc::get_material_category_description') }}', {id: String(that.default_material_id).split('_')[0]}).then(function (response) {--}}
{{--                                that.default_material_description = response.data.message;--}}
{{--                                that.documents = response.data.documents;--}}
{{--                            }).catch((err)=>{});--}}
{{--                    } else {--}}
{{--                        that.default_material_description = 'Нет описания';--}}
{{--                        that.documents = [];--}}
{{--                    }--}}

{{--                    descriptionModal.material = this;--}}
{{--                },--}}
{{--                hideTooltips() {--}}
{{--                    for (let ms = 50; ms <= 1050; ms += 100) {--}}
{{--                        setTimeout(() => {--}}
{{--                            $('[data-balloon-pos]').blur();--}}
{{--                        }, ms);--}}
{{--                    }--}}
{{--                },--}}
{{--                materialNotes() {--}}
{{--                    return materialNotes;--}}
{{--                },--}}
{{--                locationFromName() {--}}
{{--                    if (typeof(vm) !== 'undefined') {--}}
{{--                        return vm.$refs['search_from'] ? vm.$refs['search_from'].query : null;--}}
{{--                    } else if (typeof(this.predefinedLocation) !== 'undefined') {--}}
{{--                        return this.predefinedLocation;--}}
{{--                    }--}}
{{--                    return null;--}}
{{--                },--}}
{{--                materialName() {--}}
{{--                    return this.$refs['usernameInput'] ? this.$refs['usernameInput'].query : null;--}}
{{--                },--}}
{{--                loadComments(mat) {--}}
{{--                    if (typeof(mat) !== 'undefined' && mat.base_id) {--}}
{{--                        axios.get('{{ route('building::mat_acc::report_card::get_base_comments') }}', { params: { base_id: mat.base_id }})--}}
{{--                        .then(response => {--}}
{{--                            this.id = mat.base_id;--}}
{{--                            this.comments = response.data.comments;--}}
{{--                        })--}}
{{--                        .catch(error => console.log(error));--}}
{{--                    }--}}
{{--                }--}}
{{--            },--}}
{{--            data: function () {--}}
{{--                return {--}}
{{--                    default_material_id: materials.material_inputs[this.inputs_length - 1].material_id,--}}
{{--                    default_material_unit: materials.material_inputs[this.inputs_length - 1].material_unit,--}}
{{--                    default_material_count: materials.material_inputs[this.inputs_length - 1].material_count,--}}
{{--                    default_material_used: materials.material_inputs[this.inputs_length - 1].used,--}}
{{--                    default_material_description: 'Нет описания',--}}
{{--                    documents: [],--}}
{{--                    comments: [],--}}
{{--                    id: null,--}}
{{--                    predefinedLocation: {!! json_encode($operation->object_from->name_tag) !!}--}}
{{--                }--}}
{{--            },--}}
{{--            mounted() {--}}
{{--                if (this.default_material_id) {--}}
{{--                    this.changeMaterialId(this.default_material_id);--}}
{{--                }--}}
{{--            }--}}
{{--        })--}}

{{--        var materials = new Vue({--}}
{{--            el: '#materials',--}}
{{--            data: {--}}
{{--                options: [],--}}
{{--                selected: '',--}}
{{--                material_unit: '',--}}
{{--                next_mat_id: 1,--}}
{{--                material_inputs: [],--}}
{{--                units: {!! json_encode($operation->materials()->getModel()::$main_units) !!},--}}
{{--                exist_materials: {!! $operation->materials->where('type', 1) !!}--}}
{{--            },--}}
{{--            created: function () {--}}
{{--                const that = this;--}}

{{--                axios.post('{{ route('building::mat_acc::report_card::get_materials') }}', {base_id: {{ $operation->object_id_from }}, material_ids: {{ $operation->materials->pluck('manual_material_id') }} }).then(function (response) {--}}
{{--                    that.new_materials = response.data;--}}

{{--                    Object.keys(that.exist_materials).map(function (key) {--}}
{{--                        if (!that.inArray(that.new_materials, {id: that.exist_materials[key].manual_material_id, used: that.exist_materials[key].used})) {--}}
{{--                            that.new_materials.push({--}}
{{--                                id: that.exist_materials[key].manual_material_id,--}}
{{--                                label: that.exist_materials[key].manual.name--}}
{{--                            })--}}
{{--                        }--}}

{{--                        setTimeout(() => {--}}
{{--                            that.material_inputs.push({--}}
{{--                                id: that.next_mat_id++,--}}
{{--                                material_id: `${that.exist_materials[key].manual_material_id}_${that.exist_materials[key].used ? 1 : 0}`,--}}
{{--                                material_unit: that.exist_materials[key].unit,--}}
{{--                                material_count: Number(that.exist_materials[key].count),--}}
{{--                                used: that.exist_materials[key].used,--}}
{{--                                units: that.units,--}}
{{--                                materials: that.new_materials--}}
{{--                            });--}}
{{--                        }, 200)--}}
{{--                    });--}}
{{--                });--}}
{{--            },--}}
{{--            methods: {--}}
{{--                add_material() {--}}
{{--                    const that = this;--}}

{{--                    axios.post('{{ route('building::mat_acc::report_card::get_materials') }}', {base_id: {{ $operation->object_id_from }}}).then(function (response) {--}}
{{--                        that.new_materials = response.data--}}

{{--                        that.material_inputs.push({--}}
{{--                            id: that.next_mat_id++,--}}
{{--                            material_id: '',--}}
{{--                            material_unit: '',--}}
{{--                            material_label: '',--}}
{{--                            material_count: '',--}}
{{--                            used: false,--}}
{{--                            units: that.units,--}}
{{--                            materials: that.new_materials--}}
{{--                        });--}}
{{--                    });--}}
{{--                },--}}
{{--                inArray: function (array, element) {--}}
{{--                    var length = array.length;--}}
{{--                    for (var i = 0; i < length; i++) {--}}
{{--                        if (array[i].id == element.id && (element.used === undefined || array[i].used == element.used)) return true;--}}
{{--                    }--}}
{{--                    return false;--}}
{{--                }--}}
{{--            }--}}
{{--        })--}}


    var answer = new Vue({
        el: '#answer',
        data: {
            csrf: "{{ csrf_token() }}",
            imageList: [],
            docsList: [],
            dialogImageUrl: '',
            dialogVisible: false,
            comment : '',
            request: {author_type: 3}
        },
        methods: {
            onSuccess: function (response, file, fileList) {
                console.log(response);
            },
            remove_file: function (file, fileLis) {
                axios.post('{{ route('building::mat_acc::delete_file', $operation->id) }}', {file_name: file.file_name}).then(function (response) {
                    if (response.data) {
                        answer.$message({
                          showClose: true,
                          message: 'Файл успешно удален.',
                          type: 'success'
                        });
                    }
                });
            },
            imagePreview: function(file) {
                this.dialogImageUrl = file.url;
                this.dialogVisible = true;
            },
            send() {
                swal({
                    title: 'Вы уверены?',
                    html: "Весь материал будет списан с объекта.",
                    type: 'warning',
                    showCancelButton: true,
                    cancelButtonText: 'Назад',
                    confirmButtonText: 'Подтверждаю, всё верно.'
                }).then((result) => {
                    if (result.value) {
                        axios.post('{{ route('building::mat_acc::write_off::accept', $operation->id) }}').then(function (response) {
                            if (!response.data.message) {
                                window.location = '{{ route('building::mat_acc::operations') }}';
                            } else {
                                answer.$message({
                                    showClose: true,
                                    message: response.data.message,
                                    type: 'error',
                                    duration: 10000
                                });
                            }
                        });
                    }
                });
            },
            {{--sendAndAdd() {--}}
            {{--    axios.post('{{ route('building::mat_acc::' . $operation->english_type_name . '::accept', $operation->id) }}', {materials: materials.material_inputs, comment: answer.comment}).then(function (response) {--}}
            {{--        if (!response.data.message) {--}}
            {{--            window.location = '{!! route('building::mat_acc::' . $operation->english_type_name . '::create',--}}
            {{--             ['parent_id' => $operation->id,--}}
            {{--             'resp' => $operation->responsible_users->where('type', 0)->first()->user->id,--}}
            {{--             'obj' => $operation->object_id_from--}}
            {{--             ])   !!}';--}}
            {{--            } else {--}}
            {{--                answer.$message({--}}
            {{--                    showClose: true,--}}
            {{--                    message: response.data.message,--}}
            {{--                    type: 'error',--}}
            {{--                    duration: 10000--}}
            {{--                });--}}
            {{--            }--}}
            {{--        });--}}
            {{--    },--}}
            }
        })

        var sender_answer = new Vue({
            el: '#sender_answer',
            data: {
                docsList: {!! json_encode(array_values($operation->materialFiles->whereIn('type', [0, 1])->toArray())) !!},
                dialogTableVisible: false,
            },
            methods: {
                url_to_doc: function (index, row) {
                    window.open(row.url, '_blank');
                }
            }
        });
        var author_answer = new Vue({
            el: '#author_answer',
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
