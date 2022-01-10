@extends('layouts.app')

@section('title', $data['source'] === 'job_category' ? 'Должностные категории' :
                  ($data['source'] === 'project' ? 'Проекты'
                    : 'Бригады'))

@section('url', route($data['source'] === 'job_category' ? 'human_resources.job_category.index' :
                  ($data['source'] === 'project' ? 'projects::index'
                    : 'human_resources.brigade.index')))

@section('css_top')
    <script>
        if (localStorage.getItem('userprofilevisited')) {
            window.location.reload(true);
            localStorage.removeItem('userprofilevisited');
        }
    </script>
    <link rel="stylesheet" href="{{ asset('css/balloon.css') }}">
    <style media="screen">
        th.text-truncate {
            position: relative;
            overflow: visible;
            cursor: auto !important;
        }
        @media (min-width: 768px) {
            span.text-truncate {
                max-width: 50px;
            }
        }
        @media (min-width: 1200px) {
            span.text-truncate {
                max-width: 80px;
            }
        }
        @media (min-width: 1360px) {
            span.text-truncate {
                max-width: 140px;
            }
        }
        @media (min-width: 1560px) {
            span.text-truncate {
                max-width: 220px;
            }
        }
        @media (min-width: 1920px) {
            span.text-truncate {
                max-width: 300px;
            }
        }
        @media (max-width: 769px) {
            .responsive-button {
                width: 100%;
            }
        }

        .el-radio-button__inner, .el-radio-group {
            width: 100%;
        }
        .el-radio-button {
            width: 50%;
        }
        [data-balloon],
        [data-balloon]:before,
        [data-balloon]:after {
            z-index: 9999;
        }
    </style>
@endsection

