<form class="" action="{{ route('projects::work_volume::attach_material', $work_volume->id) }}" method="post">
    @csrf
    <input type="hidden" name="is_tongue" value="{{ $is_tongue }}">
    <input id="is_node" type="hidden" name="is_node">

    <div class="materials-container">
        @if ($is_tongue)
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
        @endif
        <div class="row">
            <div class="col-md-6 col-xl-6">
                <label for="">Материал</label>
                <div class="form-group select-accessible-140">
                    <select id="new_material_select" name="manual_material_id" style="width:100%;" required>
                    </select>
                </div>
            </div>
            <div class="col-xl-2 col-md-2" id="new_material_unit_block">
                <label>Единица изм.</label>
                <select id="new_material_unit" class="form-control" name="unit">
                  <option>шт</option>
                  <option>т</option>
                  <option>м.п</option>
                    <option>м²</option>
                    <option>м³</option>
                </select>
            </div>
            <div class="col-xl-2 col-md-2" id="new_material_count_block">
                <label id="material_count" for="count">Количество</label>
                <input id="material_count_input" type="number" name="count" placeholder="Укажите количество" class="form-control" min="0.001" step="0.001" required>
            </div>
            <div class="col-md-2 text-center" style="margin-top: 14px;">
                    <button class="btn-check btn btn-link">
                        <i class="pe-7s-check"></i>
                    </button>
            </div>
        </div>
        <div class="row" style="margin-top:-20px">
            <div class="col-md-12">
                <div class="bootstrap-tagsinput">
                    @foreach($work_volume_materials->where('combine_id', null)->groupBy('node_id') as $node_id => $materials)
                        @if($node_id == '')
                            @foreach($materials as $material)
                                <span class="badge badge-azure">
                                {{ ($material->name) .'; '. number_format($material->count, 3, '.', '') .' '. $material->unit . ';'}}
                                <span onclick="detach_material(this)" mat_id="{{ $material->id }}" data-role="remove" class="badge-remove-link"></span>
                            </span>
                            @endforeach
                        @endif
                    @endforeach
                    @if($work_volume->type == 0)
                        @foreach($complects as $material)
                            <span class="badge badge-azure" style="border-color: #1771F1; color: #1771F1">
                            {{ $material->name .'; '. number_format($material->count, 3, '.', '') .' '. $material->unit . ';' }}
                            <span onclick="detach_compile(this)" complect_id="{{ $material->id }}" data-role="remove" class="badge-remove-link"></span>
                        </span>
                        @endforeach
                    @endif
                    @php $combine_ids = []; @endphp

                    @foreach($work_volume_materials->where('combine_id', '!=', null) as $material)
                        @if(!in_array($material->combine_id, $combine_ids))
                            <span class="badge badge-azure">
                        {{ $material->combine_pile() }}
                        (
                        @foreach($work_volume_materials->where('combine_id' , $material->combine_id) as $key => $item)
                                    @if(!in_array($item->combine_id, $combine_ids))
                                        {{ $item->name . ' + ' }}
                                    @else
                                        {{ $item->name }}
                                    @endif

                                    @php $combine_ids[] = $material->combine_id; @endphp
                                @endforeach
                                )
                                {{  $material->count .' '. $material->unit . ';' }}

                        <span onclick="detach_material(this)" mat_id="{{ $material->id }}" data-role="remove" class="badge-remove-link"></span>
                    </span>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
        @if ($work_volume->type === 0)
        <div class="row" style="margin-top:15px">
            <div class="col-md-12 text-center">
                <button type="button" name="button" class="btn btn-sm btn-warning btn-outline btn-round" data-toggle="modal" data-target="#complect_materials_modal">
                    <i class="fa fa-compress-arrows-alt"></i>
                    Объединение материалов
                </button>
            </div>
        </div>
        @endif
    </div>
</form>


@push('js_footer')
    <script type="text/javascript">
        Vue.component('validation-provider', VeeValidate.ValidationProvider);
        Vue.component('validation-observer', VeeValidate.ValidationObserver);
    </script>
