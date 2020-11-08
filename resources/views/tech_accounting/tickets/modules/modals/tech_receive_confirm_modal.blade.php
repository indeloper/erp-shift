<div class="modal fade bd-example-modal-lg show" id="tech-receive-confirm" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content pb-3">
            <div class="modal-header">
                <h5 class="modal-title">Фиксация получения техники</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body pt-0">
                <validation-observer ref="observer" :key="observer_key">
                    <div class="modal-section mt-3">
                        <label for="">Комментарий</label>
                        <validation-provider rules="max:300" vid="receive-confirm-comment-input"
                                             ref="receive-confirm-comment-input" v-slot="v">
                            <el-input
                                :class="v.classes"
                                type="textarea"
                                :rows="4"
                                maxlength="300"
                                id="receive-confirm-comment-input"
                                clearable
                                placeholder="Укажите подробности отправки техники"
                                v-model="comment"
                            ></el-input>
                            <div class="error-message">@{{ v.errors[0] }}</div>
                        </validation-provider>
                    </div>
                    <div class="modal-section mt-3" id="back-upload-section">
                        <label for="">Фото транспорта сзади<span class="star">*</span>
                            <br><span class="important-tip">Изображения должны отображать состояние техники</span>
                        </label>
                        <el-upload
                            :drag="window_width > 769"
                            action="{{ route('file_entry.store') }}"
                            :headers="{ 'X-CSRF-TOKEN': '{{ csrf_token() }}' }"
                            :limit="10"
                            ref="back_photo_upload"
                            :before-upload="beforeUpload"
                            :on-preview="handlePreview"
                            :on-remove="handleRemove"
                            :on-exceed="handleExceed"
                            :on-success="handleBackSuccess"
                            :on-error="handleError"
                            multiple
                        >
                            <template v-if="window_width > 769">
                                <i class="el-icon-upload"></i>
                                <div class="el-upload__text">Перетащите сюда или <em>кликните, чтобы выбрать файлы для загрузки</em></div>
                            </template>
                            <el-button v-else size="small" type="primary">Загрузить</el-button>
                            <div class="el-upload__tip" slot="tip">Файлы формата jpg/png размером до 10Мб</div>
                        </el-upload>
                        <div class="error-message d-none" id="back-upload-section-error">Обязательное поле</div>
                    </div>
                    <div class="modal-section mt-3" id="front-upload-section">
                        <label for="">Фото транспорта спереди<span class="star">*</span>
                            <br><span class="important-tip">Изображения должны отображать состояние техники</span>
                        </label>
                        <el-upload
                            :drag="window_width > 769"
                            action="{{ route('file_entry.store') }}"
                            :headers="{ 'X-CSRF-TOKEN': '{{ csrf_token() }}' }"
                            :limit="10"
                            ref="front_photo_upload"
                            :before-upload="beforeUpload"
                            :on-preview="handlePreview"
                            :on-remove="handleRemove"
                            :on-exceed="handleExceed"
                            :on-success="handleFrontSuccess"
                            :on-error="handleError"
                            multiple
                        >
                            <template v-if="window_width > 769">
                                <i class="el-icon-upload"></i>
                                <div class="el-upload__text">Перетащите сюда или <em>кликните, чтобы выбрать файлы для загрузки</em></div>
                            </template>
                            <el-button v-else size="small" type="primary">Загрузить</el-button>
                            <div class="el-upload__tip" slot="tip">Файлы формата jpg/png размером до 10Мб</div>
                        </el-upload>
                        <div class="error-message d-none" id="front-upload-section-error">Обязательное поле</div>
                    </div>
                    <div class="modal-section mt-3" id="doc-upload-section">
                        <label for="">Фото транспортной накладной<span class="star">*</span></label>
                        <el-upload
                            :drag="window_width > 769"
                            action="{{ route('file_entry.store') }}"
                            :headers="{ 'X-CSRF-TOKEN': '{{ csrf_token() }}' }"
                            :limit="10"
                            ref="doc_photo_upload"
                            :before-upload="beforeUpload"
                            :on-preview="handlePreview"
                            :on-remove="handleRemove"
                            :on-exceed="handleExceed"
                            :on-success="handleDocSuccess"
                            :on-error="handleError"
                            multiple
                        >
                            <template v-if="window_width > 769">
                                <i class="el-icon-upload"></i>
                                <div class="el-upload__text">Перетащите сюда или <em>кликните, чтобы выбрать файлы для загрузки</em></div>
                            </template>
                            <el-button v-else size="small" type="primary">Загрузить</el-button>
                            <div class="el-upload__tip" slot="tip">Файлы формата jpg/png размером до 10Мб</div>
                        </el-upload>
                        <div class="error-message d-none" id="doc-upload-section-error">Обязательное поле</div>
                    </div>
                    <div class="modal-section text-center mt-30">
                        <button :class="{'btn btn-success btn-sm' : window_width > 769, 'btn btn-success w-100' : window_width <= 769}" @click.stop="submit">Подтвердить получение</button>
                    </div>
                </validation-observer>
            </div>
        </div>
    </div>
</div>

