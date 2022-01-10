@extends('layouts.app')

@section('title', 'Бригады')

@section('url', route('human_resources.brigade.index'))

@section('css_top')
    <style media="screen">
        td .cell {
            text-align: center;
        }

        .el-table_1_column_1 .cell {
            text-align: left;
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
                        <a href="{{ route('human_resources.brigade.index') }}" class="table-link">Бригады</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Информация о бригаде</li>
                </ol>
            </div>
        </div>
        <div class="card col-xl-10 mr-auto ml-auto pd-0-min card-body-tech" id="base" v-cloak>
            <div class="card-header">
                <div class="row">
                    <div class="col-md-8">
                        <h4 class="h4-tech fw-500" style="margin-top:0">Бригада @{{ brigade.number }}</h4>
                    </div>
                    @can('human_resources_brigades_update')
                        <div class="col-md-4 text-right">
                            <a href="{{ route('human_resources.brigade.edit', $brigade->id) }}" class="btn btn-round btn-sm btn-primary responsive-button">Редактировать</a>
                        </div>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                <div class="card-section">
                    <h6 class="h6-decor" style="margin-top:0">Номер</h6>
                    <p class="p-light" style="white-space: pre-wrap;">{{ $brigade->number }}</p>
                </div>
                <div class="card-section">
                    <h6 class="h6-decor" style="margin-top:0">Направление</h6>
                    <p class="p-light" style="white-space: pre-wrap;">{{ $directions[$brigade->direction] }}</p>
                </div>
                <div class="card-section">
                    <h6 class="h6-decor" style="margin-top:0">Бригадир</h6>
                    @if($brigade->foreman)
                        <a href="{{ route('users::card', $brigade->foreman->id) }}" class="tech-link p-light">{{ $brigade->foreman->full_name }}</a>
                    @else
                        <p class="p-light" style="white-space: pre-wrap;">Не назначен</p>
                    @endif
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
                                <a href="{{ route('human_resources.brigade.users', $brigade->id) }}" class="tech-link purple-link d-inline-block" style="margin: 35px 0px 10px 0px;">Перейти в список сотрудников</a><span class="purple-link"> → </span>
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
                brigade: {!! json_encode($brigade) !!},
                users: JSON.parse('{!! addslashes(json_encode($brigade->users->take(10))) !!}'),
                userCount: {!! json_encode($brigade->users->count()) !!},
            },
        });
    </script>
@endsection
