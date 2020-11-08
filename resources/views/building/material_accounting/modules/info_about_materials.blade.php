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

                            @if($operation->type == 4)
                                @if($operation->materials->where('type', 1)->count())
                                    <th class="text-right">Отправлено</th>
                                @endif

                                @if($operation->materials->where('type', 2)->count())
                                    <th class="text-right">Принято</th>
                                @endif

                                @if($operation->materials->where('type', 4)->count())
                                    <th class="text-right">Итог</th>
                                @endif
                            @else
                                @if($operation->materials->where('type', 1)->count())
                                    <th class="text-right">Факт</th>
                                @endif

                                @if($operation->materials->where('type', 2)->count())
                                    <th class="text-right">Итог</th>
                                @endif
                            @endif
                        </tr>
                        </thead>
                        <tbody>
                        @php $manual_ids = []; @endphp
                        @foreach($operation->materials->groupBy(['manual_material_id', 'used']) as $materialsGroupedByManualMaterialId)
                            @foreach($materialsGroupedByManualMaterialId as $materialsGroupedByUsage)
                                <tr>
                                    <td data-label="Материал">{{ $materialsGroupedByUsage->first()->material_name }}</td>
                                    <td data-label="Ед. измерения" class="text-center">
                                        {{$materialsGroupedByUsage->first()->units_name[$materialsGroupedByUsage->first()->unit]}} <br>
                                        @if($materialsGroupedByUsage->first())
                                            @foreach($materialsGroupedByUsage->first()->converted_count as $conv_count)
                                                {{ $conv_count['unit'] }} <br>
                                            @endforeach
                                        @endif
                                    </td>
                                    <td data-label="План" class="text-right">
                                        {{ $materialsGroupedByUsage->where('type', 3)->first() ? round($materialsGroupedByUsage->where('type', 3)->sum('count'), 3) : '-' }} <br>
                                        @if($materialsGroupedByUsage->where('type', 3)->first())
                                            @foreach($materialsGroupedByUsage->where('type', 3)->first()->converted_count as $key=>$conv_count)
                                                {{ round($materialsGroupedByUsage->where('type', 3)->map(function ($item) use ($key) {
                                                    return $item->converted_count[$key];
                                                })->sum('count'), 3) }} <br>
                                            @endforeach
                                        @endif
                                    </td>
                                    @if($operation->type > 2)
                                        @if($operation->materials->where('type', 1)->count())
                                            <td data-label="Отправлено" class="text-right">
                                                {{ $materialsGroupedByUsage->where('type', 1)->first() ? round($materialsGroupedByUsage->where('type', 1)->sum('count'), 3) : '-' }}
                                                @if($materialsGroupedByUsage->where('type', 1)->first() && $materialsGroupedByUsage->where('type', 3)->first())
                                                    @php $diff = round($materialsGroupedByUsage->where('type', 1)->sum('count') - $materialsGroupedByUsage->where('type', 3)->sum('count'), 3); @endphp
                                                    @if($diff < 0)
                                                        <span class="negative-fact"> ({{ $diff }})</span>
                                                    @elseif($diff > 0)
                                                        <span class="positive-fact"> ({{ '+' . $diff }})</span>
                                                    @endif
                                                @endif <br>
                                                @if($materialsGroupedByUsage->where('type', 1)->first())
                                                    @foreach($materialsGroupedByUsage->where('type', 1)->first()->converted_count as $key=>$conv_count)
                                                        {{ round($materialsGroupedByUsage->where('type', 1)->map(function ($item) use ($key) {
                                                            return $item->converted_count[$key];
                                                        })->sum('count'), 3) }}
                                                        @if($materialsGroupedByUsage->where('type', 1)->first() && $materialsGroupedByUsage->where('type', 3)->first())
                                                            @php $diff = round($materialsGroupedByUsage->where('type', 1)->map(function ($item) use ($key) {
                                                                return $item->converted_count[$key];
                                                            })->sum('count') - $materialsGroupedByUsage->where('type', 3)->map(function ($item) use ($key) {
                                                                return $item->converted_count[$key];
                                                            })->sum('count'), 3); @endphp
                                                            @if($diff < 0)
                                                                <span class="negative-fact"> ({{ $diff }})</span>
                                                            @elseif($diff > 0)
                                                                <span class="positive-fact"> ({{ '+' . $diff }})</span>
                                                            @endif
                                                        @endif
                                                        <br>
                                                    @endforeach
                                                @endif
                                            </td>
                                        @endif

                                        @if($operation->materials->where('type', 2)->count())
                                            <td data-label="Принято" class="text-right">
                                                {{ $materialsGroupedByUsage->where('type', 2)->first() ? round($materialsGroupedByUsage->where('type', 2)->sum('count'), 3) : '-' }}
                                                @if($materialsGroupedByUsage->where('type', 2)->first()  && $materialsGroupedByUsage->where('type', 3)->first())
                                                    @php $diff = round($materialsGroupedByUsage->where('type', 2)->sum('count') - $materialsGroupedByUsage->where('type', 3)->sum('count'), 3); @endphp
                                                    @if($diff < 0)
                                                        <span class="negative-fact"> ({{ $diff }})</span>
                                                    @elseif($diff > 0)
                                                        <span class="positive-fact"> ({{ '+' . $diff }})</span>
                                                    @endif
                                                @endif <br>
                                                @if($materialsGroupedByUsage->where('type', 2)->first())
                                                    @foreach($materialsGroupedByUsage->where('type', 2)->first()->converted_count as $key=>$conv_count)
                                                        {{ round($materialsGroupedByUsage->where('type', 2)->map(function ($item) use ($key) {
                                                            return $item->converted_count[$key];
                                                        })->sum('count'), 3) }}
                                                        @if($materialsGroupedByUsage->where('type', 2)->first() && $materialsGroupedByUsage->where('type', 3)->first())
                                                            @php $diff = round($materialsGroupedByUsage->where('type', 2)->map(function ($item) use ($key) {
                                                                return $item->converted_count[$key];
                                                            })->sum('count') - $materialsGroupedByUsage->where('type', 3)->map(function ($item) use ($key) {
                                                                return $item->converted_count[$key];
                                                            })->sum('count'), 3); @endphp
                                                            @if($diff < 0)
                                                                <span class="negative-fact"> ({{ $diff }})</span>
                                                            @elseif($diff > 0)
                                                                <span class="positive-fact"> ({{ '+' . $diff }})</span>
                                                            @endif
                                                        @endif
                                                        <br>
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
                                    @else
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

                                        @if($operation->materials->where('type', 2)->count())
                                            <td data-label="Итог" class="text-right">
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
    @if($curr->isAuthor() || $curr->responsible_RP == Auth::user()->id)
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
                        if (response.data.status == 'success') {
                            info.$message({
                                type: 'success',
                                message: 'Операция отменена'
                            });

                            window.location = '{{ route('building::mat_acc::operations') }}';
                        } else if (response.data.status == 'error') {
                            info.$message({
                              message: response.data.message,
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
