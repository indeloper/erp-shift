@extends('layouts.app')

@section('title', 'Отчётные группы')

@section('url', route('human_resources.report_group.index'))

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
                        <a href="{{ route('human_resources.report_group.index') }}" class="table-link">Отчётные группы</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Создание группы</li>
                </ol>
            </div>
        </div>
        <div class="card col-lg-6 mr-auto ml-auto pd-0-min card-body-tech" id="base" v-cloak>
            <div class="card-header">
                <div class="row">
                    <div class="col-md-12">
                        <h4 class="h4-tech fw-500 mt-0">Создание отчётной группы</h4>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <validation-observer ref="observer">
                    <h6 class="decor-h6-modal">Основная информация</h6>
                    <div class="row">
                        <div class="col-md-12">
                            <label>Наименование<span class="star">*</span></label>
                            <validation-provider rules="required|max:100" vid="name-input"
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
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="decor-h6-modal">Должностные категории</h6>
                        </div>
                        <div class="col-md-6 text-right">
                            <button type="button" @click="addJobCategory"
                                    v-if="jobCategories.length < 1 && windowWidth >= 1600"
                                    style="margin: 20px 0 10px;"
                                    class="btn btn-round btn-success btn-outline add-material responsive-button">
                                <i class="fa fa-plus"></i>
                                Добавить должностную категорию
                            </button>
                        </div>
                    </div>
                    <div class="row mt-10 flex-md-nowrap align-content-end"
                         v-for="(jobCategory, i) in jobCategories">
                        <div class="col-xs-12 col-md">
                            <label class="mt-10__mobile">
                                Должностная категория @{{i+1}}<span class="star">*</span>
                            </label>
                            <validation-provider rules="required" :vid="`wrapper-job-category-select-${i+1}`"
                                                 :ref="`wrapper-job-category-select-${i+1}`" v-slot="v">
                                <el-select v-model="jobCategory.id"
                                           clearable
                                           filterable
                                           :ref="`job-category-select-${i+1}`"
                                           :class="v.classes"
                                           :id="`wrapper-job-category-select-${i+1}`"
                                           placeholder="Поиск должностной категории"
                                >
                                    <el-option
                                        v-for="item in jobCategoryOptions(i)"
                                        :key="item.id"
                                        :label="item.name"
                                        :value="item.id"
                                    ></el-option>
                                </el-select>
                                <div class="error-message">@{{ v.errors[0] }}</div>
                            </validation-provider>
                        </div>
                        <div class="col align-self-start" :style="windowWidth > 769 ? '-ms-flex-positive: 0; flex-grow: 0;' : ''">
                            <el-button type="danger"
                                       v-if="windowWidth > 769"
                                       @click="removeJobCategory(i)"
                                       data-balloon-pos="up"
                                       style="margin-top: 31px;"
                                       size="small"
                                       aria-label="Удалить"
                                       icon="el-icon-delete"
                                       circle
                            ></el-button>
                            <button type="button"
                                    v-else
                                    @click="removeJobCategory(i)"
                                    class="btn btn-danger responsive-button mt-20__mobile"
                            >Удалить должностную категорию</button>
                        </div>
                    </div>
                    <div class="row" v-if="jobCategories.length > 0 || windowWidth < 1600">
                        <div class="col-md-12 text-right" style="margin-top:25px">
                            <button type="button" @click="addJobCategory"
                                    class="btn btn-round btn-success responsive-button btn-outline add-material">
                                <i class="fa fa-plus"></i>
                                Добавить должностную категорию
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
                CONFIRM_ERRORS: ['override'],
                name: '',
                jobCategories: [],

                allJobCategories: [],

                skip_job_categories_check: false,
                windowWidth: 10000,
                loading: false,
            },
            computed: {
                filteredJobCategories() {
                    return this.allJobCategories.filter(el => this.jobCategories.map(cat => cat.id).indexOf(el.id) === -1);
                }
            },
            created() {
                this.searchJobCategories('');
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
                        payload.name = this.name;
                        if (this.jobCategories.length > 0) {
                            payload.job_categories = this.jobCategories.map(el => el.id);
                        }
                        if (this.skip_job_categories_check) {
                            payload.skip_job_categories_check = this.skip_job_categories_check;
                        }

                        this.loading = true;
                        axios.post('{{ route('human_resources.report_group.store') }}', payload)
                            .then(response => {
                                window.location = response.data.redirect;
                            })
                            .catch(error => {
                                this.handleError(error);
                                this.loading = false;
                            });
                    });
                },
                addJobCategory() {
                    this.jobCategories.push({ id: '', });
                },
                removeJobCategory(index) {
                    this.jobCategories.splice(index, 1);
                },
                jobCategoryOptions(index) {
                    return this.jobCategories[index].id ? [this.allJobCategories.find(el => el.id === this.jobCategories[index].id)].concat(this.filteredJobCategories) : this.filteredJobCategories;
                },
                searchJobCategories(query) {
                    if (query) {
                        axios.get('{{ route('human_resources.job_category.get') }}', {
                            params: {
                                q: query,
                            }
                        })
                            .then(response => this.allJobCategories = response.data)
                            .catch(error => console.log(error));
                    } else {
                        axios.get('{{ route('human_resources.job_category.get') }}')
                            .then(response => this.allJobCategories = response.data)
                            .catch(error => console.log(error));
                    }
                },
                handleError(error) {
                    const keys = Object.keys(error.response.data.errors);
                    let msg = '';
                    const enableConfirm = !keys.some(el => this.CONFIRM_ERRORS.indexOf(el) === -1);
                    for (let i in keys) {
                        switch (keys[i]) {
                            case 'name':
                                msg += `${error.response.data.errors[keys[i]][0]}<br>`;
                                break;
                            case 'override': {
                                if (enableConfirm) {
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
                        const labels = Object.entries(this.$refs)
                            .filter(ref => /^job-category-select/.test(ref[0]))
                            .filter(ref => ref[1][0].value ? ids.indexOf(ref[1][0].value) !== -1 : false)
                            .map(el => `<li>${el[1][0].query}</li>`);
                        const CUSTOM_ERROR_MESSAGES = {
                            'override': `<div>Следующие должностные категории уже состоят в составе отчётных групп:</div>
                                    <ul style="margin: 0">
                                        ${labels.join('')}
                                    </ul>
                                <div>Изменить отчётную группу этих должностных категорий?</div>`,
                        };
                        this.$confirm(CUSTOM_ERROR_MESSAGES[errorType], 'Подтвердите действие', {
                            dangerouslyUseHTMLString: true,
                            confirmButtonText: 'Подтвердить',
                            cancelButtonText: 'Отмена',
                            type: 'warning',
                        }).then(() => {
                            this.skip_job_categories_check = true;
                            this.submit();
                        }).catch(() => {});
                    }
                },
                handleResize() {
                    this.windowWidth = $(window).width();
                },
            },
        });
    </script>
@endsection
