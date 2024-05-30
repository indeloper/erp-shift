<!-- Modal for material notes -->
<div class="modal fade bd-example-modal-lg show" id="material-notes" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content material-notes-content">
            <div class="modal-header">
                <h5 class="modal-title">Примечания материала</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body" style="line-height: 1.5">
                <div class="row">
                    <div class="col">
                        <div class="task-info__text-unit">
                            <span class="task-info__head-title" style="color: #333333 !important">
                                Проект
                            </span>
                            <span class="task-info__body-title" style="color: #333333 !important">
                                @{{ base ? (base.object.short_name ? base.object.short_name : base.object.name)
                                   : (materialInput && materialInput.locationFromName() ? materialInput.locationFromName() : 'Выберите материал') }}
                            </span>
                        </div>
                        <div class="task-info__text-unit" v-if="base">
                            <span class="task-info__head-title" style="color: #333333 !important">
                                Адрес
                            </span>
                            <span class="task-info__body-title" style="color: #333333 !important">
                                @{{ base ? base.object.address : 'Выберите материал' }}
                            </span>
                        </div>
                    </div>
                    <div class="col">
                        <div class="task-info__text-unit">
                            <span class="task-info__head-title" style="color: #333333 !important">
                                Материал
                            </span>
                            <span class="task-info__body-title" style="color: #333333 !important">
                                @{{ base ? base.material_name
                                    : (materialInput && materialInput.materialName() ? materialInput.materialName() : 'Выберите материал') }}
                            </span>
                        </div>
                        <div class="task-info__text-unit" v-if="base">
                            <span class="task-info__head-title" style="color: #333333 !important">
                                Количество
                            </span>
                            <span class="task-info__body-title" style="color: #333333 !important">
                                <span v-if="!base">Выберите материал</span>
                                <template v-else>
                                    <div class="row">
                                        <div class="col-auto pr-0 text-right">
                                            @{{ base.round_count }}&nbsp;<br>
                                            <span v-for="(item, item_key) in base.convert_params" class="amount-materials">
                                                @{{ calculateConvertedAmount(base.round_count, item.value) }}&nbsp;<br>
                                            </span>
                                        </div>
                                        <div class="col pl-0">
                                            @{{ base.unit }}<br>
                                            <span v-for="(item, item_key) in base.convert_params" class="amount-materials">
                                                @{{ item.unit }}<br>
                                            </span>
                                        </div>
                                    </div>
                                </template>
                            </span>
                        </div>
                    </div>
                </div>
                <h6 class="text-center mt-2">Примечания</h6>
                <template v-if="baseOrMaterialInput">
                    <div class="row align-items-center" v-for="(comment, index) in baseOrMaterialInput.comments">
                        <div class="col-auto">
                            @{{ index + 1 }}.
                        </div>
                        <div class="col my-2 px-0">
                            <material-note-row  :key="deletedCount + '-' + index"
                                                :comment="comment"
                                                :index="index"
                                                :base="baseOrMaterialInput"
                                                :deleted-comments-ids="deletedCommentsIds"
                                                :increment-deleted-count="incrementDeletedCount"
                                                :hide-tooltips="hideTooltips"
                                                :like_new="like_new"
                                                :read_only="read_only"
                            ></material-note-row>
                        </div>
                        <div v-if="index + 1 < baseOrMaterialInput.comments.length" class="col-12">
                            <hr class="my-0">
                        </div>
                    </div>
                    <div class="row mt-2" v-if="!read_only">
                        <div class="col-12 text-right">
                            <button class="btn btn-sm btn-round btn-success" @click="addNote">
                                <i class="el-icon-plus"></i> Добавить
                            </button>
                        </div>
                    </div>
                </template>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                <button v-if="!read_only" type="button" id="ttn_form_button" class="btn btn-info" @click="save">Сохранить</button>
            </div>
        </div>
    </div>
</div>


