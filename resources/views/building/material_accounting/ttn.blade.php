<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <link rel="apple-touch-icon" sizes="76x76" href="{{ mix('img/apple-icon.png') }}">
        <link rel="icon" type="image/ico" href="{{ mix('img/favicon.ico') }}">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
        <!-- CSS Files -->
        <link href="{{ mix('css/ttn.css') }}" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,900&display=swap" rel="stylesheet">
      </head>
      <style media="screen">
      .title {
          font-size: 5pt;
      }

      .logo-print {
          width:180px;
          margin-top:-5px
      }

      body  {
          margin: 0px;
      }

      .pbprint {
          padding-bottom:15px;
      }

      @media print {
          body  {
              width:100% !important;
              height:100% !important;
              padding:0 !important;
              margin:0 !important;
          }
      }

      .head-applic {
          font-size: 5pt;
      }

      @media (max-width:450px){
          .logo-print{
              width:120px
          }

          .head-applic {
              font-size: 5pt;
          }

          .pbprint {
              padding-bottom:5px;
          }
      }
      </style>
    <style type="text/css" media="print">

        .logo-print {
            width:170px;
            margin-top:-5px
        }

        @page {
            size: auto;   /* auto is the initial value */
            margin: 20px;
        }

        body  {
            margin: 0px;
        }

        .pbprint {
            padding-bottom:14px;
        }

        @media print {
            body  {
                width:100% !important;
                height:100% !important;
                padding:0 !important;
                margin:0 !important;
            }
        }

        .head-applic {
            font-size: 5pt;
        }

        @media (max-width:450px){
            .logo-print{
                width:120px
            }

            .head-applic {
                font-size: 5pt;
            }

            .pbprint {
                padding-bottom:5px;
            }
        }
    </style>
    <body>
        <div class="ttn-list clearfix">
            <div class="list-header">
                <div class="top-header clearfix">
                    <img src="{{ asset('img/logosvg.png') }}" alt="ск город" class="logo-print">
                    <div style="text-align: right; float:right">
                        <p class="text-right head-applic">
                          Приложение № 4 к Правилам перевозок грузов автомобильным транспортом <br>
                          (в ред. постановления Правительства РФ от 30.12.2011 № 1208)
                        </p>
                    </div>
                </div>
            </div>
            <div class="list-body" style="margin-top:-20px">
                <div style="margin:0 auto; margin-bottom:2px">
                    <h1 style="font-size:8pt; text-align:center; text-transform: uppercase">
                        <b>Транспортная накладная</b>
                    </h1>
                </div>
                <table class="ttn-table" style="border-bottom:0">
                    <tbody>
                        <tr>
                            <th style="width:50%;text-align:left;">Экземпляр №</th>
                            <th style="width:25%">Дата: <b>{{ \Carbon\Carbon::now()->format('d.m.Y') }}</b></th>
                            <th style="width:25%"><b>№ {{ $id }}</b></th>
                        </tr>
                        <tr>
                            <th style="width:50%;text-align:left"><span class="dib title">1. Грузоотправитель</span><span class="dib" style="margin-left:10px;"><b>{{ $main_entity_from }}</b></span></th>
                            <th style="width:50%;text-align:left" colspan="2"><span class="title">2. Грузополучатель</span><span class="dib" style="margin-left:10px;"><b>{{ $main_entity_to }}</b></span></th>
                        </tr>
                        <tr>
                            <td style="width:50%; " class="pbprint">
                              (фамилия, имя, отчество, адрес места жительства,данные о средствах связи - для физ. лица)

                              <!-- <b style="font-size:7pt;text-align:left">196128, г. Санкт-Петербург, Варшавская ул., д. 9, корп. 1, литер А, пом. 56Н</b> -->
                            </td>
                            <td style="width:50%" colspan="2">
                                (фамилия, имя, отчество, адрес места жительства, данные о средствах связи – для физ. лица)
                                <b style="font-size:6pt"></b>
                              </td>
                            </td>
                        </tr>
                        <tr>
                            <td style="width:50%;" class="pbprint">
                              полное наименование, адрес места нахождения – для юридического лица
                            </td>
                            <td style="width:50%;" colspan="2" class="pbprint">полное наименование, адрес места нахождения – для юридического лица</td>
                        </tr>
                        <tr>
                            <td style="width:50%;">
                              (фамилия, имя, отчество, данные о средствах связи, ответственного за перевозку)
                            </td>
                            <td style="width:50%" colspan="2">фамилия, имя, отчество, данные о средствах связи, ответственного за перевозку)</td>
                        </tr>
                        <tr>
                            <th colspan="3" style="text-align:left">
                                <span class="dib title">3. Наименование груза</span>
                                <b style="font-size:6pt; margin-left:10px">
                                @foreach($manual_materials as $key => $material)
                                {{  $material['name'] }}  {{  $material['count'] }} {{  $material['unit'] }}@if($key != count($manual_materials) - 1), @endif
                                @endforeach
                                </b>
                            </th>
                        </tr>
                        <tr>
                            <td colspan="3" style="" class="pbprint">отгрузочное наименование груза (для опасных грузов – в соответствии с ДОПОГ), его состояние и другая необходимая информация о грузе)

                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" style="" class="pbprint">(количество грузовых мест, маркировка, вид тары и способ упаковки)

                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" style="" class="pbprint">(масса нетто (брутто) грузовых мест в килограммах, размеры (высота, ширина и длина) в метрах, объем грузовых мест в кубических метрах)

                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">(в случае перевозки опасного груза – информация по каждому опасному веществу, материалу или изделию в соответствии с пунктом 5.4.1 ДОПОГ)
                            </td>
                        </tr>
                        <tr>
                            <th colspan="3" style="text-align:left"><span class="dib title">4. Сопроводительные документы на груз</span>
                            </th>
                        </tr>
                        <tr>
                            <td colspan="3" style="text-align:right;" class="pbprint">
                              (перечень прилагаемых к транспортной накладной документов, предусмотренных ДОПОГ, санитарными, таможенными, карантинными, иными правилами в соответствии с законодательством Российской Федерации)

                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                              (перечень прилагаемых к грузу сертификатов, паспортов качества, удостоверений, разрешений, инструкций, товарораспорядительных и других документов, наличие которых установлено законодательством Российской Федерации)
                            </td>
                        </tr>
                        <tr>
                            <th colspan="3" style="text-align:left"><span class="dib title">5. Указания грузоотправителя</span></th>
                        </tr>
                        <tr>
                            <td colspan="3" style="" class="pbprint">
                              (параметры транспортного средства, необходимые для осуществления перевозки груза (тип, марка, грузоподъемность, вместимость и др.))

                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" style="" class="pbprint">
                              (указания, необходимые для выполнения фитосанитарных, санитарных, карантинных, таможенных и прочих требований, установленных законодательством Российской Федерации)
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" style="" class="pbprint">
                              (рекомендации о предельных сроках и температурном режиме перевозки, сведения о запорно-пломбировочных устройствах (в случае их предоставления грузоотправителем)
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                              объявленная стоимость (ценность) груза, запрещение перегрузки груза)
                            </td>
                        </tr>
                        <tr>
                            <th style="width:50%; text-align:left">
                                <span class="dib title" style="margin-right: 7px">
                                    6. Прием груза
                                </span>
                                <b>{{ $operation->object_from->address }}</b>
                            </th>
                            <th style="width:50%; text-align:left" colspan="2">
                                <span class="dib title" style="margin-right: 7px">
                                    7. Сдача груза
                                </span>
                                <b>{{ $operation->object_to->address }}</b>
                            </th>
                        </tr>
                        <tr>
                            <td style="width:50%;">
                              (адрес места погрузки) <br/>
                              <b style="font-size:6pt">{{ $take['time'] ? \Carbon\Carbon::parse($take['time'])->format('d.m.Y H:i') : '' }}</b>
                            </td>
                            <td style="width:50%" colspan="2">
                              (адрес места выгрузки) <br/>
                              <b style="font-size:6pt">{{ $give['time'] ? \Carbon\Carbon::parse($give['time'])->format('d.m.Y H:i') : '' }}</b>
                            </td>
                        </tr>
                        <tr>
                            <td style="width:50%;">
                              (дата и время подачи транспортного средства под погрузку)<br>
                              <div style="font-size:6pt;width:100%;display:inline-block;float:left; margin-top: -5px;padding-top:5px; padding-bottom:5px">
                                  <b>{{ $take['fact_arrival_time'] ? \Carbon\Carbon::parse($take['fact_arrival_time'])->format('d.m.Y H:i') : '' }}</b><br/>
                                  <span style="border-top:0.5px solid black; font-size: 4pt; width:100%; display:inline-block;">
                                    (фактические дата и время прибытия)
                                  </span>
                              </div>
                              <!-- <div style="font-size:8pt;width:45%;display:inline-block;padding-top:5px;float:right">
                                  <b>
                                    {{ $take['fact_departure_time'] ? \Carbon\Carbon::parse($take['fact_departure_time'])->format('d.m.Y H:i') : '' }}
                                  </b>
                                  <br/>
                                  <span style="border-top:1px solid black; font-size: 6pt; width:100%; display:inline-block;">
                                    (фактические дата и время убытия)
                                  </span>
                              </div> -->
                              <div class="clearfix"></div>
                            </td>
                            <td style="width:50%" colspan="2">
                              (дата и время подачи транспортного средства под выгрузку)
                              <div style="font-size:6pt;width:100%;display:inline-block;float:left; margin-top: -5px;padding-top:5px; padding-bottom:5px">
                                  <b>{{ $give['fact_arrival_time'] ? \Carbon\Carbon::parse($give['fact_arrival_time'])->format('d.m.Y H:i') : ''  }}</b><br/>
                                  <span style="border-top:0.5px solid black; font-size: 4pt; width:100%; display:inline-block;  margin-top: -5px;">
                                    (фактические дата и время прибытия)
                                  </span>
                              </div>
                              <!-- <div style="font-size:8pt;width:45%;display:inline-block;padding-top:5px; float:right">
                                  <b>
                                    {{ $give['fact_departure_time'] ? \Carbon\Carbon::parse($give['fact_departure_time'])->format('d.m.Y H:i') : '' }}
                                  </b>
                                  <br/>
                                  <span style="border-top:1px solid black; font-size: 6pt; width:100%; display:inline-block;">
                                    (фактические дата и время убытия)
                                  </span>
                              </div> -->
                              <div class="clearfix"></div>
                            </td>
                        </tr>
                        <tr>
                            <td style="width:50%;" class="pbprint">
                              (фактическое состояние груза, тары, упаковки, маркировки и опломбирования)<br/>
                              <div style="font-size:6pt;width:100%;display:inline-block;float:left;">
                                  <b>{{ $take['weight'] == '0.000' ? null : $take['weight'] * 1000 . ' кг.,' }}</b>
                                  <b>{{ $take['places_count'] == '0.000' ? null : $take['places_count'] }}</b>
                                  <br/>
                                  <span style="border-top:0.5px solid black; font-size: 4pt;width:100%; display:inline-block;">
                                    (масса груза, количество грузовых мест)
                                  </span>
                              </div>
                              <!-- <div style="font-size:8pt;width:45%;display:inline-block;padding-top:5px; float:right">
                                  <b>{{ $take['places_count'] == '0.000' ? null : $take['places_count'] }}</b>
                                  <br/>
                                  <span style="border-top:1px solid black; font-size: 6pt; width:100%; display:inline-block;">
                                    (количество грузовых мест)
                                  </span>
                              </div> -->
                              <div class="clearfix"></div>
                            </td>
                            <td style="width:50%;" colspan="2" class="pbprint">
                              (фактическое состояние груза, тары, упаковки, маркировки и опломбирования)<br/>
                              <div style="font-size:6pt;width:100%;display:inline-block;float:left;">
                                  <b>{{ $give['weight'] == '0.000' ? null : $give['weight'] * 1000 . ' кг.,' }} </b>
                                  <b>{{ $give['places_count'] == '0.000' ? null : $give['places_count'] }}</b>
                                  <br/>
                                  <span style="border-top:0.5px solid black; font-size: 4pt;width:100%; display:inline-block;">
                                    (масса груза, количество грузовых мест)
                                  </span>
                              </div>
                              <!-- <div style="font-size:8pt;width:45%;display:inline-block;padding-top:5px; float:right">
                                  <b>{{ $give['places_count'] == '0.000' ? null : $give['places_count'] }}</b>
                                  <br/>
                                  <span style="border-top:1px solid black; font-size: 6pt; width:100%; display:inline-block;">
                                    ()
                                  </span>
                              </div> -->
                              <div class="clearfix"></div>
                            </td>
                        </tr>
                        <tr>
                          <td>(подпись и оттиск печати грузоотправителя (при наличии) подпись водителя, принявшего груз)
                          </td>
                          <td colspan="2">(подпись и оттиск печати грузополучателя (при наличии) подпись водителя, сдавшего груз)
                          </td>
                        </tr>
                        <tr>
                          <th colspan="3" style="text-align:left">
                              <span class="dib title" style="margin-right: 7px">
                                  8. Условия перевозки
                              </span>
                          </th>
                        </tr>
                        <tr>
                          <td colspan="3" style="text-align:right" class="pbprint">
                            (сроки, по истечении которых грузоотправитель и грузополучатель вправе считать груз утраченным, форма уведомления о проведении экспертизы для определения размера фактических недостачи, повреждения (порчи) груза)

                          </td>
                        </tr>
                        <tr>
                          <td colspan="3" class="pbprint">
                            (размер платы и предельный срок хранения груза в терминале перевозчика, сроки погрузки (выгрузки) грузов, порядок предоставления и установки приспособлений, необходимых для погрузки, выгрузки и перевозки груза)

                          </td>
                        </tr>
                        <tr>
                          <td colspan="3" class="pbprint">
                            (масса груза и способ ее определения, сведения об оплобировании крытых транспортных средств и контейнеров)

                          </td>
                        </tr>
                        <tr>
                          <td colspan="3" class="pbprint">
                            (порядок выполнения погрузо-разгрузочных работ, работ по промывке и дезинфекции транспортных средств)

                          </td>
                        </tr>
                        <tr>
                          <td colspan="3" class="pbprint">
                              размер штрафа за невывоз груза по вине перевозчика, несвоевременное предоставление транспортного средства, контейнера и просрочку доставки груза, порядок исчисления срока просрочки)

                          </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                (размер штрафа за непредъявление транспортных средств для перевозки груза, за задержку (простой) транспортных средств, поданных под погрузку, выгрузку, за простой специализированных транспортных средств и задержку (простой) контейнеров)
                            </td>
                        </tr>
                        <tr>
                          <th colspan="3" style="border-bottom:0; " class="pbprint">
                              <span class="dib title" style="margin-right: 7px">
                                  9. Информация о принятии заказа (заявки) к исполнению
                              </span>
                          </th>
                        </tr>
                        <tr>
                          <td colspan="3" style="text-align:center">
                              (дата принятия заказа (заявки) к исполнению - фамилия, имя, отчество, должность лица, принявшего заказ (заявку) к исполнению, оттиск печати (при наличии), подпись)
                            <!-- <div style="width:25%;display:inline-block;float:left;padding-top:5px">
                                <br/>
                                <span style="border-top:1px solid black; font-size: 6pt;width:100%; display:inline-block;">
                                  (дата принятия заказа (заявки) к исполнению)
                                </span>
                            </div>
                            <div style="width:25%;display:inline-block;padding-top:5px">
                                <br/>
                                <span style="border-top:1px solid black; font-size: 6pt;width:100%; display:inline-block;">
                                  (фамилия, имя, отчество, должность лица, принявшего заказ (заявку) к исполнению)
                                </span>
                            </div>
                            <div style="width:25%;display:inline-block;padding-top:5px; float:right">
                                <br/>
                                <span style="border-top:1px solid black; font-size: 6pt;width:100%; display:inline-block;">
                                  (подпись)
                                </span>
                            </div> -->
                          </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <table class="ttn-table" style="border-bottom:0;border-top: 0;">
              <tbody>
                <tr>
                  <th colspan="2" style="border-bottom:0; border-top:0;" class="pbprint">
                      <span class="dib title">
                          10. Перевозчик
                      </span>
                  </th>
                </tr>
                <tr>
                  <td colspan="2">
                    (фамилия, имя, отчество, адрес места жительства – для физического лица)
                    <br/><br/>
                    <span style="font-size:6pt;"><b>{{ $entity }}</b></span>
                  </td>
                </tr>
                <tr>
                  <td colspan="2">
                    (наименование и адрес места нахождения – для юридического лица)
                    <br/><br/>
                    <span style="font-size:6pt;"><b>{{ $driver_name ? $driver_name .  ',' : '' }} {{ $driver_phone_number }}</b></span>
                  </td>
                </tr>
                <tr>
                  <td colspan="2" style="border-bottom:0">
                    (фамилия, имя, отчество лица, ответственного за перевозку, данные о средствах связи)
                    <br/><br/>
                  </td>
                </tr>
            </tbody>
        </table>
        <table class="ttn-table" style="border-top: 0;">
          <tbody>
                <tr>
                  <th colspan="3" style="border-top: 0; border-bottom:0">
                      <span class="dib title">
                          11. Транспортное средство
                      </span>
                  </th>
                </tr>
                <tr>
                  <td style="width:50%">
                    <span style="font-size:7pt;"><b>{!! $vehicle ?? '<div class="pbprint"></div>' !!}</b></span>
                  </td>
                  <td style="width:50%">
                    <span style="font-size:7pt;"><b>{{ $vehicle_number }}</b></span>
                  </td>
                </tr>
                <tr>
                  <td style="width:50%">
                    (количество, тип, марка, грузоподъемность (в тоннах), вместимость (в кубических метрах))
                    <br/><br/>
                    <span style="font-size:7pt;"><b>{{ $trailer }}</b></span>
                  </td>
                  <td style="width:50%" style="border-bottom:0">
                    (регистрационные номера)
                    <br/><br/>
                    <span style="font-size:7pt;"><b>{{ $trailer_number }}</b></span>
                  </td>
                </tr>
            </tbody>
        </table>
        <table class="ttn-table" style="border-top: 0;">
          <tbody>
                <tr>
                  <th colspan="2" style="border-top: 0; border-bottom:0;" class="pbprint">
                      <span class="dib title">
                          12. Оговорки и замечания перевозчика
                      </span>
                  </th>
                </tr>
                <tr>
                  <td style="width:50%" class="pbprint">
                    (фактическое состояние груза, тары, упаковки, маркировки и опломбирования при приеме груза)
                  </td>
                  <td style="width:50%" class="pbprint">
                    (фактическое состояние груза, тары, упаковки, маркировки и опломбирования при сдаче груза)
                  </td>
                </tr>
                <tr>
                  <td style="width:50%; border-bottom:0" class="pbprint">
                    (изменение условий перевозки при движении)
                  </td>
                  <td style="width:50%; border-bottom:0" class="pbprint">
                    (изменение условий перевозки при выгрузке)
                  </td>
                </tr>
                <tr>
                  <th colspan="2">
                      <span class="dib title" style="border-top:0; border-bottom:0;" class="pbprint">
                          13. Прочие условия
                      </span>
                  </th>
                </tr>
                <tr>
                  <td class="pbprint">
                    (номер, дата и срок действия специального разрешения, установленный маршрут перевозки опасного, тяжеловесного или крупногабаритного груза)
                  </td>
                  <td>
                      (режим труда и отдыха водителя в пути следования, сведения о коммерческих и иных актах)
                  </td>
                </tr>
                <tr>
                  <th colspan="2">
                      <span class="dib title" style="border-top:0; border-bottom:0;" class="pbprint">
                          14. Переадресовка
                      </span>
                  </th>
                </tr>
                <tr>
                  <td style="width:50%;" class="pbprint">
                    (дата, форма переадресовки (устно или письменно))
                  </td>
                  <td style="width:50%;" class="pbprint">
                    (адрес нового пункта выгрузки, дата и время подачи транспортного средства под выгрузку)
                  </td>
                </tr>
                <tr>
                  <td style="width:50%">
                    (сведения о лице, от которого получено указание на переадресовку (наименование, фамилия, имя, отчество и др.))
                  </td>
                  <td style="width:50%">
                    (при изменении получателя груза – новое наименование грузополучателя и место его нахождения)
                  </td>
                </tr>
            </tbody>
        </table>
        <table class="ttn-table" style="border-top: 0;">
          <tbody>
                <tr>
                  <th colspan="2" style="border-top: 0;">
                      <span class="dib title" style="border-top:0; border-bottom:0;" class="pbprint">
                          15. Стоимость услуг перевозчика и порядок расчета провозной платы
                      </span>
                  </th>
                </tr>
                <tr>
                  <td style="width:50%">
                    (стоимость услуги в рублях)
                    <br/><br/>
                  </td>
                  <td style="width:50%">
                    (расходы перевозчика и предъявляемые грузоотправителю платежи за проезд по платным автомобильным дорогам,
                    <br/><br/>
                  </td>
                </tr>
                <tr>
                  <td style="width:50%">
                    (порядок (механизм) расчета (исчислений) платы)
                    <br/><br/>
                  </td>
                  <td style="width:50%">
                    за перевозку опасных, тяжеловесных и крупногабаритных грузов, уплату таможенных пошлин и сборов,
                    <br/><br/>
                  </td>
                </tr>
                <tr>
                  <td style="width:50%">
                      (размер провозной платы (заполняется после окончания перевозки) в рублях)
                  </td>
                  <td style="width:50%">
                    выполнение погрузо-разгрузочных работ, а также работ по промывке и дезинфекции транспортных средств)
                  </td>
                </tr>
                <tr>
                  <td colspan="2" style="border: 2px solid black; font-size:6pt">
                      <b>
                        ООО «СК ГОРОД», 196128, г. Санкт-Петербург, Варшавская ул., д. 9, корп. 1, литер А, помещение 56Н, ИНН/КПП: 7807348494/781001001
                        р/счет 40702810755380000284 в Северо-Западном банке ПАО «Сбербанк» г. Санкт-Петербург БИК 044030653 к/с 30101810500000000653
                      </b>
                  </td>
                </tr>
                <tr>
                  <td colspan="2">
                    (полное наименование организации плательщика (грузоотправителя), адрес, банковские реквизиты организации плательщика (грузоотправителя))
                  </td>
                </tr>
            </tbody>
        </table>
        <table class="ttn-table" style="border-top: 0;">
          <tbody>
                <tr>
                  <th colspan="2" style="border-top:0; border-bottom:0">
                      <span class="dib title">
                          16. Дата составления, подписи сторон
                      </span>
                  </th>
                </tr>
                <tr>
                    <td style="width: 50%; text-align:left; border-top:0; font-size:6pt">
                        <b style="margin-right:7px">{{ $consignor }}</b>
                        <b style="float:right">{{ \Carbon\Carbon::now()->format('d.m.Y') }} г</b>
                    </td>
                    <td style="width: 50%;text-align:left; border-top:0;font-size:6pt">
                        <b style="margin-right:7px">{{ $carrier }}</b>
                        <b style="float:right">{{ \Carbon\Carbon::now()->format('d.m.Y') }} г</b>
                    </td>
                </tr>
                <tr>
                    <td style="width: 50%">
                        (грузоотправитель, оттиск печати (при наличии), дата, подпись)
                    </td>
                    <td style="width: 50%">
                        (перевозчик, оттиск печати (при наличии), дата, подпись)
                    </td>
                </tr>
                <!-- <tr>
                  <th colspan="3" style="padding-top:15px; padding-bottom: 20px">
                    <div style="width:47%; display:inline-block;">
                      <div style="font-size:8pt;width:30%;display:inline-block;">
                          <b>{{ $consignor }}</b>
                          <br/>
                          <span style="border-top:1px solid black; font-size: 6pt;width:100%; display:inline-block;vertical-align:top">
                            грузоотправитель (уполномоченное лицо)
                          </span>
                      </div>
                      <div style="font-size:8pt;width:30%;display:inline-block;">
                          <b>{{ \Carbon\Carbon::now()->format('d.m.Y') }} г</b>
                          <br/>
                          <span style="border-top:1px solid black; font-size: 6pt;width:100%; display:inline-block;vertical-align:top">
                            (дата)
                          </span>
                      </div>
                      <div style="font-size:8pt;width:30%;display:inline-block;">
                          <br/>
                          <span style="border-top:1px solid black; font-size: 6pt;width:100%; display:inline-block;vertical-align:top">
                            (подпись)
                          </span>
                      </div>
                    </div>
                    <div style="width:47%; display:inline-block;">
                      <div style="font-size:8pt;width:30%;display:inline-block;">
                          <b>{{ $carrier }}</b>
                          <br/>
                          <span style="border-top:1px solid black; font-size: 6pt;width:100%; display:inline-block;vertical-align:top">
                            перевозчик (уполномоченное лицо)
                          </span>
                      </div>
                      <div style="font-size:8pt;width:30%;display:inline-block;">
                          <b>{{ \Carbon\Carbon::now()->format('d.m.Y') }} г</b>
                          <br/>
                          <span style="border-top:1px solid black; font-size: 6pt;width:100%; display:inline-block;vertical-align:top">
                            (дата)
                          </span>
                      </div>
                      <div style="font-size:8pt;width:30%;display:inline-block;">
                          <br/>
                          <span style="border-top:1px solid black; font-size: 6pt;width:100%; display:inline-block;vertical-align:top">
                            (подпись)
                          </span>
                      </div>
                    </div>
                    <div class="clearfix"></div>
                  </th>
                </tr> -->
            </tbody>
            <table class="ttn-table" style="border-top: 0;">
              <tbody>
                <tr>
                  <th colspan="3" style="border-top: 0;">
                      <span class="dib title">
                          17. Отметки грузоотправителей, грузополучателей, перевозчиков
                      </span>
                  </th>
                </tr>
                <tr>
                  <th style="width: 50%; font-size:5pt">
                    Краткое описание обстоятельств, послуживших основанием для отметки
                  </th>
                  <th style="width: 25%; font-size:5pt">
                    Расчет и размер штрафа
                  </th>
                  <th style="width: 25%; font-size:5pt">
                    Подпись, дата
                  </th>
                </tr>
                <tr>
                  <td style="" class="pbprint">

                  </td>
                  <td style="" class="pbprint">

                  </td>
                  <td style="" class="pbprint">

                  </td>
                </tr>
                <tr>
                  <td style="" class="pbprint">

                  </td>
                  <td style="" class="pbprint">

                  </td>
                  <td style="" class="pbprint">

                  </td>
                </tr>
                <tr>
                  <td style="" class="pbprint">

                  </td>
                  <td style="" class="pbprint">

                  </td>
                  <td style="" class="pbprint">

                  </td>
                </tr>
              </tbody>
            </table>
        </div>
    </body>
</html>

<script>
    setTimeout(function() {
        window.print();
    }, 2000);
</script>
