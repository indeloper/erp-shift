<div id="vm">
    <div class="card-body task-body">
        <div class="row">
            <div id="main_descr" class="col-md-12">
                <h6 style="margin-top:0">
                    Описание
                </h6>
                <div id="description">
                    <p>На объекте {{ $task->object->name_tag ?? '' }} была создана операция без договора. Необходимо прикрепить договор. </p>

                    @if(! $task->is_solved)
                        @if(Auth::id() == $task->responsible_user_id)
                            <hr style="border-color:#F6F6F6">
                            <div>
                                <h6 style="margin-top:0">
                                    Договор
                                </h6>
                                <template>
                                    <el-select v-model="contract_id" clearable filterable :remote-method="search_contracts" remote name="contract_id" placeholder="Поиск договора">
                                        <el-option
                                            v-for="item in contracts"
                                            :key="item.code"
                                            :label="item.label"
                                            :value="item.code">
                                        </el-option>
                                    </el-select>
                                </template>
                            </div>
                        @endif
                    @elseif($task->is_solved)
                        <div class="row">
                            <div class="col-sm-12">
                                <div>

                                </div>
                                <br>
                                <p style="font-size:16px;">
                                    <b>Результат:</b>
                                    {{ $task->getResult }}
                                </p>
                            </div>
                        </div>
                    @endif
                    <br>
                    <h6 style="margin-top:0">
                        Информация о операции
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-hover mobile-table">
                            <thead>
                            <tr>
                                <th>Материал</th>
                                <th>Единица измерения</th>
                                <th>Количество</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($operation->materialsPartTo as $material)
                                <tr>
                                    <td data-label="Материал">
                                        {{ $material->manual->name }}
                                    </td>
                                    <td data-label="Единица измерения">{{ $material->unit_for_humans }}</td>
                                    <td data-label="Количество">{{ round($material->count, 3) }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer">
        <div class="row" style="margin-top:25px">
            <div class="col-md-3 btn-center">
                <a href="{{ route('tasks::index') }}" class="btn btn-wd">Назад</a>
            </div>
            <div class="col-md-9 text-right btn-center">
                @if(Auth::id() == $task->responsible_user_id and ! $task->is_solved)
                    <el-button :loading="loading" @click="attach_contract" type="primary">Сохранить</el-button>
                @endif
            </div>
        </div>
    </div>

</div>

@push('js_footer')
<script>
let vm = new Vue({
    el: '#vm',
    data: {
        contracts: [],
        contract_id: '',
        object_id: {{ $task->project->object_id }},
        operation_id: {{ $task->target_id }},
        loading: false,
        task_id: {{ $task->id }}
    },
    mounted: function () {
      this.search_contracts();
    },
    methods: {
        search_contracts(query) {
            if (query !== '') {
                setTimeout(() => {
                    axios.post('{{ route('contracts::get_contracts') }}', {q: query, object_id: this.object_id, from_mat_acc: true}).then(function (response) {
                        if (response.data.length > 0) {
                            vm.contracts = response.data;
                        } else {
                            vm.contracts = [{label: "На данном объекте отсутствуют договоры"}];
                        }
                    })
                }, 200);
            } else {
                axios.post('{{ route('contracts::get_contracts') }}', {object_id: this.selected, from_mat_acc: true}).then(response => vm.contracts = response.data);
            }
        },
        attach_contract() {
            let that = this;

            that.loading = true;

            axios.post('{{ route('building::mat_acc::attach_contract') }}', {operation_id: this.operation_id, contract_id: this.contract_id, task_id: this.task_id}).then(function (response) {
                that.loading = false;
                if (response.data.status == 'success') {
                    location.reload();
                } else {
                    this.$message.error('Ошибка.');
                }
            }).catch(error => this.handleError(error))
        },
        handleError(error) {
            let message = '';
            let errors = error.response.data.errors;
            for (let key in errors) {
                message += errors[key][0] + '<br>';
            }
            this.loading = false;

            swal({
                type: 'error',
                title: "Ошибка",
                html: message,
            });
        },
    }
})
</script>
@endpush
