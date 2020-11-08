@extends('layouts.app')

@section('title', 'Материальный учет')

@section('url', route('building::mat_acc::operations'))

@section('css_top')
<style>
    .el-select {width: 100%}
    .el-date-editor.el-input {width: inherit;}
    .margin-top-15 {
        margin-top: 15px;
    }
    .el-mobile-table {
        font-size: 12px;
        line-height: 1;
        width: 235px;
    }

    .el-table__expanded-cell[class*=cell] {
        padding: 15px 5px;
    }

    .el-table .warning-row {
      background: oldlace;
    }

    .el-table .success-row {
      background: #f9fff5;
    }

    /* .el-table .work-row {
      background: #F0F8FF;
    } */

    ol, ul {
        padding-inline-start: 10px;
    }

    .el-mobile-table ol, .el-mobile-table ul {
        padding-inline-start: 25px;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="nav-container" style="margin:0 0 10px 15px">
            <ul class="nav nav-icons" role="tablist">
                <li class="nav-item ">
                    <a class="nav-link link-line " id="#" href="{{ route('building::mat_acc::report_card') }}">
                        Табель материального учёта
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link link-line" href="{{ route('building::mat_acc::operations') }}">
                        Журнал операций
                    </a>
                </li>
                <li class="nav-item active">
                    <a class="nav-link link-line active-link-line" href="{{ route('building::mat_acc::certificateless_operations') }}">
                        Журнал операций без сертификата
                    </a>
                </li>
            </ul>
        </div>
        {{--<div class="card strpied-tabled-with-hover" id="filter">
            <div class="card-body">
                <h6 style="margin-bottom:10px">Фильтрация</h6>
                <div class="row">
                    <div class="col-md-3" style="margin-top:10px">
                        <template>
                          <el-select v-model="parameter" @change="changeFilterValues" value-key="text" filterable :remote-method="search" remote placeholder="Поиск">
                            <el-option
                              v-for="param in parameters"
                              :selected="param.id == 1"
                              :value="param"
                              :key="param.id"
                              :label="param.text">
                            </el-option>
                          </el-select>
                        </template>
                    </div>
                    <div class="col-md-5" style="margin-top:10px">
                        <template>
                          <el-select v-model="param_value" value-key="label" clearable filterable :remote-method="search" remote placeholder="Поиск">
                            <el-option
                              v-for="item in filter_values"
                              :key="item.code"
                              :label="item.label"
                              :value="item">
                            </el-option>
                          </el-select>
                        </template>
                    </div>
                    <div class="col-md-4" style="margin-top:10px">
                        <div class="left-edge">
                            <div class="page-container">
                                <button type="button" v-on:click="filter" class="btn btn-wd btn-info" name="button">Добавить</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row" v-if="filter_items.length > 0">
                    <div class="col-md-12" style="margin: 10px 0 10px 0">
                        <h6>Выбранные фильтры</h6>
                    </div>
                </div>
                <template>
                    <div class="row" v-if="filter_items.length > 0">
                        <div class="col-md-9">
                            <div class="bootstrap-tagsinput">
                                <span class="badge badge-azure" v-on:click="delete_budge(index)" v-for="(item, index) in filter_items">@{{ item.parameter_text }}: @{{ item.value }}<span data-role="remove" class="badge-remove-link"></span></span>
                            </div>
                        </div>
                        <div class="col-md-3 text-right mnt-20--mobile text-center--mobile">
                            <button type="button" @click="filter_items.splice(0, filter_items.length)" class="btn btn-sm show-all">
                                Удалить фильтры
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>--}}
        <div class="card strpied-tabled-with-hover" id="operations">
            <div class="fixed-table-toolbar toolbar-for-btn">
                {{--<div class="row" style="margin-bottom:20px">
                    <div class="col-md-7" style="margin-top:5px;">
                        <template>
                            <el-input placeholder="Поиск" v-model="search_tf" clearable
                                      prefix-icon="el-icon-search" id="search-tf" @clear="doneTyping"
                                      class="d-inline-block"
                                      style="width: 200px; margin-bottom: 10px"
                            ></el-input>
                            <el-date-picker style="cursor:pointer; width: 200px !important; margin-bottom: 10px"
                                            v-model="search_date"
                                            format="dd.MM.yyyy"
                                            value-format="dd.MM.yyyy"
                                            type="date"
                                            placeholder="Выберите день"
                                            name="planned_date_from"
                                            :picker-options="{firstDayOfWeek: 1}"
                                            @focus = "onFocus"
                            >
                            </el-date-picker>
                            <el-checkbox v-model="with_closed" label="Закрытые" border v-on:change="updateResults" style="text-transform: none"></el-checkbox>
                        </template>
                    </div>
                    <div class="col-md-5 text-right mnt-20--mobile">
                        <button type="button" data-toggle="modal" data-target="#operation_excel" class="btn btn-wd btn-outline" style="margin-top:5px;">
                            Отчет по материалам
                        </button>
                        <button type="button" v-on:click="print_rep()" name="button" class="btn btn-wd btn-outline" style="margin-top:5px;">
                            <i class="fa fa-print"></i>
                            Печать
                        </button>
                        @if(Gate::check('mat_acc_arrival_draft_create') || Gate::check('mat_acc_arrival_create')
                            || Gate::check('mat_acc_write_off_draft_create') || Gate::check('mat_acc_write_off_create')
                            || Gate::check('mat_acc_transformation_draft_create') || Gate::check('mat_acc_transformation_create')
                            || Gate::check('mat_acc_moving_draft_create') || Gate::check('mat_acc_moving_create'))
                        <div class="btn-group" role="group">
                            <button id="btnGroupDrop1" type="button" class="btn btn-secondary dropdown-toggle btn-success" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Создать операцию
                            </button>
                            <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                            @if(Gate::check('mat_acc_arrival_draft_create') || Gate::check('mat_acc_arrival_create'))
                              <a class="dropdown-item" href="{{ route('building::mat_acc::arrival::create') }}">Поступление</a>
                            @endif
                            @if(Gate::check('mat_acc_write_off_draft_create') || Gate::check('mat_acc_write_off_create'))
                              <a class="dropdown-item" href="{{ route('building::mat_acc::write_off::create') }}">Списание</a>
                            @endif
                            @if(Gate::check('mat_acc_transformation_draft_create') || Gate::check('mat_acc_transformation_create'))
                              <a class="dropdown-item" href="{{ route('building::mat_acc::transformation::create') }}">Преобразование</a>
                            @endif
                            @if(Gate::check('mat_acc_moving_draft_create') || Gate::check('mat_acc_moving_create'))
                              <a class="dropdown-item" href="{{ route('building::mat_acc::moving::create') }}">Перемещение</a>
                            @endif
                            </div>
                         </div>
                         @endif
                    </div>
                </div>--}}
                <template>
                  <el-table
                    :data="operations"
                    empty-text="Данных нет"
                    style="width: 100%"
                    :row-class-name="tableRowClassName"
                    :header-row-class-name="headerRowClassName"
                    @row-dblclick="link_to_operation"
                    >

                    <!-- for mobile phone -->
                    <el-table-column v-if="showMobile">
                      <template slot-scope="props">
                          <p class="el-mobile-table"><span class="table-stroke__label">Тип:</span> <span class="table-stroke__content">@{{ props.row.type_name }}</span></p>
                          <div class="el-mobile-table"><span class="table-stroke__label">Материалы:</span> <ul>
                                  <li  class="table-stroke__content" v-for="(material, i) in props.row.materials" {{--:style="i > 0 ? 'border-top: 1px solid #EBEEF5;' : ''"--}}>@{{ material.manual ? material.manual.name : '' }}</li>
                              </ul></div>
                        <p class="el-mobile-table"><span class="table-stroke__label">Объект:</span> <span class="table-stroke__content">@{{ props.row.object_text }}</span></p>
                        <p class="el-mobile-table"><span class="table-stroke__label">Адрес:</span> <span class="table-stroke__content">@{{ props.row.address_text }}</span></p>
                        <p class="el-mobile-table"><span class="table-stroke__label">Договор:</span> <span class="table-stroke__content">@{{ props.row.contract.name_for_humans }}</span></p>
                        <p class="el-mobile-table"><span class="table-stroke__label">Автор:</span> <span class="table-stroke__content">@{{ props.row.author.full_name }}</span></p>
                        <p class="el-mobile-table"><span class="table-stroke__label">Дата создания:</span> <span class="table-stroke__content">@{{ props.row.created_date }}</span></p>
                        <p class="el-mobile-table"><span class="table-stroke__label">Дата начала:</span> <span class="table-stroke__content">@{{ props.row.actual_date_from }}</span></p>
                        <p class="el-mobile-table"><span class="table-stroke__label">Дата закрытия:</span> <span class="table-stroke__content">@{{ props.row.closed_date }}</span></p>
                        <p class="el-mobile-table"><span class="table-stroke__label">Статус:</span> <span class="table-stroke__content">@{{ props.row.status_name }}</span></p>
                          <p class="el-mobile-table text-right" style="margin-top: 20px">
                                  <a :href="props.row.url">Перейти в операцию →</a>
                          </p>
                      </template>
                    </el-table-column>
                    <el-table-column
                      v-if="hideMobile"
                      prop="type_name"
                      label="Тип"
                      align="center"
                      min-width="45">
                        <template slot-scope="scope">
                            <el-tooltip :content="scope.row.type_name" placement="right" effect="light">
                                <i :class="getTypeIcon(scope.row.type_name)">
                                </i>
                            </el-tooltip>
                        </template>
                    </el-table-column>
                    <el-table-column
                      v-if="hideMobile"
                      prop="total_weigth"
                      label="Количество, т"
                      min-width="70">
                    </el-table-column>
                      <el-table-column
                          v-if="hideMobile"
                          prop="materials"
                          label="Материалы"
                          min-width="140">
                          <template slot-scope="scope">
                              <ul>
                                  <li v-for="(material, i) in scope.row.materials" {{--:style="i > 0 ? 'border-top: 1px solid #EBEEF5;' : ''"--}}>@{{ material.manual ? material.manual.name : '' }}</li>
                              </ul>
                          </template>
                      </el-table-column>
                    <el-table-column
                      v-if="hideMobile"
                      prop="object_text"
                      label="Объект"
                      min-width="110">
                    </el-table-column>
                    <el-table-column
                      v-if="hideMobile"
                      prop="address_text"
                      label="Адрес"
                      min-width="110">
                    </el-table-column>
                    <el-table-column
                      v-if="hideMobile"
                      prop="contract.name_for_humans"
                      label="Договор"
                      min-width="110">
                    </el-table-column>
                    <el-table-column
                      v-if="hideMobile"
                      prop="author.full_name"
                      label="Автор"
                      min-width="80">
                    </el-table-column>
                    <el-table-column
                      v-if="hideMobile"
                      prop="created_date"
                      label="Дата создания"
                      min-width="80">
                    </el-table-column>
                    <el-table-column
                      v-if="hideMobile"
                      prop="actual_date_from"
                      label="Дата начала"
                      min-width="80">
                    </el-table-column>
                    <el-table-column
                      v-if="hideMobile"
                      prop="closed_date"
                      label="Дата закрытия"
                      min-width="80">
                    </el-table-column>
                    <el-table-column
                      v-if="hideMobile"
                      prop="status_name"
                      label="Статус"
                      min-width="130">
                    </el-table-column>
                  </el-table>
                </template>
            </div>
        </div>
    </div>
</div>

{{--<form id="print_operations" target="_blank" method="post" multisumit="true" action="{{route('building::mat_acc::operations::print')}}">
    @csrf
    <input id="print_data" type="hidden" name="results">
</form>
<!-- экспорт кп -->

<div class="modal fade bd-example-modal-lg show" id="operation_excel" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
       <div class="modal-content">
           <div class="modal-header">
               <h5 class="modal-title">Экспорт операций в Excel</h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                   <span aria-hidden="true">×</span>
               </button>
           </div>
           <div class="modal-body">
               <hr style="margin-top:0">
               <div class="card border-0" >
                   <div class="card-body ">
                       <form id="formExportToExcel" multisumit="true" target="_blank" method="post" action="{{ route('building::mat_acc::export_object_actions') }}" class="form-horizontal">
                           @csrf
                           <input type="hidden" v-model="object_id.code" name="object_id">
                           <div class="row">
                               <div class="col-md-12">
                                   <div class="form-group" style="margin-bottom: 0">
                                       <div class="row">
                                           <div class="col-md-4">
                                               <label>Объект</label>
                                           </div>
                                           <div class="col-md-8">
                                               <template>
                                                   <el-select v-model="object_id" value-key="label" clearable filterable :remote-method="search" required remote placeholder="Поиск">
                                                       <el-option
                                                           v-for="item in objects"
                                                           :key="item.code"
                                                           :label="item.label"
                                                           :value="item">
                                                       </el-option>
                                                   </el-select>
                                                   <p class="text-muted" style="font-size: 12px;">
                                                       Если в получившимся отчете в ячейке "Наименование объекта" отображается слишком длинное название - вы можете заполнить "сокращенное наименование" у объекта в системе, чтобы отображалось то, что вам нужно.
                                                   </p>
                                               </template>
                                           </div>
                                       </div>
                                    </div>
                               </div>
                           </div>
                           <div class="row">
                               <div class="col-md-4">
                                   <label>Диапазон дат</label>
                               </div>
                               <div class="col-md-8">
                                       <el-date-picker
                                           style="cursor:pointer"
                                           v-model="start_date"
                                           class="mb-3"
                                           format="dd.MM.yyyy"
                                           name="start_date"
                                           value-format="dd.MM.yyyy"
                                           type="date"
                                           placeholder="Дата с"
                                           :picker-options="{firstDayOfWeek: 1}"
                                           @focus = "onFocus">
                                       </el-date-picker>

                                       <el-date-picker
                                           style="cursor:pointer"
                                           v-model="end_date"
                                           format="dd.MM.yyyy"
                                           name="end_date"
                                           value-format="dd.MM.yyyy"
                                           type="date"
                                           placeholder="Дата по"
                                           :picker-options="{firstDayOfWeek: 1}"
                                           @focus = "onFocus">
                                       </el-date-picker>
                               </div>
                           </div>
                       </form>
                   </div>
               </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                <button type="button" @click="checkObject" class="btn btn-info btn-outline">Подтвердить</button>
           </div>
        </div>
    </div>
</div>--}}

@endsection

@section('js_footer')
{{--<script type="text/javascript">
        $( document ).ready(function() {
                $('.el-select').click(function (){
                    input = $(this).find('.el-input__inner');
                    input.focus();
                    setTimeout( function(){
                        input[0].setSelectionRange(0, 9999);
                        input.focus();
                    }, 1)
                });

                $('.el-select').on('touchstart', function(){
                    input = $(this).find('.el-input__inner');
                    input.focus();
                    setTimeout( function(){
                        input[0].setSelectionRange(0, 9999);
                        input.focus();
                    }, 1)
                });

                $('.el-select').on('touchend', function(){
                    input = $(this).find('.el-input__inner');
                    input.focus();
                    setTimeout( function(){
                        input[0].setSelectionRange(0, 9999);
                        input.focus();
                    }, 1)
                });
        });
    </script>
--}}
{{--<script type="text/javascript">
    $( document ).ready(function() {
        $("#materials").datetimepicker({
            format: 'DD.MM.YYYY',
            locale: 'ru',
            icons: {
                time: "fa fa-clock-o",
                date: "fa fa-calendar",
                up: "fa fa-chevron-up",
                down: "fa fa-chevron-down",
                previous: 'fa fa-chevron-left',
                next: 'fa fa-chevron-right',
                today: 'fa fa-screenshot',
                clear: 'fa fa-trash',
                close: 'fa fa-remove'
            },
            maxDate: moment(),
            date: null
        });
    });

    $(document).ready(function() {
        $('.js-example-basic-single').select2({
            language: "ru",
        });
    });

</script>
--}}
{{--
<script type="text/javascript">
$( document ).ready(function() {
    $('.show-operations').click(function(){
        $(this).closest('.operations').show();
    });
});
</script>
--}}
{{--
<script type="text/javascript">
    function remove_tag(e){
        $(e).closest('.material-item').remove();
    };
</script>
--}}

<script>
{{--var requests = Array();

var filter = new Vue({
    el: '#filter',
    data: {
        parameter: {!! json_encode($filter_params) !!}[0],
        param_value: '',
        filter_items: [],
        filter_values: [],
        parameters: {!! json_encode($filter_params) !!}
    },
    mounted: function () {
        var parameter_ids = [{{Request::get('parameter_id')}}];
        var value_ids = [{{Request::get('value_ids')}}];
        var date = '{{Request::get('date')}}';
        var that = this;

        if (parameter_ids.length === value_ids.length) {
            parameter_ids.forEach(function(id, key) {
                var value = '';
                var parameter = '';
                if (id === 0) {
                    parameter = 'Объект';
                    requests.push(axios.post('{{ route('building::mat_acc::report_card::get_objects') }}', {
                        'object_id': value_ids[key],
                    }).then(response => {
                        value = response.data.find(item => item.code == value_ids[key])['label'];
                        that.filter_items.push({
                            'value': value,
                            'parameter_text': parameter,
                            'parameter_id': id,
                            'value_id': value_ids[key],
                        });
                    }));
                } else if (id === 1) { //DO SMTH WITH THIS (CODE AND ID ARE NOT THE SAME)
                    parameter = 'Материал';
                    requests.push(axios.post('{{ route('building::mat_acc::report_card::get_materials') }}', {
                        'material_ids': [value_ids[key]],
                    }).then(response => {
                        value = response.data.find(item => item.code == value_ids[key])['label'];
                        that.filter_items.push({
                            'value': value,
                            'parameter_text': parameter,
                            'parameter_id': id,
                            'value_id': value_ids[key],
                        });
                    }));
                } else if (id === 2) {
                    parameter = 'Автор';
                    requests.push(axios.post('{{ route('building::mat_acc::get_users') }}', {
                        'author_id': value_ids[key],
                    }).then(response => {
                        value = response.data.find(item => item.code == value_ids[key])['label'];
                        that.filter_items.push({
                            'value': value,
                            'parameter_text': parameter,
                            'parameter_id': id,
                            'value_id': value_ids[key],
                        });
                    }));
                } else if (id === 3) {//status
                    parameter = 'Статус';
                    requests.push(axios.post('{{ route('building::mat_acc::get_statuses') }}', {
                        'status_id': value_ids[key],
                    }).then(response => {
                        value = response.data[0];
                        that.filter_items.push({
                            'value': response.data[0]['label'],
                            'parameter_text': parameter,
                            'parameter_id': id,
                            'value_id': value_ids[key],
                        });
                    }));
                } else if (id === 4) {//type
                    parameter = 'Тип';
                    requests.push(axios.post('{{ route('building::mat_acc::get_types') }}', {
                        'type_id': value_ids[key],
                    }).then(response => {
                        value = response.data[0];
                        that.filter_items.push({
                            'value': response.data[0]['label'],
                            'parameter_text': parameter,
                            'parameter_id': id,
                            'value_id': value_ids[key],
                        });
                    }));
                }
            });
        }
        if(requests.length > 0) {
            var defer = $.when.apply($, requests);
            defer.done(function () {
                filter.filter();
            });
        }
        if (this.parameter.id == 0) {
            axios.post('{{ route('building::mat_acc::report_card::get_objects') }}').then(response => filter.filter_values = response.data)
        }
    },
    methods: {
        filter: function () {
            if (!this.inArray(this.filter_items, {parameter_id: this.parameter.id, value: this.param_value.label})) {
                if(this.param_value != '') {
                    this.filter_items.push({parameter_id: this.parameter.id, parameter_text: this.parameter.text, value: this.param_value.label, value_id: this.param_value.code });
                }

                this.send_filter(this.filter_items);
            }
        },
        delete_budge: function (index) {
            this.filter_items.splice(index, 1);

            this.send_filter(this.filter_items);
        },
        send_filter: function (filter) {
            var that = this;
            axios.post('{{ route('building::mat_acc::report_card::filter') }}', {
                filter: filter,
                with_closed: {!! (Request::get('with_closed') != '' ?: 'false') !!}
            }).then(function (response) {
                window.history.pushState("", "", "?" + that.compactFilters());
                operations.operations = response.data['result'];
            })
        },
        compactFilters: () => {
            var filter_url = '';
            var filter_params = [];
            var filter_values = [];
            filter.filter_items.forEach(filter_item => {
                filter_params.push(filter_item['parameter_id']);
                filter_values.push(filter_item['value_id']);
            });
            filter_url = 'parameter_id=' + filter_params.toString() + '&value_ids=' + filter_values.toString() + '&date=' + operations.search_date + '&search=' + operations.search_tf + '&with_closed=' + operations.with_closed;
            return filter_url;
        },
        changeFilterValues: function () {
            filter.filter_values = [];

            if (filter.parameter.id === 0) {
                axios.post('{{ route('building::mat_acc::report_card::get_objects') }}').then(response => filter.filter_values = response.data)
            } else if (filter.parameter.id === 1) {
                axios.post('{{ route('building::mat_acc::report_card::get_materials') }}').then(response => filter.filter_values = response.data)
            } else if (filter.parameter.id === 2) {
                axios.post('{{ route('building::mat_acc::get_users') }}').then(response => filter.filter_values = response.data)
            } else if (filter.parameter.id === 3) {//status
                axios.post('{{ route('building::mat_acc::get_statuses') }}').then(response => filter.filter_values = response.data)
            } else if (filter.parameter.id === 4) {//type
                axios.post('{{ route('building::mat_acc::get_types') }}').then(response => filter.filter_values = response.data)
            } else if (filter.parameter.id === 5) {
                filter.filter_values = [];
            }
        },
        search(query) {
            if (query !== '') {
                if (filter.parameter.id == 0) {
                  setTimeout(() => {
                    axios.post('{{ route('building::mat_acc::report_card::get_objects') }}', {q: query}).then(function (response) {
                        filter.filter_values = response.data;
                    })
                    }, 100);
                }
                if (filter.parameter.id == 1) {
                  setTimeout(() => {
                    axios.post('{{ route('building::mat_acc::report_card::get_materials') }}', {q: query}).then(function (response) {
                        filter.filter_values = response.data;
                    })
                    }, 100);
                }
                if (filter.parameter.id == 2) {
                  setTimeout(() => {
                    axios.post('{{ route('building::mat_acc::get_users') }}', {q: query}).then(function (response) {
                        filter.filter_values = response.data;
                    })
                    }, 100);
                }
                if (filter.parameter.id == 4) {
                  setTimeout(() => {
                    axios.post('{{ route('building::mat_acc::get_statuses') }}', {q: query}).then(function (response) {
                        filter.filter_values = response.data;
                    })
                    }, 100);
                }
                if (filter.parameter.id == 5) {
                  setTimeout(() => {
                    axios.post('{{ route('building::mat_acc::get_types') }}', {q: query}).then(function (response) {
                        filter.filter_values = response.data;
                    })
                    }, 100);
                } else {
                  filter.filter_values = [];
              }
            }
        },
        inArray: function(array, element) {
            var length = array.length;
            for(var i = 0; i < length; i++) {
                if(array[i].parameter_id == element.parameter_id && array[i].value == element.value) return true;
            }
            return false;
        }
    }
})
--}}
var operations = new Vue({
    el: '#operations',
    data: {
        // DONE_TYPING_INTERVAL: 1000,
        operations: {!! $operations !!},
        {{--search_date : '{{Request::get('date')}}',--}}
        {{--search_tf: '{{Request::get('search')}}',--}}
        windowWidth: window.innerWidth,
        showMobile: false,
        hideMobile: true,
        {{--with_closed: {!!(Request::get('with_closed') != '' ? Request::get('with_closed') : 'false')!!},--}}
    },
    {{--mounted: function() {
        var url_date = '{{Request::get('date')}}';

        if (url_date != '') {
            this.search_date = url_date;
        }

        let searchTF = $('#search-tf');

        searchTF.on('keyup', () => {
            clearTimeout(this.typingTimer);
            this.typingTimer = setTimeout(this.doneTyping, this.DONE_TYPING_INTERVAL);
        });

        searchTF.on('keydown', () => {
            clearTimeout(this.typingTimer);
        });

        if(requests.length > 0) {
            var defer = $.when.apply($, requests);
            defer.done(function () {
                // This is executed only after every ajax request has been completed
                filter.filter();
            });
        }

    },
    watch: {
        search_date: function (val) {
            axios.post('{{ route('building::mat_acc::report_card::filter') }}', {
                date: operations.search_date,
                search: operations.search_tf,
                filter: filter.filter_items,
                with_closed: operations.with_closed
            }).then(function (response) {
                window.history.pushState("", "", "?" + filter.compactFilters());
                operations.operations = response.data['result'];
            })
        },
    },--}}
    beforeUpdate() {
        window.addEventListener('resize', () => {
          this.windowWidth = window.innerWidth
      })
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
        link_to_operation(row, column, cell, event) {
            window.location = row.general_url;
        },
        {{--doneTyping() {
            axios.post('{{ route('building::mat_acc::report_card::filter') }}', {
                date: operations.search_date,
                search: operations.search_tf,
                filter: filter.filter_items,
                with_closed: operations.with_closed
            }).then(function (response) {
                window.history.pushState("", "", "?" + filter.compactFilters());
                operations.operations = response.data['result'];
            })
        },
        --}}
        getTypeIcon(type) {
            switch (type.toLowerCase()) {
                case 'поступление':
                    return 'fa fa-plus text-success';
                case 'списание':
                    return 'fa fa-minus text-danger';
                case 'преобразование':
                    return 'fa fa-sync-alt text-primary';
                case 'перемещение':
                    // return 'fa fa-truck-moving text-primary';
                    // return 'fa fa-exchange-alt text-primary';
                    return 'fa fa-arrow-right text-primary';
                default:
                    return 'fa fa-sync-alt text-primary';

            }
        },
        tableRowClassName(row, rowIndex) {
            if (row.row.status === 1) {
                return '';
            } else if (row.row.status === 2) {
                return '';
            } else if (row.row.status === 3) {
                return 'success-row';
            } else if (row.row.status === 4) {
                return 'warning-row';
            } else if (row.row.status === 5) {
                return '';
            }
        },
        headerRowClassName(row, rowIndex) {
            if( this.isMobile ){
                return 'd-none';
            }
        },
        {{--
        print_rep() {

            var data = ('{' + '"type":[' + operations.operations.map(function(operation) { return operation.type}) +
                '],"status":[' + operations.operations.map(function(operation) { return operation.status}) +
                '],"object_text":[' + operations.operations.map(function(operation) { return '"' + operation.object_text  + '"'}) +
                '],"created_at":[' + operations.operations.map(function(operation) { return '"' + operation.created_at + '"'}) +
                '],"actual_date_to":[' + operations.operations.map(function(operation) { return '"' + operation.actual_date_to + '"'}) +
                '],"actual_date_from":[' + operations.operations.map(function(operation) { return '"' + operation.actual_date_from + '"'}) +
                '],"author_id":[' + operations.operations.map(function(operation) { return '"' + operation.author_id + '"'}) + '],' +
                '"filter_params":[' + filter.filter_items.map(function(badge) { return badge.parameter_id}) +
                '],"filter_values":[' + filter.filter_items.map(function(badge) { return badge.value_id}) + ']' + '}'
            );

            $('#print_data').val(data);


            $('#print_operations').submit();
        },

        updateResults() {
            axios.post('{{ route('building::mat_acc::report_card::filter') }}', {
                date: operations.search_date,
                filter: filter.filter_items,
                with_closed: operations.with_closed
            }).then(function (response) {
                window.history.pushState("", "", "?" + filter.compactFilters());
                operations.operations = response.data['result'];
            })
        },--}}
        onFocus: function() {
            $('.el-input__inner').blur();
        }
    },
});
{{--
var operation_excel = new Vue({
    el: '#operation_excel',
    data: {
        objects: [],
        object_id: {'code':0},
        range_dates: '',
        start_date: '',
        end_date: ''
    },
    mounted: function () {
        axios.post('{{ route('building::mat_acc::report_card::get_objects') }}').then(function (response) {
            operation_excel.objects = response.data;
        })
    },
    methods: {
        search(query) {
            if (query !== '') {
                  setTimeout(() => {
                        axios.post('{{ route('building::mat_acc::report_card::get_objects') }}', {q: query}).then(function (response) {
                            operation_excel.objects = response.data;
                        })
                    }, 100);
            } else {
                axios.post('{{ route('building::mat_acc::report_card::get_objects') }}').then(function (response) {
                    operation_excel.objects = response.data;
                })
            }
        },
        onFocus: function() {
            $('.el-input__inner').blur();
        },
        checkObject() {
            if (this.object_id == '' || this.object_id.code == 0) {
                this.$message({
                  showClose: true,
                  message: 'Заполните поле объект.',
                  type: 'error',
                  duration: 5000
                });
            } else {
                $('#formExportToExcel').submit();
            }
        }
    }
})
--}}
</script>

@endsection
