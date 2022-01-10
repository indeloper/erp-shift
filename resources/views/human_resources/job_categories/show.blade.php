@extends('layouts.app')

@section('title', 'Должностные категории')

@section('url', route('human_resources.job_category.index'))

@section('css_top')
<style media="screen">
    td .cell {
        text-align: center;
    }

    @media (max-width: 769px) {
        .h4-tech {
            margin-bottom: 0;
        }
        .responsive-button {
            width: 100%;
            margin-top: 15px;
        }
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
                    <a href="{{ route('human_resources.job_category.index') }}" class="table-link">Должностные категории</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Информация о категории</li>
            </ol>
        </div>
    </div>
    <div class="card col-xl-10 mr-auto ml-auto pd-0-min card-body-tech" id="base" v-cloak>
        <div class="card-header">
            <div class="row">
                <div class="col-md-8">
                    <h4 class="h4-tech fw-500" style="margin-top:0">Должностная категория @{{ category.name }}</h4>
                </div>
                @can('human_resources_job_categories_update')
                    <div class="col-md-4 text-right">
                        <a href="{{ route('human_resources.job_category.edit', $job_category->id) }}" class="btn btn-round btn-sm btn-primary responsive-button">Редактировать</a>
                    </div>
                @endcan
            </div>
        </div>
        <div class="card-body">
            <div class="card-section">
                <h6 class="h6-decor" style="margin-top:0">Отчётная группа</h6>
                @if($job_category->report_group_id)
                    <a href="{{ route('human_resources.report_group.show', $job_category->report_group_id) }}" class="tech-link">{{ $job_category->reportGroup->name }}</a>
                @else
                    <p class="p-light" style="white-space: pre-wrap;">Не указана</p>
                @endif
            </div>
            <div class="card-section">
                <h6 class="h6-decor">Тарифы</h6>
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
                            <th class="text-center">Ставка</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-if="category.tariffs.length === 0">
                            <td>
                                Тарифов нет
                            </td>
                            <td>
                            </td>
                        </tr>
                        <tr v-for="tariff in tariffs_filtered">
                            <td data-label="Наименование">
                                @{{ tariff.name ? tariff.name : 'Отсутствует' }}
                            </td>
                            <td class="text-center" data-label="Ставка">
                                @{{ tariff.rate }}
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-section">
                <div class="fixed-table-toolbar toolbar-for-btn" style="padding-left:0">
                    <div class="row align-bottom">
                        <div class="col-md-4">
                            <h6 class="h6-decor text-left">Сотрудники</h6>
                            <a class="tech-link modal-link d-block ml-1">
                                Численность: @{{ userCount }} чел.
                            </a>
                        </div>
                        <div class="col-md-8 text-right">
                            <a href="{{ route('human_resources.job_category.users', $job_category->id) }}" class="tech-link purple-link d-inline-block" style="margin: 35px 0px 10px 0px;">Перейти в список сотрудников</a><span class="purple-link"> → </span>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mobile-table">
                        <thead>
                        <tr>
                            <th>ФИО</th>
                            <th>Должность</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-if="users.length === 0">
                            <td>
                                Сотрудников нет
                            </td>
                            <td>
                            </td>
                        </tr>
                        <tr v-for="user in users">
                            <td data-label="ФИО">
                                @{{ user.long_full_name }}
                            </td>
                            <td data-label="Должность">
                                @{{ user.group_name }}
                            </td>
                        </tr>
                        </tbody>
                    </table>
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
            category: JSON.parse('{!! addslashes(json_encode($job_category)) !!}'),
            users: JSON.parse('{!! addslashes(json_encode($job_category->users->take(10))) !!}'),
            userCount: {!! json_encode($job_category->users->count()) !!},
        },
        computed: {
            tariffs_filtered() {
                return this.category.tariffs.filter(tarrif => {
                    //Search textfield filter
                    const search_tf_pattern = new RegExp(_.escapeRegExp(this.search_tf), 'i');
                    const search_tf_filter = search_tf_pattern.test(tarrif.name);
                    return search_tf_filter;
                });
            }
        },
    });
</script>
@endsection
