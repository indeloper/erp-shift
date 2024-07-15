@extends('layouts.app')

@section('title', 'Учет техники')

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
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12 mobile-card">
            <div aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('building::tech_acc::technic_category.index') }}" class="table-link">Техника</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Создание категории</li>
                </ol>
            </div>
            <div class="card col-xl-10 mr-auto ml-auto pd-0-min">
                <div class="card-body card-body-tech">
                    <h4 class="h4-tech fw-500" style="margin-top:0">Создание категории</h4>
                    <div id="category_name">
                        <div class="row mb-10">
                            <div class="col-md-6">
                                <template>
                                    <label>Наименование<span class="star">*</span></label>
                                    <el-input placeholder="Введите наименование" v-model="cat_name" maxlength="60"></el-input>
                                </template>
                            </div>
                        </div>
                        <div class="row mb-10">
                            <div class="col-md-6">
                                <template>
                                    <label>Описание<span class="star">*</span></label>
                                    <el-input
                                            placeholder="Опишите категорию"
                                            v-model="cat_description"
                                            type="textarea"
                                            rows="4"
                                            resize="none"
                                            maxlength="200"
                                    >
                                    </el-input>
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
                                <!-- <div class="col-md-6 text-right">
                                    <el-button @click="addRow" size="small" round plain type="primary" style="margin-top:15px">
                                        <i class="el-icon-plus"></i>
                                        Добавить
                                    </el-button>
                                </div> -->
                            </div>
                            <el-table :data="tableData" :default-sort="{order: 'ascending'}" style="width: 100%" >
                                <el-table-column v-if="showMobile">
                                    <template slot-scope="scope">
                                        <p class="el-mobile-table">
                                            <el-button @click.native.prevent="deleteRow(scope.$index, scope.row)" type="danger" round plain size="mini" class="pull-right" style="margin-top: 5px;">
                                                <i class="el-icon-delete"></i> Удалить
                                            </el-button>
                                        </p>
                                        <p class="el-mobile-table">
                                            <span class="table-stroke__label">Наименование</span>
                                            <span class="table-stroke__content">
                                      <el-input size="small"
                                                style="text-align:center"
                                                v-model="scope.row.name">
                                      </el-input>
                                  </span>
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
                                            <span class="table-stroke__label">Скрывать в таблице</span>
                                            <span class="table-stroke__content">
                                      <el-checkbox name="type" v-model="scope.row.is_hidden"></el-checkbox>
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
                                      <el-input size="small"
                                                style="text-align:center"
                                                v-model="scope.row.description">
                                      </el-input>
                                </span>
                                        </p>
                                        <p class="el-mobile-table text-center"><span class="table-stroke__label"></span>
                                            <span class="table-stroke__content">

                                </span>
                                        </p>
                                    </template>
                                </el-table-column>
                                <el-table-column prop="name" label="Наименование" min-width="200" v-if="hideMobile">
                                    <template slot-scope="scope">
                                        <el-input size="small"
                                                  style="text-align:center"
                                                  v-model="scope.row.name"></el-input>
                                    </template>
                                </el-table-column>
                                <el-table-column prop="unit" label="Ед. измерения" min-width="100" v-if="hideMobile">
                                    <template slot-scope="scope">
                                        <el-input size="small"
                                                  style="text-align:center"
                                                  v-model="scope.row.unit"
                                        ></el-input>
                                    </template>
                                </el-table-column>
                                <el-table-column prop="is_hidden" label="Скрывать в таблице" header-align="center" min-width="100" v-if="hideMobile">
                                    <template slot-scope="scope">
                                        <el-checkbox name="type" v-model="scope.row.is_hidden"></el-checkbox>
                                    </template>
                                </el-table-column>
                                <el-table-column prop="required" label="Обязательное" header-align="center" min-width="100" v-if="hideMobile">
                                    <template slot-scope="scope">
                                        <el-checkbox name="required" v-model="scope.row.required"></el-checkbox>
                                    </template>
                                </el-table-column>
                                <el-table-column prop="description" label="Краткое наименование" min-width="180" v-if="hideMobile">
                                    <template slot-scope="scope">
                                        <el-input size="small"
                                                  style="text-align:center"
                                                  v-model="scope.row.description"></el-input>
                                    </template>
                                </el-table-column>
                                <el-table-column label="Действия" header-align="center" min-width="70" v-if="hideMobile">
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
                                    <el-button @click="submitForm()" style="margin-top:15px">
                                        Сохранить
                                    </el-button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js_footer')
    <script>
        var category = new Vue({
            el: '#category_name',
            data() {
                return {
                    cat_name: '',
                    cat_description: ''
                }
            }
        });

        var characteristics = new Vue({
            el:'#cat_options',
            data: {
                tableData: [],
                addCount:0,
                windowWidth: window.innerWidth,
                showMobile: false,
                hideMobile: true,
                formRequest: [],
            },
            beforeUpdate() {
                window.addEventListener('resize', () => {
                    this.windowWidth = window.innerWidth
                    console.log(this.isMobile)
                });
                this.isMobile;
            },
            computed: {
                isMobile() {
                    if (this.windowWidth <= 1024) {
                        this.hideMobile = false;
                        this.showMobile = true;
                        return true;
                    } else {
                        this.hideMobile = true;
                        this.showMobile = false;
                        return false;
                    }
                }
            },
            methods: {
                deleteRow(index, rows) {
                    this.tableData.splice(index, 1);
                    if(this.addCount > 0)
                        -- this.addCount;
                },
                addRow:function(){
                    let newRow  = {
                        name: '',
                        unit: '',
                        is_hidden: true,
                        description: '',
                        required: false
                    };
                    this.tableData.push(newRow);
                    ++ this.addCount;
                },
                headerRowClassName(row, rowIndex) {
                    if( this.isMobile ){
                        return 'd-none';
                    }
                },
                submitForm() {
                    var formError = '';
                    if (!category.cat_name.trim()) {
                        formError = 'Заполните, пожалуйста, поле Наименование';
                    } else if(!category.cat_description.trim()) {
                        formError = 'Заполните, пожалуйста, поле Описание';
                    } else if(this.tableData.length > 0) {
                        this.tableData.forEach(row => {
                            if (!row.name.trim()) {
                                formError = 'Заполните, пожалуйста, поле Наименование параметра';
                            }
                        });
                    }

                    if (formError) {
                        swal({
                            type: 'error',
                            title: "Ошибка ввода данных",
                            text: formError,
                        });
                    } else {
                        axios.post('{{ route('building::tech_acc::technic_category.store') }}', {
                            characteristics: characteristics.tableData,
                            name: category.cat_name,
                            description: category.cat_description,
                        }).then(function (data) {
                            window.location = data.data.redirect;
                        }, function (error) {
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
                    }


                }
            },

        });
        // var Ctor = Vue.extend(characteristics);
        // new Ctor().$mount('#cat_options');
    </script>
@endsection
