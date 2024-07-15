<div class="card">
    <div class="card-header">
        <h4 class="card-title">
            <a data-target="#collapseUsersOnProject" href="#" data-toggle="collapse">
                Сотрудники проекта
                <b class="caret"></b>
            </a>
        </h4>
    </div>
    <div id="collapseUsersOnProject" class="card-collapse collapse">
        <div class="card-body card-body-table">
            <div class="row">
                <div class="col-md-12" id="project-users">
                    <div class="text-right">
                        <a href="{{ route('projects::users', $project->id) }}" class="tech-link purple-link d-inline-block" style="margin: 0px 0px 10px 0px;">Перейти в список сотрудников</a><span class="purple-link"> → </span>
                    </div>
                    <div class="card strpied-tabled-with-hover" style="border:none">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                <tr>
                                    <th>ФИО</th>
                                    <th>Должность</th>
                                    <th>Должностная категория</th>
                                    <th>Отчётная группа</th>
                                    <th>Юр. Лицо</th>
                                </tr>
                                </thead>
                                <tbody>
                                <template v-if="users.length">
                                    <tr v-for="user in users">
                                        <td>@{{ user.full_name }}</td>
                                        <td>@{{ user.group_name }}</td>
                                        <td>@{{ user.job_category_name }}</td>
                                        <td>@{{ user.report_group_name }}</td>
                                        <td>@{{ user.company_name }}</td>
                                    </tr>
                                </template>
                                <template v-else>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td>Нет сотрудников на проекте</td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push ('js_footer')
<script>
    var users = new Vue({
        el: '#project-users',
        data: {
            project: [],
            users: [],
        },
        mounted() {
            this.getUsers();
        },
        methods: {
            getUsers() {
                const payload = {};
                payload.project_id = '{{ $project->id }}';
                axios.post('{{ route('projects::get_project_users') }}', payload)
                    .then(function (response) {
                        users.project = response.data.project;
                        users.users = response.data.users.slice(0, 9);
                    });
            }
        }
    });
</script>

@endpush
