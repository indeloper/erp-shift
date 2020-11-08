<div class="modal fade bd-example-modal-lg show" id="calc-tongue" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Расчет шпунтового ограждения</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <hr style="margin-top:0">
                <div class="card border-0" >
                    <div class="card-body ">
                        <div class="row" style="margin-bottom:30px">
                            <div class="col-md-12 mr-auto ml-auto">
                                <div class="row" id="add_new_material_via_category_calc">
                                    <div class="col-md-12 col-xl-12">
                                        <template>
                                            <el-form label-position="top">
                                                <validation-observer ref="observer" :key="observer_key">
                                                    <label class="d-block">Категория<span class="star">*</span></label>
                                                    <validation-provider rules="required" vid="select-category"
                                                                         ref="select-category" v-slot="v">
                                                        <el-select v-model="category_id" :class="v.classes" @change="getNeedAttributes" placeholder="Выберите категорию материала">
                                                            <el-option
                                                                v-for="item in categories_for_calc"
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
                                <hr>
                                <form id="form_tongue_calc" action="{{ route('projects::work_volume::create_tongue_calc', $work_volume->id) }}" method="post">
                                    @csrf
                                    <div class="materials-container">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label for="">Количество котлованов<star class="star">*</star></label>
                                            </div>
                                            <div class="col-md-2">
                                                <input type="number" id="multiplier" name="multiplier" class="form-control" value="1" step="1" min="1" max="1000" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label for="">Периметр ограждения, м.п<star class="star">*</star></label>
                                            </div>
                                            <div class="col-md-8">
                                                <input type="number" id="perimeter_calc" name="perimeter" class="form-control" step="0.01" min="0" maxlength="10" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label for="">Тип шпунта<star class="star">*</star></label>
                                            </div>
                                            <div class="col-md-4">
                                                <select name="tongue_type" id="get_tongue" style="width:100%;" class="selectpicker" data-title="Выберите шпунт" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                                                    <option value="none">Шпунт</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-check form-check-radio">
                                                    <label class="form-check-label">
                                                        <input class="form-check-input" type="radio" name="is_out" value="0" checked>
                                                        <span class="form-check-sign"></span>
                                                        Конструкт.
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-check form-check-radio">
                                                    <label class="form-check-label">
                                                        <input class="form-check-input" type="radio" name="is_out" value="1">
                                                        <span class="form-check-sign"></span>
                                                        Извлекаем.
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label for="">Количество по проекту, шт  </label>
                                            </div>
                                            <div class="col-md-4">
                                                <input type="number" name="project_count" class="form-control" min="0" maxlength="10">
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-check">
                                                    <label class="form-check-label" style="margin-top:12px">
                                                        <input class="form-check-input" type="checkbox" name="is_required_count" value="1">
                                                        <span class="form-check-sign"></span>
                                                        Обязательное условие
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label for="">Количество шпунта, шт</label>
                                            </div>
                                            <div class="col-md-8">
                                                <input type="number" id="calc_tongue_count" readonly name="tongue_count" class="form-control" min="0" maxlength="10">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label for="">Дополнительный шпунт, шт</label>
                                            </div>
                                            <div class="col-md-8">
                                                <input type="number" id="extra_count_tongue" name="extra_count" class="form-control" min="0" maxlength="10">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label for="">Вид погружения<star class="star">*</star></label>
                                            </div>
                                            <div class="col-md-8">
                                                <select name="dive_type" class="selectpicker" data-title="Выберите вид погружения" data-style="btn-default btn-outline" data-menu-style="dropdown-blue">
                                                    <option value="static">Статика</option>
                                                    <option selected value="vibro">Вибро</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row" id="type_angle">
                                            <div class="col-md-4">
                                                <label for="">Тип углового элемента</label>
                                            </div>
                                            <div class="col-md-4">
                                                <select name="type_angle" id="get_angle" style="width:100%;" class="selectpicker" data-title="Выберите тип углового элемента" data-style="btn-default btn-outline" data-menu-style="dropdown-blue">
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row"  id="number_angle" style="display:none">
                                            <div class="col-md-4">
                                                <label for="">Количество углового элемента, шт</label>
                                            </div>
                                            <div class="col-md-8">
                                                <input type="number" name="count_angle" class="form-control" value="0" min="0" maxlength="10">
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                <button form="form_tongue_calc" class="btn btn-primary">Произвести расчет</button>
            </div>
        </div>
    </div>
</div>

@push('js_footer')

@if(session()->get('edited_wv_id', 'default') == $work_volume->id)
    <!-- calculation -->
    <script>
        $('#get_tongue').select2({
            language: "ru",
            ajax: {
                url: '/projects/ajax/get_tongue',
                dataType: 'json',
                delay: 250,
            }
        });

        $('#get_angle').select2({
            language: "ru",
            ajax: {
                url: '/projects/ajax/get_angle',
                dataType: 'json',
                delay: 250,
            }
        });

        $('#get_tongue').on('change', function() {
            $.ajax({
                url:'{{ route("projects::work_volume::calc_tongue_count", $work_volume->id) }}',
                type: 'GET',
                data: {
                    _token: CSRF_TOKEN,
                    material_id:  $('#get_tongue').val(),
                    perimeter: $('#perimeter_calc').val()
                },
                dataType: 'JSON',
                success: function (data) {
                    $('#calc_tongue_count').val(data);
                }
            });
        });

        $('#extra_count_tongue').on('change', function() {
            $.ajax({
                url:'{{ route("projects::work_volume::calc_tongue_count", $work_volume->id) }}',
                type: 'GET',
                data: {
                    _token: CSRF_TOKEN,
                    material_id:  $('#get_tongue').val(),
                    perimeter: $('#perimeter_calc').val()
                },
                dataType: 'JSON',
                success: function (data) {
                    $('#calc_tongue_count').val(Math.ceil($('#extra_count_tongue').val()) + parseInt(data));
                }
            });
        });

        $('#perimeter_calc').on('change', function () {
            if ($('#get_tongue').val()) {
                $.ajax({
                    url:'{{ route("projects::work_volume::calc_tongue_count", $work_volume->id) }}',
                    type: 'GET',
                    data: {
                        _token: CSRF_TOKEN,
                        material_id:  $('#get_tongue').val(),
                        perimeter: $('#perimeter_calc').val()
                    },
                    dataType: 'JSON',
                    success: function (data) {
                        $('#calc_tongue_count').val(Math.ceil($('#extra_count_tongue').val()) + parseInt(data));
                    }
                });
            }
        });


        let materials_create_calc = new Vue({
            el: '#add_new_material_via_category_calc',
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
            computed: {
                categories_for_calc: function() {
                    return this.categories.filter(item => (item.id == 2 || item.id == 10));
                }
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
                            let elem = that.category_id == 2 ? 'get_tongue' : 'get_angle';
                            $('#' + elem).append(new_option).trigger('change');
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
@endif

@endpush