@section('content')
    <div class="row" id="base" v-cloak>
        <div class="col-md-12 mobile-card">
            <div class="col-md-12 mobile-card">
                <div aria-label="breadcrumb" role="navigation">
                    <ol class="breadcrumb">
                        @switch($data['source'])
                            @case('job_category')
                                <li class="breadcrumb-item">
                                    <a href="{{ route('human_resources.job_category.index') }}" class="table-link">Должностные категории</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('human_resources.job_category.show', $data['job_category']->id) }}" class="table-link">{{ $data['job_category']->name }}</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">Список сотрудников</li>
                            @break
                            @case('brigade')
                                <li class="breadcrumb-item">
                                    <a href="{{ route('human_resources.brigade.index') }}" class="table-link">Бригады</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('human_resources.brigade.show', $data['brigade']->id) }}" class="table-link">{{ $data['brigade']->number }}</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">Список сотрудников</li>
                            @break
                            @case('project')
                            <li class="breadcrumb-item">
                                <a href="{{ route('projects::index') }}" class="table-link">Проекты</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('projects::card', [$data['project']->id, 'project_id' => $data['project']->id, 'contractor_id' => $data['project']->contractor_id]) }}" class="table-link">{{ $data['project']->name }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Список сотрудников</li>
                            @break
                        @endswitch
                    </ol>
                </div>
            </div>
            <div class="card col-xl-10 mr-auto ml-auto pd-0-min">
                <div class="card-body card-body-tech">
                    <h4 class="h4-tech fw-500 m-0" style="margin-top:0">
                        <span v-pre>
                            @switch($data['source'])
                                @case('job_category')
                                Должностная категория {{ $data['job_category']->name }}
                                @break
                                @case('brigade')
                                Бригада {{ $data['brigade']->number }}
                                @break
                                @case('project')
                                Проект {{ $data['project']->name }}
                                @break
                            @endswitch
                        </span>
                    </h4>
                    <a v-if="displayType === 'Бригады'" class="tech-link modal-link d-block ml-1">
                        Найдено бригад: @{{ brigadesCount }}
                    </a>
                    <a v-else class="tech-link modal-link d-block ml-1">
                        Найдено сотрудников: @{{ source === 'project' ? usersCount : totalItems }}
                    </a>
                    <div class="fixed-table-toolbar toolbar-for-btn" style="padding-left:0">
                        <div :class="{'row flex-md-row-reverse' : source === 'project', 'row' : source !== 'project'}">
                            @if($data['source'] === 'project')
                                <div :class="{'col-xl-4': window_width >= 1600, 'col-md-6 text-right': true}">
                                    <el-radio-group v-model="displayType">
                                        <el-radio-button label="Сотрудники"></el-radio-button>
                                        <el-radio-button label="Бригады"></el-radio-button>
                                    </el-radio-group>
                                </div>
                                <div :class="{'d-none': window_width < 1600, 'col-md-2': true}"></div>
                            @endif
                            @can('human_resources_job_category_create')
                                <div class="col-md-6">
                                    <div v-if="displayType === 'Сотрудники'" class="d-md-flex">
                                            <el-select v-model="newUserId"
                                                       clearable filterable
                                                       :remote-method="searchUsers"
                                                       @clear="searchUsers('')"
                                                       ref="search-users-input"
                                                       class="mt-10__mobile mr-3"
                                                       remote
                                                       placeholder="Поиск сотрудника"
                                            >
                                                <el-option
                                                    v-for="item in foundUsers"
                                                    :key="item.id"
                                                    :label="item.name"
                                                    :value="item.id"
                                                ></el-option>
                                            </el-select>
                                        <div class="mt-10__mobile align-self-center">
                                            <el-button @click.stop="addUser"
                                                       :loading="loading"
                                                       icon="el-icon-plus"
                                                       size="small"
                                                       type="primary"
                                                       style="margin: 0 !important;"
                                                       round
                                                       class="responsive-button"
                                            >@{{ addUserButtonLabel }}</el-button>
                                        </div>
                                    </div>
                                    <div v-else class="d-md-flex">
                                        <el-select v-model="newBrigadeId"
                                                   clearable filterable
                                                   :remote-method="searchBrigades"
                                                   @clear="searchBrigades('')"
                                                   ref="search-brigades-input"
                                                   class="mt-10__mobile mr-3"
                                                   remote
                                                   placeholder="Поиск бригады"
                                        >
                                            <el-option
                                                v-for="item in foundBrigades"
                                                :key="item.id"
                                                :label="item.name"
                                                :value="item.id"
                                            ></el-option>
                                        </el-select>
                                        <div class="mt-10__mobile align-self-center">
                                            <el-button @click.stop="addBrigade"
                                                       :loading="loading"
                                                       icon="el-icon-plus"
                                                       size="small"
                                                       type="primary"
                                                       style="margin: 0 !important;"
                                                       round
                                                       class="responsive-button"
                                            >@{{ addBrigadeButtonLabel }}</el-button>
                                        </div>
                                    </div>
                                </div>
                            @endcan
                        </div>
                    </div>
                    @include('human_resources.users')
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js_footer')
    <script>
        vm = new Vue({
            el: '#base',
            router: new VueRouter({
                mode: 'history',
                routes: [],
            }),
            data: {
                // PAGE_SIZE: 15,
                // currentPage: 1,
                CONFIRM_ERRORS: ['override'],
                skip_users_check: false,
                newUserId: '',
                newUser: {},
                newBrigadeId: '',
                newBrigade: {},
                foundUsers: [],
                foundBrigades: [],
                directions: {!! array_key_exists('directions', $data) ? json_encode($data['directions']) : json_encode([]) !!},
                totalItems: {!! json_encode($data[$data['source']]->users->count()) !!},
                users: {!! array_key_exists('project_users', $data) ? json_encode($data['project_users']) : json_encode($data[$data['source']]->users) !!},
                brigades: {!! array_key_exists('project_brigades', $data) ? json_encode($data['project_brigades']) : json_encode([]) !!},
                source: {!! json_encode($data['source']) !!},
                displayType: 'Сотрудники',
                window_width: 10000,
                loading: false,
            },
            watch: {
                newUserId(val) {
                    this.newUser = this.foundUsers.find(el => el.id === val);
                },
                newBrigadeId(val) {
                    this.newBrigade = this.foundBrigades.find(el => el.id === val);
                },
            },
            computed: {
                addUserButtonLabel() {
                    return this.window_width > 1400 || this.window_width < 769  ? 'Добавить сотрудника' : 'Сотрудник';
                },
                addBrigadeButtonLabel() {
                    return this.window_width > 1400 || this.window_width < 769  ? 'Добавить бригаду' : 'Бригада';
                },
                usersCount() {
                    return this.users.length;
                },
                brigadesCount() {
                    return this.brigades.length;
                },
            },
            created(){
                this.searchUsers('');
                this.searchBrigades('');
                /* if (this.$route.query.page && !Array.isArray(this.$route.query.page)) {
                    this.currentPage = +this.$route.query.page;
                } */
            },
            mounted() {
                $(window).on('resize', this.handleResize);
                this.handleResize();
            },
            methods: {
               /* updateFilteredUsers() {
                    @switch($data['source'])
                        @case('job_category')
                            axios.post('{{ route('users::index', ['job_category_id' => $data['job_category']->id]) }}', {url: vm.$route.fullPath, page: vm.currentPage})
                                .then(response => {
                                    vm.users = response.data.data.users;
                                    vm.totalItems = response.data.data.users_count;
                                })
                                .catch(error => console.log(error));
                        @break
                        @case('brigade')
                            axios.post('{{ route('users::index', ['brigade_id' => $data['brigade']->id]) }}', {url: vm.$route.fullPath, page: vm.currentPage})
                                .then(response => {
                                    vm.users = response.data.data.users;
                                    vm.totalItems = response.data.data.users_count;
                                })
                                .catch(error => console.log(error));
                        @break
                        @case('project')
                        axios.post('{{ route('users::index', ['project_id' => $data['project']->id]) }}', {url: vm.$route.fullPath, page: vm.currentPage})
                            .then(response => {
                                vm.users = response.data.data.users;
                                vm.totalItems = response.data.data.users_count;
                            })
                            .catch(error => console.log(error));
                        @break
                    @endswitch
                }, */
                removeUser(id) {
                    swal({
                        title: 'Вы уверены?',
                        text: "Пользователь будет исключен из " +
                        @switch($data['source'])
                            @case('job_category')
                                "должностной категории!",
                            @break
                            @case('brigade')
                                "бригады!",
                            @break
                            @case('project')
                                "проекта!",
                            @break
                            @default
                                "",
                        @endswitch
                        type: 'warning',
                        showCancelButton: true,
                        cancelButtonText: 'Назад',
                        confirmButtonText: 'Удалить'
                    }).then((result) => {
                        if (result.value) {
                            @switch($data['source'])
                                @case('job_category')
                                    axios.put('{{ route('human_resources.job_category.update_users', $data['job_category']->id) }}', {
                                        deleted_user_ids: [ id ],
                                    })
                                        .then(() => {
                                            this.users.splice(this.users.findIndex(el => el.id === id), 1);
                                            this.totalItems -= 1;
                                            this.searchUsers(this.$refs['search-users-input'].query.split(',')[0]);
                                            this.hideTooltips();
                                        })
                                        .catch(error => {
                                            this.handleError(error);
                                            this.hideTooltips();
                                        });
                                @break
                                @case('brigade')
                                    axios.post('{{ route('human_resources.brigade.update_users', $data['brigade']->id) }}', {
                                        brigade_id: {{ $data['brigade']->id }},
                                        deleted_user_ids: [ id ],
                                        user_ids: this.users.map(user => user.id),
                                    })
                                        .then(() => {
                                            this.users.splice(this.users.findIndex(el => el.id === id), 1);
                                            this.totalItems -= 1;
                                            this.searchUsers(this.$refs['search-users-input'].query.split(',')[0]);
                                            this.hideTooltips();
                                        })
                                        .catch(error => {
                                            this.handleError(error);
                                            this.hideTooltips();
                                        });
                                @break
                                @case('project')
                                axios.post('{{ route('projects::detach_user', $data['project']->id) }}', {
                                    project_id: {{ $data['project']->id }},
                                    user_id: id,
                                })
                                    .then(() => {
                                        this.users.splice(this.users.findIndex(el => el.id === id), 1);
                                        this.totalItems -= 1;
                                        this.searchUsers(this.$refs['search-users-input'].query.split(',')[0]);
                                        this.hideTooltips();
                                    })
                                    .catch(error => {
                                        this.handleError(error);
                                        this.hideTooltips();
                                    });
                            @break
                            @endswitch
                        } else {
                            this.hideTooltips();
                        }
                    });
                },
                removeBrigade(id) {
                    @if(array_key_exists('project', $data))
                    swal({
                        title: 'Вы уверены?',
                        text: "Бригада будет снята с проекта!",
                        type: 'warning',
                        showCancelButton: true,
                        cancelButtonText: 'Назад',
                        confirmButtonText: 'Удалить'
                    }).then((result) => {
                        if (result.value) {
                            axios.post('{{ route('projects::detach_brigade', $data['project']->id) }}', {
                                project_id: {{ $data['project']->id }},
                                brigade_id: id,
                            })
                                .then(() => {
                                    const deletedUsersIds = [];
                                    const brigadeNumber = this.brigades.find(el => el.id === id).number;
                                    for (let i in this.users) {
                                        const userId = this.users[i].id;
                                        if (this.users[i].brigade && this.users[i].brigade.number === brigadeNumber && deletedUsersIds.indexOf(userId) === -1) {
                                            deletedUsersIds.push(userId);
                                        }
                                    }
                                    while (deletedUsersIds.length > 0) {
                                        const takenUserId = deletedUsersIds.pop();
                                        this.users.splice(this.users.findIndex(el => el.id === takenUserId), 1);
                                    }
                                    this.brigades.splice(this.brigades.findIndex(el => el.id === id), 1);
                                    this.searchBrigades(this.$refs['search-brigades-input'].query.split(' ').length > 2
                                        ? this.$refs['search-brigades-input'].query.split(' ')[2]
                                        : '');
                                    this.hideTooltips();
                                })
                                .catch(error => {
                                    this.handleError(error);
                                    this.hideTooltips();
                                });
                        } else {
                            this.hideTooltips();
                        }
                    });
                    @endif
                },
                addUser() {
                    @switch($data['source'])
                        @case('job_category')
                            if (this.newUserId) {
                                if (this.users.map(user => user.id).indexOf(this.newUserId) === -1) {
                                    this.loading = true;
                                    axios.put('{{ route('human_resources.job_category.update_users', $data['job_category']->id) }}', {
                                        user_ids: this.users.map(user => user.id).concat([ this.newUserId ]).filter((elem, i, a) => i === a.indexOf(elem)),
                                    })
                                        .then(() => {
                                            this.users.unshift(this.newUser);
                                            this.totalItems += 1;
                                            this.newUserId = '';
                                            this.newUser = {};
                                            this.searchUsers('');
                                            this.loading = false;
                                        })
                                        .catch(error => {
                                            this.handleError(error);
                                            this.loading = false;
                                        });
                                } else {
                                    this.handleError({response: { data: { errors: {1: ['Указанный пользователь уже состоит в данной должностной категории.']}}}});
                                }
                            }
                        @break
                        @case('brigade')
                            if (this.newUserId) {
                                if (this.users.map(user => user.id).indexOf(this.newUserId) === -1) {
                                    const payload = {
                                        brigade_id: {{ $data['brigade']->id }},
                                        user_ids: this.users.map(user => user.id).concat([ this.newUserId ]).filter((elem, i, a) => i === a.indexOf(elem)),
                                    };
                                    if (this.skip_users_check) {
                                        payload.skip_users_check = this.skip_users_check;
                                    }
                                    this.loading = true;
                                    axios.post('{{ route('human_resources.brigade.update_users', $data['brigade']->id) }}', payload)
                                        .then(() => {
                                            this.users.unshift(this.newUser);
                                            this.totalItems += 1;
                                            this.newUserId = '';
                                            this.newUser = {};
                                            this.searchUsers('');
                                            this.skip_users_check = false;
                                            this.loading = false;
                                        })
                                        .catch(error => {
                                            this.handleError(error);
                                            this.loading = false;
                                        });
                                } else {
                                    this.handleError({response: { data: { errors: {1: ['Указанный пользователь уже состоит в данной бригаде.']}}}});
                                }
                            }
                        @break
                        @case('project')
                            if (this.newUserId) {
                                const payload = {
                                    project_id: {{ $data['project']->id }},
                                    user_id: this.newUserId,
                                };
                                this.loading = true;
                                axios.post('{{ route('projects::appoint_user', $data['project']->id) }}', payload)
                                    .then((response) => {
                                        this.users = response.data.users;
                                        this.totalItems = this.users.length;
                                        this.newUserId = '';
                                        this.newUser = {};
                                        this.searchUsers('');
                                        this.loading = false;
                                    })
                                    .catch(error => {
                                        this.handleError(error);
                                        this.loading = false;
                                    });
                            }
                        @break
                    @endswitch
                },
                @if($data['source'] === 'project')
                addBrigade() {
                    if (this.newBrigadeId) {
                        const payload = {
                            project_id: {{ $data['project']->id }},
                            brigade_id: this.newBrigadeId,
                        };
                        this.loading = true;
                        axios.post('{{ route('projects::appoint_brigade', $data['project']->id) }}', payload)
                            .then((response) => {
                                this.users = response.data.brigade.users.concat(this.users.reverse());
                                this.brigades.unshift(response.data.brigade);
                                this.newBrigadeId = '';
                                this.newBrigade = {};
                                this.searchBrigades('');
                                this.newUserId = '';
                                this.newUser = {};
                                this.searchUsers('');
                                this.loading = false;
                            })
                            .catch(error => {
                                this.handleError(error);
                                this.loading = false;
                            });
                    }
                },
                @endif
                searchUsers(query) {
                    if (query) {
                        axios.get('{{ route('tasks::get_users') }}', {
                            params: {
                                q: query,
                            }
                        })
                            .then(response => this.foundUsers = response.data.results
                                .filter(el => this.users.map(user => user.id).indexOf(el.id) === -1)
                                .map(el => {
                                    el.name = el.text;
                                    el.long_full_name = el.text.split(',')[0];
                                    el.group_name = el.text.split(',').slice(1).map(el => el.trim()).join(' ');
                                    return el;
                                }))
                                //.filter(el => this.users.map(user => user.id).indexOf(el.id) === -1))
                            .catch(error => console.log(error));
                    } else {
                        this.foundUsers = [];
                        /* axios.get('{{ route('tasks::get_users') }}')
                            .then(response => this.foundUsers = response.data.results
                                .map(el => {
                                    el.name = el.text;
                                    el.long_full_name = el.text.split(',')[0];
                                    el.group_name = el.text.split(',').slice(1).map(el => el.trim()).join(' ');
                                    return el;
                                })
                                .filter(el => this.users.map(user => user.id).indexOf(el.id) === -1))
                            .catch(error => console.log(error)); */
                    }
                },
                searchBrigades(query) {
                     if (query) {
                        axios.get('{{ route('human_resources.brigade.get_brigades') }}', {
                            params: {
                                q: query,
                            }
                        })
                            .then(response => this.foundBrigades = response.data.map(el => ({ name: el.label, id: el.code })))
                            .catch(error => console.log(error));
                    } else {
                        this.foundUsers = [];
                        /* axios.get('{{ route('human_resources.brigade.get_brigades') }}')
                             .then(response => this.foundBrigades = response.data.map(el => ({ name: el.label, id: el.code })))
                             .catch(error => console.log(error)); */
                    }
                },
                handleError(error) {
                    const keys = Object.keys(error.response.data.errors);
                    let msg = '';
                    const enableConfirm = !keys.some(el => this.CONFIRM_ERRORS.indexOf(el) === -1);
                    for (let i in keys) {
                        switch (keys[i]) {
                            case 'add_foreman_as_brigade_user':
                                msg += `Сотрудник уже является бригадиром существующей бригады<br>`;
                                break;
                            case 'override': {
                                if (enableConfirm) {
                                    this.skip_users_check = true;
                                    const ids = error.response.data.errors[keys[i]][0].replace(/^\[+|]+$/g, '').split(',');
                                    this.confirm(ids, keys[i]);
                                }
                                break;
                            }
                            default:
                                msg += `${error.response.data.errors[keys[i]][0]}<br>`;
                        }
                    }
                    if (msg) {
                        this.$message.error({
                            dangerouslyUseHTMLString: true,
                            message: msg,
                        });
                    }
                },
                confirm(ids, errorType) {
                    if (ids.length > 0) {
                        const label = Object.entries(this.$refs)
                            .filter(ref => /^search-users-input/.test(ref[0]))
                            .find(ref => String(ref[1].value) === ids[ids.length - 1])[1].query;
                        const CUSTOM_ERROR_MESSAGES = {
                            'override': `Сотрудник ${label} уже состоит в составе существующей бригады<br>
                            <br>Исключить его из состава существующей бригады и включить в состав новой?`,
                        };
                        this.$confirm(CUSTOM_ERROR_MESSAGES[errorType], 'Подтвердите действие', {
                            dangerouslyUseHTMLString: true,
                            confirmButtonText: 'Подтвердить',
                            cancelButtonText: 'Отмена',
                            type: 'warning',
                        }).then(() => {
                            this.addUser();
                        }).catch(() => {
                            this.skip_users_check = false;
                        });
                    }
                },
                handleResize() {
                    this.window_width = $(window).width();
                },
                hideTooltips() {
                    for (let ms = 50; ms <= 1050; ms += 100) {
                        setTimeout(() => {
                            $('[data-balloon-pos]').blur();
                        }, ms);
                    }
                },
                /* changePage(page) {
                    this.$router.replace({query: Object.assign({}, this.$route.query, {page: page})}).catch(err => {});
                    this.updateFilteredUsers();
                },
                resetCurrentPage() {
                    this.$router.replace({query: Object.assign({}, this.$route.query, {page: 1})}).catch(err => {});
                    this.currentPage = 1;
                    this.updateFilteredUsers();
                }, */
            }
        });
    </script>
@endsection
