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
                <li class="breadcrumb-item active" aria-current="page">Информация о категории</li>
            </ol>
        </div>
        <div class="card col-xl-10 mr-auto ml-auto pd-0-min card-body-tech" id="base" v-cloak>
            <div class="card-header">
                <div class="row">
                    <div class="col-md-8">
                        <h4 class="h4-tech fw-500" style="margin-top:0">{{ $vehicle_category->name }}</h4>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="card-section">
                    <h6 class="h6-decor" style="margin-top:0">Описание</h6>
                    <p class="p-light" style="white-space: pre-wrap;">{{ $vehicle_category->description ?? 'Не указано' }}.</p>
                </div>
                <div class="card-section">
                    <h6 class="h6-decor">Параметры</h6>
                    <div class="fixed-table-toolbar toolbar-for-btn" style="padding-left:0">
                        <div class="row">
                            <div class="col-sm-6 col-md-4">
                                <input class="form-control" type="text" v-model="search_tf" placeholder="Поиск по наименованию">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mobile-table">
                            <thead>
                                <tr>
                                    <th>Наименование</th>
                                    <th class="text-center">Ед. измерения</th>
                                    <th class="text-center">Отображать в таблице</th>
                                    <th class="text-center">Обязательное</th>
                                    <th class="">Краткое наименование</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="characteristics_filtered.length === 0">
                                    <td data-label="Наименование">
                                        Характеристик нет.
                                    </td>
                                    <td class="text-center" data-label="Ед. измерения">
                                    </td>
                                    <td class="text-center" data-label="Отображать в таблице">
                                    </td>
                                    <td class="text-center" data-label="Обязательное">
                                    </td>
                                    <td data-label="Краткое наименование">
                                    </td>
                                </tr>
                                <tr v-for="char in characteristics_filtered">
                                    <td data-label="Наименование">
                                        @{{ char.name }}
                                    </td>
                                    <td class="text-center" data-label="Ед. измерения">
                                        @{{ char.unit }}
                                    </td>
                                    <td class="text-center" data-label="Отображать в таблице">
                                        @{{ char.show ? 'да' : 'нет'}}
                                    </td>
                                    <td class="text-center" data-label="Обязательное">
                                        @{{ char.required ? 'да' : 'нет'}}
                                    </td>
                                    <td data-label="Краткое наименование">
                                        @{{ char.short_name }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row" style="margin-top:50px">
                    <div class="col-md-12 text-right">
                        <a href="{{ route('building::vehicles::vehicle_categories.our_vehicles.index_trashed', $vehicle_category->id) }}" class="tech-link purple-link">Перейти в список транспорта</a><span class="purple-link"> → </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('js_footer')
    <script type="text/javascript">
        vm = new Vue({
            el: '#base',
            data: {
                search_tf: '',
                category: {!! json_encode($vehicle_category) !!},
            },
            computed: {
                characteristics_filtered() {
                    return this.category.characteristics.filter(characteristic => {
                        //Search textfield filter
                        const search_tf_pattern = new RegExp(_.escapeRegExp(this.search_tf), 'i');
                        const search_tf_filter = search_tf_pattern.test(characteristic.name) || search_tf_pattern.test(characteristic.short_name);
                        return search_tf_filter;
                    });
                }
            },
        });
    </script>
@endsection