@push('js_footer')
<script>
    var techReceiveConfirm = new Vue({
        el: '#tech-receive-confirm',
        data: {
            observer_key: 1,
            comment: '',
            files_uploaded_front: [],
            files_uploaded_back: [],
            files_uploaded_doc: [],
            window_width: 10000,
        },
        mounted() {
            $(window).on('resize', this.handleResize);
            this.handleResize();
        },
        methods: {
            handleRemove(file, fileList) {
                if (file.hasOwnProperty('response')) {
                    let index = -1;
                    if ((index = this.files_uploaded_front.findIndex(el => el.id === file.response.data[0].id)) !== -1) {
                        this.files_uploaded_front.splice(index, 1);
                    } else if ((index = this.files_uploaded_back.findIndex(el => el.id === file.response.data[0].id)) !== -1) {
                        this.files_uploaded_back.splice(index, 1);
                    } else if ((index = this.files_uploaded_doc.findIndex(el => el.id === file.response.data[0].id)) !== -1) {
                        this.files_uploaded_doc.splice(index, 1);
                    }
                }
            },
            handleResize() {
                this.window_width = $(window).width();
            },
            handleFrontSuccess(response, file, fileList) {
                file.url = file.response.data[0] ? '{{ asset('storage/docs/tech_accounting/') }}' + '/' + file.response.data[0].filename : '#';
                this.files_uploaded_front.push(...file.response.data);
                $('#front-upload-section').removeClass('failed');
                $('#front-upload-section-error').addClass('d-none');
            },
            handleBackSuccess(response, file, fileList) {
                file.url = file.response.data[0] ? '{{ asset('storage/docs/tech_accounting/') }}' + '/' + file.response.data[0].filename : '#';
                this.files_uploaded_back.push(...file.response.data);
                $('#back-upload-section').removeClass('failed');
                $('#back-upload-section-error').addClass('d-none');
            },
            handleDocSuccess(response, file, fileList) {
                file.url = file.response.data[0] ? '{{ asset('storage/docs/tech_accounting/') }}' + '/' + file.response.data[0].filename : '#';
                this.files_uploaded_doc.push(...file.response.data);
                $('#doc-upload-section').removeClass('failed');
                $('#doc-upload-section-error').addClass('d-none');
            },
            handleError(error, file, fileList) {
                let message = '';
                let errors = error.response.data.errors;
                for (let key in errors) {
                    message += errors[key][0] + '<br>';
                }
                swal({
                    type: 'error',
                    title: "Ошибка загрузки файла",
                    html: message,
                });
            },
            handlePreview(file) {
                window.open(file.url ? file.url : '{{ asset('storage/docs/tech_accounting/') }}' + '/' + file.filename, '_blank');
                $('#form_tech').focus();
            },
            handleExceed(files, fileList) {
                this.$message.warning(`Невозможно загрузить ${files.length} файлов. Вы можете загрузить еще ${10 - fileList.length} файлов`);
            },
            beforeUpload(file) {
                const ALLOWED_EXTENSIONS = ['jpeg','jpg','png'];
                const FILE_MAX_LENGTH = 10000000;
                const nameParts = file.name.split('.');
                if (ALLOWED_EXTENSIONS.indexOf((nameParts[nameParts.length - 1]).toLowerCase()) === -1) {
                    this.$message.warning(`Ошибка загрузки файла. Разрешенные форматы: JPEG, PNG.`);
                    return false;
                }
                if (file.size > FILE_MAX_LENGTH) {
                    this.$message.warning(`Ошибка загрузки файла. Размер файла не должен превышать 5Мб`);
                    return false;
                }
                return true;
            },
            reset() {
                this.observer_key += 1;
                this.comment = '';
                this.files_uploaded_front = [];
                this.files_uploaded_back = [];
                this.files_uploaded_doc = [];
                this.$refs.front_photo_upload.clearFiles();
                this.$refs.back_photo_upload.clearFiles();
                this.$refs.doc_photo_upload.clearFiles();
            },
            submit() {
                this.$refs.observer.validate().then(success => {
                    let error = false;
                    if (this.files_uploaded_front.length > 0) {
                        $('#front-upload-section').removeClass('failed');
                        $('#front-upload-section-error').addClass('d-none');
                    } else {
                        $('#front-upload-section').addClass('failed');
                        $('#front-upload-section-error').removeClass('d-none');
                        error = true;
                    }
                    if (this.files_uploaded_back.length > 0) {
                        $('#back-upload-section').removeClass('failed');
                        $('#back-upload-section-error').addClass('d-none');
                    } else {
                        $('#back-upload-section').addClass('failed');
                        $('#back-upload-section-error').removeClass('d-none');
                        error = true;
                    }
                    if (this.files_uploaded_doc.length > 0) {
                        $('#doc-upload-section').removeClass('failed');
                        $('#doc-upload-section-error').addClass('d-none');
                    } else {
                        $('#doc-upload-section').addClass('failed');
                        $('#doc-upload-section-error').removeClass('d-none');
                        error = true;
                    }
                    if (error) {
                        return;
                    }
                    if (!success) {
                        const error_field_vid = Object.keys(this.$refs.observer.errors).find(el => this.$refs[el].errors.length > 0);
                        $('#' + error_field_vid).focus();
                        return;
                    }
                    const files_uploaded = [
                        ...this.files_uploaded_front,
                        ...this.files_uploaded_back,
                        ...this.files_uploaded_doc
                    ];
                    const payload = {
                        result: 'confirm',
                        file_ids: files_uploaded.map(file => file.id),
                        comment: this.comment,
                        task_status: 32,
                    };
                    axios.put('{{ route('building::tech_acc::our_technic_tickets.update', ['']) }}' + '/' + cardTicket.ticket.id, payload)
                        .then((response) => {
                            const ticketIndex = vm.tickets.findIndex(el => el.id == cardTicket.ticket.id);
                            vm.$set(vm.tickets, ticketIndex, response.data.data);
                            vm.tickets[ticketIndex].is_loaded = true;
                            cardTicket.update(vm.tickets[ticketIndex]);
                            $('#tech-receive-confirm').modal('hide');
                            this.reset();
                        })
                        .catch((error) => {
                            console.log(error)
                        })
                });
            },
        }
    });
</script>
@endpush