<script>
    $('#new_material_unit').select2()
    var eventHub = new Vue();

    Vue.component('material-item', {
        template: '\
            <div class="row" style="margin-top: 7px;">\
                <div class="col-md-6">\
                    <label :class="[material_index !== 1 ? \'show-mobile-label mt-10__mobile d-block\' : \'mt-10__mobile d-block\']">\
                        Материал <span class="star">*</span>\
                    </label>\
                    <template>\
                      <el-select class="d-block" @change="changeMaterialId" ref="usernameInput" v-model="default_material_id" clearable filterable :remote-method="search" remote size="large" placeholder="Выберите материал">\
                        <el-option\
                          v-for="item in materials"\
                          :label="item.label"\
                          :key="item.id"\
                          :value="item.id">\
                        </el-option>\
                      </el-select>\
                    </template>\
                </div>\
                <div :class="[inputs_length === 1 ? \'col-md-3\' : \'col-md-2\']">\
                    <label for="" :class="[material_index !== 1 ? \'show-mobile-label mt-10__mobile d-block\' : \'mt-10__mobile d-block\']">\
                        Ед. изм. <span class="star">*</span>\
                    </label>\
                    <template>\
                      <el-select class="d-block" @change="changeMaterialUnit" v-model="default_material_unit" placeholder="Ед. измерения">\
                        <el-option\
                          v-for="item in units"\
                          :key="item.id"\
                          :value="item.id"\
                          :label="item.text">\
                        </el-option>\
                      </el-select>\
                    </template>\
                </div>\
                <div class="col-md-3">\
                    <label for="" :class="[material_index !== 1 ? \'show-mobile-label mt-10__mobile d-block\' : \'mt-10__mobile d-block\']">\
                        Количество <span class="star">*</span>\
                    </label>\
                    <template>\
                        <el-input-number class="d-block w-100" @change="changeMaterialCount" :min="0" v-model="default_material_count" :precision="3" :step="0.001" :max="10000000" required></el-input-number>\
                    </template>\
                </div>\
                <div class="col-md-1 text-center" v-if="inputs_length > 1">\
                  <button rel="tooltip" type="button" v-on:click="$emit(\'remove\')" class="btn-remove-mobile" data-original-title="Удалить">\
                      <i style="font-size:18px;" class="fa fa-times" :class="[material_index !== 1 ? \'fa fa-times remove-stroke-index\' : \'fa fa-times remove-stroke\']"></i>\
                  </button>\
                </div>\
            </div>\
          ',
        props: ['material_id', 'material_unit', 'material_count', 'inputs_length', 'material_index', 'materials', 'units'],
        methods: {
            changeMaterialId(value) {
                this.$emit('update:material_id', value);
            },
            changeMaterialUnit(value) {
                this.$emit('update:material_unit', value)
            },
            changeMaterialCount(value) {
                this.$emit('update:material_count', value)
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
                default_material_id: this.material_id,
                default_material_unit: this.material_unit,
                default_material_count: this.material_count,
            }
        }
    });

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
                    that.attrs_all = response.data.attrs;
                    that.attrs_all = that.attrs_all.reverse();

                    that.attrs_all.forEach(function(attribute) {
                        that.need_attributes.push({
                            id: attribute.id,
                            name: attribute.name,
                            unit: attribute.unit,
                            attr_id: attribute.id,
                            category_id: attribute.category_id,
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

                        var new_option = $("<option selected='selected'></option>").val(new_material.id).text(new_material.name);
                        $('#new_material_select').append(new_option).trigger('change');
                        $('#new_material_unit').val(new_material.category.category_unit).trigger('change');

                        //TODO complete
                        eventHub.$emit('addEvent', '');
                        that.need_attributes.map(el => el.value = '');
                        that.observer_key += 1;
                        that.$nextTick(() => {
                            that.$refs.observer.reset();
                        });
                    })
                        .catch(error => this.handleError(error));
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

</script>

@endpush

