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

                            <th class="text-right">План</th>

                            <th class="text-right">Факт (расхожд.)</th>

                            @if($operation->allMaterials->where('type', 5)->count())
                                <th class="text-right">Итог</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($operation->allMaterials->whereIn('type', [3, 7]) as $plan_mat)
                            @php $materials = $plan_mat->sameMaterials @endphp
                            <tr>
                                <td data-label="Материал">{{ $plan_mat->comment_name }}</td>
                                <td data-label="Ед. измерения" class="text-center">
                                    {{ $plan_mat->units_name[$plan_mat->unit]  }} <br>
                                    @foreach($plan_mat->converted_count as $conv_count)
                                        {{ $conv_count['unit'] }} <br>
                                    @endforeach
                                </td>
                                <td data-label="План" class="text-right">
                                    {{ round($plan_mat->count, 3) }} <br>
                                    @foreach($plan_mat->converted_count as $key => $conv_count)
                                        {{ round($conv_count['count'], 3) }} <br>
                                    @endforeach
                                </td>

                                <td data-label="Факт (расхож.)" class="text-right">
                                    {{ round($materials->where('type', 8)->sum('count'), 3) }}
                                    @php $diff = round($materials->where('type', 8)->sum('count') - ($plan_mat->count ?? 0), 3); @endphp
                                    @if($diff < 0)
                                        <span class="negative-fact"> ({{ $diff }})</span>
                                    @elseif($diff > 0)
                                        <span class="positive-fact"> ({{ '+' . $diff }}) </span>
                                    @endif <br>
                                    @foreach($plan_mat->converted_count as $key => $conv_count)
                                        {{ round($materials->where('type', 8)->map(function ($item) use ($key) {
                                            return $item->converted_count[$key];
                                        })->sum('count'), 3) }}
                                        @php $diff = round($materials->where('type', 8)->map(function ($item) use ($key) {
                                                                return $item->converted_count[$key];
                                                            })->sum('count') - $conv_count['count'], 3); @endphp
                                        @if($diff < 0)
                                            <span class="negative-fact"> ({{ $diff }})</span>
                                        @elseif($diff > 0)
                                            <span class="positive-fact"> ({{ '+' . $diff }})</span>
                                        @endif
                                        <br>
                                    @endforeach
                                </td>

                                @if($operation->status > 2)
                                    <td data-label="Итог" class="text-right">{{ $materials->whereIn('type', [2, 5])->first() ? round($materials->whereIn('type', [2, 5])->first()->count, 3) : '-' }}<br>
                                        @if($materials->whereIn('type', [2, 5])->first())
                                            @foreach($materials->whereIn('type', [2, 5])->first()->converted_count as $key=>$conv_count)
                                                {{ round($materials->whereIn('type', [2, 5])->map(function ($item) use ($key) {
                                                    return $item->converted_count[$key];
                                                })->sum('count'), 3) }} <br>
                                    @endforeach
                                @endif
                                @endif
                            </tr>
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

                            <th class="text-right">План</th>

                            <th class="text-right">Факт (расхожд.)</th>

                            @if($operation->allMaterials->where('type', 4)->count())
                                <th class="text-right">Итог</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($operation->allMaterials->whereIn('type', [3, 6]) as $plan_mat)
                            @php $materials = $plan_mat->sameMaterials @endphp
                            <tr>
                                <td data-label="Материал">{{ $plan_mat->comment_name }}</td>
                                <td data-label="Ед. измерения" class="text-center">
                                    {{ $plan_mat->units_name[$plan_mat->unit]  }} <br>
                                    @foreach($plan_mat->converted_count as $conv_count)
                                        {{ $conv_count['unit'] }} <br>
                                    @endforeach
                                </td>
                                <td data-label="План" class="text-right">
                                    {{ round($plan_mat->count, 3) }} <br>
                                    @foreach($plan_mat->converted_count as $key => $conv_count)
                                        {{ round($conv_count['count'], 3) }} <br>
                                    @endforeach
                                </td>

                                <td data-label="Факт (расхож.)" class="text-right">
                                    {{ round($materials->where('type', 9)->sum('count'), 3) }}
                                    @php $diff = round($materials->where('type', 9)->sum('count') - $plan_mat->count, 3); @endphp
                                    @if($diff < 0)
                                        <span class="negative-fact"> ({{ $diff }})</span>
                                    @elseif($diff > 0)
                                        <span class="positive-fact"> ({{ '+' . $diff }})</span>
                                    @endif
                                    <br>
                                    @foreach($plan_mat->converted_count as $key => $conv_count)
                                        {{ round($materials->where('type', 9)->map(function ($item) use ($key) {
                                            return $item->converted_count[$key];
                                        })->sum('count'), 3) }}
                                        @php $diff = round($materials->where('type', 9)->map(function ($item) use ($key) {
                                                            return $item->converted_count[$key];
                                                        })->sum('count') - $conv_count['count'], 3); @endphp
                                        @if($diff < 0)
                                            <span class="negative-fact"> ({{ $diff }})</span>
                                        @elseif($diff > 0)
                                            <span class="positive-fact"> ({{ '+' . $diff }})</span>
                                        @endif
                                        <br>
                                    @endforeach
                                </td>

                                @if($operation->status > 2)
                                    <td data-label="Итог" class="text-right">{{ $materials->whereIn('type', [2, 4])->first() ? round($materials->whereIn('type', [2, 4])->first()->count, 3) : '-' }}<br>
                                        @if($materials->whereIn('type', [2, 4])->first())
                                            @foreach($materials->whereIn('type', [2, 4])->first()->converted_count as $key=>$conv_count)
                                                {{ round($materials->whereIn('type', [2, 4])->map(function ($item) use ($key) {
                                                    return $item->converted_count[$key];
                                                })->sum('count'), 3) }} <br>
                                            @endforeach
                                        @endif
                                    </td>
                                @endif
                            </tr>
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
