@extends('layouts.app')

@section('title', 'Материальный учет')

@section('url', '')

@section('css_top')
<style>
    .el-select {width: 100%}
    .el-date-editor.el-input {width: inherit;}
    .margin-top-15 {
        margin-top: 15px;
    }
    .el-input-number {
        width: inherit;
    }
</style>
@endsection

@section('content')


@include('building.material_accounting.modules.breadcrump')

@include('building.material_accounting.modules.operation_title')

<div class="row">
    <div class="col-md-12 col-xl-10 ml-auto mr-auto pd-0-min">
        @if($operation->materialsPartTo()->count() || $operation->materialsPartFrom()->count())
        <div class="card tasks-sidebar__item strpied-tabled-with-hover" style="margin-bottom:30px">
            <div class="card-body story-collapse-card">
                <div class="accordions">
                    <div class="card" style="margin-bottom:0">
                        <div class="card-header">
                            <h5 class="card-title">
                                <span class="collapsed story-collapse-card__link" data-target="#collapse2" data-toggle="collapse">
                                    История трансформации материалов
                                    <b class="caret" style="margin-top:8px"></b>
                                </span>
                            </h5>
                        </div>
                        <div id="collapse2" class="card-collapse collapse show">
                            <div class="card-body without-shadow" id="materials-story">
                                <div>
                                    @include('building.material_accounting.modules.history_composit')
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @include('building.material_accounting.modules.info_about_materials_transformation')
    <div class="card strpied-tabled-with-hover">
        <div class="card-body">
            <div class="row" id="sender_answer">
                <div class="col-md-12">
                    <label style="margin:10px">Комментарий исполнителя</label>
                    <blockquote>
                        <p style="font-size:14px">
                            {{ $operation->comment_from }}
                        </p>
                        <div class="row">
                            <div class="col-md-7">
                                <a href="#" data-toggle="modal" data-target="#view-photo-sender" class="table-link blockquote-link">
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
            <div class="row" id="recipient_answer">
                <div class="col-md-12">
                    <label style="margin:10px">Комментарий автора при подтверждении</label>
                    <blockquote>
                        <p style="font-size:14px">
                            {{ $operation->comment_to }}
                        </p>
                        <div class="row">
                            <div class="col-md-7">
                                <a href="#" data-toggle="modal" data-target="#view-photo-recipient" class="table-link blockquote-link">
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
                                    <el-table-column property="url" label="Действия">
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

            <div class="row" id="author_answer">
                <div class="col-md-12">
                    <label style="margin:10px">Комментарий автора при создании</label>
                    <blockquote>
                        <p style="font-size:14px">
                            {{ $operation->comment_author }}
                        </p>
                        <div class="row">
                            <div class="col-md-7">

                            </div>
                            <div class="col-md-5 text-right">
                                <small>{{ $operation->author->full_name }}</small>
                            </div>
                        </div>

                        <div class="clearfix"></div>
                    </blockquote>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<div class="modal fade bd-example-modal-lg show" id="view-photo-sender" role="dialog" aria-labelledby="modal-search" style="display: none;">
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
                    @foreach($operation->materialFiles->where('type', 2) as $image)

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

<div class="modal fade bd-example-modal-lg show" id="view-photo-recipient" role="dialog" aria-labelledby="modal-search" style="display: none;">
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
                    @foreach($operation->images_recipient as $image)

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
    docsList: {!! json_encode(array_values($operation->materialFiles->whereIn('type', [0, 1])->toArray())) !!},
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
    docsList: {!! json_encode(array_values($operation->documents_recipient->toArray())) !!},
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
