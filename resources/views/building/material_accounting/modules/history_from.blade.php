<!-- arrival story -->

    @if($operation->materialsPartFrom->count())
        <div class="card tasks-sidebar__item strpied-tabled-with-hover" style="margin-bottom:30px">
            <div class="card-body story-collapse-card">
                <div class="accordions">
                    <div class="card" style="margin-bottom:0">
                        @if($operation->type != 4 && $operation->type != 3)
                            <div class="card-header">
                                <h5 class="card-title">
                                    <a class="collapsed story-collapse-card__link show" data-target="#collapse2" href="#" data-toggle="collapse">
                                        Поступившие материалы
                                        <b class="caret" style="margin-top:8px"></b>
                                    </a>
                                </h5>
                            </div>
                        @endif
                        <div id="collapse2" class="card-collapse collapse show">
                            <div class="card-body without-shadow" id="materials-story">
                                <div class="table-responsive">
                                    <table class="table table-hover mobile-table">
                                        <thead>
                                        <tr>
                                            <th>Дата</th>
                                            <th>Материал</th>
                                            <!-- <th class="text-center">Ед. измерения</th> -->
                                            <th class="text-center">Количество</th>
                                            <th class="text-right">Автор</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($operation->materialsPartFrom as $material)

                                            <tr data-toggle="modal" data-target="#material_arrival{{$material->id}}" class="tr-pointer">
                                                <td data-label="Дата">
                                                    <i class="el-icon-time" style="margin-right: 5px;"></i>
                                                    {{ $material->created_at }}
                                                </td>
                                                <td data-label="Материал">
                                                    {{ $material->manual->name }}
                                                </td>
                                                <!-- <td data-label="Ед. измерения" class="text-center">
                                                    {{ $material->manual->category_unit }}
                                                </td> -->
                                                <td data-label="Количество" class="text-center">
                                                    @if($material->count)
                                                        <b>{{ round($material->count, 3) }}</b> {{ $material->manual->category_unit }}<br>
                                                        @foreach($material->manual->convertation_parameters as $item)
                                                            <b>{{ round($item->value * $material->count, 3) }}</b> {{ $item->unit }}<br>
                                                        @endforeach
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td data-label="Автор" class="text-right">{{ $material->materialAddition->user->full_name }}</td>
                                            </tr>
                                        @endforeach

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="modals_for_history_from">
            @foreach($operation->materialsPartFrom as $material)
                <div class="modal fade bd-example-modal-lg show" id="material_arrival{{$material->id}}" role="dialog" aria-labelledby="modal-search" style="display: none;">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">{{ $operation->type_name }}</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <hr style="margin-top:0">
                                <div class="card border-0" >
                                    <div class="card-body ">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="story-info-block">
                                                    <h6 class="story-info-label">Дата</h6>
                                                    <span class="story-info-item">{{ $material->created_at }}</span>
                                                </div>
                                                <div class="story-info-block">
                                                    <h6 class="story-info-label">Материал</h6>
                                                    <span class="story-info-item">{{ $material->manual->name }}</span>
                                                </div>
                                                <div class="story-info-block">
                                                    <h6 class="story-info-label">Количество</h6>
                                                    <span class="story-info-item">{{ round($material->count, 3) }} {{ $material->manual->category_unit }}</span>
                                                </div>
                                                <!-- <div class="story-info-block">
                                                    <h6 class="story-info-label">Гос. номер авто</h6>
                                                    <span class="story-info-item">E123EE29</span>
                                                </div> -->
                                            </div>
                                            <div class="col-md-12">
                                                <h6 class="h6-none-transform">Комментарий исполнителя</h6>
                                                <blockquote>
                                                    <p style="font-size:14px">
                                                        {{ $material->materialAddition->description }}
                                                    </p>
                                                    <div class="row">
                                                        <div class="col-md-7">

                                                        </div>
                                                        <div class="col-md-5 text-right">
                                                            <small>{{ $material->materialAddition->user->full_name }}</small>
                                                        </div>
                                                    </div>
                                                    <div class="clearfix"></div>
                                                </blockquote>
                                            </div>
                                            @if($material->materialFiles->where('type', 2)->count())
                                                <div class="col-md-12">
                                                    <h6 class="h6-none-transform">Приложенные фото</h6>
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            @foreach($material->materialFiles->where('type', 2) as $photo)
                                                                <div class="report-photo">
                                                                    <div class="about-photo">
                                                                        {{ $photo->created_at }}
                                                                    </div>
                                                                    <a href="{{ $photo->url }}" target="_blank">
                                                                        <div class="operation-story-photo">
                                                                            <img class="operation-story__attached-photo" src="{{ $photo->url }}" alt="фото">
                                                                        </div>
                                                                    </a>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                            @if($material->materialFiles->whereIn('type', [0, 1])->count())
                                                <div class="col-md-12">
                                                    <h6 class="h6-none-transform">Сопроводительная документация</h6>
                                                    <div class="table-responsive">
                                                        <table class="table table-hover mobile-table">
                                                            <thead>
                                                            <tr>
                                                                <th class="sort">Имя файла</th>
                                                                <th class="sort">Дата добавления</th>
                                                                <th class="sort">Действия</th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            @foreach($material->materialFiles->whereIn('type', [0, 1]) as $file)
                                                                <tr>
                                                                    <td data-label="Имя файла">{{ $file->file_name }}</td>
                                                                    <td data-label="Дата добавления">{{ $file->created_at }}</td>
                                                                    <td data-label="Действия">
                                                                        <a href="{{ $file->url }}" target="_blank">
                                                                            <i class="fa fa-eye"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    @endif

@push('js_footer')
<script>
var modals_to = new Vue({
    el: '#modals_for_history_from',
    methods: {
        url_to_doc: function (index, row) {
            window.open(row.url, '_blank');
        }
    }
});

var story = new Vue({
    el: '#materials-story'
});
</script>
@endpush
