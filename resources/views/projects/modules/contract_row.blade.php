<tr @if($contract->version < $contract_map[$contract->contract_id]->count() and $contract_map[$contract->contract_id]->count() > 1) id="" class="collapseContract{{ $contract->contract_id }} contact-note card-collapse collapse activity-detailed" @endif>
    <td data-label="Внешний №">
        <div class="d-flex">
            @if(isset($contract->main_contract_id))
                <div style="height: 12px" class="d-xs-none d-md-block">
                    <div style="width: 30px; height: 100%; margin-right: 10px" class="flex-column">
                        <div style="border-bottom: 2px solid grey; border-left: 2px solid grey; width: 100%; height: 100%"></div>
                        <div style="@if($contract->id !== $contracts->where('main_contract_id', $parent->id)->last()->id) border-left: 2px solid grey; @endif width: 100%; height: 100%"></div>
                    </div>
                </div>
        @endif
            <div>
                {{ ($contract->foreign_id)? $contract->foreign_id : 'Отсутствует' }}
            </div>
        </div>
        @if(isset($contract->main_contract_id))
            <br class="d-md-none">
                <a class="table-link d-md-none" href="{{ route('projects::contract::card', [$contract->project_id, $contract->main_contract_id]) }}">
                    <u>Осн. договор: {{ $contract->main_contract->name_for_humans }} </u>
                </a>
        @endif
    </td>
    <td data-label="Тип" @if($contract->version === $contract_map[$contract->contract_id]->count()) data-target=".collapseContract{{ $contract->contract_id}}" data-toggle="collapse" class="collapsed tr-pointer" aria-expanded="false" @endif>
        {{ $contract->name }}
    </td>
    <td data-label="Контрагент">
        @if($contract->subcontractor)
            <a class="table-link" href="{{ route('contractors::card', $contract->subcontractor->id) }}">{{ $contract->subcontractor->short_name }}</a>
        @else
            <a class="table-link" href="{{ route('contractors::card', $contractor->id) }}">{{ $contractor->short_name }}</a>
        @endif
    </td>
    <td data-label="Дата добавления" class="prerendered-date-time">{{$contract->updated_at}}</td>
    <td data-label="Версия" class="text-center">
        {{ $contract->version }}
    </td>
    <td data-label="Статус">{{ $contract->contract_status[$contract->status] }}</td>
    <td data-label="Дата КС" class="text-center {{ $contract->status == 6 ? 'font-weight-bold' : ''  }}">{{ $contract->ks_date_and_notify_date_text }}</td>
    <td data-label="Заявки" class="text-center">
        {{ $contract_requests->where('contract_id', $contract->id)->count() }}
    </td>
    <td data-label="" class="text-right actions">
        @if($contract->garant_file_name)
            <a rel="tooltip" target="_blank" href="{{ asset('storage/docs/contracts/' . $contract->garant_file_name) }}" class="btn-info btn-link btn-xs btn padding-actions mn-0   " data-original-title="Просмотр гарантийного письма">
                <i class="fa fa-file-text-o"></i>
            </a>
        @endif
        @if(($contract->status === 2) and in_array(Auth::user()->id, $contract_resp_user_ids))
            <button rel="tooltip"
                    type="button"
                    @if($contract->get_requests->where('status', 1)->count()) disabled title="Сначала нужно ответить на заявки" @endif
                    class="btn btn-info btn-link btn-xs padding-actions mn-0"
                    onclick="approve_contract({{ $contract->id }})"
                    data-original-title="Подтвердить согласование">
                <i class="fa fa-check"></i>
            </button>
        @endif
        @if(($contract->status === 4 or $contract->status === 5) and in_array(Auth::user()->id, $contract_resp_user_ids))
            <button rel="tooltip
                                                        type="button"
            @if($contract->get_requests->where('status', 1)->count()) disabled title="Сначала нужно ответить на заявки" @endif
            class="btn btn-success btn-link btn-xs padding-actions mn-0"
            data-toggle="modal"
            data-target="#signing-contract-{{ $contract->id }}"
            data-original-title="Подтвердить подписание">
            <i class="fa fa-check"></i>
            </button>
        @endif
        @if($contract->status === 6)
            <a rel="tooltip" target="_blank" href="{{ asset('storage/docs/contracts/' . $contract->final_file_name) }}" class="btn btn-success btn-link btn-xs padding-actions mn-0" data-original-title="Просмотр подписанного договора">
                <i class="fa fa-eye"></i>
            </a>
        @endif
        @if($contract->status > 1 and $contract->status != 6)
            <a rel="tooltip" target="_blank" href="{{ asset('storage/docs/contracts/' . $contract->file_name) }}" class="btn btn-info btn-link btn-xs padding-actions mn-0" data-original-title="Просмотр договора">
                <i class="fa fa-eye"></i>
            </a>
    @endif
    <!-- @if(isset($contract->main_contract_id))
        <a rel="tooltip" href="{{ route('projects::contract::card', [$contract->project_id, $contract->main_contract_id]) }}" class="btn btn-info btn-link btn-xs btn-space mn-0" data-original-title="Просмотр основного договора">
                                                    <i class="fa fa-home"></i>
                                                </a>
                                            @endif -->
        <a href="{{ route('projects::contract::card', [$contract->project_id, $contract->id]) }}" rel="tooltip" class="btn-link btn-xs btn btn-open padding-actions mn-0" data-original-title="Открыть">
            <i class="fa fa-share-square-o"></i>
        </a>
    </td>
</tr>
