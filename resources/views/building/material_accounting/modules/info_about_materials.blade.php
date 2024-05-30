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
                        @foreach($operation->materials->groupBy('base_id') as $materialsGroupedByBaseId)
                            <tr>
                                    <td data-label="Материал">{{ $materialsGroupedByBaseId->first()->comment_name }}</td>
                                    <td data-label="Ед. измерения" class="text-center">{{ $materialsGroupedByBaseId->first()->units_name[$materialsGroupedByBaseId->first()->unit]  }}</td>
                                    <td data-label="План" class="text-right">{{ $materialsGroupedByBaseId->where('type', 3)->first() ? round($materialsGroupedByBaseId->where('type', 3)->sum('count'), 3) : '-' }}</td>
                                    @if($operation->type > 2)
                                        @if($operation->materials->where('type', 1)->count())
                                            <td data-label="Отправлено" class="text-right">{{ $materialsGroupedByBaseId->where('type', 1)->first() ? round($materialsGroupedByBaseId->where('type', 1)->sum('count'), 3) : '-' }}
                                            @if($materialsGroupedByBaseId->where('type', 1)->first() && $materialsGroupedByBaseId->where('type', 3)->first())
                                                @php $diff = round($materialsGroupedByBaseId->where('type', 1)->sum('count') - $materialsGroupedByBaseId->where('type', 3)->sum('count'), 3); @endphp
                                                @if($diff < 0)
                                                    <span class="negative-fact"> ({{ $diff }})</span>
                                                @elseif($diff > 0)
                                                    <span class="positive-fact"> ({{ '+' . $diff }})</span>
                                                @endif
                                            @endif
                                            </td>
                                        @endif

                                        @if($operation->materials->where('type', 2)->count())
                                            <td data-label="Принято" class="text-right">{{ $materialsGroupedByBaseId->where('type', 2)->first() ? round($materialsGroupedByBaseId->where('type', 2)->sum('count'), 3) : '-' }}
                                            @if($materialsGroupedByBaseId->where('type', 2)->first()  && $materialsGroupedByBaseId->where('type', 3)->first())
                                                @php $diff = round($materialsGroupedByBaseId->where('type', 2)->sum('count') - $materialsGroupedByBaseId->where('type', 3)->sum('count'), 3); @endphp
                                                @if($diff < 0)
                                                    <span class="negative-fact"> ({{ $diff }})</span>
                                                @elseif($diff > 0)
                                                    <span class="positive-fact"> ({{ '+' . $diff }})</span>
                                                @endif
                                            @endif
                                            </td>
                                        @endif

                                        @if($operation->materials->where('type', 4)->count())
                                            <td data-label="Итог" class="text-right">{{ $materialsGroupedByBaseId->where('type', 4)->first() ? round($materialsGroupedByBaseId->where('type', 4)->sum('count'), 3) : '-' }}</td>
                                        @endif
                                    @else
                                        @if($operation->materials->where('type', 1)->count())
                                            <td data-label="Факт" class="text-right">{{ $materialsGroupedByBaseId->where('type', 1)->first() ? round($materialsGroupedByBaseId->where('type', 1)->sum('count'), 3) : '-' }}</td>
                                        @endif

                                        @if($operation->materials->where('type', 2)->count())
                                            <td data-label="Итог" class="text-right">{{ $materialsGroupedByBaseId->where('type', 2)->first() ? round($materialsGroupedByBaseId->where('type', 2)->sum('count'), 3) : '-' }}</td>
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
