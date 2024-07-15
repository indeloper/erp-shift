<div class="card-body task-body">
    <div class="row">
        <div id="main_descr" class="col-md-12">
            <h6 style="margin-top:0">
                Описание
            </h6>
            <div id="description">
                <p>Необходимо выполнить контроль ремонта <a href="{{ $task->taskable->card_route() }}">завки на неисправность техники</a></p>
                @if(! $task->is_solved)
                    @if(Auth::id() == $task->responsible_user_id)
                        <hr style="border-color:#F6F6F6">
                        <div id="form" v-cloak>
                            <validation-observer ref="observer" :key="observer_key">
                                <div class="row">
                                    <div class="col-md-12 text-left">
                                        <label>Результат<span class="star">*</span></label>
                                        <div class="form-group">
                                            <validation-provider rules="required" vid="result-select"
                                                ref="result-select" name="результат" v-slot="v">
                                                <el-select :class="v.classes"
                                                   id="result-select"
                                                   required
                                                   v-model="type"
                                                   clearable filterable
                                                   placeholder="Результат"
                                                   @change="refresh()"
                                                >
                                                    <el-option
                                                        v-for="item in types"
                                                        :key="item.id"
                                                        :label="item.name"
                                                        :value="item.id">
                                                    </el-option>
                                                </el-select>
                                                <div class="error-message">@{{ v.errors[0] }}</div>
                                            </validation-provider>
                                        </div>
                                    </div>
                                </div>
                                <template v-if="type == 1">
                                    <div class="row">
                                        <div class="col-md-12 mb-20 mt-20 text-left">
                                            <label for="">Местоположение<span class="star">*</span></label>
                                            <validation-provider rules="required" v-slot="v" vid="location-select"
                                                         ref="location-select">
                                                <el-select v-model="tech_location_id"
                                                           :class="v.classes"
                                                           clearable filterable
                                                           id="location-select"
                                                           :remote-method="searchLocations"
                                                           @clear="searchLocations('')"
                                                           remote
                                                           name="object_id"
                                                           placeholder="Поиск объекта"
                                                >
                                                    <el-option
                                                        v-for="item in tech_locations"
                                                        :key="item.id"
                                                        :label="item.name"
                                                        :value="item.id">
                                                    </el-option>
                                                </el-select>
                                                <div class="error-message">@{{ v.errors[0] }}</div>
                                            </validation-provider>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 mb-20 mt-20 text-left">
                                            <label for="">Комментарий<span class="star">*</span></label>
                                            <validation-provider rules="required|max:300" vid="comment-input"
                                                                 ref="comment-input" name="комментарий" v-slot="v">
                                                <el-input type="textarea"
                                                          :class="v.classes"
                                                          id="comment-input"
                                                          placeholder="Укажите комментарий"
                                                          maxlength="300"
                                                          v-model="comment"
                                                          :autosize="{ minRows:4, maxRows:6 }"
                                                          clearable
                                                ></el-input>
                                                <div class="error-message">@{{ v.errors[0] }}</div>
                                            </validation-provider>
                                        </div>
                                    </div>
                                </template>
                                <template v-else-if="type == 2">
                                    <div class="row">
                                        <div class="col-md-12 text-left">
                                            <label for="">Время окончания ремонта<span class="star">*</span></label>
                                            <validation-provider rules="required" vid="end_date"
                                                                 ref="end_date" name="дата окончания" v-slot="v">
                                                <el-date-picker
                                                    style="cursor:pointer"
                                                    :class="v.classes"
                                                    id="end_date"
                                                    v-model="end_date"
                                                    format="dd.MM.yyyy"
                                                    value-format="dd.MM.yyyy"
                                                    placeholder="Укажите время окончания ремонта"
                                                    name="start_of_exploitation"
                                                    :picker-options="endDatePickerOptions"
                                                    @focus = "onFocus">
                                                </el-date-picker>
                                                <div class="error-message">@{{ v.errors[0] }}</div>
                                            </validation-provider>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 mb-20 mt-20 text-left">
                                            <label for="">Комментарий<span class="star">*</span></label>
                                            <validation-provider rules="required|max:300" vid="comment-input"
                                                                 ref="comment-input" name="комментарий" v-slot="v">
                                                <el-input type="textarea"
                                                          :class="v.classes"
                                                          id="comment-input"
                                                          placeholder="Укажите причину изменения периода ремонта"
                                                          maxlength="300"
                                                          v-model="comment"
                                                          :autosize="{ minRows:4, maxRows:6 }"
                                                          clearable
                                                ></el-input>
                                                <div class="error-message">@{{ v.errors[0] }}</div>
                                            </validation-provider>
                                        </div>
                                    </div>
                                </template>
                            </validation-observer>
                        </div>
                    @endif
                @elseif($task->is_solved)
                    <div class="row">
                        <div class="col-sm-12">
                            <p>
                                Ремонт по заявке был завершён
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
        <div class="col-md-9 text-right btn-center" id="bottom_buttons" v-cloak>
            @if(Auth::id() == $task->responsible_user_id and ! $task->is_solved)
                <el-button type="primary" @click.stop="submit" :loading="submit_loading">Отправить</el-button>
            @endif
        </div>
    </div>
