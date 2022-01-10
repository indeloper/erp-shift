@extends('layouts.app')
@section('title', 'Задачи')
@section('url', route('tasks::index'))
@section('css_top')
<style>
    .periods__user-info {
        text-align: right;
        padding-bottom: 0.6rem;
    }
    .periods__user-time {
        max-width: 100px;
        margin-bottom: 0.6rem;
    }
    @media (max-width: 767px) {
        .periods__user-info {
            text-align: center;
        }
    }
</style>
@endsection
@section('content')
@php setlocale(LC_TIME, 'ru'); @endphp
<div class="row">
    <div class="col-md-12 col-xl-11 mr-auto ml-auto">
        <div class="row task-card">
            <div class="col-md-4 tasks-sidebar">
                <div class="card tasks-sidebar__item tasks-sidebar__item1">
                    <div class="card-body">
                        <div class="tasks-sidebar__text-unit">
                            <span class="tasks-sidebar__head-title">
                                Проект
                            </span>
                            <span class="tasks-sidebar__body-title">
                               {{ $task->project->name }}
                            </span>
                        </div>
                        <div class="tasks-sidebar__text-unit">
                            <span class="tasks-sidebar__head-title">
                                Время создания
                            </span>
                            <span class="tasks-sidebar__body-title">
                               {{ $task->created_at }}
                            </span>
                        </div>
                        <div class="tasks-sidebar__text-unit">
                            <span class="tasks-sidebar__head-title">
                                Автор
                            </span>
                            <span class="tasks-sidebar__body-title">
                                @if($task->user_id) <a href="{{ route('users::card', $task->user_id) }}" class="tasks-sidebar__author">{{ $task->full_name }}</a> @else Система @endif
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card tasks-sidebar__item">
                    <div class="card-body">
                        <div class="accordions" id="links-accordion">
                            <div class="card" style="margin-bottom:0; word-wrap: normal">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <a class="collapsed tasks-sidebar__collapsed-link" data-target="#collapse1" href="#" data-toggle="collapse">
                                            Расшифровка аббревиатур
                                            <b class="caret"></b>
                                        </a>
                                    </h5>
                                </div>
                                <div id="collapse1" class="card-collapse collapse">
                                    <div class="card-body tasks-sidebar__body-links">
                                        <dl class="row mb-0">
                                            <dt class="col-sm-2">Б</dt>
                                            <dd class="col-sm-10">Больничный</dd>
                                            <dt class="col-sm-2">БИР</dt>
                                            <dd class="col-sm-10">Отпуск по беременности и родам</dd>
                                            <dt class="col-sm-2">Д</dt>
                                            <dd class="col-sm-10">Отпуск по уходу за ребенком</dd>
                                            <dt class="col-sm-2">З</dt>
                                            <dd class="col-sm-10">Отпуск за свой счет</dd>
                                            <dt class="col-sm-2">Н</dt>
                                            <dd class="col-sm-10">Отсутствие по невыясненной причине</dd>
                                            <dt class="col-sm-2">О</dt>
                                            <dd class="col-sm-10">Отпуск</dd>
                                            <dt class="col-sm-2">П</dt>
                                            <dd class="col-sm-10">Прогул</dd>
                                            <dt class="col-sm-2">У</dt>
                                            <dd class="col-sm-10">Учебный отпуск</dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-9 task-header__title">
                                <h4>{{ $task->name }}</h4>
                            </div>
                            <div class="col-md-3 text-right" style="margin-top:3px;">
                                до
                                <span class="task-header__date">
                                     {{ \Carbon\Carbon::parse($task->expired_at)->isoFormat('Do MMMM') }}
                                </span>
                                <span class="task-header__time">
                                    {{ \Carbon\Carbon::parse($task->expired_at)->format('H:m') }}
                                </span>
                            </div>
                        </div>
                        <hr style="margin-top:7px;border-color:#F6F6F6">
                    </div>
                    <div class="card-body task-body">
                        <div class="row">
                            <div id="main_descr" class="col-md-12">
                                <h6 style="margin-top:0">
                                    Описание
                                </h6>
                                <div id="description">
                                    <p>Необходимо отметить время заступления сотрудников на смену (время в формате H:mm или один из вариантов причины отсутствия)</p>
                                </div>
                                <div id="base" class="modal-body" v-cloak>
                                    @if(! $task->is_solved)
                                        @if(Auth::id() == $task->responsible_user_id)
                                        <transition-group
                                            name="user-list"
                                            tag="div"
                                            v-bind:css="false"
                                            v-on:leave="leave"
                                        >
                                            <template v-for="(user, index) in users">
                                                <div class="row" :key="'row-' + user.id">
                                                    <div class="col-12 col-md-5 periods__user-info" style="line-height: 1.1">
                                                        <span class="font-weight-bold">@{{ user.long_full_name }}</span><br>
                                                        <small>@{{ user.group_name }}<br>@{{ user.company_name }}</small>
                                                    </div>
                                                    <div class="col-12 col-md-7">
                                                        <div class="form-row justify-content-center justify-content-md-start">
                                                            <div class="col-auto">
                                                                <el-select v-model="user.working_status"
                                                                        placeholder="Статус"
                                                                        class="periods__user-time"
                                                                >
                                                                    <el-option
                                                                        v-for="item in statuses"
                                                                        :key="item.id"
                                                                        :label="item.name"
                                                                        :value="item.id"
                                                                    ></el-option>
                                                                </el-select>
                                                            </div>
                                                            <div class="col-auto">
                                                                <el-time-picker
                                                                    :disabled="user.working_status !== 1"
                                                                    class="periods__user-time"
                                                                    v-model="user.period.start"
                                                                    placeholder="Время заступления"
                                                                    format="H:mm"
                                                                    value-format="H-mm"
                                                                ></el-time-picker>
                                                            </div>
                                                            <div class="col-auto">
                                                                <el-button type="primary"
                                                                        :disabled="user.working_status === 1 && !user.period.start"
                                                                        :loading="user.loading"
                                                                        @click.stop="save(user)"
                                                                >Сохранить</el-button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <hr v-if="index !== users.length - 1" :key="'hr-' + user.id">
                                            </template>
                                        </transition-group>
                                        @endif
                                    @elseif($task->is_solved)
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <p>
                                                    {{ $task->get_result }}
                                                </p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                      </div>
                      <div class="card-footer">
                        <div class="row" style="margin-top:25px">
                            <div class="col-md-3 btn-center">
                                <a href="{{ route('tasks::index') }}" class="btn btn-wd">Назад</a>
                            </div>
                            {{--<div class="col-md-9 text-right btn-center" id="bottom_buttons" v-cloak>
                                @if(Auth::id() == $task->responsible_user_id and ! $task->is_solved)
                                    <el-button type="primary" @click.stop="submit" :loading="loadings[i]">Отправить</el-button>
                                @endif
                            </div>--}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@if(! $task->is_solved and Auth::id() == $task->responsible_user_id)
    @push('js_footer')
        <script src="{{ asset('js/velocity.min.js') }}"></script>
        <script>
            var vm = new Vue({
                el: '#base',
                data: {
                    {{--statuses: {!! json_encode($statuses) !!},--}}
                    statuses: [
                        {
                            id: 1,
                            name: '------',
                        },
                        {
                            id: 2,
                            name: 'Б',
                        },
                        {
                            id: 3,
                            name: 'БИР',
                        },
                        {
                            id: 4,
                            name: 'Д',
                        },
                        {
                            id: 5,
                            name: 'З',
                        },
                        {
                            id: 6,
                            name: 'Н',
                        },
                        {
                            id: 7,
                            name: 'О',
                        },
                        {
                            id: 8,
                            name: 'П',
                        },
                        {
                            id: 9,
                            name: 'У',
                        },
                    ],
                    users: {!! json_encode($users) !!},
                },
                mounted() {
                    if ($(window).width() > 768) {
                        $('#collapse1').collapse('show');
                    }
                },
                methods: {
                    save(user) {
                        user.loading = true;
                        this.$forceUpdate();
                        const payload = {
                            timecard_day_id: user.timecard_day_id,
                        };

                        if (user.working_status === 1 && user.period.start) {
                            payload.periods = [{
                                id: user.period.id,
                                project_id: user.period.project_id,
                                start: user.period.start[0] === '0' && user.period.start.length > 4 ? user.period.start.slice(1) : user.period.start,
                            }];
                            payload.task_id =  '{{ $task->id }}';
                            axios.put('{{ route('human_resources.timecard_day.update_time_periods', 'TIMECARD_DAY_ID') }}'
                                    .split('TIMECARD_DAY_ID').join(user.timecard_day_id), payload)
                                    .then(response => {
                                        this.users.splice(this.users.indexOf(user), 1);
                                        user.loading = false;
                                        this.$forceUpdate();
                                        this.$message.success('Изменения успешно сохранены.');
                                        // if (this.users.length === 0) {
                                        //     window.location.href = '{{route('tasks::index')}}';
                                        // }
                                    })
                                    .catch(error => {
                                        user.loading = false;
                                        this.$forceUpdate();
                                        this.$message.error('Произошла ошибка.');
                                    });
                        } else if (user.working_status > 1) {
                            payload.periods = [{
                                id: user.period.id,
                                project_id: user.period.project_id,
                                commentary: this.statuses[this.statuses.map(status => status.id).indexOf(user.working_status)].name,
                            }];
                            payload.task_id =  '{{ $task->id }}';
                            axios.put('{{ route('human_resources.timecard_day.update_time_periods', 'TIMECARD_DAY_ID') }}'
                                .split('TIMECARD_DAY_ID').join(user.timecard_day_id), payload)
                                .then(response => {
                                    this.users.splice(this.users.indexOf(user), 1);
                                    user.loading = false;
                                    this.$forceUpdate();
                                    this.$message.success('Изменения успешно сохранены.');
                                    // if (this.users.length === 0) {
                                    //     window.location.href = '{{route('tasks::index')}}';
                                    // }
                                })
                                .catch(error => {
                                    user.loading = false;
                                    this.$forceUpdate();
                                    this.$message.error('Произошла ошибка.');
                                });
                        }
                    },
                    leave: function (el, done) {
                        Velocity(
                            el,
                            { opacity: 0, height: 0 },
                            { complete: done }
                        );
                    }
                }
            });
      </script>
  @endpush
@endif
