<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Коммерческое предложение</title>
    <link
            rel="apple-touch-icon"
            sizes="76x76"
            href="{{ mix('img/apple-icon.png') }}"
    >
    <link
            rel="icon"
            type="image/ico"
            href="{{ mix('img/favicon.ico') }}"
    >
    <meta
            http-equiv="X-UA-Compatible"
            content="IE=edge,chrome=1"
    />
    <meta
            content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no'
            name='viewport'
    />
    <!-- CSS Files -->
    <link
            href="{{ mix('css/offer.css') }}"
            rel="stylesheet"
    />
    <link
            href="https://fonts.googleapis.com/css?family=Lora|PT+Sans&display=swap"
            rel="stylesheet"
    >
    <style>
      .page-break {
        page-break-before: always;
      }

      li {
        list-style-type: none;
      }

      .prim {
        font-style: italic;
        font-size: {{ 11 * ($rel_size ?? 1) }}px;
        line-height: 1.2;
      }

      .kp-table tbody tr td {
        font-size: {{ 10 * ($rel_size ?? 1) }}px;
        font-family: 'Palatino Linotype', serif;
      }

      @page {
        header: page-header;
        margin-top: 4cm;
      }
      }
    </style>
</head>

<body>
<htmlpageheader name="page-header">
    <table>
        <tr>
            <td style="width:70%; padding-left: 45px;">
                <img
                        src="{{ $company->logo }}"
                        width="250px"
                >
            </td>
            <td style="text-align: right; padding-right: 35px; font-size: 10px">
                {{ $company->legal_address }}
                <br>
                Тел.: {{ $company->phone }}
                <br>
                ОГРН: {{ $company->ogrn }}
                <br>
                ИНН: {{ $company->inn }}
                <br>
                {{ $company->web_site }}
                <br>
                {{ $company->email }}
            </td>
        </tr>
    </table>
