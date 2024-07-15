@if($projects->pluck('contracts')->flatten()->count())
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">
                <a data-target="#collapseContract" href="#" data-toggle="collapse">
                    Договоры
                    <b class="caret"></b>
                </a>
            </h4>
        </div>
        <div id="collapseContract" class="card-collapse collapse">
            <div class="card-body card-body-table">
                <div class="card strpied-tabled-with-hover">
                    <div class="table-responsive">
                            <table class="table table-hover mobile-table" id="contracts-table">
                                <thead>
                                <tr>
                                    <th>Внешний №</th>
                                    <th>Тип</th>
                                    <th>Объект</th>
                                    <th>Дата добавления</th>
                                    <th class="text-center">Версия</th>
                                    <th>Статус</th>
                                    <th>Дата КС
                                        <button type="button" name="button" class="btn btn-link btn-primary btn-xs mn-0 pd-0" data-container="body"
                                                data-toggle="popover" data-placement="top" data-content="Ближайшая дата сдачи КС. В скобках указана дата, когда система начнёт посылать уведомления об отсутствующих сертификатах. Даты, выделенные жирным шрифом, уже нельзя редактировать." style="position:absolute;">
                                            <i class="fa fa-info-circle"></i>
                                        </button>
                                    </th>
                                    <th class="text-center">
                                        Заявки</th>
                                    <th class="text-right">
                                        Действия</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($projects as $project)
                                    @foreach($project->contracts->where('main_contract_id', '') as $parent)
                                        @include('contractors.modules.contract_row', ['contract' => $parent])
                                        @foreach($project->contracts->where('main_contract_id', $parent->id) as $contract)
                                            @include('contractors.modules.contract_row')
                                        @endforeach
                                    @endforeach
                                @endforeach
                                </tbody>
                            </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@push('js_footer')
<script>
    var contractsTable = new Vue({
        el: '#contracts-table',
    })
</script>
@endpush
