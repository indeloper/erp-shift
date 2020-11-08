@extends('layouts.app')

@section('title', 'Материальный учет')

@section('url', '')

@section('content')


@include('building.material_accounting.modules.breadcrump')

@include('building.material_accounting.modules.operation_title')


<div class="row">
    <div class="col-md-12 col-xl-10 ml-auto mr-auto">
        @if($operation->type != 3)
        <div class="card strpied-tabled-with-hover">
            <div class="card-body">
                <h5 class="materials-info-title mb-10__mobile">Информация о материалах</h5>
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-hover mobile-table">
                                <thead>
                                    <tr>
                                        <th>Материал</th>

                                        <th class="text-center">Ед. измерения</th>

                                        <th class="text-right">План</th>

                                        @if($operation->materials->where('type', 1)->count())
                                        <th class="text-right">{{ ($operation->type == 4 and ($operation->status == 2)) ? 'Отправлено' : 'Факт' }}</th>
                                        @endif

                                        @if($operation->materials->where('type', 2)->count())
                                        <th class="text-right">{{ ($operation->type == 4 and $operation->status == 2) ? 'Принято' : 'Итог' }}</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($operation->materials->groupBy('manual_material_id') as $materials)
                                        @foreach($materials as $key => $material)

                                        @if($key == 0)
                                        <tr>
                                            <td data-label="Материал">{{ $material->manual->name }}</td>
                                            <td data-label="Ед. измерения" class="text-center">{{ $material->units_name[$material->unit]  }}</td>
                                        @endif

                                                <!-- <td data-label="План" class="text-right">{{ round(round($material->count, 3), 3) }}</td> -->

                                        @if($materials->count() == 2)
                                            @if($material->type == 3)
                                                <td data-label="План" class="text-right">{{ round($material->count, 3) }}</td>
                                            @endif
                                            @if($material->type == 1)
                                                <td data-label="Факт" class="text-right">{{ round($material->count, 3) }}</td>
                                            @endif
                                        @else
                                            @if($material->type == 3)
                                                <td data-label="План" class="text-right">{{ round($material->count, 3) }}</td>
                                            @endif
                                        @endif

                                        @if($key == count($materials))
                                        </tr>
                                        @endif
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="card strpied-tabled-with-hover">
            <div class="card-body">
                <h5 class="materials-info-title mb-10__mobile">Материалы после</h5>
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-hover mobile-table">
                                <thead>
                                    <tr>
                                        <th>Материал</th>

                                        <th class="text-center">Ед. измерения</th>

                                        <th class="text-right">План</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $manual_ids = []; @endphp
                                    @foreach($operation->materials->whereIn('type', [2, 4, 6])->groupBy('manual_material_id') as $materials)
                                        @foreach($materials as $key => $material)
                                            @if (!in_array($material->manual_material_id, $manual_ids))
                                            @php $manual_ids[] = $material->manual_material_id; @endphp
                                            <tr>
                                                <td data-label="Материал">{{ $material->manual->name }}</td>
                                                <td data-label="Ед. измерения" class="text-center">{{ $material->units_name[$material->unit]  }}</td>
                                                <td data-label="План" class="text-right">{{ $materials->where('type', 6)->first() ? round($materials->where('type', 6)->first()->count, 3) : '-' }}</td>
                                            </tr>
                                            @endif
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card strpied-tabled-with-hover">
            <div class="card-body">
                <h5 class="materials-info-title mb-10__mobile">Материалы до</h5>
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-hover mobile-table">
                                <thead>
                                    <tr>
                                        <th>Материал</th>

                                        <th class="text-center">Ед. измерения</th>

                                        <th class="text-right">План</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $manual_ids = []; @endphp
                                    @foreach($operation->materials->whereIn('type', [1, 5, 7])->groupBy('manual_material_id') as $materials)
                                        @foreach($materials as $key => $material)
                                            @if (!in_array($material->manual_material_id, $manual_ids))
                                            @php $manual_ids[] = $material->manual_material_id; @endphp
                                            <tr>
                                                <td data-label="Материал">{{ $material->manual->name }}</td>
                                                <td data-label="Ед. измерения" class="text-center">{{ $material->units_name[$material->unit]  }}</td>
                                                <td data-label="План" class="text-right">{{ $materials->where('type', 7)->first() ? round($materials->where('type', 7)->first()->count, 3) : '-' }}</td>
                                            </tr>
                                            @endif
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

        @if($operation->comment_author)
        <div class="card strpied-tabled-with-hover">
            <div class="card-body">
                <div class="row" id="sender_answer">
                    <div class="col-md-12">
                        <label style="margin:10px">Комментарий автора</label>
                        <blockquote>
                            <p style="font-size:14px">
                                {{ $operation->comment_author }}
                            </p>
                            <div class="row">
                                <div class="col-md-7">
                                    <a href="#" data-toggle="modal" data-target="#view-photo" class="table-link blockquote-link">
                                        <i class="fa fa-picture-o "></i>
                                        Фото
                                    </a>
                                    <el-button type="text" @click="dialogTableVisible = true">
                                        <i class="fa fa-file"></i>
                                        Сопроводительные документы
                                    </el-button>

                                    <el-dialog title="Сопроводительные документы" :visible.sync="dialogTableVisible">
                                      <el-table :data="docsList">
                                        <el-table-column property="file_name" label="Имя файла"></el-table-column>
                                        <el-table-column property="created_at" label="Дата добавления"></el-table-column>
                                        <el-table-column property="url" align="right" label="Действия">
                                            <template slot-scope="scope">
                                                <el-button type="text" @click="url_to_doc(scope.$index, scope.row)">
                                                    <i class="fa fa-eye"></i>
                                                </el-button>
                                            </template>
                                        </el-table-column>
                                      </el-table>
                                    </el-dialog>
                                </div>
                                <div class="col-md-5 text-right">
                                    <small>{{ $operation->author
                                        ->full_name }}</small>
                                </div>
                            </div>

                            <div class="clearfix"></div>
                        </blockquote>
                    </div>
                </div>
                @endif

                @if($operation->comment_to)
                <div class="row" id="recipient_answer">
                    <div class="col-md-12">
                        <label style="margin:10px">Комментарий исполнителя</label>
                        <blockquote>
                            <p style="font-size:14px">
                                {{ $operation->comment_to }}
                            </p>
                            <div class="row">
                                <div class="col-md-7">
                                    <a href="#" data-toggle="modal" data-target="#view-photo" class="table-link blockquote-link">
                                        <i class="fa fa-picture-o "></i>
                                        Фото
                                    </a>
                                    <el-button type="text" @click="dialogTableVisible = true">
                                        <i class="fa fa-file"></i>
                                        Сопроводительные документы
                                    </el-button>

                                    <el-dialog title="Сопроводительные документы" :visible.sync="dialogTableVisible">
                                      <el-table :data="docsList">
                                        <el-table-column property="file_name" label="Имя файла"></el-table-column>
                                        <el-table-column property="created_at" label="Дата добавления"></el-table-column>
                                        <el-table-column property="url" align="right" label="Действия">
                                            <template slot-scope="scope">
                                                <el-button type="text" @click="url_to_doc(scope.$index, scope.row)">
                                                    <i class="fa fa-eye"></i>
                                                </el-button>
                                            </template>

                                        </el-table-column>
                                      </el-table>
                                    </el-dialog>
                                </div>
                                <div class="col-md-5 text-right">
                                    <small>{{ $operation->recipient->full_name }}</small>
                                </div>
                            </div>

                            <div class="clearfix"></div>
                        </blockquote>
                    </div>
                </div>
                @endif

                @if($operation->comment_from)
                <div class="row" id="recipient_answer">
                    <div class="col-md-12">
                        <label style="margin:10px">Комментарий исполнителя</label>
                        <blockquote>
                            <p style="font-size:14px">
                                {{ $operation->comment_from }}
                            </p>
                            <div class="row">
                                <div class="col-md-7">
                                    <a href="#" data-toggle="modal" data-target="#view-photo" class="table-link blockquote-link">
                                        <i class="fa fa-picture-o "></i>
                                        Фото
                                    </a>
                                    <el-button type="text" @click="dialogTableVisible = true">
                                        <i class="fa fa-file"></i>
                                        Сопроводительные документы
                                    </el-button>

                                    <el-dialog title="Сопроводительные документы" :visible.sync="dialogTableVisible">
                                      <el-table :data="docsList">
                                        <el-table-column property="file_name" label="Имя файла"></el-table-column>
                                        <el-table-column property="created_at" label="Дата добавления"></el-table-column>
                                        <el-table-column property="url" align="right" label="Действия">
                                            <template slot-scope="scope">
                                                <el-button type="text" @click="url_to_doc(scope.$index, scope.row)">
                                                    <i class="fa fa-eye"></i>
                                                </el-button>
                                            </template>

                                        </el-table-column>
                                      </el-table>
                                    </el-dialog>
                                </div>
                                <div class="col-md-5 text-right">
                                    <small>{{ $operation->sender->full_name }}</small>
                                </div>
                            </div>

                            <div class="clearfix"></div>
                        </blockquote>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="modal fade bd-example-modal-lg show" id="view-photo" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Фото</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <hr style="margin-top:0">
                <div class="card border-0" >
                    <div class="card-body ">
                        @foreach($operation->images_sender as $image)

                        <div class="row attached-photocard">
                            <div class="col-md-5">
                                <div class="attached-photo-container">
                                    <a href="{{ $image->url }}" target="_blank">
                                        <img class="attached-photo" src="{{ $image->url }}" alt="фото">
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-7" style="margin-top:10px">
                                <div class="meta-photo-container">
                                    <div class="row">
                                        <label class="col-sm-4 meta-title">Создано</label>
                                        <div class="col-sm-8">
                                            <span class="meta-content">{{ $image->created_at }}</span>
                                        </div>
                                    </div>
                                    <!-- <div class="row">
                                        <label class="col-sm-4 meta-title">Геометка</label>
                                        <div class="col-sm-8">
                                            <a href="https://yandex.ru/maps/2/saint-petersburg/?ll=30.397923%2C59.808618&mode=whatshere&whatshere%5Bpoint%5D=30.395531%2C59.807651&whatshere%5Bzoom%5D=16.9&z=16.9" class="table-link" target="_blank">
                                                <span class="meta-content">Шушары, Поселковая улица, 12В</span>
                                            </a>
                                        </div>
                                    </div> -->
                                    <!-- <div class="row">
                                        <label class="col-sm-4 meta-title">Разрешение</label>
                                        <div class="col-sm-8">
                                            <span class="meta-content">3264 x 2448</span>
                                        </div>
                                    </div> -->
                                    <!-- <div class="row">
                                        <label class="col-sm-4 meta-title">Размер файла</label>
                                        <div class="col-sm-8">
                                            <span class="meta-content">2,79 мб</span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-sm-4 meta-title">Устройство</label>
                                        <div class="col-sm-8">
                                            <span class="meta-content">iphone 5s</span>
                                        </div>
                                    </div> -->
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>


@endsection

@section('js_footer')
<script>

    var sender_answer = new Vue({
        el: '#sender_answer',
        data: {
            docsList: {!! json_encode($operation->documents_sender) !!},
            dialogTableVisible: false,
        },
        methods: {
            url_to_doc: function (index, row) {
                window.open(row.url, '_blank');
            }
        }
    });


    var recipient_answer = new Vue({
        el: '#recipient_answer',
        data: {
            docsList: {!! json_encode($operation->documents_recipient) !!},
            dialogTableVisible: false,
        },
        methods: {
            url_to_doc: function (index, row) {
                window.open(row.url, '_blank');
            }
        }
    });

</script>
@endsection
