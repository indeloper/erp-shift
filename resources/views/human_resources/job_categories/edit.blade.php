@extends('layouts.app')

@section('title', 'Должностные категории')

@section('url', route('human_resources.job_category.index'))

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
                        <a href="{{ route('human_resources.job_category.index') }}" class="table-link">Должностные категории</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Редактирование категории</li>
                </ol>
            </div>
        </div>
        <div class="card col-xl-9 mr-auto ml-auto pd-0-min card-body-tech" id="base" v-cloak>
            <div class="card-header">
                <div class="row">
                    <div class="col-md-12">
                        <h4 class="h4-tech fw-500 mt-0">Редактирование должностной категории</h4>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <validation-observer ref="observer">
                    <h6 class="decor-h6-modal">Основная информация</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <label>Наименование<span class="star">*</span></label>
                            <validation-provider rules="required|max:100|min:5" vid="name-input"
                                                 ref="name-input" v-slot="v">
                                <el-input
                                    :class="v.classes"
                                    maxlength="100"
                                    id="name-input"
                                    clearable
                                    placeholder="Введите наименование"
                                    v-model="name"
                                ></el-input>
                                <div class="error-message">@{{ v.errors[0] }}</div>
                            </validation-provider>
                        </div>
                        <div class="col-md-6">
                            <label>Отчётная группа</label>
                            <el-select v-model="reportGroup"
                                       clearable filterable
                                       :remote-method="searchReportGroups"
                                       @clear="searchReportGroups('')"
                                       remote
                                       placeholder="Поиск отчётной группы"
                            >
                                <el-option
                                    v-for="item in reportGroups"
                                    :key="item.id"
                                    :label="item.name"
                                    :value="item.id"
                                ></el-option>
                            </el-select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="decor-h6-modal">Создание тарифов</h6>
                        </div>
                        <div class="col-md-6 text-right">
                            <button type="button" @click="addRate"
                                    v-if="rates.length < 1"
                                    style="margin: 20px 0 10px;"
                                    class="btn btn-round btn-success btn-outline add-material responsive-button">
                                <i class="fa fa-plus"></i>
                                Добавить
                            </button>
                        </div>
                    </div>
                    <div class="row mt-10 flex-md-nowrap align-content-end"
                         v-for="(rate, i) in rates">
                        <div class="col-xs-12 col-md">
                            <label class="mt-10__mobile">
                                Тариф @{{i+1}}<span class="star">*</span>
                            </label>
                            <validation-provider rules="required" :vid="`tariff-select-${i+1}`"
                                                 :ref="`tariff-select-${i+1}`" v-slot="v">
                                <el-select v-model="rate.tariff"
                                           clearable
                                           filterable
                                           :class="v.classes"
                                           :id="`tariff-select-${i+1}`"
                                           placeholder="Поиск тарифа"
                                >
                                    <el-option
                                        v-for="item in tariffOptions(i)"
                                        :key="item.id"
                                        :label="item.name"
                                        :value="item.id"
                                    ></el-option>
                                </el-select>
                                <div class="error-message">@{{ v.errors[0] }}</div>
                            </validation-provider>
                        </div>
                        <div class="col-xs-12 col-md">
                            <label class="mt-10__mobile">
                                Ставка<span class="star">*</span>
                            </label>
                            <validation-provider rules="required|positive" :vid="`rate-input-${i+1}`"
                                                 :ref="`rate-input-${i+1}`" v-slot="v">
                                <el-input-number
                                    class="w-100"
                                    maxlength="10"
                                    :class="v.classes"
                                    :id="`rate-input-${i+1}`"
                                    :min="0"
                                    :step="1"
                                    placeholder="Введите ставку"
                                    v-model="rates[i].rate"
                                ></el-input-number>
                                <div class="error-message">@{{ v.errors[0] }}</div>
                            </validation-provider>
                        </div>
                        <div class="col align-self-start" :style="windowWidth > 769 ? ' -ms-flex-positive: 0; flex-grow: 0;' : ''">
                            <el-button type="danger"
                                       v-if="windowWidth > 769"
                                       @click="removeRate(i)"
                                       data-balloon-pos="up"
                                       style="margin-top: 31px;"
                                       size="small"
                                       aria-label="Удалить"
                                       icon="el-icon-delete"
                                       circle
                            ></el-button>
                            <button type="button"
                                    v-else
                                    @click="removeRate(i)"
                                    class="btn btn-danger responsive-button mt-20__mobile"
                            >Удалить тариф</button>
                        </div>
                    </div>
                    <div class="row" v-if="rates.length > 0 && rates.length < 12">
                        <div class="col-md-12 text-right" style="margin-top:25px">
                            <button type="button" @click="addRate"
                                    class="btn btn-round btn-success responsive-button btn-outline add-material">
                                <i class="fa fa-plus"></i>
                                Добавить тариф
                            </button>
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
                jobCategory: {!! json_encode($data['job_category']) !!},

                name: '',
                reportGroup: '',
                reportGroupGiven: false,
                rates: [],

                reportGroups: [],
                tariffs: {!! json_encode($data['tariff_rates']) !!},

                deletedTariffs: [],

                windowWidth: 10000,
                loading: false,
            },
            computed: {
                filteredTariffs() {
                    return this.tariffs.filter(el => this.rates.map(el => el.tariff).indexOf(el.id) === -1);
                }
            },
            created() {
                this.searchReportGroups('');
                this.name = this.jobCategory.name;
                this.reportGroup = this.jobCategory.report_group_id ? String(this.jobCategory.report_group_id) : '';
                for (const rate in this.jobCategory.tariffs) {
                    this.rates.push({
                        id: this.jobCategory.tariffs[rate].id,
                        tariff: this.jobCategory.tariffs[rate].tariff_id,
                        rate: this.jobCategory.tariffs[rate].rate,
                    });
                }
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
                        payload.job_category = this.jobCategory.id;
                        payload.name = this.name;
                        if (this.reportGroup !== this.jobCategory.report_group_id) {
                            payload.report_group_id = this.reportGroup;
                        }
                        if (this.deletedTariffs.length > 0) {
                            payload.deleted_tariffs = this.deletedTariffs;
                        }

                        const tariffDiff = [];
                        for (const rate in this.rates) {
                            const originalTariff = this.jobCategory.tariffs.find(el => el.id === this.rates[rate].id);
                            if (originalTariff && (originalTariff.rate !== this.rates[rate].rate || originalTariff.tariff_id !== this.rates[rate].tariff)) {
                                tariffDiff.push({
                                    id: originalTariff.id,
                                    tariff_id: this.rates[rate].tariff,
                                    rate: this.rates[rate].rate,
                                });
                            } else if (!originalTariff) {
                                tariffDiff.push({
                                    tariff_id: this.rates[rate].tariff,
                                    rate: this.rates[rate].rate,
                                });
                            }
                        }

                        if (tariffDiff.length > 0) {
                            payload.tariffs = tariffDiff;
                        }

                        this.loading = true;
                        axios.put('{{ route('human_resources.job_category.update', $data['job_category']->id) }}', payload)
                            .then(response => {
                                window.location = response.data.redirect;
                            })
                            .catch(error => {
                                this.handleError(error);
                                this.loading = false;
                            });
                    });
                },
                addRate() {
                    this.rates.push({ tariff: '', rate: 0, });
                },
                removeRate(index) {
                    if (this.rates[index].id) {
                        this.deletedTariffs.push(this.rates[index].id);
                    }
                    this.rates.splice(index, 1);
                },
                tariffOptions(index) {
                    return this.rates[index].tariff ? [this.tariffs.find(el => el.id === this.rates[index].tariff)].concat(this.filteredTariffs) : this.filteredTariffs;
                },
                searchReportGroups(query) {
                    if (query) {
                        axios.get('{{ route('human_resources.report_groups.get') }}', {
                            params: {
                                q: query,
                            }
                        })
                            .then(response => this.reportGroups = response.data)
                            .catch(error => console.log(error));
                    } else {
                        axios.get('{{ route('human_resources.report_groups.get') }}')
                            .then(response => this.reportGroups = response.data)
                            .catch(error => console.log(error));
                    }
                },
                handleError(error) {
                    const keys = Object.keys(error.response.data.errors);
                    let msg = '';
                    for (let i in keys) {
                        msg += `${error.response.data.errors[keys[i]][0]}<br>`;
                        /* switch (keys[i]) {
                            case 'name':
                                msg += `${error.response.data.errors[keys[i]][0]}<br>`;
                                break;
                            default:
                                msg += `${error.response.data.errors[keys[i]][0]}<br>`;
                                this.$message.error(error.response.data.errors[keys[i]][0]);
                        } */
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
