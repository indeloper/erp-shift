@foreach($projects_com_offer as $project)
<div class="card">
    <div class="card-header">
      <h4 class="card-title">
          <a data-target="#collapse_project_{{ $project->id }}" href="#" data-toggle="collapse">
              Проект {{ $project->name }}
              <b class="caret"></b>
          </a>
      </h4>
    </div>
    <div id="collapse_project_{{ $project->id }}" class="card-collapse collapse">
        <div class="card-body card-body-table">
            <div class="card strpied-tabled-with-hover">
                <div class="fixed-table-toolbar toolbar-for-btn">
                    <h6 class="mb-10">Работы</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Наименование работы</th>
                                <th class="text-center">Ед. измерения</th>
                                <th class="text-center">Количество</th>
                                <th class="text-center">Стоимость за ед., руб</th>
                                <th class="text-center">Общая стоимость, руб</th>
                                <th class="text-right">Срок производства, дней</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($work_volume_works as $work)
                            <tr>
                                <td data-label="Наименование работы">
                                    {{ $work->manual->name }}
                                    @if($work->materials->count() and $work->manual->show_materials)
                                     (@foreach($work->materials as $material) {{ $material->name }} @endforeach)
                                    @endif
                                </td>
                                <td data-label="Ед. измерения" class="text-center">{{ $work->manual->unit }}</td>
                                <td data-label="Количество" class="text-center">{{ $work->count }}</td>
                                <td data-label="Стоимость за ед., руб" class="text-center">{{ $work->price_per_one }}</td>
                                <td data-label="Общая стоимость, руб" class="text-center">{{ $work->result_price }}</td>
                                <td data-label="Срок производства, дней" class="text-center">{{ $work->term }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="card strpied-tabled-with-hover">
            <div class="fixed-table-toolbar toolbar-for-btn">
                <h6 class="mb-10">Документация</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Название документа</th>
                            <th class="text-center">Дата загрузки</th>
                            <th class="text-right">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td data-label="Название документа"><a href="">Договор на оказание услуг №12</a></td>
                            <td data-label="Дата загрузки" class="text-center">12.04.2019 12:00</td>
                            <td data-label="" class="text-right actions">
                                <button rel="tooltip" title="" class="btn btn-danger btn-link btn-xs padding-actions mn-0 " data-original-title="Удалить">
                                    <i class="fa fa-times"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td data-label="Название документа"><a href="">Договор на оказание услуг №10</a></td>
                            <td data-label="Дата загрузки" class="text-center">10.04.2019 10:18</td>
                            <td data-label="" class="text-right actions">
                                <button rel="tooltip" title="" class="btn btn-danger btn-link btn-xs padding-actions mn-0" data-original-title="Удалить">
                                    <i class="fa fa-times"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-md-12">
                <div class="right-edge">
                    <div class="page-container">
                        <a class="btn btn-sm show-all" href="{{ route('projects::card', $work->project_id) }}">
                            Перейти на страницу проекта
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach
