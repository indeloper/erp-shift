<div class="card">
    <div class="card-header">
        <h4 class="card-title">
            <a data-target="#collapseFour" href="#" data-toggle="collapse">
                Контакты
                <b class="caret"></b>
            </a>
        </h4>
    </div>
    <div id="collapseFour" class="card-collapse collapse @if(session('contacts')) show @endif">
        <div class="card-body card-body-table">
            <div class="card strpied-tabled-with-hover">
                @can('contractors_contacts')
                <div class="fixed-table-toolbar toolbar-for-btn">
                    <div class="pull-right">
                        <button class="btn btn-round btn-outline btn-sm add-btn" data-toggle="modal" data-target="#add-contact">
                            <i class="glyphicon fa fa-plus"></i>
                            Добавить
                        </button>
                    </div>
                </div>
                @endcan
                @if(!$contacts->isEmpty())
                    <div class="table-responsive">
                        <table class="table table-hover mobile-table">
                            <thead>
                                <tr>
                                    <th>ФИО</th>
                                    <th>Должность</th>
                                    <th>Контактный номер</th>
                                    <th>email</th>
                                    @can('contractors_contacts')
                                    <th class="text-right">Действия</th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($contacts as $contact)
                                    <tr>
                                        <td data-label="ФИО" data-target="#collapse{{$contact->id}}" data-toggle="collapse" class="collapsed tr-pointer" aria-expanded="false">
                                            {{ $contact->last_name }} {{ $contact->first_name }} {{ $contact->patronymic }}
                                        </td>
                                        <td data-label="Должность" data-target="#collapse{{$contact->id}}" data-toggle="collapse" class="collapsed tr-pointer" aria-expanded="false">
                                            {{ $contact->position }}
                                        </td>
                                        <td data-label="Контактный номер">
                                            @if ($contact->phones->where('is_main', 1)->count() > 0)
                                            {{ $contact->phones->where('is_main', 1)->pluck('name')->first() . ': ' . $contact->phones->where('is_main', 1)->pluck('phone_number')->first() . ' ' . $contact->phones->where('is_main', 1)->pluck('dop_phone')->first() }}
                                            @endif
                                        </td>
                                        <td data-label="E-mail">{{ $contact->email }}</td>
                                        @can('contractors_contacts')
                                        <td data-label="" class="text-right actions">
                                            <button rel="tooltip" onClick="edit_contact({{ $contact }})"  data-toggle="modal" data-target="#edit-contact" class="btn-success btn-link btn-xs" data-original-title="Редактировать">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            <a href="#" rel="tooltip" data-original-title="Удалить" class="btn btn-link btn-danger btn-space remove-contact-mn" contact_id="{{ $contact->id }}"><i class="fa fa-times"></i></a>
                                        </td>
                                        @endcan
                                    </tr>
                                    <tr id="collapse{{$contact->id}}" class="contact-note card-collapse collapse" style>
                                        <td colspan="2" style="vertical-align: top;">
                                            <div class="comment-container">
                                               Общее примечание: {{ $contact->note }}
                                            </div>
                                        </td>
                                        <td colspan="2">
                                            @foreach($contact->phones as $phone)
                                                @if (!$phone->is_main)
                                                <p class="p-info-card">
                                                    {{ $phone->name . ': ' . $phone->phone_number . ' ' . $phone->dop_phone }}
                                                </p>
                                                @endif
                                            @endforeach
                                        </td>
                                        <td colspan="2">
                                            @foreach($contact->projects as $project)
                                            <a data-toggle="collapse" href="#comment{{ $project->id }}" class="table-link">
                                                {{ $project->name }} </a>
                                            <div class="comment-container collapse"  id="comment{{ $project->id }}">
                                                <p class="comment">
                                                    {{ $project->note }}
                                                </p>
                                            </div><br>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-center">В этом разделе пока нет ни одного контакта</p>
                @endif
            </div>
        </div>
    </div>
</div>
