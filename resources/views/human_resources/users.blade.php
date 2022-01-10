<users :users="users"
       :remove-user="removeUser"
       :brigades="brigades"
       :remove-brigade="removeBrigade"
       :source="source"
       :display-type="displayType"
       :directions="directions"
       {{-- :window_width="window_width"
       :total-items="totalItems"
       :page-size="PAGE_SIZE"
       :current-page="currentPage"
       :change-page="changePage"
       :reset-current-page="resetCurrentPage" --}}
></users>

@section('js_footer')
    <script>
        Vue.component('users', {
            props: ['users', 'removeUser', 'brigades', 'removeBrigade', 'source', 'displayType', 'directions'/* 'window_width', 'totalItems', 'pageSize', 'currentPage', 'changePage', 'resetCurrentPage' */],
            computed: {
                /* pagerCount() {
                    return this.window_width > 1000 ? 7 : 5;
                },
                smallPager() {
                    return this.window_width < 1000;
                },
                pagerBackground() {
                    return this.window_width > 300;
                }, */
                extendedColumns() {
                    return this.source === 'project';
                },
            },
            methods: {
                getDirectionName(id) {
                    return this.directions[id];
                },
                getBrigadeUsersCount(id) {
                    return this.users.filter(user => user.brigade_id === id)
                        .filter((user, index, users) => users.map(el => el.id).indexOf(user.id) === index).length;
                }
            },
            template: `
<div class="user-list-component">
    <div class="table-responsive">
        <table class="table table-hover mobile-table table-fix-column">
            <thead>
            <tr v-if="displayType === 'Сотрудники'">
                <th class="text-truncate" data-balloon-pos="up-left" aria-label="ФИО"><span class="text-truncate d-inline-block">ФИО</span></th>
                <th class="text-truncate" data-balloon-pos="up-left" aria-label="Должность"><span class="text-truncate d-inline-block">Должность</span></th>
                <template v-if="extendedColumns">
                    <th class="text-truncate" data-balloon-pos="up-left" aria-label="Бригада"><span class="text-truncate d-inline-block">Бригада</span></th>
                    <th class="text-truncate" data-balloon-pos="up-left" aria-label="Должностная категория"><span class="text-truncate d-inline-block">Должностная категория</span></th>
                    <th class="text-truncate" data-balloon-pos="up-left" aria-label="Отчётная группа"><span class="text-truncate d-inline-block">Отчётная группа</span></th>
                    <th class="text-truncate" data-balloon-pos="up-left" aria-label="Юр. лицо"><span class="text-truncate d-inline-block">Юр. лицо</span></th>
                </template>
                <th></th>
            </tr>
            <tr v-else>
                <th class="text-truncate" data-balloon-pos="up-left" aria-label="Номер"><span class="text-truncate d-inline-block">Номер</span></th>
                <th class="text-truncate" data-balloon-pos="up-left" aria-label="Направление"><span class="text-truncate d-inline-block">Направление</span></th>
                <th class="text-truncate" data-balloon-pos="up-left" aria-label="Числ. на проекте, чел."><span class="text-truncate d-inline-block">Числ. на проекте, чел.</span></th>
                <th class="text-truncate" data-balloon-pos="up-left" aria-label="Бригадир"><span class="text-truncate d-inline-block">Бригадир</span></th>
                <th></th>
            </tr>
            </thead>
            <tbody v-if="displayType === 'Сотрудники'">
                <tr v-if="users.length === 0">
                    <td>
                        Нет данных
                    </td>
                    <td></td>
                    <template v-if="extendedColumns">
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </template>
                    <td></td>
                </tr>
                <tr v-for="user in users">
                    <td data-label="ФИО">
                        @{{ user.long_full_name }}
                    </td>
                    <td data-label="Должность">
                        @{{ user.group_name }}
                    </td>
                    <template v-if="extendedColumns">
                        <td data-label="Бригада">@{{ user.brigade ? user.brigade.number : 'Не указана' }}</td>
                        <td data-label="Должностная категория">@{{ user.job_category_name }}</td>
                        <td data-label="Отчётная группа">@{{ user.report_group_name }}</td>
                        <td data-label="Юр. лицо">@{{ user.company_name }}</td>
                    </template>
                    <td class="text-right actions flex-nowrap">
                       <a data-balloon-pos="up" :href="'{{ route('users::card', 'user_id') }}'.split('user_id').join(user.id)"
                           aria-label="Просмотр" class="btn btn-link btn-xs btn-space actions btn-primary mn-0">
                            <i class="fa fa-external-link-alt"></i>
                        </a>
                        <button v-if="!user.brigade
                            || brigades.map(el => el.number).indexOf(user.brigade.number) === -1
                            || users.filter(el => el.id === user.id).indexOf(user) !== 0"
                                data-balloon-pos="up" aria-label="Удалить" class="btn btn-link btn-xs btn-space btn-danger mn-0"
                        @click="removeUser(user.id)">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
            <tbody v-else>
                <tr v-if="brigades.length === 0">
                    <td>
                        Нет данных
                    </td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr v-for="brigade in brigades">
                    <td data-label="Номер">
                        @{{ brigade.number }}
                    </td>
                    <td data-label="Направление">
                        @{{ getDirectionName(brigade.direction) }}
                    </td>
                    <td data-label="Числ. на проекте, чел.">
                        @{{ getBrigadeUsersCount(brigade.id) }}
                    </td>
                    <td data-label="Бригадир">
                        @{{ brigade.foreman_name ? brigade.foreman_name : 'Не назначен' }}
                    </td>
                    <td class="text-right actions flex-nowrap">
                        <a data-balloon-pos="up" :href="'{{ route('human_resources.brigade.show', 'brigade_id') }}'.split('brigade_id').join(brigade.id)"
                           aria-label="Просмотр" class="btn btn-link btn-xs btn-space actions btn-primary mn-0">
                            <i class="fa fa-external-link-alt"></i>
                        </a>
                        {{-- <a data-balloon-pos="up" :href="'{{ route('human_resources.brigade.edit', 'brigade_id') }}'.split('brigade_id').join(brigade.id)"
                               aria-label="Редактировать" class="btn btn-link btn-xs btn-space btn-warning mn-0">
                                <i class="fa fa-pencil"></i>
                        </a> --}}
                        <button data-balloon-pos="up" aria-label="Удалить" class="btn btn-link btn-xs btn-space btn-danger mn-0"
                                @click="removeBrigade(brigade.id)">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
{{-- <div class="d-flex justify-content-end mt-2">
    <el-pagination
        :background="pagerBackground"
        :page-size="pageSize"
        :total="totalItems"
        :small="smallPager"
        :current-page.sync="currentPage"
        :pagerCount="pagerCount"
        layout="prev, pager, next"
        @prev-click="changePage"
        @next-click="changePage"
        @current-change="changePage"
    >
    </el-pagination>
</div> --}}
</div>
`
        });
    </script>
    @parent
@endsection
