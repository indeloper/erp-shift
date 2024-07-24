@extends('layouts.app')

@section('title', 'Коммерческие предложения')

@section('url', route('commercial_offers::index'))

@section('css_top')
    @vite('resources/css/projects.css')
    <style>
        /* balloon.css tooltip styles */
        :root {
            --balloon-color: white;
            --balloon-font-size: 14px;
        }

        [aria-label][data-balloon-pos]::after {
            color: #000;
            border: 1px solid black;
        }

        [aria-label][data-balloon-pos][data-balloon-pos="left"]::before {
            border-left-color: black;
        }

        [aria-label][data-balloon-pos][data-balloon-pos="up"]::before {
            border-top-color: black;
        }
        /* end balloon.css tooltip styles */

        @media (min-width: 4000px)  {
            .tooltip {
                left:65px!important;
            }
        }

        @media (min-width: 3600px) and (max-width: 4000px)  {
            .tooltip {
                left:45px!important;
            }
        }

        @media (min-width: 1000px) {
            .commerce-table-responsive {
                overflow-x: auto;
                overflow-y: hidden;
            }
        }

    </style>
@endsection

@section('content')
<div class="row search-documents">
    <div class="col-md-12">
        <div class="card" style="border:1px solid rgba(0,0,0,.125);">
            <div class="materials-container">
                <div class="row">
                    <div class="col-md-12">
                        <h6 class="mb-20" style="margin-top:5px">Фильтры</h6>
                    </div>
                    <div class="col-md-5">
                        <label>Артибут</label>
                        <div class="form-group">
                            <select id="attr" class="selectpicker" data-title="Выберите атрибут" data-style="btn-default btn-outline" data-menu-style="dropdown-blue">
                                <option value="commercial_offers.id">№</option>
                                <option value="commercial_offers.name">Название</option>
                                <option value="commercial_offers.updated_at">Дата обновления</option>
                                <option value="projects.name">Проект</option>
                                <option value="contractors.short_name">Контрагент</option>
                                <option value="project_objects.address">Адрес</option>
                                <option value="commercial_offers.status">Статус</option>
                                <option value="projects.entity">Юр. Лицо</option>
                                <option value="material">Материал</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 mt-10__mobile">
                        <label for="count">Значение</label>
                        <input id="mobileSearch" type="text" name="count" placeholder="Введите значение" class="form-control" required style="margin-top:4px">
                        <div class="row date_update" style="padding-top:4px; display: none">
                            <div class="col-sm-6">
                                <input id="from" type="text" class="form-control datepicker" placeholder="С даты">
                            </div>
                            <div class="col-sm-6 mt-10__mobile">
                                <input id="to" type="text" class="form-control datepicker" placeholder="По дату">
                            </div>
                        </div>
                        <div class="row material" style="padding-top:4px; display: none">
                            <div class="col-sm-12 mt-10__mobile">
                                <select id="material" class="selectpicker" data-title="Выберите атрибут" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" style="width: 100%;">
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 text-center--mobile" style="margin:30px 10px 20px 0">
                        <button id="addBadge" type="button" name="" class="btn btn-info btn-wd">
                            Добавить
                        </button>
                    </div>
                </div>
                <div class="row d-none filter-title">
                    <div class="col-md-12" style="margin: 10px 0 10px 0">
                        <h6>Выбранные фильтры</h6>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-9">
                        <div class="bootstrap-tagsinput" style="margin-top:5px">
                            <div id="parameters"></div>
                        </div>
                    </div>
                    <div class="col-md-3 text-right mnt-20--mobile text-center--mobile">
                        <button id="clearAll" type="button" class="btn btn-sm show-all d-none">
                            Снять фильтры
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                @if(!$com_offers->isEmpty())
                    <div id="contractsTable" class="table-responsive commerce-table-responsive {{ $com_offers->count() ? '' : 'd-none' }}">
                        <table class="table table-hover search-table mobile-table">
                            <thead>
                            <tr>
                                <th class="text-left">
                                    №
                                </th>
                                <th>
                                    Название
                                </th>
                                <th>
                                    Дата обновления
                                </th>
                                <th>
                                    Проект
                                </th>
                                <th>
                                    Контрагент
                                </th>
                                <th>
                                    Адрес
                                </th>
                                <th>Автор</th>
                                <th class="text-center">Версия</th>
                                <th class="text-center">
                                    Статус
                                </th>
                                <th>
                                    Юр. Лицо
                                </th>
                                <th class="text-right">Действия</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($com_offers as $document)
                                <tr style="cursor:default" class="@if ($document->isNeedToBeColored()) row-important @endif header">
                                    <td data-label="№">{{ $document->id }}</td>
                                    <td data-label="Название">{{ 'Коммерческое предложение (' . $document->option . ')' }}</td>
                                    <td data-label="Дата обновления" class="prerendered-date" data-docid="{{$document->id}}">{{ $document->updated_at }}</td>
                                    <td data-label="Проект">{{ $document->project_name }}</td>
                                    <td data-label="Контрагент"><a href="{{ route('contractors::card', $document->contractor_id) }}" class="table-link">{{ $document->contractor_name }}</a></td>
                                    <td data-label="Адрес"><a href="{{ route('contractors::card', $document->contractor_id) }}" class="table-link">{{ $document->address }}</a></td>
                                    <td data-label="Автор"><a href="{{ route('users::card', $document->user_id) }}" class="table-link">{{ $document->user_full_name }}</a></td>
                                    <td data-label="Версия" class="text-center">{{ $document->version }}</td>
                                    <td data-label="Статус" class="text-center">
                                        <span
                                            class="pd-0 task-info__link"
                                            style="color: inherit; font-weight: inherit;"
                                            data-balloon-pos="up"
                                            data-balloon-length="medium"
                                            aria-label="{{ $document->status_description }}">
                                            {{ $document->com_offer_status[$document->status] }}
                                        </span>
                                    </td>
                                    <td data-label="Юр. Лицо">{{ $entities[$document->project_entity] }}</td>
                                    <td data-label="" class="td-actions text-right actions">
                                    @if($document->status != 3)
                                        <a href="{{ route('projects::commercial_offer' . (($document->is_tongue) ? '::card_tongue' : '::card_pile'), [$document->project_id, $document->id]) }}" rel="tooltip" class="btn-link btn-primary btn btn-xs btn icon-margin" data-original-title="Карта">
                                            <i class="fa fa-share-square-o"></i>
                                        </a>
                                    @endif
                                    @if ($document->file_name)
                                        <a target="_blank" href="{{ asset('storage/docs/commercial_offers/' . $document->file_name) }}" rel="tooltip" class="btn-info btn-link btn-xs btn" data-original-title="Просмотр">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
                <div id="nothingTip" class="col-md-12 text-center {{ !$com_offers->count() ? '' : 'd-none' }}" style="margin-bottom: 15px">
                    Не нашлось ни одного коммерческого предложения.
                </div>
                <div class="col-md-12 pages" style="padding:0; margin-top:20px; margin-left:-2px">
                    <div class="right-edge fix-pagination">
                        <div class="page-container">
                            @if (key_exists('search', $old_request))
                                {{ $com_offers->appends(['search' => $old_request['search'], 'values' => $old_request['values'], 'parameters' => $old_request['parameters']])->links() }}
                            @else
                                {{ $com_offers->links() }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="attrSearch" class="d-none" method="get" action="{{ route('commercial_offers::index') }}">
    <button type="submit" disabled style="display: none" aria-hidden="true"></button>
    <input id="searchInput" type="hidden" name="search" required>
    <input id="valuesInput" type="hidden" name="values" required>
    <input id="parametersInput" type="hidden" name="parameters" required>
</form>

@endsection

@section('js_footer')
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <script>
        var request = {};
        var entities = {!! json_encode($entities) !!};
        request.search = [];
        request.values = [];
        request.parameters = [];

        $('#from').datetimepicker({
            format: 'DD.MM.YYYY',
            locale: 'ru',
            icons: {
                time: "fa fa-clock-o",
                date: "fa fa-calendar",
                up: "fa fa-chevron-up",
                down: "fa fa-chevron-down",
                previous: 'fa fa-chevron-left',
                next: 'fa fa-chevron-right',
                today: 'fa fa-screenshot',
                clear: 'fa fa-trash',
                close: 'fa fa-remove'
            },
            maxDate: moment()
        });

        $('#to').datetimepicker({
            format: 'DD.MM.YYYY',
            locale: 'ru',
            icons: {
                time: "fa fa-clock-o",
                date: "fa fa-calendar",
                up: "fa fa-chevron-up",
                down: "fa fa-chevron-down",
                previous: 'fa fa-chevron-left',
                next: 'fa fa-chevron-right',
                today: 'fa fa-screenshot',
                clear: 'fa fa-trash',
                close: 'fa fa-remove'
            },
            maxDate: moment()
        });

        $('#from').val('').data("DateTimePicker").clear();
        $('#to').val('').data("DateTimePicker").clear();

        $('#attr').change(function(){
            val = $('#attr').val();
            if(val == "commercial_offers.updated_at"){
                $('.date_update').show();
                $('#mobileSearch').hide();
            } else {
                $('.date_update').hide();
                $('#mobileSearch').show();
            }
        });

        $('#material').select2({
            ajax: {
                url: '/projects/ajax/get-material?all=true',
                dataType: 'json',
                delay: 250,
            }
        });

        function checkDates()
        {
            var from = moment($('#from').val(),"DD.MM.YYYY");
            var to = moment($('#to').val(),"DD.MM.YYYY");

            if (from < to) return 1;
            else return 0;
        }
        var mat_names = JSON.parse('{!!  json_encode($material_names) !!}');

        function pushAndPrint(value, search, parameter) {
            if (value.length != 0 && parameter !== 'Выберите атрибут') {
                request.search.push(search);
                request.values.push(value);
                request.parameters.push(parameter);
                var search_value = value;
                var mat_name = mat_names[value];
                if (mat_name) {
                    value = mat_name;
                }
                result = parameter + ': ' + value;

                var button = "<span class=\"badge badge-azure\">" + result +
                    "<span data-role=\"remove\" class=\"badge-remove-link\" parameter=\"" + parameter + "\"" +
                    "search=\"" + search + "\" search_value=\"" + search_value + "\" value=\"" + value + "\">" +
                    "</span></span>";

                $('#parameters').append(button);

                return true;
            }

            return false;
        }

        @if(count(array_filter($old_request)) > 2)
            var old_values = {!! json_encode(explode(',', $old_request['values'])) !!};
            var old_search = {!! json_encode(explode(',', $old_request['search'])) !!};
            var old_parameters = {!! json_encode(explode(',', $old_request['parameters'])) !!};

            $.each(old_values, function (key, value) {
                pushAndPrint(value, old_search[key], old_parameters[key])
            });
            $('.filter-title').removeClass('d-none');
            $('#clearAll').removeClass('d-none');
        @endif

        $(document).ready(function(){
            $('#addBadge').click(function (elem) {
                var value = $('#mobileSearch').val() != '' ? $('#mobileSearch').val() : $('#material').val();
                var search = $('#attr').val();
                var parameter = $.trim($('#attr option:selected').text());
                var from = $('#from').val();
                var to = $('#to').val();
                var datetimepicker = from + '|' + to;

                if (_.compact(datetimepicker.split('|')).length < 2 && (value == '' || !value)) {
                    // empty dates and empty values
                    $('#from').css("display")=="none";
                    $('#to').css("display")=="none";
                    $('#from').val('').data("DateTimePicker").clear();
                    $('#to').val('').data("DateTimePicker").clear();
                } else if (value && parameter !== 'Выберите атрибут') {
                    // we have value without dates
                    $('.filter-title').removeClass('d-none');

                    pushAndPrint(value, search, parameter) ? alterSubmit() : '';
                } else if (_.compact(datetimepicker.split('|')).length === 2) {
                    // we have dates without value
                    if (checkDates()) {
                        value = datetimepicker;

                        $('#from').val('').data("DateTimePicker").clear();
                        $('#to').val('').data("DateTimePicker").clear();

                        $('.filter-title').removeClass('d-none');
                        pushAndPrint(value, search, parameter) ? alterSubmit() : '';
                    } else {
                        swal({
                            title: "Внимание",
                            text: "Укажите верный промежуток времени",
                            type: 'warning',
                            timer: 3000
                        })
                    }
                }
            });
        });

        $(document).on('click', '.badge-remove-link', function() {
            var deleteSearch = $(this).attr('search');
            var deleteValue = $(this).attr('search_value');
            var deleteParameter = $(this).attr('parameter');

            request.search.splice($.inArray(deleteSearch, request.search),1);
            request.values.splice($.inArray(deleteValue, request.values),1);
            request.parameters.splice($.inArray(deleteParameter, request.parameters),1);

            $(this).closest('.badge').remove();

            alterSubmit();
        });

        $('#clearAll').click(function () {
            $('.filter-title').addClass('d-none');
            $('#clearAll').addClass('d-none');
            $('#parameters').empty();
            request.values = [];
            request.search = [];
            request.parameters = [];

            alterSubmit();
        });

        function alterSubmit() {
            $('#searchInput').val(request.search);
            $('#valuesInput').val(request.values);
            $('#parametersInput').val(request.parameters);
            $('#attrSearch').submit();
        }
    </script>
    <script type="text/javascript">
        function pagination (){
            if(screen.width<=769){
                if($('.pagination .page-item').length > 7){
                    $('.pagination .dot').remove();
                    first = $('.pagination .page-item:first-child');
                    last = $('.pagination .page-item:last-child');
                    active = $('.pagination .page-item.active');

                    $('.pagination .page-item').addClass('d-none');
                    $(first).removeClass('d-none');
                    $(last).removeClass('d-none');
                    $(active).removeClass('d-none');
                    $(first).next().removeClass('d-none');
                    $(last).prev().removeClass('d-none');

                    $(active).next().removeClass('d-none');
                    $(active).prev().removeClass('d-none');

                    if($(first).nextAll(':lt(2)').hasClass('d-none')){
                        $('<span class="dot" style="padding-top:5px">...</span>').insertBefore($(active).prev());
                    }

                    if($(last).prevAll(':lt(2)').hasClass('d-none')){
                        $('<span class="dot" style="padding-top:5px">...</span>').insertAfter($(active).next());
                    }
                }
                return true;
            } else {
                return false;
            }
        };

        $(document).ready(function() {
            if ($(window).width() <= 769) {
                pagination();
            } else {
                pagination() == false;
                $('[data-balloon-pos]').removeAttr('data-balloon-length');
                $('[data-balloon-pos]').attr('data-balloon-pos', 'left');
            }
        });

        $(window).resize(function() {
            if ($(window).width() <= 769) {
                if ($('.pagination .page-item').length > 7) {
                    pagination();
                }
                $('[data-balloon-pos]').attr('data-balloon-length', 'medium');
                $('[data-balloon-pos]').attr('data-balloon-pos', 'up');
            } else {
                $('.pagination .page-item').removeClass('d-none');
                $('.pagination .dot').remove();
                $('[data-balloon-pos]').removeAttr('data-balloon-length');
                $('[data-balloon-pos]').attr('data-balloon-pos', 'left');
            }
        });
    </script>
    <script>
        vm = new Vue({
            el: '#contractsTable',
            data: {
                com_offers: {!!json_encode($com_offers)!!}
            },
            mounted() {
                const that = this;
                $('.prerendered-date').each(function() {
                    const docId = $(this).data('docid');
                    const date = that.com_offers.data[that.com_offers.data.map(el => el.id).indexOf(docId)].updated_at;
                    const content = that.isValidDate(date, 'DD.MM.YYYY HH:mm:ss') ? that.weekdayDate(date, 'DD.MM.YYYY HH:mm:ss', 'DD.MM.YYYY dd HH:mm:ss') : '-';
                    const innerSpan = $('<span/>', {
                        'class': that.isWeekendDay(date, 'DD.MM.YYYY HH:mm:ss') ? 'weekend-day' : ''
                    });
                    innerSpan.text(content);
                    $(this).html(innerSpan);
                })
            },
            methods: {
                isWeekendDay(date, format) {
                    return [5, 6].indexOf(moment(date, format).weekday()) !== -1;
                },
                isValidDate(date, format) {
                    return moment(date, format).isValid();
                },
                weekdayDate(date, inputFormat, outputFormat) {
                    return moment(date, inputFormat).format(outputFormat ? outputFormat : 'DD.MM.YYYY dd');
                },
            }
        });
    </script>
@endsection
