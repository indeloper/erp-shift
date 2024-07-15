<div class="card">
    <div class="card-header">
        <h4 class="card-title">
            <a data-target="#collapseSeven" href="#" data-toggle="collapse">
                События
                <b class="caret"></b>
            </a>
        </h4>
    </div>
    <div id="collapseSeven" class="card-collapse collapse">
        <div class="card-body card-body-table">
            <div class="card strpied-tabled-with-hover">
                @if(Gate::check('tasks_default_myself') || Gate::check('tasks_default_others'))
                <div class="fixed-table-toolbar toolbar-for-btn">
                    <div class="pull-right">
                        <button class="btn btn-round btn-outline btn-sm add-btn" data-toggle="modal" data-target="#add-new-task">
                            <i class="glyphicon fa fa-plus"></i>
                            Добавить
                        </button>
                    </div>
                </div>
                @endif
                <div class="card table-with-links">
                    @if(!$solved_tasks->isEmpty())
                    <div class="table-responsive">
                        <table class="table  table-hover mobile-table">
                            <thead>
                                <tr>
                                    <th>Дата создания</th>
                                    <th>Дата исполнения</th>
                                    <th>Событие</th>
                                    @if($solved_tasks->groupBy('project_id')->count() > 1)
                                    <th>Проект</th>
                                    @endif
                                    <th>Исполнитель</th>
                                    <th>Автор</th>
                                    <th style="display:none"></th>
                                </tr>
                            </thead>
                            @include('sections.history_for_tasks')
                        </table>
                    </div>
                    @else
                        <p class="text-center">События не найдены</p>
                    @endif
                </div>

                @if(!$solved_tasks->isEmpty())
                <div class="col-md-12">
                    <div class="right-edge">
                        <div class="page-container">
                            <a class="btn btn-sm show-all" href="{{ route('contractors::tasks', $contractor->id) }}">
                                Показать все
                            </a>
                        </div>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>
</div>