@push('js_footer')
<script>
    var materialNotes = new Vue({
        el: '#material-notes',
        data: {
            base: null,
            materialInput: null,
            deletedCommentsIds: [],
            deletedCount: 0,
            errorsOccured: false,
            like_new: false,
            read_only: false,
        },
        computed: {
            baseOrMaterialInput() {
                if (this.base) {
                    return VueBases.bases.find(base => base.id == this.base.id);
                } else {
                    return this.materialInput;
                }
            }
        },
        methods: {
            parseCustomFloat: function(str) {
                str = String(str);
                return +str.split(',').join('');
            },
            prettifyNumber: function(num) {
                const [wholePart, decimalPart] = num.toFixed(3).split('.');
                const wholePartDestructed = wholePart.split('');
                const prettyWholePartDestructed = [];

                for (let i = 0; i < wholePartDestructed.length; i++) {
                    prettyWholePartDestructed.unshift(wholePartDestructed[wholePartDestructed.length - 1 - i]);
                    if (i !== 0 && i % 3 === 2 && i !== wholePartDestructed.length - 1) {
                        prettyWholePartDestructed.unshift(',');
                    }
                }

                return `${prettyWholePartDestructed.join('')}.${decimalPart}`;
            },
            calculateConvertedAmount: function(base, coef) {
                return this.prettifyNumber(this.parseCustomFloat(base) * this.parseCustomFloat(coef));
            },
            hideTooltips() {
                for (let ms = 50; ms <= 1050; ms += 100) {
                    setTimeout(() => {
                        $('[data-balloon-pos]').blur();
                    }, ms);
                }
            },
            changeBase(base) {
                if (this.base !== base) {
                    this.base = base;
                    this.deletedCommentsIds = [];
                    this.deletedCount = 0;
                }
                this.errorsOccured = false;
            },
            changeMaterialInput(materialInput, like_new = false, read_only = false) {
                if (this.materialInput !== materialInput) {
                    this.like_new = like_new;
                    this.read_only = read_only;
                    this.materialInput = materialInput;
                    this.deletedCommentsIds = [];
                    this.deletedCount = 0;
                }
                this.errorsOccured = false;
            },
            addNote() {
                if (this.baseOrMaterialInput.comments.filter(comment => !comment.id && !comment.wasEdited).length === 0) {
                    this.baseOrMaterialInput.comments.push({ comment: '' });
                }
            },
            incrementDeletedCount() {
                this.deletedCount += 1;
            },
            save() {
                console.log(this.baseOrMaterialInput.id , this.like_new);
                let changesStaged = this.deletedCommentsIds.length + this.baseOrMaterialInput.comments.filter(comment => !comment.id && comment.comment || comment.id & comment.comment && comment.wasEdited).length;
                eventBus.$emit('saveEvent', this.index);
                if (this.baseOrMaterialInput.id && this.like_new == false) {
                    this.baseOrMaterialInput.comments.forEach(comment => {
                        if (!comment.id && comment.comment) {
                            axios.post('{{ route('comments.store') }}', {
                                comment: comment.comment,
                                commentable_id: this.baseOrMaterialInput.id,
                                commentable_type: 'App\\Models\\MatAcc\\MaterialAccountingBase'
                            })
                            .then((response) => {
                                comment.id = response.data.data.comment.id;
                                changesStaged -= 1;
                                this.showResultMessage(changesStaged);
                            })
                            .catch((error) => {
                                this.handleError(error);
                                changesStaged -= 1;
                                this.errorsOccured = true;
                            });
                        } else if (comment.id && comment.comment && comment.wasEdited) {
                            axios.put('{{ route('comments.update', 'COMMENT_ID') }}'.split('COMMENT_ID').join(comment.id), {
                                comment: comment.comment
                            })
                            .then(() => {
                                changesStaged -= 1;
                                this.showResultMessage(changesStaged);
                            })
                            .catch((error) => {
                                this.handleError(error);
                                changesStaged -= 1;
                                this.errorsOccured = true;
                            });
                        }
                    });
                }
                this.deletedCommentsIds.forEach((deletedCommentId, key) => {
                    axios.delete('{{ route('comments.destroy', 'COMMENT_ID') }}'.split('COMMENT_ID').join(deletedCommentId))
                        .then(() => {
                            changesStaged -= 1;
                            this.showResultMessage(changesStaged);
                        })
                        .catch((error) => {
                            this.handleError(error);
                            changesStaged -= 1;
                            this.errorsOccured = true;
                        });
                });
                this.deletedCommentsIds = [];
                $('#material-notes').modal('hide');
            },
            handleError(error) {
                    let msg = '';
                    if (error.response && error.response.data.errors) {
                        const keys = Object.keys(error.response.data.errors);
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
                    } else {
                        console.log(error);
                        msg = 'Произошла ошибка. Пожалуйста, попробуйте снова или свяжитесь с тех. поддержкой.'
                    }
                    if (msg) {
                        this.$message.error({
                            dangerouslyUseHTMLString: true,
                            message: msg,
                            showClose: true,
                            duration: 10000,
                        });
                    }
                },

            showResultMessage(changesStaged) {
                if (!changesStaged && !this.errorsOccured) {
                    this.$message.success({
                        message: 'Изменения успешно сохранены.',
                        showClose: true,
                    });
                }
            }
        }
    });

    Vue.component('material-note-row', {
        template: `
            <div class="row align-items-center">
                <div class="col pr-0">
                    <div v-if="!isEditingOrNew">
                        @{{ comment.comment }}
                    </div>
                    <el-input v-else
                              v-model="localValue"
                              @keydown.native.enter="apply"
                    ></el-input>
                </div>
                <div class="col-auto" v-if="!read_only">
                    <div v-if="!isEditingOrNew">
                        <button data-balloon-pos="up"
                                aria-label="Редактировать"
                                class="btn btn-link btn-xs btn-space btn-success mn-0"
                                @click="edit"
                            ><i class="fa fa-edit"></i>
                        </button>
                        <button data-balloon-pos="up"
                                aria-label="Удалить"
                                class="btn btn-link btn-xs btn-space btn-danger mn-0"
                                @click="remove"
                            ><i class="fa fa-trash"></i>
                        </button>
                    </div>
                    <div v-else>
                        <button data-balloon-pos="up"
                                aria-label="Применить"
                                class="btn btn-link btn-xs btn-space btn-primary mn-0"
                                @click="apply"
                            ><i class="fa fa-check"></i>
                        </button>
                        <button data-balloon-pos="up"
                                aria-label="Отмена"
                                class="btn btn-link btn-xs btn-space btn-danger mn-0"
                                @click="cancel"
                            ><i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        `,
        props: ['comment', 'base', 'index', 'deletedCommentsIds', 'incrementDeletedCount', 'hideTooltips', 'like_new', 'read_only'],
        data: () => ({
            isEditing: false,
            localValue: '',
            backdoor: 1,
        }),
        computed: {
            isEditingOrNew() {
                this.backdoor;
                return this.isEditing || !this.comment.comment;
            }
        },
        created() {
            eventBus.$on('saveEvent', (e) => {
                this.apply();
            });
        },
        methods: {
            edit() {
                this.localValue = this.comment.comment;
                this.isEditing = true;
                this.$forceUpdate();
                this.hideTooltips();
            },
            cancel() {
                if (this.isEditingOrNew) {
                    if (!this.comment.id && !this.comment.comment) {
                        this.base.comments.splice(this.index, 1);
                        this.incrementDeletedCount();
                    }
                    this.isEditing = false;
                }
                this.hideTooltips();
            },
            apply() {
                if (this.localValue && this.isEditingOrNew) {
                    this.comment.wasEdited = true;
                    this.comment.comment = this.localValue;
                    this.isEditing = false;
                    this.backdoor += 1;
                }
                this.hideTooltips();
            },
            remove() {
                if (this.comment.id && this.like_new == false) {
                    this.deletedCommentsIds.push(this.comment.id);
                }
                this.base.comments.splice(this.index, 1);
                this.incrementDeletedCount();
                this.hideTooltips();
            },
        }
    });

    var eventBus = new Vue();
</script>
@endpush
