<div class="card strpied-tabled-with-hover">
    <div class="card-body">
        <h5 class="materials-info-title mb-10__mobile">Сведения о материалах</h5>
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-hover mobile-table">
                        <thead>
                        <tr>
                            <th>Материал</th>

                            <th class="text-center">Ед. измерения</th>

                            <th class="text-right">План</th>

                            @if($operation->status == 1)
                                @if($operation->type != 1)<th class="text-right">Отправлено (расхожд.)</th>@endif
                                @if($operation->type != 2)<th class="text-right">Прибыло (расхожд.)</th>@endif
                            @endif

                            @if($operation->status >= 2)
                                <th class="text-right">{{ ($operation->type == 4 ? 'Отправлено' : 'Факт') }}</th>
                            @endif

                            @if($operation->status > 1 and !in_array($operation->type, [1, 2]))
                                <th class="text-right">{{ ($operation->type == 4 ? 'Принято' : 'Итог') }}</th>
                            @endif

                            @if($operation->status > 2)
                                <th class="text-right">Итог</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody>
                        @php $base_ids = []; @endphp

                        @foreach($operation->allMaterials->whereIn('type', [3, 7]) as $plan_mat)
                            @php $materials = $plan_mat->sameMaterials @endphp
                            <tr>
                                <td data-label="Материал">{{ $plan_mat->comment_name }}</td>
                                <td data-label="Ед. измерения" class="text-center">
                                    {{ $plan_mat->units_name[$plan_mat->unit] }} <br>
                                    @foreach($plan_mat->converted_count as $conv_count)
                                        {{ $conv_count['unit'] }} <br>
                                    @endforeach
                                </td>
                                <td data-label="План" class="text-right">{{ round($plan_mat->count, 3) }}<br>
                                    @foreach($plan_mat->converted_count as $key => $conv_count)
                                        {{ round($conv_count['count'], 3) }} <br>
                                    @endforeach
                                </td>
                                @if($operation->type != 1)
                                    <td data-label="Отправлено(расхож.)" class="text-right">
                                        @foreach(array_merge([['unit' => $plan_mat->units_name[$plan_mat->unit], 'count' => $plan_mat->count]], $plan_mat->converted_count) as $key => $conv_count)
                                            {{ round($materials->where('type', 8)->map(function ($item) use ($conv_count) {
                                                return collect(array_merge([['unit' => $item->units_name[$item->unit], 'count' => $item->count]], $item->converted_count))->where('unit', $conv_count['unit'])->sum('count');
                                            })->sum(), 3) }}
                                            @php $diff = round($materials->where('type', 8)->map(function ($item) use ($conv_count) {
                                                return collect(array_merge([['unit' => $item->units_name[$item->unit], 'count' => $item->count]], $item->converted_count))->where('unit', $conv_count['unit'])->sum('count');
                                                        })->sum() - $conv_count['count'], 3); @endphp
                                            @if($diff < 0)
                                                <span class="negative-fact"> ({{ $diff }})</span>
                                            @elseif($diff > 0)
                                                <span class="positive-fact"> ({{ '+' . $diff }})</span>
                                            @endif
                                            <br>
                                        @endforeach
                                    </td>
                                @endif
                                @if ($operation->type != 2)
                                    <td data-label="Прибыло(расхож.)" class="text-right">
                                        @foreach(array_merge([['unit' => $plan_mat->units_name[$plan_mat->unit], 'count' => $plan_mat->count]], $plan_mat->converted_count) as $key => $conv_count)
                                            {{ round($materials->where('type', 9)->map(function ($item) use ($conv_count) {
                                                return collect(array_merge([['unit' => $item->units_name[$item->unit], 'count' => $item->count]], $item->converted_count))->where('unit', $conv_count['unit'])->sum('count');
                                            })->sum(), 3) }}
                                            @php $diff = round($materials->where('type', 9)->map(function ($item) use ($conv_count) {
                                                return collect(array_merge([['unit' => $item->units_name[$item->unit], 'count' => $item->count]], $item->converted_count))->where('unit', $conv_count['unit'])->sum('count');
                                                    })->sum() - $conv_count['count'], 3); @endphp
                                            @if($diff < 0)
                                                <span class="negative-fact"> ({{ $diff }})</span>
                                            @elseif($diff > 0)
                                                <span class="positive-fact"> ({{ '+' . $diff }})</span>
                                            @endif
                                            <br>
                                        @endforeach
                                    </td>
                                @endif
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
        @if(($curr->status == 1 or $curr->status == 4 or $curr->status == 2) and $curr->type != 5)
            <div class="card-footer" style="margin-top:30px" id="info">
                <div class="row">
                    <div class="col-md-12 mobile-btn-align">
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
    @if($curr->isAuthor())
        <script>
            var info = new Vue({
                el: '#info',
                methods: {
                    close_operation: function () {
                        info.$confirm('Это действие приведет к отмене операции.', 'Внимание', {
                            confirmButtonText: 'Подтвердить',
                            cancelButtonText: 'Назад',
                            type: 'warning'
                        }).then(() => {
                            axios.post('{{ route('building::mat_acc::close_operation', $curr->id) }}').then(function (response) {
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
        </script>
    @endif

@endpush
