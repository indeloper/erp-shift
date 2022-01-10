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
            <!-- moving story -->
            @if($operation->materialsPartTo()->count() || $operation->materialsPartFrom()->count())
                <div class="card tasks-sidebar__item strpied-tabled-with-hover" style="margin-bottom:30px">
                    <div class="card-body story-collapse-card">
                        <div class="accordions">
                            <div class="card" style="margin-bottom:0">
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


        <!-- фото -->
            <div class="modal fade bd-example-modal-lg show" id="view-photo-sender" role="dialog"
                 aria-labelledby="modal-search" style="display: none;">
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
                                    @foreach($operation->images_sender as $image)

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

            <div class="modal fade bd-example-modal-lg show" id="view-photo-recipient" role="dialog"
                 aria-labelledby="modal-search" style="display: none;">
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
                                    @foreach($operation->images_recipient as $image)

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
        </div>
    </div>

@endsection


@section('js_footer')

    <script>

        {{--Vue.component('material-item', {--}}
        {{--    template: '\--}}
        {{--  <div class="form-row" style="margin-top: 7px;">\--}}
        {{--      <div class="col-md-4">\--}}
        {{--          <label v-if="material_index === 1">\--}}
        {{--              Материал <span class="span">*</span>\--}}
        {{--          </label>\--}}
        {{--          <template>\--}}
        {{--            <el-select @change="changeMaterialId" ref="usernameInput" v-model="default_material_id" clearable filterable :remote-method="search" @clear="search(``)" remote size="large" placeholder="Выберите материал">\--}}
        {{--              <el-option\--}}
        {{--                v-for="item in new_materials_filtered"\--}}
        {{--                :label="item.label"\--}}
        {{--                :key="`${item.id}_${item.used ? 1 : 0}`"\--}}
        {{--                :value="`${item.id}_${item.used ? 1 : 0}`">\--}}
        {{--              </el-option>\--}}
        {{--            </el-select>\--}}
        {{--          </template>\--}}
        {{--      </div>\--}}
        {{--      <div class="col-md-1 align-self-end text-center">\--}}
        {{--        <button data-toggle="modal" data-target="#material-notes" @click="() => { materialNotes().changeMaterialInput(this); hideTooltips(); }"\--}}
        {{--                @mouseleave="hideTooltips" type="button"\--}}
        {{--                data-balloon-pos="up" :aria-label="notesLabel"\--}}
        {{--                data-balloon-length="medium"\--}}
        {{--                :disabled="!material_id"\--}}
        {{--                class="btn btn-link btn-xs pd-0 mt-10__mobile mr-1" style="height: 40px;"\--}}
        {{--                :class="material_id && comments.length > 0 ? \'btn-danger\' : \' btn-secondary\'">\--}}
        {{--            <i style="font-size:18px;" class="fa fa-info-circle"></i>\--}}
        {{--        </button>\--}}
        {{--        <button data-toggle="modal" data-target="#description" @click="() => { getDescription(); hideTooltips(); }"\--}}
        {{--                @mouseleave="hideTooltips" type="button"\--}}
        {{--                data-balloon-pos="up" aria-label="Описание категории материала"\--}}
        {{--                :disabled="!material_id"\--}}
        {{--                class="btn btn-link btn-xs pd-0 mt-10__mobile" style="height: 40px;"\--}}
        {{--                :class="material_id ? \'btn-primary\' : \' btn-secondary\'">\--}}
        {{--            <i style="font-size:18px;" class="fa fa-info-circle"></i>\--}}
        {{--        </button>\--}}
        {{--        </div>\--}}
        {{--      <div class="col-md-2">\--}}
        {{--          <label for="" style="margin-bottom:7" v-if="material_index === 1">\--}}
        {{--              Ед. измерения <span class="span">*</span>\--}}
        {{--          </label>\--}}
        {{--          <template>\--}}
        {{--            <el-select @change="changeMaterialUnit" v-model="default_material_unit" placeholder="Ед. измерения">\--}}
        {{--              <el-option\--}}
        {{--                v-for="item in units"\--}}
        {{--                :key="item.id"\--}}
        {{--                :value="item.id"\--}}
        {{--                :label="item.text">\--}}
        {{--              </el-option>\--}}
        {{--            </el-select>\--}}
        {{--          </template>\--}}
        {{--      </div>\--}}
        {{--      <div :class="[inputs_length === 1 ? \'col-md-3\' : \'col-md-2\']">\--}}
        {{--          <label for="" v-if="material_index === 1">\--}}
        {{--              Количество <span class="span">*</span>\--}}
        {{--          </label>\--}}
        {{--          <template>\--}}
        {{--              <el-input-number @change="changeMaterialCount" :min="0" v-model="default_material_count" :precision="3" :step="0.001" :max="10000000" required></el-input-number>\--}}
        {{--          </template>\--}}
        {{--      </div>\--}}
        {{--      <div class="col-md-1">\--}}
        {{--        <label for="" :class="[material_index !== 1 ? \'show-mobile-label mt-10__mobile\' : \'mt-10__mobile\']">\--}}
        {{--            Б/У\--}}
        {{--        </label>\--}}
        {{--        <template>\--}}
        {{--            <el-checkbox v-model="default_material_used"\--}}
        {{--               border class="d-block"\--}}
        {{--               @can('mat_acc_base_move_to_new') @change="changeUsageValue" @endcan @cannot('mat_acc_base_move_to_new') disabled @endcannot></el-checkbox>\--}}
        {{--        </template>\--}}
        {{--    </div>\--}}
        {{--        <div class="col-md-1 text-center" v-if="inputs_length > 1">\--}}
        {{--          <button rel="tooltip" type="button" v-on:click="$emit(\'remove\')" class="btn-danger btn-link btn pd-0 mn-0" data-original-title="Удалить">\--}}
        {{--              <i style="font-size:18px;" class="fa fa-times" :class="[material_index === 1 ? \'remove-stroke\' : \'m-2\']"></i>\--}}
        {{--          </button>\--}}
        {{--        </div>\--}}
        {{--    </div>\--}}
        {{--  ',--}}
        {{--    props: ['material_id', 'material_unit', 'material_count', 'inputs_length', 'used', 'material_index', 'materials', 'units', 'index', 'material_input', 'used'],--}}
        {{--    computed: {--}}
        {{--        new_materials_filtered() {--}}
        {{--            return this.materials--}}
        {{--                .filter(el => {--}}
        {{--                    const count = materials.material_inputs.filter(input => input.material_id == el.id && input.used == el.used).length;--}}
        {{--                    return count < 1 || String(this.default_material_id).split('_')[0] == el.id && String(this.default_material_id).split('_')[1] == el.used;--}}
        {{--                });--}}
        {{--        },--}}
        {{--        notesLabel() {--}}
        {{--            if (!this.material_id) {--}}
        {{--                return 'Примечания';--}}
        {{--            }--}}
        {{--            if (this.comments && this.comments.length > 0) {--}}
        {{--                const commentsString = this.comments.map(comment => comment.comment).join(', ');--}}
        {{--                if (commentsString.length > 90) {--}}
        {{--                    return commentsString.slice(0, 90) + '... см. полный список примечаний в справочнике.';--}}
        {{--                } else {--}}
        {{--                    return commentsString;--}}
        {{--                }--}}
        {{--            } else {--}}
        {{--                return 'Вы можете добавить к этому материалу примечания';--}}
        {{--            }--}}
        {{--        }--}}
        {{--    },--}}
        {{--    methods: {--}}
        {{--        changeMaterialId(value) {--}}
        {{--            this.$emit('update:material_id', value.split('_')[0]);--}}
        {{--            this.getDescription();--}}

        {{--            if (value) {--}}
        {{--                let mat = this.materials.filter(input => input.id == value.split('_')[0]--}}
        {{--                    && input.used == value.split('_')[1])[0];--}}
        {{--                let used = (!mat || mat.used === undefined ? false : mat.used);--}}
        {{--                this.changeUsageValue(used);--}}
        {{--                this.default_material_used = used;--}}
        {{--                this.loadComments(mat);--}}

        {{--                let unit = (!mat || mat.unit === undefined ? null : mat.unit);--}}
        {{--                this.autoChangeUnit(unit);--}}
        {{--            } else {--}}
        {{--                this.changeUsageValue(false);--}}
        {{--                this.default_material_used = false;--}}
        {{--            }--}}
        {{--        },--}}
        {{--        changeMaterialUnit(value) {--}}
        {{--            this.$emit('update:material_unit', value)--}}
        {{--        },--}}
        {{--        changeMaterialCount(value) {--}}
        {{--            this.$emit('update:material_count', value)--}}
        {{--        },--}}
        {{--        changeUsageValue(value) {--}}
        {{--            this.$emit('update:used', value)--}}
        {{--        },--}}
        {{--        autoChangeUnit(unit)--}}
        {{--        {--}}
        {{--            this.changeMaterialUnit(unit);--}}
        {{--            this.default_material_unit = unit;--}}
        {{--        },--}}
        {{--        search(query) {--}}
        {{--            const that = this;--}}
        {{--            axios.post('{{ route('building::mat_acc::report_card::get_materials') }}', {--}}
        {{--                q: query,--}}
        {{--                base_id: {{ $operation->object_id_to }}}).then(function (response) {--}}
        {{--                materials.material_inputs[that.index].materials = response.data--}}
        {{--            })--}}
        {{--        },--}}
        {{--        getDescription() {--}}
        {{--            let that = this;--}}

        {{--            if (String(that.default_material_id)) {--}}
        {{--                axios.post('{{ route('building::mat_acc::get_material_category_description') }}', {id: String(that.default_material_id).split('_')[0]}).then(function (response) {--}}
        {{--                        that.default_material_description = response.data.message;--}}
        {{--                        that.documents = response.data.documents;--}}
        {{--                    }).catch((err)=>{});--}}
        {{--                } else {--}}
        {{--                    that.default_material_description = 'Нет описания';--}}
        {{--                    that.documents = [];--}}
        {{--                }--}}

        {{--                descriptionModal.material = this;--}}
        {{--            },--}}
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

        {{--    },--}}
        {{--    data: function () {--}}
        {{--        return {--}}
        {{--            default_material_id: materials.material_inputs[this.inputs_length - 1].material_id,--}}
        {{--            default_material_unit: materials.material_inputs[this.inputs_length - 1].material_unit,--}}
        {{--            default_material_count: materials.material_inputs[this.inputs_length - 1].material_count,--}}
        {{--            default_material_used: materials.material_inputs[this.inputs_length - 1].used,--}}
        {{--            default_material_description: 'Нет описания',--}}
        {{--            predefinedLocation: {!! json_encode($operation->object_from->name_tag) !!},--}}
        {{--            documents: [],--}}
        {{--            comments: [],--}}
        {{--            id: null--}}
        {{--        }--}}
        {{--    },--}}
        {{--    mounted() {--}}
        {{--        if (this.default_material_id) {--}}
        {{--            this.changeMaterialId(this.default_material_id);--}}
        {{--        }--}}
        {{--    }--}}
        {{--})--}}

        {{--var materials = new Vue({--}}
        {{--    el: '#materials',--}}
        {{--    data: {--}}
        {{--        options: [],--}}
        {{--        selected: '',--}}
        {{--        material_unit: '',--}}
        {{--        next_mat_id: 1,--}}
        {{--        material_inputs: [],--}}
        {{--        units: {!! json_encode($operation->materials()->getModel()::$main_units) !!},--}}
        {{--        exist_materials: {!! $operation->materials->where('type', 2) !!}--}}
        {{--    },--}}
        {{--    created: function () {--}}
        {{--        const that = this;--}}

        {{--        axios.post('{{ route('building::mat_acc::report_card::get_materials') }}', {base_id: {{ $operation->object_id_to }}, material_ids: {{ $operation->materials->pluck('manual_material_id') }} }).then(function (response) {--}}
        {{--            that.new_materials = response.data;--}}

        {{--            Object.keys(that.exist_materials).map(function (key) {--}}
        {{--                if (!that.inArray(that.new_materials, {id: that.exist_materials[key].manual_material_id, used: that.exist_materials[key].used})) {--}}
        {{--                    that.new_materials.push({--}}
        {{--                        id: that.exist_materials[key].manual_material_id,--}}
        {{--                        label: that.exist_materials[key].manual.name + (that.exist_materials[key].used ? ' Б/У' : ''),--}}
        {{--                        used: that.exist_materials[key].used,--}}
        {{--                    })--}}
        {{--                }--}}

        {{--                setTimeout(() => {--}}
        {{--                    that.material_inputs.push({--}}
        {{--                        id: that.next_mat_id++,--}}
        {{--                        material_id: `${that.exist_materials[key].manual_material_id}_${that.exist_materials[key].used ? 1 : 0}`,--}}
        {{--                        material_unit: that.exist_materials[key].unit,--}}
        {{--                        material_count: Number(that.exist_materials[key].count),--}}
        {{--                        used: that.exist_materials[key].used,--}}
        {{--                        units: that.units,--}}
        {{--                        materials: that.new_materials--}}
        {{--                    });--}}
        {{--                }, 200)--}}
        {{--            });--}}
        {{--        });--}}
        {{--    },--}}
        {{--    methods: {--}}
        {{--        add_material() {--}}
        {{--            const that = this;--}}

        {{--            axios.post('{{ route('building::mat_acc::report_card::get_materials') }}', {base_id: {{ $operation->object_id_to }}}).then(function (response) {--}}
        {{--                that.new_materials = response.data--}}

        {{--                that.material_inputs.push({--}}
        {{--                    id: that.next_mat_id++,--}}
        {{--                    material_id: '',--}}
        {{--                    material_unit: '',--}}
        {{--                    material_label: '',--}}
        {{--                    material_count: '',--}}
        {{--                    used: false,--}}
        {{--                    units: that.units,--}}
        {{--                    materials: that.new_materials--}}
        {{--                });--}}
        {{--            });--}}
        {{--        },--}}
        {{--        inArray: function (array, element) {--}}
        {{--            var length = array.length;--}}
        {{--            for (var i = 0; i < length; i++) {--}}
        {{--                if (array[i].id == element.id && (element.used === undefined || array[i].used == element.used)) return true;--}}
        {{--            }--}}
        {{--            return false;--}}
        {{--        }--}}
        {{--    }--}}
        {{--})--}}

        var answer = new Vue({
        el: '#answer',
        data: {
            csrf: "{{ csrf_token() }}",
            imageList: [],
            docsList: [],
            dialogImageUrl: '',
            dialogVisible: false,
            comment : '',
            request: {author_type: 1}
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
                    html: "Данное действие нельзя отменить.<br> {!! $conflicts == true ? " <b> Обратите внимание</b>, что было отправлено и получено разное количество материала!<br> Вы можете отредактировать запись в истории перемещении материалов." : "" !!}",
                    type: 'warning',
                    showCancelButton: true,
                    cancelButtonText: 'Назад',
                    confirmButtonText: 'Подтверждаю, всё верно.'
                }).then((result) => {
                    if (result.value) {
                        axios.post('{{ route('building::mat_acc::moving::accept', $operation->id) }}').then(function (response) {
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
            {{--             'from_resp' => $operation->responsible_users->where('type', 1)->first()->user->id,--}}
            {{--             'from_obj' => $operation->object_id_from,--}}
            {{--             'to_resp' => $operation->responsible_users->where('type', 2)->first()->user->id,--}}
            {{--             'to_obj' => $operation->object_id_to--}}
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
        $("body").on('DOMSubtreeModified', ".materials", function () {
            $('.materials').children('.row:first-child').find('label').removeClass('show-mobile-label');
            $('.materials').children('.row:first-child').find('.btn-remove-mobile').children().removeClass('remove-stroke-index').addClass('remove-stroke');
        });
    </script>

@endsection
