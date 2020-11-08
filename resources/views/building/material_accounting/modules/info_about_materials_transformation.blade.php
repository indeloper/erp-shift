<div class="card strpied-tabled-with-hover">
    <div class="card-body">
        <h5 class="materials-info-title mb-10__mobile">Сведения о материалах</h5>
        <div class="row">
            <div class="col-md-12">
                <h6 style="margin:20px 0px 20px 5px">
                    Материалы до преобразования
                </h6>
                <div class="table-responsive">
                    <table class="table table-hover mobile-table">
                        <thead>
                            <tr>
                                <th style="width: 41%">Материал</th>

                                <th class="text-center" style="width: 17%">Ед. измерения</th>

                                <th class="text-right" style="width: 14%">План</th>

                                @if($operation->materials->where('type', 1)->count())
                                <th class="text-right" @if($operation->materials->where('type', 5)->count()) style="width: 14%" @endif>Факт</th>
                                @endif

                                @if($operation->materials->where('type', 5)->count())
                                <th class="text-right">Итог</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($operation->materials->whereIn('type', [7, 1, 5])->groupBy(['manual_material_id', 'used']) as $materialsGroupedByManualMaterialId)
                                @foreach($materialsGroupedByManualMaterialId as $materialsGroupedByUsage)
                                    <tr>
                                    <td data-label="Материал">{{ $materialsGroupedByUsage->first()->material_name }}</td>
                                    <td data-label="Ед. измерения" class="text-center">
                                        {{ $materialsGroupedByUsage->first()->units_name[$materialsGroupedByUsage->first()->unit]  }} <br>
                                        @if($materialsGroupedByUsage->first())
                                            @foreach($materialsGroupedByUsage->first()->converted_count as $conv_count)
                                                {{ $conv_count['unit'] }} <br>
                                            @endforeach
                                        @endif
                                    </td>
                                    <td data-label="План" class="text-right">
                                        {{ $materialsGroupedByUsage->where('type', 7)->first() ? round($materialsGroupedByUsage->where('type', 7)->sum('count'), 3) : '-' }} <br>
                                        @if($materialsGroupedByUsage->where('type', 7)->first())
                                            @foreach($materialsGroupedByUsage->where('type', 7)->first()->converted_count as $key=>$conv_count)
                                                {{ round($materialsGroupedByUsage->where('type', 7)->map(function ($item) use ($key) {
                                                    return $item->converted_count[$key];
                                                })->sum('count'), 3) }} <br>
                                            @endforeach
                                        @endif
                                    </td>

                                    @if($operation->materials->where('type', 1)->count())
                                        <td data-label="Факт" class="text-right">
                                            {{ $materialsGroupedByUsage->where('type', 1)->first() ? round($materialsGroupedByUsage->where('type', 1)->sum('count'), 3) : '-' }} <br>
                                            @if($materialsGroupedByUsage->where('type', 1)->first())
                                                @foreach($materialsGroupedByUsage->where('type', 1)->first()->converted_count as $key=>$conv_count)
                                                    {{ round($materialsGroupedByUsage->where('type', 1)->map(function ($item) use ($key) {
                                                        return $item->converted_count[$key];
                                                    })->sum('count'), 3) }} <br>
                                                @endforeach
                                            @endif
                                        </td>
                                    @endif

                                    @if($operation->materials->where('type', 5)->count())
                                        <td data-label="Итог" class="text-right">
                                            {{ $materialsGroupedByUsage->where('type', 5)->first() ? round($materialsGroupedByUsage->where('type', 5)->sum('count'), 3) : '-' }} <br>
                                            @if($materialsGroupedByUsage->where('type', 5)->first())
                                                @foreach($materialsGroupedByUsage->where('type', 5)->first()->converted_count as $key=>$conv_count)
                                                    {{ round($materialsGroupedByUsage->where('type', 5)->map(function ($item) use ($key) {
                                                        return $item->converted_count[$key];
                                                    })->sum('count'), 3) }} <br>
                                                @endforeach
                                            @endif
                                        </td>
                                    @endif
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <h6 style="margin:20px 0px 20px 5px">
                    Материалы после преобразования
                </h6>
                <div class="table-responsive">
                    <table class="table table-hover mobile-table">
                        <thead>
                            <tr>
                                <th style="width: 41%">Материал</th>

                                <th class="text-center" style="width: 17%">Ед. измерения</th>

                                <th class="text-right" style="width: 14%">План</th>

                                @if($operation->materials->where('type', 2)->count())
                                <th class="text-right" @if($operation->materials->where('type', 4)->count()) style="width: 14%" @endif>Факт</th>
                                @endif

                                @if($operation->materials->where('type', 4)->count())
                                <th class="text-right">Итог</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($operation->materials->whereIn('type', [6, 2, 4])->groupBy(['manual_material_id', 'used']) as $materialsGroupedByManualMaterialId)
                                @foreach($materialsGroupedByManualMaterialId as $materialsGroupedByUsage)
                                    <tr>
                                        <td data-label="Материал">{{ $materialsGroupedByUsage->first()->material_name }}</td>
                                        <td data-label="Ед. измерения" class="text-center">
                                            {{ $materialsGroupedByUsage->first()->units_name[$materialsGroupedByUsage->first()->unit]  }} <br>
                                            @if($materialsGroupedByUsage->first())
                                                @foreach($materialsGroupedByUsage->first()->converted_count as $conv_count)
                                                    {{ $conv_count['unit'] }} <br>
                                                @endforeach
                                            @endif
                                        </td>
                                        <td data-label="План" class="text-right">
                                            {{ $materialsGroupedByUsage->where('type', 6)->first() ? round($materialsGroupedByUsage->where('type', 6)->sum('count'), 3) : '-' }} <br>
                                            @if($materialsGroupedByUsage->where('type', 6)->first())
                                                @foreach($materialsGroupedByUsage->where('type', 6)->first()->converted_count as $key=>$conv_count)
                                                    {{ round($materialsGroupedByUsage->where('type', 6)->map(function ($item) use ($key) {
                                                        return $item->converted_count[$key];
                                                    })->sum('count'), 3) }} <br>
                                                @endforeach
                                            @endif
                                        </td>

                                        @if($operation->materials->where('type', 2)->count())
                                            <td data-label="Факт" class="text-right">
                                                {{ $materialsGroupedByUsage->where('type', 2)->first() ? round($materialsGroupedByUsage->where('type', 2)->sum('count'), 3) : '-' }} <br>
                                                @if($materialsGroupedByUsage->where('type', 2)->first())
                                                    @foreach($materialsGroupedByUsage->where('type', 2)->first()->converted_count as $key=>$conv_count)
                                                        {{ round($materialsGroupedByUsage->where('type', 2)->map(function ($item) use ($key) {
                                                            return $item->converted_count[$key];
                                                        })->sum('count'), 3) }} <br>
                                                    @endforeach
                                                @endif
                                            </td>
                                        @endif

                                        @if($operation->materials->where('type', 4)->count())
                                            <td data-label="Итог" class="text-right">
                                                {{ $materialsGroupedByUsage->where('type', 4)->first() ? round($materialsGroupedByUsage->where('type', 4)->sum('count'), 3) : '-' }} <br>
                                                @if($materialsGroupedByUsage->where('type', 4)->first())
                                                    @foreach($materialsGroupedByUsage->where('type', 4)->first()->converted_count as $key=>$conv_count)
                                                        {{ round($materialsGroupedByUsage->where('type', 4)->map(function ($item) use ($key) {
                                                            return $item->converted_count[$key];
                                                        })->sum('count'), 3) }} <br>
                                                    @endforeach
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @if($curr->isAuthor())
        @if($curr->status == 1 or $curr->status == 4 or $curr->status == 2)
        <div class="card-footer" style="margin-top:30px" id="info">
            <div class="row">
                <div class="col-md-12 text-right">
                    @if ($curr->status != 2)
                        <a href="{{ $curr->edit_url }}" class="btn btn-sm btn-success">Редактировать</a>
                    @endif
                    <button type="button" @click="close_operation" class="btn btn-sm btn-danger">Отмена операции</button>
                </div>
            </div>
        </div>
        @endif
    @endif

</div>

@push('js_footer')
@if($operation->isAuthor())
<script>

    @if($operation->status == 1 or $operation->status == 4 or $curr->status == 2)
    var info = new Vue({
        el: '#info',
        methods: {
            close_operation: function () {
                info.$confirm('Это действие приведет к отмене операции.', 'Внимание', {
                    confirmButtonText: 'Подтвердить',
                    cancelButtonText: 'Назад',
                    type: 'warning'
                }).then(() => {
                    axios.post('{{ route('building::mat_acc::close_operation', $operation->id) }}').then(function (response) {
                        if (response.data) {
                            info.$message({
                                type: 'success',
                                message: 'Операция отменена'
                            });

                            window.location = '{{ route('building::mat_acc::operations') }}';
                        } else {
                            info.$message({
                              message: 'Нельзя отменить операцию.',
                              type: 'error'
                            });
                        }
                    })
                });
            }
        }
    })
    @endif
</script>
@endif

@endpush
