<div class="card">
    <div class="card-header">
        <h4 class="card-title">
            <a data-target="#collapseFive" href="#" data-toggle="collapse">
                Проекты
                <b class="caret"></b>
            </a>
        </h4>
    </div>
    <div id="collapseFive" class="card-collapse collapse">
        <div class="card-body card-body-table">
            <div class="card strpied-tabled-with-hover">
                <div class="fixed-table-toolbar toolbar-for-btn">
                    @can('projects_create')
                        <div class="pull-right">
                            <a class="btn btn-success btn-round btn-outline btn-sm add-btn" href="{{ route('projects::create', ['contractor_id' => $contractor->id]) }}">
                                <i class="glyphicon fa fa-plus"></i>
                                Добавить
                            </a>
                        </div>
                    @endcan
                </div>
                @if(!$projects->isEmpty())
                <div class="table-responsive">
                    <table class="table table-hover mobile-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Проект</th>
                                <th>Тип</th>
                                <th>Автор</th>
                                <th>Объект</th>
                                <th>Контрагент</th>
                                <th>ИНН</th>
                                <th>Статус проекта</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($projects as $project)
                            <tr class="tr-pointer" data-href="{{ route('projects::card', $project->id) }}">
                                <td data-label="ID">{{ $project->id }}</td>

                                <td data-label="Проект">
                                    <a href="{{ route('projects::card', $project->id) }}" class="table-link">
                                        {{ $project->name }}
                                    </a>
                                </td>
                                <td data-label="Тип">Главный</td>
                                <td data-label="Автор">{{ $project->author->full_name ?? 'Не найден' }}</td>
                                <td data-label="Объект">{{ $project->object->name_tag }}</td>
                                <td data-label="Контрагент">{{ $project->contractor_name }}</td>
                                <td data-label="ИНН">{{ $project->contractor_inn }}</td>
                                <td data-label="Статус проекта">{{ $project->project_status[$project->status] }}</td>
                            </tr>
                            @endforeach
                            @foreach($contractor->additions_projects as $project)
                            <tr class="tr-pointer" data-href="{{ route('projects::card', $project->id) }}">
                                <td data-label="ID">{{ $project->id }}</td>

                                <td data-label="Проект">
                                    <a href="{{ route('projects::card', $project->id) }}" class="table-link">
                                        {{ $project->name }}
                                    </a>
                                </td>
                                <td data-label="Тип">Второстепенный</td>
                                <td data-label="Автор">{{ $project->author->full_name ?? 'Не найден' }}</td>
                                <td data-label="Контрагент">{{ $project->contractor_name }}</td>
                                <td data-label="ИНН">{{ $project->contractor_inn }}</td>
                                <td data-label="Статус проекта">{{ $project->project_status[$project->status] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- <div class="col-md-12">
                    <div class="right-edge">
                        <div class="page-container">
                            <button class="btn btn-sm show-all">
                                Показать все
                            </button>
                        </div>
                    </div>
                </div> -->
                @else
                    <p class="text-center">В этом разделе пока нет ни одного проекта</p>
                @endif
            </div>
        </div>
    </div>
</div>
