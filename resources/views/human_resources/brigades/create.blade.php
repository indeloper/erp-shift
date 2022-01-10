@extends('layouts.app')

@section('title', 'Бригады')

@section('url', route('human_resources.brigade.index'))

@section('css_top')
    <link rel="stylesheet" href="{{ asset('css/balloon.css') }}">
    <style>
        @media (max-width: 769px) {
            .h4-tech {
                margin-bottom: 0;
            }
            .responsive-button {
                width: 100%;
            }
        }

        [data-balloon],
        [data-balloon]:before,
        [data-balloon]:after {
            z-index: 9999;
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
                    <li class="breadcrumb-item active" aria-current="page">Создание бригады</li>
                </ol>
            </div>
        </div>
        <div class="card col-md-8 col-lg-6 col-xl-4 mr-auto ml-auto pd-0-min card-body-tech" id="base" v-cloak>
            <div class="card-header">
                <div class="row">
                    <div class="col-md-12">
                        <h4 class="h4-tech fw-500 mt-0">Создание бригады</h4>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <validation-observer ref="observer">
                    <div class="row mb-2">
                        <div class="col">
                            <label>Номер<span class="star">*</span></label>
                            <validation-provider rules="required" vid="number-input"
                                                 ref="number-input" v-slot="v">
                                <el-input-number
                                    class="w-100"
                                    :class="v.classes"
                                    id="number-input"
                                    :min="0"
                                    :step="1"
                                    :precision="0"
                                    placeholder="Введите номер"
                                    v-model="number"
                                ></el-input-number>
                                <div class="error-message">@{{ v.errors[0] }}</div>
                            </validation-provider>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col">
                            <label>Направление<span class="star">*</span></label>
                            <validation-provider rules="required" vid="direction-select"
                                                 ref="direction-select" v-slot="v">
                                <el-select v-model="direction"
                                           clearable
                                           :class="v.classes"
                                           id="direction-select"
                                           placeholder="Выберите направление"
                                >
                                    <el-option
                                        v-for="item in Object.entries(directions).map(entry => ({ id: entry[0], name: entry[1] }))"
                                        :key="item.id"
                                        :label="item.name"
                                        :value="item.id"
                                    ></el-option>
                                </el-select>
                                <div class="error-message">@{{ v.errors[0] }}</div>
                            </validation-provider>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <label>Бригадир</label>
                            <el-select v-model="foreman"
                                       clearable filterable
                                       :remote-method="searchForemen"
                                       @clear="searchForemen('')"
                                       ref="foreman-select"
                                       remote
                                       placeholder="Поиск бригадира"
                            >
                                <el-option
                                    v-for="item in foremen"
                                    :key="item.id"
                                    :label="item.name"
                                    :value="item.id"
                                ></el-option>
                            </el-select>
                        </div>
                    </div>
                </validation-observer>
            </div>
            <div class="card-footer text-center">
                <el-button @click.stop="submit"
                           :loading="loading"
                           type="primary"
                           round
                           class="responsive-button"
                >Сохранить</el-button>
            </div>
        </div>
    </div>
@endsection

@section('js_footer')
    <script>
        Vue.component('validation-provider', VeeValidate.ValidationProvider);
        Vue.component('validation-observer', VeeValidate.ValidationObserver);

        vm = new Vue({
            el: '#base',
            data: {
                CONFIRM_ERRORS: ['user_in_other_brigade', 'foreman_in_other_brigade'],
                number: 1,
                direction: '',
                foreman: '',

                directions: {!! json_encode($data['directions']) !!},
                foremen: [],

                skip_other_brigade_check: false,
                skip_other_brigade_foreman_check: false,
                windowWidth: 10000,
                loading: false,
            },
            created() {
                this.searchForemen('');
            },
            mounted() {
                $(window).on('resize', this.handleResize);
                this.handleResize();
            },
            watch: {
              foreman() {
                  this.skip_other_brigade_check = false;
                  this.skip_other_brigade_foreman_check = false;
              }
            },
            methods: {
                submit() {
                    this.$refs.observer.validate().then(success => {
                        if (!success) {
                            const error_field_vid = Object.keys(this.$refs.observer.errors)
                                .find(el => Array.isArray(this.$refs[el])
                                    ? this.$refs[el][0].errors.length > 0 : this.$refs[el].errors.length > 0);
                            $('#' + error_field_vid).focus();
                            return;
                        }

                        const payload = {};
                        payload.number = this.number;
                        payload.direction = this.direction;
                        if (this.foreman) {
                            payload.foreman_id = this.foreman;
                        }
                        if (this.skip_other_brigade_check) {
                            payload.skip_other_brigade_check = this.skip_other_brigade_check;
                        }
                        if (this.skip_other_brigade_foreman_check) {
                            payload.skip_other_brigade_foreman_check = this.skip_other_brigade_foreman_check;
                        }

                        this.loading = true;
                        axios.post('{{ route('human_resources.brigade.store') }}', payload)
                            .then(response => {
                                window.location = response.data.redirect;
                            })
                            .catch(error => {
                                this.handleError(error);
                                this.loading = false;
                            });
                    });
                },
                searchForemen(query) {
                    if (query) {
                        axios.get('{{ route('tasks::get_users') }}', {
                            params: {
                                q: query,
                            }
                        })
                            .then(response => this.foremen = response.data.results.map(el => { el.name = el.text; return el; }))
                            .catch(error => console.log(error));
                    } else {
                        axios.get('{{ route('tasks::get_users') }}')
                            .then(response => this.foremen = response.data.results.map(el => { el.name = el.text; return el; }))
                            .catch(error => console.log(error));
                    }
                },
                handleError(error) {
                    const keys = Object.keys(error.response.data.errors);
                    let msg = '';
                    const enableConfirm = !keys.some(el => this.CONFIRM_ERRORS.indexOf(el) === -1);
                    for (let i in keys) {
                        switch (keys[i]) {
                            case 'number':
                                msg += `Бригада с таким номером уже существует<br>`;
                                break;
                            case 'user_in_other_brigade':
                                if (enableConfirm) {
                                    const ids = error.response.data.errors[keys[i]][0].replace(/^\[+|]+$/g, '').split(',');
                                    this.confirm(ids, keys[i]);
                                }
                                break;
                            case 'foreman_in_other_brigade':
                                if (enableConfirm) {
                                    const ids = error.response.data.errors[keys[i]][0].replace(/^\[+|]+$/g, '').split(',');
                                    this.confirm(ids, keys[i]);
                                }
                                break;
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
                        const label = this.$refs['foreman-select'].query;
                        const CUSTOM_ERROR_MESSAGES = {
                            'user_in_other_brigade': `Пользователь ${label} уже состоит в составе существующей бригады.<br>
                                <br>Исключить его из состава существующей бригады и назначить бригадиром новой?`,
                            'foreman_in_other_brigade': `Пользователь ${label} уже является бригадиром существующей бригады.<br>
                                <br>Назначить его бригадиром новой бригады? (существующая при этом останется без бригадира)`,
                        };
                        this.$confirm(CUSTOM_ERROR_MESSAGES[errorType], 'Подтвердите действие', {
                            dangerouslyUseHTMLString: true,
                            confirmButtonText: 'Подтвердить',
                            cancelButtonText: 'Отмена',
                            type: 'warning',
                        }).then(() => {
                            switch (errorType) {
                                case 'user_in_other_brigade':
                                    this.skip_other_brigade_check = true;
                                    break;
                                case 'foreman_in_other_brigade':
                                    this.skip_other_brigade_foreman_check = true;
                                    break;
                            }
                            this.submit();
                        }).catch(() => {
                            switch (errorType) {
                                case 'user_in_other_brigade':
                                    this.skip_other_brigade_check = false;
                                    break;
                                case 'foreman_in_other_brigade':
                                    this.skip_other_brigade_foreman_check = false;
                                    break;
                            }
                        });
                    }
                },
                handleResize() {
                    this.windowWidth = $(window).width();
                },
            },
        });
    </script>
@endsection
