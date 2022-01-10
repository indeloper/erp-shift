<script>
    Vue.component('project-tariffs-cell', {
        template: `
            <el-popover
                trigger="click"
                placement="top"
                v-on:show="update"
                width="400"
            >
                <div class="row pt-2">
                    <div class="col text-center">
                        <h6>@{{ title }}</h6>
                    </div>
                </div>
                <div class="row align-content-end pb-2" v-for="(project, i) in projects">
                    <div class="col pr-1">
                        <el-select v-model="project.location"
                                   clearable filterable
                                   :remote-method="searchLocations"
                                   @clear="searchLocations('')"
                                   remote
                                   placeholder="Поиск проекта"
                        >
                            <el-option
                                v-for="item in locations"
                                :key="item.code"
                                :label="item.name"
                                :value="item.code"
                            ></el-option>
                        </el-select>
                    </div>
                    <div class="col px-1">
                        <el-input-number
                            class="w-100"
                            :min="1"
                            :max="24"
                            :step="1"
                            :precision="0"
                            placeholder="Кол-во часов"
                            v-model="project.time"
                        ></el-input-number>
                    </div>
                    <div class="col-auto align-self-center justify-self-center pl-1" style="-ms-flex-positive: 0; flex-grow: 0;">
                        <el-button type="danger"
                                   @click="removeProject(i)"
                                   data-balloon-pos="up"
                                   style="margin-bottom: 0 !important;"
                                   size="small"
                                   aria-label="Удалить"
                                   icon="el-icon-delete"
                                   circle
                        ></el-button>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="btn-group w-100" role="group">
                            <button type="button" @click="addProject" v-if="projects.length < 20"
                                    class="btn btn-success btn-outline add-material">
                                <i class="fa fa-plus"></i>
                                Добавить проект
                            </button>
                            <button type="button" @click="save"
                                    class="btn btn-primary btn-block add-material">
                                Сохранить
                            </button>
                        </div>
                    </div>
                </div>
                <div slot="reference">
                    <div style="min-height: 23px">
                        <div class="row">
                            <div class="col text-truncate">
                                @{{ time }}
                            </div>
                        </div>
                    </div>
                </div>
            </el-popover>
        `,
        props: ['time', 'hideTooltips', 'locations', 'searchLocations', 'title'],
        data: () =>({
            projects: [],
        }),
        methods: {
            removeProject(i) {
                this.projects.splice(i, 1);
                this.hideTooltips();
            },
            addProject(i) {
                this.projects.push({
                    location: '',
                    time: 0,
                });
            },
            save() {

            },
            update() {

            },
        },
    });
</script>
