<div class="row">
    <div class="col-xl-10 mr-auto ml-auto pd-0-min">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 text-left">
                        <div class="row" style="margin-bottom:15px">
                            <div class="col-sm-8">
                                <h5 style="font-size:19px" class="mobile-title">
                                    {{ $operation->type_names[$operation->type] }} материала
{{--                                    <a href="#" rel="tooltip" data-original-title="История операции" class="btn-view btn btn-xs btn-link mn-0 btn-space" target="_blank">--}}
{{--                                        <i class="fa fa-history"></i>--}}
{{--                                    </a>--}}
                                </h5>
                            </div>
                            <div class="col-sm-4 status-align
                                        @if($operation->status == 1) status-work
                                        @elseif($operation->status == 2) status-awaiting
                                        @elseif($operation->status == 3) status-confirm
                                        @elseif($operation->status == 4) status-conflict
                                        @elseif($operation->status == 5) status-draft
                                        @elseif($operation->status == 6) status-plan
                                        @endif">
                                <label class="operation-status">
                                    {{ $operation->status_name }}
                                </label>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-3 col-md-2 operation-label">Дата операции</label>
                            <div class="col-sm-9 col-md-10 mnt-5">
                                @if($operation->planned_date_from and $operation->planned_date_to)
                                    <span>{{ weekdayDate($operation->planned_date_from) . ' - ' . weekdayDate($operation->planned_date_to) }}</span> <br>
                                @elseif ($operation->planned_date_from or $operation->planned_date_to)
                                    <span>{{ weekdayDate($operation->planned_date_from ?? "") . ' ' . weekdayDate($operation->planned_date_to ?? "") }}</span> <br>
                                @endif
                                @if($operation->actual_date_from and $operation->actual_date_to)
                                    <span  style="color:#0000c2;">{{ weekdayDate($operation->actual_date_from) . ' - ' . weekdayDate($operation->actual_date_to) }}</span> <br>
                                @elseif ($operation->actual_date_from or $operation->actual_date_to)
                                    <span  style="color:#0000c2;">{{ weekdayDate($operation->actual_date_from ?? "") . ' ' . weekdayDate($operation->actual_date_to ?? "") }}</span> <br>
                                @endif
                            </div>
                        </div>
                        @if($operation->reason)
                        <div class="row">
                            <label class="col-sm-3 col-md-2 operation-label">Основание </label>
                            <div class="col-sm-9 col-md-10 mnt-5">
                                <span>{{ $operation->reason }}</span>
                            </div>
                        </div>
                        @endif
                        @if($operation->type == 1)
                        <div class="row">
                            <label class="col-sm-3 col-md-2 operation-label">Поставщик</label>
                            <div class="col-sm-9 col-md-10 mnt-5">
                                <a class="table-link">{{ $operation->supplier->short_name?? '' }}</a>
                            </div>
                        </div>
                        @endif
                        @if($operation->type == 4)
                            <div class="row">
                                <label class="col-sm-3 col-md-2 operation-label">Ответственные сотрудники</label>
                                <div class="col-sm-9 col-md-10 mnt-5">
                                @foreach($operation->responsible_users->sortBy('type') as $relation)
                                    @if($relation->type == 1)
                                        <a href="{{ route('users::card', $relation->user->id) }}" class="table-link">{{ $relation->user->full_name?? '' }}</a>
                                        <span style="font-size: 18px;">→</span>
                                    @elseif($relation->type == 2)
                                        <a href="{{ route('users::card', $relation->user->id) }}" class="table-link">{{ $relation->user->full_name?? '' }}</a>
                                    @endif
                                @endforeach
                                </div>
                            </div>
                        @else
                            @if($operation->responsible_user)
                                <div class="row">
                                    <label class="col-sm-3 col-md-2 operation-label">Ответственный сотрудник</label>
                                    <div class="col-sm-9 col-md-10 mnt-5">
                                        <a href="{{ route('users::card', $operation->responsible_user->user->id) }}" class="table-link">{{ $operation->responsible_user->user->full_name?? '' }}</a>
                                    </div>
                                </div>
                            @endif
                        @endif
                        <div class="row">
                            <label class="col-sm-3 col-md-2 operation-label">Объект</label>
                            <div class="col-sm-9 col-md-10 mnt-5">
                                <a class="table-link">{{ $operation->object_from ? ($operation->object_from->short_name ? $operation->object_from->short_name : ($operation->object_from->name ?? '')) : '' }}<span style="font-size: 18px;">→</span>{{ $operation->object_to ? ($operation->object_to->short_name ? $operation->object_to->short_name : ($operation->object_to->name ?? '')) : '' }}</a>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-3 col-md-2 operation-label">Адрес </label>
                            <div class="col-sm-9 col-md-10 mnt-5">
                                <a class="table-link">{{ $operation->object_from->address ?? '' }}<span style="font-size: 18px;">→</span>{{ $operation->object_to->address ?? '' }}</a>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-3 col-md-2 operation-label">Автор </label>
                            <div class="col-sm-9 col-md-10 mnt-5">
                                <a href="{{ route('users::card', $operation->author_id) }}" class="table-link">{{ $operation->author->full_name }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
