<div class="card">
    <div class="card-header">
        <h4 class="card-title">
            <a data-target="#collapseSix" href="#" data-toggle="collapse">
                Коммерческие предложения
                <b class="caret"></b>
            </a>
        </h4>
    </div>
    <div id="collapseSix" class="card-collapse collapse @if(session('commercial_offers')) show @endif">
        <div class="card-body card-body-table">
            <div class="card strpied-tabled-with-hover">
                @if(!$com_offers->isEmpty())
                <div class="table-responsive">
                    <table class="table table-hover mobile-table">
                        <thead>
                            <tr>
                                <th class="text-left">ID</th>
                                <th>Название</th>
                                <th class="text-center">Объект</th>
                                <th class="text-center">Дата добавления</th>
                                <th>Автор</th>
                                <th class="text-center">Версия</th>
                                <th class="text-center">Статус</th>
                                <th class="text-right">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($com_offers as $document)
                                <tr class="header">
                                    <td data-label="ID" data-target=".collapse{{$document->id}}" data-toggle="collapse" class="collapsed tr-pointer" aria-expanded="false">
                                        {{ $document->id }}
                                    </td>
                                    <td data-label="Название" data-target=".collapse{{$document->id}}" data-toggle="collapse" class="collapsed tr-pointer" aria-expanded="false">
                                        {{ $document->name }}
                                    </td>
                                    <td data-label="Название" class="collapsed tr-pointer" aria-expanded="false">
                                        {{ $document->project->object->name_tag }}
                                    </td>
                                    <td data-label="Дата добавления" class="text-center">{{ $document->updated_at }}</td>
                                    <td data-label="Автор"><a href="{{ route('users::card', $document->user_id) }}" class="table-link">{{ $document->user_full_name }}</a></td>
                                    <td data-label="Версия" class="text-center">{{ $document->version }}</td>
                                    <td data-label="Статус" class="text-center">{{ $document->com_offer_status[$document->status] }}</td>
                                    <td data-label="" class="td-actions text-right actions">
                                        @if ($document->file_name)
                                            <a target="_blank" href="{{ asset('storage/docs/commercial_offers/' . $document->file_name) }}" rel="tooltip" class="btn-default btn-link btn-xs" data-original-title="Просмотр">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                    <p class="text-center">Документы не найдены</p>
                @endif
                <!-- <div class="col-md-12">
                    <div class="right-edge">
                        <div class="page-container">
                            <button class="btn btn-sm show-all">
                                Показать все
                            </button>
                        </div>
                    </div>
                </div> -->
            </div>
        </div>
    </div>
</div>
