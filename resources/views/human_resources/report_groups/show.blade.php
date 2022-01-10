@extends('layouts.app')

@section('title', 'Отчётные группы')

@section('url', route('human_resources.report_group.index'))

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

        @media (max-width: 769px) {
            .h4-tech {
                margin-bottom: 0;
            }
            .responsive-button {
                width: 100%;
                margin-top: 15px;
            }
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
                        <a href="{{ route('human_resources.report_group.index') }}" class="table-link">Отчётные группы</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Информация о группе</li>
                </ol>
            </div>
        </div>
        <div class="card col-xl-10 mr-auto ml-auto pd-0-min card-body-tech" id="base" v-cloak>
            <div class="card-header">
                <div class="row">
                    <div class="col-md-8">
                        <h4 class="h4-tech fw-500" style="margin-top:0">Отчётная группа @{{ report_group.name }}</h4>
                    </div>
                    @can('human_resources_report_group_update')
                        <div class="col-md-4 text-right">
                            <a href="{{ route('human_resources.report_group.edit', $data['report_group']->id) }}"
                               class="btn btn-round btn-sm btn-primary responsive-button">Редактировать</a>
                        </div>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                <div class="card-section">
                    <h6 class="h6-decor">Должностные категории</h6>
                    <a class="tech-link modal-link d-block ml-1">
                        Количество: @{{ job_categories.length }}
                    </a>
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
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-if="job_categories.length === 0">
                                <td>
                                    Должностных категорий нет
                                </td>
                            </tr>
                            <tr v-for="job_category in job_categories_filtered">
                                <td data-label="Наименование">
                                    @{{ job_category.name ? job_category.name : 'Отсутствует' }}
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
                report_group: JSON.parse('{!! addslashes(json_encode($data['report_group'])) !!}'),
                job_categories: JSON.parse('{!! addslashes(json_encode($data['job_categories'])) !!}')
            },
            computed: {
                job_categories_filtered() {
                    return this.job_categories.filter(category => {
                        //Search textfield filter
                        const search_tf_pattern = new RegExp(_.escapeRegExp(this.search_tf), 'i');
                        const search_tf_filter = search_tf_pattern.test(category.name);
                        return search_tf_filter;
                    });
                }
            },
        });
    </script>
@endsection