</htmlpageheader>
<div class="offer-list clearfix">
    <div class="main-content">
        <div class="list-header">
            <div class="top-header clearfix">

            </div>
            <div class="bottom-header clearfix">
                <div style="width:300px; float:left;">
                    <p class="number">
                        Исх. № {{ $offer->id }}-{{ $offer->version }} от {{ $today }} г.
                    </p>
                </div>
                <div class="contacts">
                    <p>
                        <i>
                            <b>{{ $contractor->short_name }}</b><br>
                            <!--<b>Тел:</b>  __Номер телефона контрагента__<br>
                            <b>E-mail: </b> __e-mail контрагента__ <br>-->
                        </i>
                        @if($contact)
                            <b>{{ $contact->position }}</b><br>
                            <b>{{ $contact->last_name . ' ' . $contact->first_name . (isset($contact->patronymic) ? ' ' . $contact->patronymic : '') }}</b>
                            <br>
                            @if($contact->email)
                                <b>{{ $contact->email }}</b><br>
                            @endif
                            @if($contact->phone_number)
                                <b>{{ 'т. +' . substr($contact->phone_number, 0, 1) . ' ('
                                     . substr($contact->phone_number, 1, 3) . ') ' . substr($contact->phone_number, 4, 3) . '-' . substr($contact->phone_number, 7, 2)
                                     . '-' . substr($contact->phone_number, 9, 2) }}
                                    @if($contact->dop_phone)
                                        {{ 'доб. ' .  $contact->dop_phone}}
                                    @endif
                                </b><br>
                            @endif
                        @endif
                    </p>
                </div>
            </div>
        </div>
        <div class="list-body">
            <div style="margin:10px auto;">
                @if($offer->title)
                    <h6 class="h6-offer">
                        {{ $offer->title }}
                    </h6>
                @else
                    <h6 class="h6-offer">Коммерческое предложение<br> на {{ $work_volume->project_name }}
                        «{{ $object->name }}»<br> по адресу: {{ $object->address }}@if($object->cadastral_number)
                            , на земельном участке с кадастровым номером {{ $object->cadastral_number }}
                        @endif.
                    </h6>
                @endif
            </div>
            <table class="kp-table">
                <thead>
                <tr>
                    <th style="width:20px;">№ п/п</th>
                    <th style="width:370px;">Наименование работ</th>
                    <th style="width: 40px">Е. И.</th>
                    <th style="width: 30px">Кол-<br>во</th>
                    <th style="width: 90px">Цена за ед., руб.</th>
                    <th style="width: 80px">Общая стоимость, руб.</th>
                </tr>
                </thead>
                <tbody>
                @php $work_group_count = 0; $work_count = 1; @endphp
                @foreach($works->groupBy('work_group_id')->sortKeys() as $id => $group)
                    @php $work_group_count++; @endphp

                    <tr>
                        <td
                                colspan="6"
                                class="td-head"
                        >{{ $work_group_count }}. {{ $work_groups[$id]}}</td>
                    </tr>
                    @php $work_count = 1; @endphp
                    @foreach($group as $work)
                        <tr>
                            <td>{{ $work_group_count }}.{{ $work_count++ }}</td>
                            <td style="text-align:left">
                                {{ $work->manual->name }}
                                @if($work->shown_materials->count() and $work->manual->show_materials)
                                    @if($id == 2)
                                        @php $combine_ids = []; @endphp
                                                    (
                                                    @foreach($work->shown_materials->sortByDesc('combine_id') as $key => $material)
                                                    @if($material->combine_id)
                                                        @if(!in_array($material->combine_id, $combine_ids))
                                                            {{ $material->combine_pile() }}
                                                            (
                                                            @foreach($work->shown_materials->where('combine_id' , $material->combine_id) as $key => $item)
                                                                @if($key < ($work->shown_materials->where('combine_id' , $material->combine_id)->count() - 1))
                                                                {{ $item->name . ' + ' }}
                                                                @else
                                                                {{ $item->name }}
                                                                @endif
                                                            @endforeach
                                                            )
                                                            @if($work->shown_materials->where('combine_id', '!=', null)->groupBy('manual_material_id')->count() == 2)
                                                                {{ $work->shown_materials->where('combine_id', '=', null)->groupBy('manual_material_id') ? ',' : ''}}
                                                            @else
                                                                {{ $key != ($work->shown_materials->where('combine_id' , $material->combine_id)->count() - 1) ? ',' : ''}}@endif
                                                        @endif
                                                        @php
                                                            $combine_ids[] = $material->combine_id;
                                                        @endphp
                                                    @else
                                                        @if($key < $work->shown_materials->where('combine_id', null)->count() - 1)
                                                        {{ $material->name . ' ,' }}
                                                        @else
                                                        {{ $material->name }}
                                                        @endif
                                                    @endif
                                                    @endforeach
                                                    )
                                    @else
                                        (
                                        @foreach($work->shown_materials->unique('manual_material_id')->slice(0, -1) as $material)
                                            {{ $material->name . ','}}
                                        @endforeach
                                        {{ $work->shown_materials->unique('manual_material_id')->last()->name }}
                                        )
                                    @endif
                                @endif
                            </td>
                            <td>{{ $work->unit }}</td>
                            <td>{{ number_format($work->count, 3, '.', '') }}</td>
                            <td>{{ number_format($work->price_per_one, 2, ',', ' ') }}</td>
                            <td>{{ number_format($work->result_price, 2, ',', ' ') }}</td>
                        </tr>
                    @endforeach

                    <tr>
                        <td
                                colspan="5"
                                class="td-footer"
                        > {{ ['Итого по шпунтовым работам:',
                                                            'Итого по устройству свайного поля:',
                                                            'Итого по земельным работам:',
                                                            'Итого по монтажу систем крепления:',
                                                            'Итого по дополнительным работам:',
                                                            ][$id - 1] }}</td>
                        <td class="td-total">
                            {{ number_format($group->pluck('result_price')->sum(), 2, ',', ' ') }}
                        </td>
                    </tr>
                @endforeach
                <!-- 2.Материалы для устройства шпунтового ограждения -->

                @foreach($work_groups as $id => $name)
                    @if($materials->where('work_group_id', $id)->first())
                        @if($splits->whereIn('man_mat_id', $materials->where('work_group_id', $id)->pluck('manual_material_id'))->whereIn('type', [1, 3])->count())
                            @php $work_group_count++; @endphp
                            <tr>
                                <td
                                        colspan="6"
                                        class="td-head"
                                >{{ $work_group_count }}. {{ ['Материалы для устройства шпунтового ограждения:',
                                                                'Материалы для устройства свайного поля:',
                                                                'Материалы для земельных работ:',
                                                                'Материалы для монтажа систем крепления:',
                                                                'Материалы для дополнительных работ:',
                                                                ][$id - 1] }}</td>
                            </tr>
                            @if($id != 2)
                                @php $work_count = 1; @endphp
                                @foreach($materials->where('work_group_id', $id) as $material)
                                    @foreach($splits->where('man_mat_id', $material->manual_material_id) as $split)
                                        @if ($split->type == 5)
                                            @continue
                                        @endif
                                        <tr>
                                            @if(in_array($split->type, [1,3,5]))
                                                <td>{{ $work_group_count }}.{{ $work_count++ }}</td>
                                                <td style="text-align:left">
                                                    {{ ['Стоимость материала ','Продажа с обратным выкупом',
                                                    'Стоимость использования '.(($split->type === "3") ? '('.$split->time.' мес.)' : ''),
                                                    'Стоимость использования '.(($split->type === "4") ? '('.$split->time.' мес.) ' : '').'с обеспечительным платежём','Давальческий'][$split->type - 1] }}
                                                    {{ $material->name . ($split->comment ? " ({$split->comment})" : "") }}
                                                    {{ $split->time ? '(' . $split->human_rent_time . ')' : '' }}
                                                    {{ $split->is_used ? '(б/у)' : '(новый)' }}
                                                </td>
                                                <td>{{ $material->unit }}</td>
                                                <td>{{ number_format($split->count, 3, '.', '') }}</td>
                                                <td>{{ number_format((float)$split->price_per_one, 2, ',', ' ') }}</td>
                                                <td>{{ number_format((float)$split->result_price, 2, ',', ' ') }}</td>
                                            @endif
                                        </tr>
                                        @if($split->type == 4)
                                            <tr>
                                                <td>{{ $work_group_count }}.{{ $work_count++ }}</td>
                                                <td style="text-align:left">
                                                    Обеспечительный платеж за
                                                    {{ $split->parent->name }}
                                                </td>
                                                <td>{{ $material->unit }}</td>
                                                <td>{{ number_format($split->count, 3, '.', '') }}</td>
                                                <td>{{ number_format((float)$split->security_price_one, 2, ',', ' ') }}</td>
                                                <td>{{ number_format((float)$split->security_price_result, 2, ',', ' ') }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                @endforeach
                            @else
                                @php $work_count = 0; $result_price_module = 0; $combine_split_ids = []; @endphp
                                @foreach($materials->where('work_group_id', $id)->groupBy('combine_id') as $combine_id => $combine_group)
                                    @if ($combine_id != '')
                                        {{-- if it's a combined pile, make header--}}
                                        @php $work_count++; $combine_count = 0 @endphp
                                        <tr>
                                            <td>{{ $work_group_count }}.{{ $work_count }}</td>
                                            <td style="text-align:left">
                                                Поставка
                                                свай {{ $materials->where('combine_id', $combine_id)->first()->combine_pile() }}
                                            </td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    @endif

                                    @foreach($combine_group as $material)
                                        {{-- iterates through group --}}
                                        @foreach($splits->where('man_mat_id', $material->manual_material_id) as $split)
                                            {{-- find split for current material --}}
                                            @if (in_array(($split->type), [1,3]))
                                                @php ($combine_id != '') ? $combine_count++ :
                                                $work_count++; @endphp{{-- magic with numbers: different for combined pile and simple --}}
                                            @endif
                                            {{-- common display of splits both for combined and simple (differ only on numbering) --}}
                                            @if(in_array($split->type, [1,3]))
                                                @php $split_siblings = 1; @endphp
                                                <tr>
                                                    <td>{{ $work_group_count }}.{{ $work_count .
                                                                        (($combine_id != '') ? ('.' . $combine_count) : '') . ((($splits->where('man_mat_id', $material->manual_material_id)->where('type', 4)->count() > 0) and ($split->type === "3")) ? ('.' . $split_siblings ) : '') }}</td>
                                                    <td style="text-align:left">
                                                        {{ ['Стоимость материала ','Продажа с обратным выкупом',
                                                        'Стоимость использования '.(($split->type === "3") ? '('.$split->time.' мес.)' : ''),
                                                        'Стоимость использования '.(($split->type === "4") ? '('.$split->time.' мес.) ' : '').'с обеспечительным платежём','Давальческий'][$split->type - 1] }}
                                                        {{ $material->name }}
                                                        {{ $split->time ? '(' . $split->human_rent_time . ')' : '' }}
                                                        с доставкой на объект
                                                    </td>
                                                    <td>{{ $material->unit }}</td>
                                                    <td>{{ number_format($split->count, 3, '.', '') }}</td>
                                                    <td>{{ number_format((float)$split->price_per_one, 2, ',', ' ') }}</td>
                                                    <td>{{ number_format((float)$split->result_price, 2, ',', ' ') }}</td>
                                                </tr>
                                            @endif
                                            @if($split->type == 4)
                                                @php $split_siblings++; @endphp
                                                <tr>
                                                    <td>{{ $work_group_count }}
                                                        .{{$work_count . (($combine_id != '') ? ('.' . $combine_count) : '') . '.' . $split_siblings }}</td>
                                                    <td style="text-align:left">
                                                        Обеспечительный платеж за
                                                        {{ $split->parent->name }}
                                                    </td>
                                                    <td>{{ $material->unit }}</td>
                                                    <td>{{ number_format($split->count, 3, '.', '') }}</td>
                                                    <td>{{ number_format((float)$split->security_price_one, 2, ',', ' ') }}</td>
                                                    <td>{{ number_format((float)$split->security_price_result, 2, ',', ' ') }}</td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    @endforeach
                                @endforeach
                            @endif
                            <tr>
                                <td
                                        colspan="5"
                                        class="td-footer"
                                >
                                    {{ ['Итого по материалам для устройства шпунтового ограждения',
                                    'Итого по материалам для устройства свайного поля',
                                    'Итого по материалам для земельных работ',
                                    'Итого по материалам для монтажа систем крепления',
                                    'Итого по материалам для дополнительных работ',
                                        ][$id - 1] }}</td>
                                <td class="td-total">
                                    {{ number_format(array_sum($splits->whereIn('man_mat_id', $materials->where('work_group_id', $id)->pluck('manual_material_id')->unique())->pluck('result_price')->toArray()) + array_sum($splits->where('type', '!=', 2)->whereIn('man_mat_id', $materials->where('work_group_id', $id)->pluck('manual_material_id')->unique())->pluck('security_price_result')->toArray()), 2, ',', ' ') }}
                                </td>
                            </tr>
                        @endif
                    @endif
                @endforeach

                @php $result_cost = array_sum($splits->pluck('result_price')->toArray()) + array_sum($splits->where('type', '!=', 2)->pluck('security_price_result')->toArray()) + $works->pluck('result_price')->sum(); @endphp
                @php $result_security_pay = array_sum($splits->where('type', 4)->pluck('security_price_result')->toArray()); @endphp
                @php $back_price = array_sum($splits->where('type', 2)->pluck('security_price_result')->toArray()); @endphp
                <tr>
                    <td
                            colspan="5"
                            class="td-footer"
                            style="background-color:rgb(150, 150, 150);"
                    >
                        Итого:
                    </td>
                    <td
                            class="td-total"
                            style="background-color:rgb(150, 150, 150)"
                    >{{ number_format($result_cost, 2, ',', ' ') }}</td>
                </tr>
                <!-- 4. Возврат обеспечительного платежа -->
                @if($splits->where('type', 4)->first())

                    @php $work_group_count++; @endphp
                    <tr>
                        <td
                                colspan="6"
                                class="td-head"
                        >{{ $work_group_count }}. Возврат обеспечительного платежа:
                        </td>
                    </tr>

                    @php $work_count = 1; @endphp
                    @foreach($materials as $material)
                        @foreach($splits->where('man_mat_id', $material->manual_material_id)->where('type', 4) as $split)
                            @if($split->type == 4)
                                <tr>
                                    <td>{{ $work_group_count }}.{{ $work_count++ }}</td>
                                    <td style="text-align:left">
                                        Возврат обеспечительного платеж за
                                        {{ $split->parent->name }}
                                    </td>
                                    <td>{{ $material->unit }}</td>
                                    <td>{{ number_format($split->count, 3, '.', '') }}</td>
                                    <td>{{ number_format((float)$split->security_price_one, 2, ',', ' ') }}</td>
                                    <td>{{ number_format((float)$split->security_price_result, 2, ',', ' ') }}</td>
                                </tr>
                            @endif
                        @endforeach
                    @endforeach
                    <tr>
                        <td
                                colspan="5"
                                class="td-footer"
                        >
                            Итого по возврату обеспечительного платежа:
                        </td>
                        <td class="td-total">{{ number_format($result_security_pay, 2, ',', ' ') }}</td>
                    </tr>
                @endif

                @if($splits->where('type', '2')->first())

                    @php $work_group_count++; @endphp
                    <tr>
                        <td
                                colspan="6"
                                class="td-head"
                        >{{ $work_group_count }}. Обратный выкуп:
                        </td>
                    </tr>

                    @php $work_count = 1; @endphp
                    @foreach($materials as $material)
                        @foreach($splits->where('man_mat_id', $material->manual_material_id) as $split)
                            @if($split->type == 2)
                                <tr>
                                    <td>{{ $work_group_count }}.{{ $work_count++ }}</td>
                                    <td style="text-align:left">
                                        Обратный выкуп (
                                        {{ $split->parent->name }}
                                        )
                                    </td>
                                    <td>{{ $material->unit }}</td>
                                    <td>{{ number_format($split->count, 3, '.', '') }}</td>
                                    <td>{{ number_format((float)$split->security_price_one, 2, ',', ' ') }}</td>
                                    <td>{{ number_format((float)$split->security_price_result, 2, ',', ' ') }}</td>
                                </tr>
                            @endif
                        @endforeach
                    @endforeach
                    <tr>
                        <td
                                colspan="5"
                                class="td-footer"
                        >
                            Итого по обратному выкупу:
                        </td>
                        <td class="td-total">{{ number_format($back_price, 2, ',', ' ') }}</td>
                    </tr>
                @endif
                @php $result = $result_cost - $result_security_pay - $back_price;
                        $buyback = $splits->where('type', '2')->count();
                        $security = $splits->where('type', '4')->count();
                @endphp

                @if($buyback or $security)
                    <tr>
                        <td
                                colspan="5"
                                class="td-footer"
                                style="background-color:rgb(150, 150, 150);"
                        >
                            ИТОГО по проекту после
                            {{ $buyback ? 'обратного выкупа' : '' }}
                            {{ ($buyback and $security) ? ' и' : ''}}
                            {{ $security ? ' возврата обеспечительного платежа' : '' }}:
                        </td>
                        <td class="td-total">{{ number_format($result, 2, ',', ' ') }}</td>
                    </tr>
                @endif
                <tr>
                    <td
                            colspan="5"
                            class="td-footer"
                    >
                        в том числе НДС {{ $offer->nds }}%:
                    </td>
                    <td>{{ number_format($result - $result / ($offer->nds / 100 + 1), 2, ',', ' ') }}</td>
                </tr>
                </tbody>
            </table>
        </div>
        <div>
            <div class="prim">
                @if($offer->advancements->count())
                    @if($set_page_break['section'] == 2 and $set_page_break['points'] == 0)
                        <div class="page-break"></div>
                    @endif
                    <h6>Авансирование</h6>
                    <ul>
                        @foreach($offer->advancements as $index => $advancement)
                            @if($set_page_break['section'] == 2 and $index == ($offer->advancements->count() - $set_page_break['points']))
                                <div class="page-break"></div>
                            @endif
                            @if(trim($advancement->description) != '')
                                <li>
                                    {!! '<b>' . ($index + 1) . '.</b> ' . $advancement->description !!}</li>
                            @endif
                        @endforeach
                    </ul>
                @endif
                @if($offer->notes->count())
                    @if($set_page_break['section'] == 1 and $set_page_break['points'] == 0)
                        <div class="page-break"></div>
                    @endif
                    <h6>Примечания</h6>
                    <ol>
                        @foreach($offer->notes as $index => $note)
                            @if($set_page_break['section'] == 1 and $index == ($offer->notes->count() - $set_page_break['points']))
                                <div class="page-break"></div>
                            @endif
                            @if(trim($note->note) != '')
                                <li>{!! '<b>' . ($index + 1) . '.</b> ' . $note->note !!}</li>
                            @endif
                        @endforeach
                    </ol>
                @endif

                @if($offer->requirements->count())
                    @if($set_page_break['section'] == 0 and $set_page_break['points'] == 0)
                        <div class="page-break"></div>
                    @endif
                    <h6>Для организации работ Заказчик предоставляет:</h6>
                    <ol>
                        @foreach($offer->requirements as $index => $requirement)
                            @if($set_page_break['section'] == 0 and $index == ($offer->requirements->count() - $set_page_break['points']))
                                <div class="page-break"></div>
                            @endif
                            @if(trim($requirement->requirement) != '')
                                <li>{!! '<b>' . ($index + 1) . '.</b> ' . $requirement->requirement !!}</li>
                            @endif
                        @endforeach
                    </ol>
                @endif
                @if(!$offer->requirements->count() and !$offer->notes->count() and !$offer->advancements->count())
                @endif
                <sethtmlpagefooter
                        name="page-footer"
                        value="on"
                />
            </div>
        </div>
    </div>

    <htmlpagefooter name="page-footer">
        <div
                class="list-footer"
                style="font-size: 10px; margin-top: 1mm; margin-left: 5mm; margin-right: 3mm; position: relative;"
        >
            @if(isset($offer->signer_user_id))
                <div>
                    <div style="float:left;">
                        {{ $offer->signer->group_name }} {{ $project::$entities[$project->entity] }}
                    </div>
                    <div style="text-align:right; margin-top: -14px">
                        {{ !is_null($offer->signer->first_name) ? mb_substr($offer->signer->first_name, 0, 1) . '.' : ''}}
                        {{ !is_null($offer->signer->patronymic) ? mb_substr($offer->signer->patronymic, 0, 1) . '.' : ''}}
                        {{ !is_null($offer->signer->last_name) ? $offer->signer->last_name : ''}}
                    </div>
                </div>
            @else
                @if($company->id == 1)
                    <div>
                        <div style="text-align: left; margin-top: -14px">
                            Генеральный директор {{ $company->name }}
                        </div>
                        <div style="text-align: right; margin-top: -14px">
                            М.Д. Исмагилов
                        </div>
                        @if(in_array($offer->status, [4, 5]) || $offer->is_tongue == 2)
                            <div style="text-align: right; margin-right: 80px; margin-top: -80px">
                                <img
                                        src="{{ asset('img/small.png') }}"
                                        max-width="1px;"
                                        max-height="1px;"
                                >
                            </div>
                        @endif
                    </div>
                @elseif($company->id == 2)
                    <div>
                        <div style="text-align: left; margin-top: -14px">
                            Директор по строительству {{ $company->name }}
                        </div>
                        <div style="text-align: right; margin-top: -14px">
                            С.А. Левичев
                        </div>
                        @if(in_array($offer->status, [4, 5]))
                            <div style="text-align: right; margin-right: 80px; margin-top: -80px">
                                <img
                                        src="{{ asset('img/small_2.png') }}"
                                        max-width="1px;"
                                        max-height="1px;"
                                >
                            </div>
                        @endif
                    </div>
                @endif

            @endif
        </div>

        <br>

        <div style="font-size: 10px; margin-top: @if(in_array($offer->status, [4, 5]) || $offer->is_tongue == 2)-15mm; @else 1mm; @endif margin-bottom: 5mm; margin-left: 5mm; margin-right: 3mm; position: relative;">
            Исп.: <br>
            {{--            Кириличева Люсьена Андреевна, <br>--}}
            {{--            Тел.--}}
            @foreach ($resp_users as $user)
                @dd($user->role)
                @if (in_array($user->role, [2,4]))
                    {!! ($user->role == 4) ? "По тех. вопросам: <br>" : 'По коммерческим вопросам: <br>' !!}
                    {{ str_replace(' (шпунт)', '' , $user->profession) . ', ' . $user->last_name }}
                    {{ (!is_null($user->first_name) ? mb_substr($user->first_name, 0 , 1) . '.' :  '') }}
                    {{ (!is_null($user->patronymic) ? mb_substr($user->patronymic, 0 , 1) . '.' : '') }}
                    {{ ($user->work_phone ? 'Тел. 326-94-06 доб. ' . $user->work_phone . '.' : '') }}
                    {!! ($user->person_phone ? ', '. $user->person_phone : '<br>') !!}
                @else
                    {{ str_replace(' (сваи)', '' , $user->profession) . ', ' . $user->last_name  }}
                    {{ $user->first_name . ' ' . $user->patronymic . ','}} <br>
                    {{ ( 'Тел.: 326-94-06 доб. ' . $user->work_phone ? $user->work_phone . ', ' : '') }}
                    {{($user->person_phone ? $user->person_phone : '') }}
                    {{($user->email ? '<br>' . $user->email : '') }}
                @endif
                <br>
            @endforeach
        </div>
    </htmlpagefooter>

</div>
</body>
</html>