</div>
@if(! $task->is_solved and Auth::id() == $task->responsible_user_id)
@push('js_footer')
    <script>
        var form = new Vue({
            el: '#form',
            data: {
                defect: JSON.parse('{!! addslashes(json_encode($task->taskable)) !!}'),
                comment: '',
                submit_loading: false,
                observer_key: 1,
                type: null,
                types: [
                    {id: 1, name: 'Завершить ремонт'},
                    {id: 2, name: 'Увеличить срок ремонта'},
                ],
                end_date: '',
                tech_location_id: null,
                tech_locations: [],
                old_end_date: '',
                endDatePickerOptions: ''
            },
            created() {
                this.searchLocations('');
            },
            mounted() {
                this.old_end_date = this.defect.repair_end;
                this.endDatePickerOptions = {
                    firstDayOfWeek: 1,
                    disabledDate: date => date < moment().startOf('date') || (this.old_end_date ? date < moment(this.old_end_date, "DD.MM.YYYY").add(1, 'days') : false),
                };
            },
            watch: {
               submit_loading(value) {
                   bottomButtons.update(value);
               }
            },
            methods: {
                submit() {
                    this.$refs.observer.validate().then(success => {
                        if (!success) {
                            const error_field_vid = Object.keys(this.$refs.observer.errors).find(el => this.$refs[el].errors.length > 0);
                            $('.modal').animate({
                                scrollTop: $('#' + error_field_vid).offset().top
                            }, 1200);
                            $('#' + error_field_vid).focus();
                            return;
                        }

                        if (this.end_date) { return this.update(); }
                        return this.close();
                    });
                },
                close() {
                    this.$refs.observer.validate().then(success => {
                        if (!success) {
                            const error_field_vid = Object.keys(this.$refs.observer.errors).find(el => this.$refs[el].errors.length > 0);
                            $('.modal').animate({
                                scrollTop: $('#' + error_field_vid).offset().top
                            }, 1200);
                            $('#' + error_field_vid).focus();
                            return;
                        }

                        this.submit_loading = true;
                        const payload = {};
                        payload.comment = this.comment;
                        payload.start_location_id = this.tech_location_id;
                        axios.put('{{ route('building::tech_acc::defects.end_repair', $task->taskable->id) }}', payload)
                            .then((response) => {
                                window.location = '{{ $task->taskable->card_route() }}';
                            });
                        // TODO add errors handling
                        //.catch(error => this.handleError(error))
                    });
                },
                update() {
                    this.$refs.observer.validate().then(success => {
                        if (!success) {
                            const error_field_vid = Object.keys(this.$refs.observer.errors).find(el => this.$refs[el].errors.length > 0);
                            $('.modal').animate({
                                scrollTop: $('#' + error_field_vid).offset().top
                            }, 1200);
                            $('#' + error_field_vid).focus();
                            return;
                        }

                        this.submit_loading = true;
                        const payload = {};
                        payload.comment = this.comment;
                        payload.repair_start_date = this.defect.repair_start;
                        payload.repair_end_date = this.end_date;
                        axios.put('{{ route('building::tech_acc::defects.update_repair_dates', $task->taskable->id) }}', payload)
                            .then((response) => {
                                window.location = '{{ route('tasks::index') }}';
                            });
                        // TODO add errors handling
                        //.catch(error => this.handleError(error))
                    });
                },
                onFocus: function() {
                    $('.el-input__inner').blur();
                },
                refresh() {
                    this.$refs.observer.reset();
                    this.comment = '';
                    this.submit_loading = false;
                    this.end_date = '';
                    this.tech_location_id = '';
                },
                searchLocations(query) {
                    if (query) {
                        axios.post('{{ route('building::mat_acc::report_card::get_objects') }}', {q: query})
                            //TODO make field names in location objects equal (name, id in tech; label, code in following route)
                            .then(response => this.tech_locations = response.data.map(el => ({ name: el.label, id: el.code })))
                            //TODO add actual error handler
                            .catch(error => console.log(error));
                    } else {
                        axios.post('{{ route('building::mat_acc::report_card::get_objects') }}')
                            //TODO make field names in location objects equal (name, id in tech; label, code in following route)
                            .then(response => this.tech_locations = response.data.map(el => ({ name: el.label, id: el.code })))
                            //TODO add actual error handler
                            .catch(error => console.log(error));
                    }
                },
            }
        });

        var bottomButtons = new Vue({
            el: '#bottom_buttons',
            data: {
                submit_loading: form.submit_loading,
            },
            methods: {
                submit() {
                    form.submit();
                },
                update(value) {
                    this.submit_loading = value;
                }
            }
        });
    </script>
@endpush
@endif
