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
                                    <th class="text-right">Отправлено (расхожд.)</th>

                                    <th class="text-right">Прибыло (расхожд.)</th>
                                @endif

                                @if($operation->status >= 2)
                                <th class="text-right">{{ ($operation->type == 4 ? 'Отправлено' : 'Факт') }}</th>
                                @endif

                                @if($operation->status > 1)
                                <th class="text-right">{{ ($operation->type == 4 ? 'Принято' : 'Итог') }}</th>
                                @endif

                                @if($operation->status > 2)
                                <th class="text-right">Итог</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @php $manual_ids = []; @endphp

                            @foreach($operation->materials->groupBy(['manual_material_id', 'used']) as $mat_id => $materialsMany)
                                @foreach($materialsMany as $key => $materials)
                                    @foreach($materials as $key => $material)
                                    @if (!in_array($material->manual_material_id . '!' . $material->used ,$manual_ids))
                                    @php $manual_ids[] = $material->manual_material_id . '!' . $material->used; @endphp
                                    <tr>
                                        <td data-label="Материал">{{ $material->material_name }}</td>
                                        <td data-label="Ед. измерения" class="text-center">
                                            {{ $material->units_name[$material->unit] }} <br>
                                            @foreach($material->converted_count as $conv_count)
                                                {{ $conv_count['unit'] }} <br>
                                            @endforeach
                                        </td>
                                        <td data-label="План" class="text-right">
                                            {{ $materials->where('type', 3)->first() ? round($materials->where('type', 3)->first()->count, 3) : '-' }} <br>
                                            @if($materials->where('type', 3)->first())
                                                @foreach($materials->where('type', 3)->first()->converted_count as $key=>$conv_count)
                                                    {{ round($materials->where('type', 3)->map(function ($item) use ($key) {
                                                        return $item->converted_count[$key];
                                                    })->sum('count'), 3) }} <br>
                                                @endforeach
                                            @endif
                                        </td>

                                        @if($operation->status == 1)
                                        <td data-label="Отправлено(расхож.)"  class="text-right">
                                            {{ round($operation->materialsPartFrom->where('manual_material_id', $material->manual_material_id)->where('used', $material->used)->sum('count'), 3) }}
                                            @if($materials->where('type', 3)->first())
                                                @php $diff = round($operation->materialsPartFrom->where('manual_material_id', $material->manual_material_id)->where('used', $material->used)->sum('count') - $materials->where('type', 3)->first()->count, 3); @endphp
                                                @if($diff < 0)
                                                    <span class="negative-fact"> ({{ $diff }})</span>
                                                @elseif($diff > 0)
                                                    <span class="positive-fact"> ({{ '+' . $diff }}) </span>
                                                @endif
                                            @endif <br>
                                            @if($materials->where('type', 3)->first())
                                                @foreach($materials->where('type', 3)->first()->converted_count as $key=>$conv_count)
                                                    {{ round($operation->materialsPartFrom->where('manual_material_id', $material->manual_material_id)->map(function ($item) use ($key) {
                                                        return $item->converted_count[$key];
                                                    })->sum('count'), 3) }}
                                                    @if($materials->where('type', 3)->first())
                                                        @php $diff = round($operation->materialsPartFrom->where('manual_material_id', $material->manual_material_id)->map(function ($item) use ($key) {
                                                            return $item->converted_count[$key];
                                                        })->sum('count') - $materials->where('type', 3)->map(function ($item) use ($key) {
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
                                        <td data-label="Прибыло(расхож.)" class="text-right">
                                            {{ round($operation->materialsPartTo->where('manual_material_id', $material->manual_material_id)->where('used', $material->used)->sum('count'), 3) }}
                                            @if($materials->where('type', 3)->first())
                                                @php $diff = round($operation->materialsPartTo->where('manual_material_id', $material->manual_material_id)->where('used', $material->used)->sum('count') - $materials->where('type', 3)->first()->count, 3); @endphp
                                                @if($diff < 0)
                                                    <span class="negative-fact"> ({{ $diff }})</span>
                                                @elseif($diff > 0)
                                                    <span class="positive-fact"> ({{ '+' . $diff }})</span>
                                                @endif
                                            @endif <br>
                                            @if($materials->where('type', 3)->first())
                                                @foreach($materials->where('type', 3)->first()->converted_count as $key=>$conv_count)
                                                    {{ round($operation->materialsPartTo->where('manual_material_id', $material->manual_material_id)->map(function ($item) use ($key) {
                                                        return $item->converted_count[$key];
                                                    })->sum('count'), 3) }}
                                                    @if($materials->where('type', 3)->first())
                                                        @php $diff = round($operation->materialsPartTo->where('manual_material_id', $material->manual_material_id)->map(function ($item) use ($key) {
                                                            return $item->converted_count[$key];
                                                        })->sum('count') - $materials->where('type', 3)->map(function ($item) use ($key) {
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
                                        @endif
                                        </td>

                                        @if($operation->status > 1)
                                        <td data-label="Отправлено" class="text-right">
                                            {{ $materials->where('type', 1)->first() ? round($materials->where('type', 1)->first()->count, 3)  : '-' }}
                                            @if($materials->where('type', 1)->first())
                                                @php $diff = round($operation->materialsPartFrom->where('manual_material_id', $material->manual_material_id)->where('used', $material->used)->sum('count') - $materials->where('type', 1)->first()->count, 3); @endphp
                                                @if($diff < 0)
                                                    <span class="negative-fact"> ({{ $diff }})</span>
                                                @elseif($diff > 0)
                                                    <span class="positive-fact"> ({{ '+' . $diff }}) </span>
                                                @endif
                                            @endif <br>
                                            @if($materials->where('type', 1)->first())
                                                @foreach($materials->where('type', 1)->first()->converted_count as $key=>$conv_count)
                                                    {{ round($materials->where('type', 1)->map(function ($item) use ($key) {
                                                        return $item->converted_count[$key];
                                                    })->sum('count'), 3) }}
                                                    @if($materials->where('type', 1)->first())
                                                        @php $diff = round($operation->materialsPartFrom->where('manual_material_id', $material->manual_material_id)->map(function ($item) use ($key) {
                                                            return $item->converted_count[$key];
                                                        })->sum('count') - $materials->where('type', 1)->map(function ($item) use ($key) {
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
                                        <td data-label="Принято" class="text-right">
                                            {{ $materials->where('type', 2)->first() ? round($materials->where('type', 2)->first()->count, 3) : '-' }}
                                            @if($materials->where('type', 2)->first())
                                                @php $diff = round($operation->materialsPartTo->where('manual_material_id', $material->manual_material_id)->where('used', $material->used)->sum('count') - $materials->where('type', 2)->first()->count, 3); @endphp
                                                @if($diff < 0)
                                                    <span class="negative-fact"> ({{ $diff }})</span>
                                                @elseif($diff > 0)
                                                    <span class="positive-fact"> ({{ '+' . $diff }})</span>
                                                @endif
                                            @endif <br>
                                            @if($materials->where('type', 2)->first())
                                                @foreach($materials->where('type', 2)->first()->converted_count as $key=>$conv_count)
                                                    {{ round($materials->where('type', 2)->map(function ($item) use ($key) {
                                                        return $item->converted_count[$key];
                                                    })->sum('count'), 3) }}
                                                    @if($materials->where('type', 2)->first())
                                                        @php $diff = round($operation->materialsPartTo->where('manual_material_id', $material->manual_material_id)->map(function ($item) use ($key) {
                                                            return $item->converted_count[$key];
                                                        })->sum('count') - $materials->where('type', 2)->map(function ($item) use ($key) {
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

                                        @if($operation->status > 2)
                                        <td data-label="Итог" class="text-right">
                                            {{ $materials->where('type', 4)->first() ? round($materials->where('type', 4)->first()->count, 3) : '-' }} <br>
                                            @if($materials->where('type', 4)->first())
                                                @foreach($materials->where('type', 4)->first()->converted_count as $key=>$conv_count)
                                                    {{ round($materials->where('type', 4)->map(function ($item) use ($key) {
                                                        return $item->converted_count[$key];
                                                    })->sum('count'), 3) }} <br>
                                                @endforeach
                                            @endif
                                        @endif
                                        </td>
                                    </tr>
                                    @endif
                                    @endforeach
                                @endforeach
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
