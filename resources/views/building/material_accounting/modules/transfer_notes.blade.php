<!-- Modal for material notes -->
<div class="modal fade bd-example-modal-md show" id="transfer-notes" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content material-notes-content">
            <div class="modal-header">
                <h5 class="modal-title">Перенос примечаний</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body" style="line-height: 1.5">
                <div class="row mb-2">
                    <div class="col" style="line-height: 1.2; font-size: 14px;">
                        @{{ description }}
                    </div>
                </div>
                <div v-for="(materialWithComments, index) in materialsWithComments">
                    <div class="row">
                        <div class="col-12">
                            <div class="task-info__text-unit">
                                <span class="task-info__head-title" style="color: #333333 !important">
                                    @{{ materialFromText }}
                                </span>
                                <span class="task-info__body-title" style="color: #333333 !important">
                                    @{{ materialWithComments.material_name }}
                                    <button
                                        type="button"
                                        data-balloon-pos="up" :aria-label="materialWithComments.comments.join(', ')"
                                        data-balloon-length="large"
                                        class="btn btn-link btn-xs pd-0 btn-danger" style="margin-bottom: 1px;">
                                        <i class="fa fa-info-circle"></i>
                                    </button>
                                </span>
                            </div>
                            <div class="task-info__text-unit">
                                <span class="task-info__head-title" style="color: #333333 !important">
                                    @{{ materialToText }}
                                </span>
                            </div>
                        </div>
                        <div class="col-12" v-for="(material, i) in materialInputs">
                            <el-checkbox v-model="materialWithComments.newMaterials[i]"
                                          @change="rerender"
                                         :label="material.material_name"
                            ></el-checkbox>
                        </div>
                    </div>
                    <div class="row" v-if="index < materialsWithComments.length - 1">
                        <hr style="width: 100%">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" @click="reset">Отмена</button>
                <button type="button" id="ttn_form_button" class="btn btn-info" @click="save">Сохранить</button>
            </div>
        </div>
    </div>
</div>


@push('js_footer')
<script>
    var transferNotes = new Vue({
        el: '#transfer-notes',
        data: {
            materialInputs: [],
            materialsWithComments: [],
            objectFrom: null,
            objectTo: null,
            callback: null
        },
        mounted() {
            var that = this;
            $('#transfer-notes').on('hide.bs.modal', function (e) {
                that.unlockButtons();
            });
        },
        computed: {
            description() {
                if (typeof(answer_to) !== 'undefined') {
                    return `В операцию были добавлены материалы с примечаниями. Вы можете перенести примечания от этих материалов к материалам, полученным на объекте ${ this.objectTo }.`;
                }
                return `В операцию были добавлены материалы с примечаниями. Вы можете перенести примечания от материалов до преобразования материалам после преобразования.`;
            },
            materialFromText() {
                if (typeof(answer_to) !== 'undefined') {
                    return `Материал с объекта ${ this.objectFrom }:`;
                }
                return `Материалы до преобразования:`;
            },
            materialToText() {
                if (typeof(answer_to) !== 'undefined') {
                    return `Материалы с объекта ${ this.objectTo }:`;
                }
                return `Материалы после преобразования:`;
            },
        },
        methods: {
            setMaterialsWithComments(materialsWithComments) {
                this.materialsWithComments = materialsWithComments;
            },
            setMaterialInputs(materialInputs) {
                if (materialInputs.length === 0) {
                    this.callback();
                } else {
                    this.materialInputs = materialInputs.map((materialInput) => {
                        const material = materialInput.materials.filter((mat) => mat.base_id == materialInput.base_id)[0];
                        materialInput.material_name = material.label;
                        return materialInput;
                    });
                    this.materialsWithComments.forEach((materialWithComments) => {
                        materialWithComments.newMaterials = this.materialInputs.map((el) => false);
                    });
                }
            },
            setObjectFrom(objectFrom) {
                this.objectFrom = objectFrom;
            },
            setObjectTo(objectTo) {
                this.objectTo = objectTo;
            },
            setCallback(callback) {
                this.callback = callback;
            },
            rerender() {
                this.$forceUpdate();
            },
            save() {
                this.materialsWithComments.forEach((materialWithComments) => {
                    materialWithComments.newMaterials.forEach((mat, i) => {
                        if (mat) {
                            if (!this.materialInputs[i].comments) {
                                this.materialInputs[i].comments = materialWithComments.comments;
                            } else {
                                this.materialInputs[i].comments.concat(materialWithComments.comments);
                            }
                        }
                    });
                });
                this.callback();
                $('#transfer-notes').modal('hide');
            },
            unlockButtons() {
                if (typeof(answer_to) !== 'undefined') {
                    answer_to.is_loading_send = false;
                } else {
                    answer.is_loading_send = false;
                }
            },
            reset() {
                this.unlockButtons();
                this.materialInputs = this.materialInputs.map((materialInput) => {
                    delete materialInput.comments;
                    return materialInput;
                });
            }
        }
    });
</script>
@endpush
