@extends('layouts.app')

@section('title', 'Выплаты и удержания')

@section('url', route('human_resources.payment.index'))

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
                        <a href="{{ route('human_resources.payment.index') }}" class="table-link">Выплаты и удержания</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Изменение сокращенного наименования</li>
                </ol>
            </div>
        </div>
        <div class="card col-md-8 col-xl-6 mr-auto ml-auto pd-0-min card-body-tech" id="base" v-cloak>
            <div class="card-header">
                <div class="row">
                    <div class="col-md-12">
                        <h4 class="h4-tech fw-500 mt-0">Изменение сокращенного наименования</h4>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <validation-observer ref="observer">
                    <div class="row">
                        <div class="col">
                            <label>Сокращенное наименование</label>
                            <validation-provider rules="max:20" vid="short-name-input"
                                                 ref="short-name-input" v-slot="v">
                                <el-input
                                    :class="v.classes"
                                    maxlength="20"
                                    id="short-name-input"
                                    clearable
                                    placeholder="Введите сокращенное наименование"
                                    v-model="shortName"
                                ></el-input>
                                <div class="error-message">@{{ v.errors[0] }}</div>
                            </validation-provider>
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
                shortName: '',
                windowWidth: 10000,
                loading: false,
            },
            created() {
                // this.short_name = this.payment.short_name;
            },
            mounted() {
                $(window).on('resize', this.handleResize);
                this.handleResize();
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
                        // payload.payment_id = this.payment.id;
                        payload.short_name = this.shortName;

                        this.loading = true;
                        axios.put(' {{ route('human_resources.payment.update', $data['payment']->id) }} ', payload)
                            .then(response => {
                                window.location = response.data.redirect;
                            })
                            .catch(error => {
                                this.handleError(error);
                                this.loading = false;
                            });
                    });
                },
                handleError(error) {
                    const keys = Object.keys(error.response.data.errors);
                    let msg = '';
                    for (let i in keys) {
                        msg += `${error.response.data.errors[keys[i]][0]}<br>`;
                    }
                    if (msg) {
                        this.$message.error({
                            dangerouslyUseHTMLString: true,
                            message: msg,
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
