@extends('layouts.app')

@section('title', 'Транспорт')

@section('css_top')
<style media="screen">
    td .cell {
        text-align: center;
    }

    .el-table_1_column_1 .cell {
        text-align: left;
    }

    .el-table td {
        padding: 5px 0!important;
    }

    .el-button,
    .el-button:active,
    .el-button:focus {
        outline: none;
    }

    .el-table__body {
        width:100%!important;
    }

    .el-table__empty-block {
        width:100%!important;
    }

    @media (max-width: 1024px){
        .el-table__header-wrapper {
            display: none;
        }

        .el-table__row td {
            padding-top: 15px!important;
            padding-bottom: 15px!important
        }
    }

    .el-mobile-table {
        text-align: left;
    }

    .el-table__row td {
        padding: 15px 0!important;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12 mobile-card">
        <div aria-label="breadcrumb" role="navigation">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('building::vehicles::vehicle_categories.index') }}" class="table-link">Транспорт</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Создание категории</li>
            </ol>
        </div>
        <div class="card" id="base">
            <div class="card-body card-body-tech">
                <h4 class="h4-tech fw-500" style="margin-top:0">Создание категории</h4>
                <validation-observer ref="observer" :key="observer_key">
                    <div id="category_name">
                        <div class="row mb-10">
                            <div class="col-md-6">
                                <template>
                                    <label>Наименование<span class="star">*</span></label>
                                    <validation-provider rules="required|max:60" vid="cat-name-input"
                                                         ref="cat-name-input"
                                                         name="наименования категории" v-slot="v">
                                        <el-input placeholder="Введите наименование" :class="v.classes" v-model="cat_name" id="cat-name-input" maxlength="60"></el-input>
                                        <div class="error-message">@{{ v.errors[0] }}</div>
                                    </validation-provider>
                                </template>
                            </div>
                        </div>
                        <div class="row mb-10">
                            <div class="col-md-6">
                                <template>
                                    <label>Описание</label>
                                    <validation-provider rules="max:200" vid="cat-description-input"
                                                         ref="cat-description-input"
                                                         name="наименования категории" v-slot="v">
                                        <el-input
                                            placeholder="Опишите категорию"
                                            v-model="cat_description"
                                            type="textarea"
                                            :class="v.classes"
                                            id="cat-description-input"
                                            rows="4"
                                            resize="none"
                                            maxlength="200"
                                        >
                                        </el-input>
                                        <div class="error-message">@{{ v.errors[0] }}</div>
                                    </validation-provider>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div id="cat_options">
                        <template>
                            <div class="row mt-20">
                                <div class="col-md-6">
                                    <h6 class="h6-decor">Параметры категории</h6>
                                </div>
                            </div>
                            <el-table :data="tableData" :default-sort="{order: 'ascending'}" style="width: 100%" >
                                <el-table-column v-if="showMobile">
                                    <template slot-scope="scope">
                                        <p class="el-mobile-table">
                                            <el-button @click.native.prevent="deleteRow(scope.$index, scope.row)"
                                                       type="danger" round plain size="mini" class="pull-right"
                                                       style="margin-top: 5px;">
                                                <i class="el-icon-delete"></i> Удалить
                                            </el-button>
                                        </p>
                                        <p class="el-mobile-table">
                                            <span class="table-stroke__label">Наименование <span class="star">*</span></span>
                                            <validation-provider rules="required|max:60"
                                                                 :vid="`cat-property-${scope.$index}-name-input`"
                                                                 :ref="`cat-property-${scope.$index}-name-input`"
                                                                 v-slot="v">
                                            <span class="table-stroke__content">
                                      <el-input size="small"
                                                style="text-align:center"
                                                :class="v.classes"
                                                :id="`cat-property-${scope.$index}-name-input`"
                                                minlength="1"
                                                maxlength="60"
                                                v-model="scope.row.name">
                                      </el-input>
                                        </span>
                                                <span class="error-message">@{{ v.errors[0] }}</span>
                                            </validation-provider>
                                        </p>
                                        <p class="el-mobile-table">
                                            <span class="table-stroke__label">Ед. измерения</span>
                                            <span class="table-stroke__content">
                                      <el-input size="small"
                                                style="text-align:center"
                                                v-model="scope.row.unit"
                                      >
                                      </el-input>
                                  </span>
                                        </p>
                                        <p class="el-mobile-table">
                                            <span class="table-stroke__label">Отображать в таблице</span>
                                            <span class="table-stroke__content">
                                      <el-checkbox name="type" v-model="scope.row.display"></el-checkbox>
                                  </span>
                                        </p>
                                        <p class="el-mobile-table">
                                            <span class="table-stroke__label">Обязательное</span>
                                            <span class="table-stroke__content">
                                      <el-checkbox name="required" v-model="scope.row.required"></el-checkbox>
                                  </span>
                                        </p>
                                        <p class="el-mobile-table"><span class="table-stroke__label">Краткое наименование</span>
                                            <span class="table-stroke__content">
                                        <validation-provider :rules="`veh_property_short_name:@cat-property-${scope.$index}-name-input|max:30`" :vid="`cat-property-${scope.$index}-short-name-input`"
                                                             :ref="`cat-property-${scope.$index}-short-name-input`"
                                                             v-slot="v">
                                      <el-input size="small"
                                                style="text-align:center"
                                                maxlength="30"
                                                :class="v.classes"
                                                :id="`cat-property-${scope.$index}-short-name-input`"
                                                v-model="scope.row.short_name">
                                      </el-input>
                                            <span class="error-message">@{{ v.errors[0] }}</span>
                                        </validation-provider>
                                </span>
                                        </p>
                                        <p class="el-mobile-table text-center"><span class="table-stroke__label"></span>
                                            <span class="table-stroke__content">

                                </span>
                                        </p>
                                    </template>
                                </el-table-column>
                                <el-table-column prop="name" label="Наименование *" min-width="200" v-if="hideMobile">
                                    <template slot-scope="scope">
                                        <validation-provider rules="required|max:60" :vid="`cat-property-${scope.$index}-name-input`"
                                                             :ref="`cat-property-${scope.$index}-name-input`"
                                                             v-slot="v">
                                            <el-input size="small"
                                                      style="text-align:center"
                                                      v-model="scope.row.name"
                                                      :class="v.classes"
                                                      :id="`cat-property-${scope.$index}-name-input`"
                                                      minlength="1"
                                                      maxlength="60"
                                            ></el-input>
                                            <div class="error-message" style="position: absolute">@{{ v.errors[0] }}</div>
                                        </validation-provider>
                                    </template>
                                </el-table-column>
                                <el-table-column prop="unit" label="Ед. измерения" min-width="100" v-if="hideMobile">
                                    <template slot-scope="scope">
                                        <el-input size="small"
                                                  style="text-align:center"
                                                  v-model="scope.row.unit"
                                                  maxlength="10"
                                        ></el-input>
                                    </template>
                                </el-table-column>
                                <el-table-column prop="display" label="Отображать в таблице" min-width="100" header-align="center" v-if="hideMobile">
                                    <template slot-scope="scope">
                                        <el-checkbox name="type" v-model="scope.row.show"></el-checkbox>
                                    </template>
                                </el-table-column>
                                <el-table-column prop="display" label="Обязательное" min-width="100" header-align="center" v-if="hideMobile">
                                    <template slot-scope="scope">
                                        <el-checkbox name="required" v-model="scope.row.required"></el-checkbox>
                                    </template>
                                </el-table-column>
                                <el-table-column prop="short_name" label="Краткое наименование" min-width="180" v-if="hideMobile">
                                    <template slot-scope="scope">
                                        <validation-provider :rules="`veh_property_short_name:@cat-property-${scope.$index}-name-input|max:30`" :vid="`cat-property-${scope.$index}-short-name-input`"
                                                             :ref="`cat-property-${scope.$index}-short-name-input`"
                                                             v-slot="v">
                                            <el-input size="small"
                                                      style="text-align:center"
                                                      :class="v.classes"
                                                      :id="`cat-property-${scope.$index}-short-name-input`"
                                                      v-model="scope.row.short_name"
                                                      maxlength="30"
                                            ></el-input>
                                            <div class="error-message">@{{ v.errors[0] }}</div>
                                        </validation-provider>
                                    </template>
                                </el-table-column>
                                <el-table-column label="Действия" min-width="70"  header-align="center" v-if="hideMobile">
                                    <template slot-scope="scope">
                                        <el-button @click.native.prevent="deleteRow(scope.$index, scope.row)" type="text" class="btn-danger btn-link">
                                            <i class="el-icon-delete"></i>
                                        </el-button>
                                    </template>
                                </el-table-column>
                            </el-table>
                            <div class="row">
                                <div class="col-md-12 text-right">
                                    <el-button @click="addRow" size="small" round plain type="primary" style="margin-top:15px">
                                        <i class="el-icon-plus"></i>
                                        Добавить
                                    </el-button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 text-center" style="margin-top:50px">
                                    <el-button type="primary" :loading="formSubmit" @click="submitForm">Сохранить</el-button>
                                </div>
                            </div>
                        </template>
                    </div>
                </validation-observer>
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
    <script>
    var parameters = new Vue({
        el:'#base',
        data: {
            observer_key: 1,
            cat_name: '',
            cat_description: '',
            tableData: [],
            addCount:0,
            number:1,
            window_width: 10000,
            rowCount: 0,
            formSubmit: false
        },
        mounted() {
            $(window).on('resize', this.handleResize);
            this.handleResize();
        },
        computed: {
            showMobile() {
                return this.window_width <= 1024;
            },
            hideMobile() {
                return this.window_width > 1024;
            },
        },
        methods: {
            handleResize() {
                this.window_width = $(window).width();
            },
            deleteRow(index, rows) {
                this.tableData.splice(index, 1);
                if(this.addCount > 0)
                    -- this.addCount;
                -- this.number;
                this.rowCount === 0 ? this.rowCount = 0 : this.rowCount -= 1;
            },
            addRow:function() {
                if (this.rowCount >= 10) {
                    this.$message({
                        showClose: true,
                        message: 'Вы можете добавить не более 10 параметров категории',
                        type: 'error'
                    });

                    return;
                }

                let newRow  = {
                    number: this.number,
                    name: '',
                    unit: '',
                    show: true,
                    short_name: '',
                    required: false,
                };
                this.tableData.push(newRow);
                ++ this.addCount;
                ++ this.number;
                ++ this.rowCount;
            },
            headerRowClassName(row, rowIndex) {
                if( this.isMobile ){
                    return 'd-none';
                }
            },
            submitForm() {
                this.formSubmit = true;
                this.$refs.observer.validate().then(success => {
                    if (!success) {
                        const error_field_vid = Object.keys(this.$refs.observer.errors).find(el => this.$refs[el].errors.length > 0);
                        $('.modal').animate({
                            scrollTop: $('#' + error_field_vid).offset().top
                        }, 1200);
                        $('#' + error_field_vid).focus();
                        return;
                    }
                    axios.post('{{ route('building::vehicles::vehicle_categories.store') }}', {
                        characteristics: this.tableData,
                        name: this.cat_name,
                        description: this.cat_description,
                    }).then(function (data) {
                        window.location = data.data.redirect;
                    }, function (error) {
                        this.formSubmit = false;

                        var message = '';
                        var errors = error.response.data.errors;
                        for (key in errors) {
                            message += errors[key][0] + '<br>';
                        }
                        swal({
                            type: 'error',
                            title: "Ошибка ввода данных",
                            html: message,
                        });
                    });
                });
                /*this.formSubmit = true;
                var formError = '';
                if (! this.cat_name.trim()) {
                    formError = 'Заполните поле Наименование';
                } /!*else if (! this.tableData.length) {
                    formError = 'Необходимо указать как минимум один параметр'
                }*!/ else if (this.tableData.length > 0) {
                    this.tableData.forEach(row => {
                        if (! row.name.trim()) {
                            formError = 'Заполните поле Наименование параметра';
                        } else if (row.name.length > 30 && ! row.short_name.trim()) {
                            formError = 'Заполните поле Краткое наименование параметра';
                        }
                    });
                }

                if (formError) {
                    this.formSubmit = false;
                    swal({
                        type: 'error',
                        title: "Ошибка ввода данных",
                        text: formError,
                    });
                } else {
                    axios.post('{{ route('building::vehicles::vehicle_categories.store') }}', {
                        characteristics: this.tableData,
                        name: this.cat_name,
                        description: this.cat_description,
                    }).then(function (data) {
                        window.location = data.data.redirect;
                    }, function (error) {
                        //this.formSubmit = false;

                        var message = '';
                        var errors = error.response.data.errors;
                        for (key in errors) {
                            message += errors[key][0] + '<br>';
                        }
                        swal({
                            type: 'error',
                            title: "Ошибка ввода данных",
                            html: message,
                        });
                    });
               }*/
            }
        },
    });
</script>
@endsection
