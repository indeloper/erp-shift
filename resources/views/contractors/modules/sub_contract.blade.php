@foreach($projects_com_offer as $project)
@if($contracts->where('type', Request::get('type'))->count())
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
        <div class="card strpied-tabled-with-hover">
            <div class="fixed-table-toolbar toolbar-for-btn">
                <h6 class="mb-10">Документация</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mobile-table">
                    <thead>
                        <tr>
                            <th>Название документа</th>
                            <th class="text-center">Дата загрузки</th>
                            <th class="text-right">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($contracts->where('type', Request::get('type')) as $contract)
                        <tr>
                            <td data-label="Название документа">{{ $contract->name_for_humans . ' ('. $contract->contract_status[$contract->status] .')'}}</td>
                            <td data-label="Дата загрузки" class="text-center">{{ $contract->created_at }}</td>
                            <td data-label="" class="text-right actions">
                                @if($contract->garant_file_name)
                                    <a rel="tooltip" href="{{ asset('storage/docs/contracts/' . $contract->garant_file_name) }}" class="btn btn-info btn-link btn-xs padding-actions mn-0" data-original-title="Просмотр гарантийного письма">
                                        <i class="fa fa-file-text-o"></i>
                                    </a>
                                @endif
                                @if(!is_null($contract->file_name) and $contract->status != 6)
                                    <a rel="tooltip" href="{{ asset('storage/docs/contracts/' . $contract->file_name) }}" class="btn btn-info btn-link btn-xs padding-actions mn-0" data-original-title="Просмотр договора">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                @endif
                                @if(!is_null($contract->final_file_name) and $contract->status == 6)
                                    <a rel="tooltip" href="{{ asset('storage/docs/contracts/' . $contract->final_file_name) }}" class="btn btn-success btn-link btn-xs padding-actions mn-0" data-original-title="Просмотр договора">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="col-md-12">
                <div class="right-edge">
                    <div class="page-container">
                        <a class="btn btn-sm show-all" href="{{ route('projects::card', $project->id) }}">
                            Перейти на страницу проекта
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endforeach
