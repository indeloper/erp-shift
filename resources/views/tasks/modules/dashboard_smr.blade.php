<style media="screen">
    .el-progress-custom {
        margin-bottom: 5px;
    }
    .el-tabs__nav {
        white-space: normal;
    }
    .el-tabs__item {
        height:100%;
        line-height: 20px;
        margin-bottom: 10px;
    }

    .el-table .cell.el-tooltip {
        white-space: normal;
    }
    .time {
        font-size: 13px;
        color: #999;
    }

    .bottom {
        margin-top: 15px;
        line-height: 12px;
    }

    .button {
        padding: 0;
        float: right;
    }
    .clearfix:before,
    .clearfix:after {
        display: table;
        content: "";
        background-color: #E4E7ED;
    }

    .el-tabs__active-bar {
        background-color: #E4E7ED;
    }

    .clearfix:after {
        clear: both
    }

</style>

<div class="row" id="dashboard" v-if="false">
    <template>
        <div class="col-md-4 col-sm-12" style="margin-bottom: 5px">
            <el-card class="box-card" style="height:100%">
                <div slot="header" class="clearfix">
                    <span>Важные проекты</span>
                </div>
                <div style="margin-bottom: 10px;">
                    <div class="el-input">
                        <input class="el-input__inner" placeholder="Поиск" @input="debounceInput"></input>
                    </div>
                </div>
                <el-tabs tab-position="left" v-model="tabPosition" style="height: 100%; width: 100%; float:left; padding-bottom: 10px;">
                    <el-tab-pane v-for="item in max" @tab-dblclick="window.location.href = item.project.link">
                        <span slot="label">
                            <div class="d-inline-block" style="width:80%; vertical-align: middle;">
                                @{{ item.project.object.name_tag + ' (' + item.project.contractor.short_name + ')' }}
                            </div>
                            <div class="d-inline-block" style="width:15%; vertical-align: middle;">
                                <el-link :href="item.project.link"><i class="fa fa-eye"></i></el-link>
                            </div>
                        </span>

                    </el-tab-pane>
                </el-tabs>
                <div class="bottom clearfix">
                    <time class="time">Статус договора: </time> <time class="time" :style="{ color: max[tabPosition].contract.length > 0 ? '#42b983' : ''}">@{{ max[tabPosition].contract.length > 0 ? max[tabPosition].contract[0].status_text : 'Не подписан' }}</time>
                    <el-link :href="'projects/' + max[tabPosition].project.id + '/contracts/' + max[tabPosition].contract[0].id+ '/card'" v-if="max[tabPosition].contract.length > 0" class="button">Перейти к договору</el-link>
                </div>

            </el-card>
        </div>
        <div class="col-md-8 col-sm-12" style="margin-bottom: 5px">
            <el-card class="box-card" style="height:100%">
                <div slot="header" class="clearfix">
                    <span>СМР</span>
                </div>
                <div class="row">
                    <div class="col-md-6 col-sm-12"  :style="{display: visability == 1 ? 'inline-block' : 'none'}">
                        <span>Объем работ</span>
                        <el-table
                            :data="max[tabPosition].work_volumes"
                            style="width: 100%"
                            ref="work_volumes_table"
                            :show-header="true">
                            <el-table-column
                                fixed
                                prop="type_name"
                                label="Тип">
                            </el-table-column>
                            <el-table-column
                                prop="version"
                                label="Версия">
                            </el-table-column>
                            <el-table-column
                                prop="option"
                                label="Наименование">
                            </el-table-column>
                            <el-table-column
                                :width="150"
                                prop="status_name"
                                label="Статус">
                            </el-table-column>
                        </el-table>
                    </div>
                    <div class="col-md-7 col-sm-12"  :style = "{display: visability == 3 ? 'inline-block' : 'none', height: '100%'}">
                        <span>Завоз/вывоз материалов</span>
                        <el-table
                            :data="max[tabPosition].materials"
                            ref="materials_table"
                            style="width: 100%"
                            :show-header="false">
                            <el-table-column
                                fixed
                                prop="name"
                                label="Название">
                                <template slot-scope="scope">
                                    @{{ scope.row.name }}
                                    <br>
                                    @{{ scope.row.count ? ( scope.row.count + ' ' + scope.row.unit) : '0 т' }}
                                    /
                                    @{{ scope.row.wv_count ? ( scope.row.wv_count + ' ' + 'т') : '0 т' }}
                                </template>
                            </el-table-column>
                            <el-table-column
                                fixed="right"
                                label="Действия">
                                <template slot-scope="scope">
                                    <el-progress class="el-progress-custom" :text-inside="true" :stroke-width="30" :percentage="Number((scope.row.sum * 100).toFixed(2))"></el-progress>
                                </template>
                            </el-table-column>
                        </el-table>

                    </div>
                    <div class="col-md-7 col-sm-12"  :style="{display: visability == 2 ? 'inline-block' : 'none'}">
                        <span>Коммерческие предложения</span>

                        <el-table
                            :data="max[tabPosition].com_offers"
                            style="width: 100%"
                            :show-header="true"
                            ref="com_offers_table">
                            <el-table-column
                                prop="type_name"
                                :show-overflow-tooltip="true"
                                label="Тип">
                            </el-table-column>
                            <el-table-column
                                :width="150"
                                prop="option"
                                :show-overflow-tooltip="true"
                                label="Наименование">
                            </el-table-column>
                            <el-table-column
                                prop="version"
                                label="Версия">
                            </el-table-column>
                            <el-table-column
                                :width="100"

                                prop="status_name"
                                label="Статус">
                            </el-table-column>
                        </el-table>

                    </div>
                    <div class="col-md-5 col-sm-12">
                        <span>Техника</span>

                        <el-table
                            :data="max[tabPosition].technics"
                            style="width: 100%"
                            :show-header="false"

                            max-height="250">
                            <el-table-column
                                fixed
                                prop="name">
                            </el-table-column>
                            <el-table-column
                                fixed
                                prop="human_status">
                            </el-table-column>
                            <el-table-column
                                fixed="right">
                                <template slot-scope="scope">
                                    <el-link :href="scope.row.work_link"><i class="fa fa-eye"></i></el-link>
                                </template>
                            </el-table-column>
                        </el-table>
                    </div>
                </div>
            </el-card>

        </div>
        {{--        <div class="col-md-3 col-sm-12" style="margin-bottom: 5px">--}}
        {{--            <el-card class="box-card" style="height:100%">--}}
        {{--                <div slot="header" class="clearfix">--}}
        {{--                    <span>Сотрудники</span>--}}
        {{--                    <el-button style="float: right; padding: 3px 0" type="text">Просмотр</el-button>--}}
        {{--                </div>--}}
        {{--                <el-table--}}
        {{--                    :data="employers"--}}
        {{--                    style="width: 100%"--}}
        {{--                    max-height="250">--}}
        {{--                    <el-table-column--}}
        {{--                        prop="name"--}}
        {{--                        width="120">--}}
        {{--                    </el-table-column>--}}
        {{--                    <el-table-column--}}
        {{--                        fixed="right"--}}
        {{--                        prop="count"--}}
        {{--                        width="120"--}}
        {{--                        label="На объекте">--}}
        {{--                    </el-table-column>--}}
        {{--                </el-table>--}}
        {{--                <br>--}}
        {{--                <el-table--}}
        {{--                    :data="employers"--}}
        {{--                    style="width: 100%"--}}
        {{--                    :show-header="false"--}}
        {{--                    max-height="250">--}}
        {{--                    <el-table-column--}}
        {{--                        prop="name"--}}
        {{--                        width="120"--}}
        {{--                        label="Название">--}}
        {{--                    </el-table-column>--}}
        {{--                    <el-table-column--}}
        {{--                        fixed="right"--}}
        {{--                        prop="count"--}}
        {{--                        width="120"--}}
        {{--                        label="Кол-во">--}}
        {{--                    </el-table-column>--}}
        {{--                </el-table>--}}
        {{--            </el-card>--}}
        {{--        </div>--}}

    </template>
</div>

@push('js_footer')
<script>
    function throttle (callback, limit) {
        var wait = false;
        return function () {
            if (!wait) {
                callback.call();
                wait = true;
                setTimeout(function () {
                    wait = false;
                }, limit);
            }
        }
    }

    var dashboard = new Vue({
        el: "#dashboard",
        data: {
            max: {!! $proj_stats !!},
            techLink: "{!! route('building::tech_acc::our_technic_tickets.show', '') !!}",
            tabPosition: 0,
            searchText: '',
            search: ''
        },
        computed: {
            visability: function () {
                let val = this.tabPosition;
                if (this.max[val].contract.length == 0 && (this.max[val].work_volumes.length > 0 || this.max[val].com_offers < 0) && this.max[val].com_offers.filter(item => (item.status == 4 || item.status == 5)).length < 0) {
                    return 1;
                } else if (this.max[val].contract.length > 0 || (this.max[val].com_offers.filter(item => (item.status > 4))).length > 0) {
                    return 3;
                } else {
                    return 2;
                }
            },
        },
        watch: {
            searchText: function () {
                let that = this;
                axios.get("{{ route('tasks::search_projects') }}" + '?search=' + that.searchText).then(response => {
                    if (response.data.length > 0) {
                        that.max = response.data;
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Не найдено',
                            text: 'Объекты не найдены!',
                        })
                    }
                })
            }
        },
        methods: {
            debounceInput: _.debounce(function (e) {
                this.searchText = e.target.value;
            }, 1200)
        }
    })
</script>
@endpush
