<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="utf-8"/>
    <link rel="apple-touch-icon" sizes="76x76" href="{{ mix('img/apple-icon.png') }}">
    <link rel="icon" type="image/ico" href="{{ mix('img/favicon.ico') }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <title>@yield('title')</title>
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no'
          name='viewport'/>
    <!--     Fonts and icons     -->
    <link href="{{ mix('css/font-awesome.min.css') }}" rel="stylesheet"/>
    <link href="{{ mix('css/v4-shims.min.css') }}" rel="stylesheet"/>
    <link href="{{ mix('css/google_fonts.css') }}" rel="stylesheet"/>

    <link href="{{ mix('css/pe-icon-7-stroke.css') }}" rel="stylesheet"/>
    <!-- CSS Files -->
    <link href="{{ mix('css/bootstrap.min.css') }}" rel="stylesheet"/>
    <link href="{{ mix('css/balloon.css') }}" rel="stylesheet"/>
    <link href="{{ mix('css/light-bootstrap-dashboard.css') }}" rel="stylesheet"/>

    <!-- CSS Just for demo purpose, don't include it in your project -->
    <link href="{{ mix('css/demo.css') }}" rel="stylesheet"/>
    <link href="{{ mix('css/additionally.css') }}" rel="stylesheet"/>
    <link href="{{ mix('css/tech.css') }}" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700&display=swap" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css?family=Montserrat:300,300i,400,400i,500,500i,600,700,800,900&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="{{ mix('css/index.css') }}">
    <link rel="stylesheet" href="{{ mix('css/emojione.css') }}">
    <link rel="stylesheet" href="{{ mix('css/emojionearea.min.css') }}">
    <link rel="stylesheet" href="{{ mix('css/main.css') }}">
    <!-- editable table -->
    <link href="https://www.jqueryscript.net/css/jquerysctipttop.css" rel="stylesheet" type="text/css">
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootswatch/4.1.3/darkly/bootstrap.min.css"> -->

    <!-- lightgalleryjs.com  -->
    <link type="text/css" rel="stylesheet" href="{{ asset('css/lightgallery/lightgallery.css')}}" />
    <link type="text/css" rel="stylesheet" href="{{ asset('css/lightgallery/lg-thumbnail.css')}}" />
    <link type="text/css" rel="stylesheet" href="{{ asset('css/lightgallery/lg-zoom.css')}}" />
    <link type="text/css" rel="stylesheet" href="{{ asset('css/lightgallery/lg-rotate.css')}}" />
    <link type="text/css" rel="stylesheet" href="{{ asset('css/lightgallery/lightgallery-bundle.css')}}" />


    <meta name="csrf-token" content="{{ csrf_token() }}"/>


    @yield('css_top')
    <style media="screen">
        @media (min-width: 1025px) {
            .sidebar-m {
                padding-bottom: 50px !important;
                display: flex;
                flex-direction: column;
            }
        }

        .sidebar-m {
            overflow-x: hidden !important;
        }

        .nav-m {
            float: none;
            display: block;
            display: -ms-flexbox;
            display: flex;
            -ms-flex-wrap: wrap;
            flex-wrap: wrap;
            padding-left: 0;
            margin-bottom: 0;
            list-style: none;
        }

        .sidebar .nav-m .nav-item .nav-link {
            color: #FFFFFF;
            margin: 5px 15px 0px 10px;
            opacity: .86;
            border-radius: 4px;
            text-transform: uppercase;
            line-height: 30px;
            font-size: 12px;
            font-weight: 600;
            padding: 10px 15px;
            white-space: nowrap;
        }

        .sidebar .nav-m a, .table > tbody > tr .td-actions .btn {
            -webkit-transition: all 150ms ease-in;
            -moz-transition: all 150ms ease-in;
            -o-transition: all 150ms ease-in;
            -ms-transition: all 150ms ease-in;
            transition: all 150ms ease-in;
        }

        .sidebar .nav-m .nav-item .nav-link i {
            font-size: 28px;
            margin-right: 15px;
            width: 30px;
            text-align: center;
            vertical-align: middle;
            float: left;
        }

        .sidebar .sidebar-wrapper .nav-m .nav-link p {
            margin: 0;
            line-height: 30px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            white-space: nowrap;
            position: relative;
            color: #FFFFFF;
            -webkit-transform: translate3d(0px, 0, 0);
            -moz-transform: translate3d(0px, 0, 0);
            -o-transform: translate3d(0px, 0, 0);
            -ms-transform: translate3d(0px, 0, 0);
            transform: translate3d(0px, 0, 0);
            display: block;
            height: auto;
            opacity: 1;
        }

        .sidebar .nav-m .nav-item .nav-link:hover {
            background: rgba(255, 255, 255, 0.13);
            opacity: 1;
        }

    </style>
</head>

<body @if(Session::has('sidebar_mini')) @if(Session::get('sidebar_mini')) class="sidebar-mini" @endif @endif>
<div class="wrapper">
    <div class="sidebar"
         @if (config("app.env") == "local")
             data-color="red"
         @elseif (config("app.env") == "local_dev")
             data-color="orange"
         @else
             data-color="purple"
         @endif

         data-image="{{ mix('img/full-screen-image-2.jpg') }}">
        <!-- data-color="purple | blue | green | orange | red" -->
        <div class="logo">
            <a class="simple-text logo-mini" href="{{ route('tasks::index') }}">
                <img src="{{ mix('img/logo-mini.png') }}" width="30">
            </a>
            <a class="simple-text logo-normal" href="{{ route('tasks::index') }}">
                <img src="{{ mix('img/logo-normal.png') }}" width="85">
            </a>
        </div>
        <div class="sidebar-wrapper sidebar-m">
            <ul class="nav first-nav">
                @if(Gate::check('tasks') || Gate::check('dashbord'))
                    <li class="nav-item @if(Request::is('tasks') || Request::is('tasks/*')) active @endif">
                        <a class="nav-link" href="{{ route('tasks::index') }}">
                            <i class="pe-7s-timer"></i>
                            <p>Задачи</p>
                        </a>
                    </li>
                @endif
                @can('projects')
                    <li class="nav-item @if (Request::is('projects') || Request::is('projects/*')) active @endif">
                        <a class="nav-link" href="{{ route('projects::index') }}">
                            <i class="pe-7s-display1"></i>
                            <p class="sidebar-normal">Проекты</p>
                        </a>
                    </li>
                @endcan
                @if(Gate::check('contractors') || Gate::check('objects'))
                    <li class="nav-item @if(Request::is('contractors') || Request::is('contractors/*') || Request::is('objects') || Request::is('objects/*')) active @endif">
                        <a class="nav-link " data-toggle="collapse" href="#commerceExamples">
                            <i class="pe-7s-portfolio"></i>
                            <p>Коммерция
                                <b class="caret"></b>
                            </p>
                        </a>
                        <div
                            class="collapse @if(Request::is('contractors') || Request::is('contractors/*') || Request::is('objects') || Request::is('objects/*') || Request::is('building/materials') || Request::is('building/materials/*') || Request::is('building/works') || Request::is('building/works/*') || Request::is('building/work_groups/*') || Request::is('building/work_groups')) show @endif"
                            id="commerceExamples">
                            <ul class="nav">
                                @can('contractors')
                                    <li class="nav-item @if (Request::is('contractors') || Request::is('contractors/*')) active @endif">
                                        <a class="nav-link" href="{{ route('contractors::index') }}">
                                            <span class="sidebar-mini"><i class="pe-7s-users pe-7s-mini"></i></span>
                                            <span class="sidebar-normal">Контрагенты</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('objects')
                                    <li class="nav-item @if (Request::is('objects') || Request::is('objects/*')) active @endif">
                                        <a class="nav-link" href="{{ route('objects::base-template') }}">
                                            <span class="sidebar-mini"><i class="pe-7s-culture pe-7s-mini"></i></span>
                                            <span class="sidebar-normal">Объекты</span>
                                        </a>
                                    </li>

                                @endcan
                                @if(Auth::user()->can('manual_materials'))
                                    <li class="nav-item @if (Request::is('building/materials') || Request::is('building/materials/*')) active @endif">
                                        <a class="nav-link" href="{{ route('building::materials::index') }}">
                                            <span class="sidebar-mini"><i class="pe-7s-diamond pe-7s-mini"></i></span>
                                            <span class="sidebar-normal">Материалы</span>
                                        </a>
                                    </li>
                                @endif
                                @if(Auth::user()->can('manual_works'))
                                    <li class="nav-item @if (Request::is('building/works') || Request::is('building/works/*') || Request::is('building/work_groups/*') || Request::is('building/work_groups'))  active @endif">
                                        <a class="nav-link" href="{{ route('building::works::index') }}">
                                            <span class="sidebar-mini"><i class="pe-7s-config pe-7s-mini"></i></span>
                                            <span class="sidebar-normal">Работы</span>
                                        </a>
                                    </li>
                                @endif
                                @if(Auth::user()->can('commercial_block_task_report_xlsx_export_access'))
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('tasks.filter-tasks-report') }}">
                                            <span class="sidebar-mini"><i class="pe-7s-download pe-7s-mini"></i></span>
                                            <span class="sidebar-normal">Отчет по задачам и КП</span>
                                        </a>
                                    </li>
                                @endif
                                <hr>
                            </ul>
                        </div>
                    </li>
                @endif
                @if(Gate::check('material_accounting_materials_types_editing') ||
                    Gate::check('material_accounting_materials_standards_editing') ||
                    Gate::check('material_accounting_material_table_access') ||
                    Gate::check('material_accounting_operation_list_access') ||
                    Gate::check('material_accounting_material_list_access'))
                    <li class="nav-item @if((Request::is('materials') || Request::is('materials/*')) && !Request::is('*fuel_tank*') && !Request::is('*our_technic_tickets*') && !Request::is('*defects*') && !Request::is('*tech_acc*') && !Request::is('*vehicles*')) active @endif">
                        <a class="nav-link" data-toggle="collapse" href="#materials">
                            <i class="pe-7s-plugin"></i>
                            <p>Строительство
                                <b class="caret"></b>
                            </p>
                        </a>

                        <div
                            class="collapse @if((Request::is('materials') || Request::is('materials/*')) && !Request::is('*fuel_tank*') && !Request::is('*our_technic_tickets*') && !Request::is('*defects*') && !Request::is('*tech_acc*') && !Request::is('*vehicles*')) show @endif"
                            id="materials">
                            <ul class="nav">
                                <!--Q3W Menu Items-->
                                @if(Auth::user()->can('material_accounting_operation_list_access'))
                                    <li class="nav-item @if (Request::is('/materials/operations/all') || Request::is('/materials/operations/all/*')) active @endif">
                                        <a class="nav-link" href="{{ route('materials.operations.index') }}">
                                            <span class="sidebar-mini"><i class="pe-7s-note2 pe-7s-mini"></i></span>
                                            <span class="sidebar-normal">Операции</span>
                                        </a>
                                    </li>
                                @endif
                                @if(Auth::user()->can('material_accounting_material_list_access'))
                                    <li class="nav-item @if (Request::is('materials/material') || Request::is('materials/material/*')) active @endif">
                                        <a class="nav-link" href="{{ route('materials.index') }}">
                                            <span class="sidebar-mini"><i class="pe-7s-note2 pe-7s-mini"></i></span>
                                            <span class="sidebar-normal">Материалы</span>
                                        </a>
                                    </li>
                                @endif
                                @if(Auth::user()->can('material_supply_planning_access'))
                                    <li class="nav-item @if (Request::is('materials/supply-planning') || Request::is('materials/supply-planning/*')) active @endif">
                                        <a class="nav-link" href="{{ route('materials.supply-planning.index') }}">
                                            <span class="sidebar-mini"><i class="pe-7s-note2 pe-7s-mini"></i></span>
                                            <span class="sidebar-normal">Планирование поставок</span>
                                        </a>
                                    </li>
                                @endif
                                @if(Auth::user()->can('material_accounting_material_table_access'))
                                    <li class="nav-item @if (Request::is('/materials/table') || Request::is('/materials/table/*')) active @endif">
                                        <a class="nav-link" href="{{ route('materials.table') }}">
                                            <span class="sidebar-mini"><i class="pe-7s-note2 pe-7s-mini"></i></span>
                                            <span class="sidebar-normal">Табель учета материалов</span>
                                        </a>
                                    </li>
                                @endif
                                @if(Auth::user()->can('material_accounting_material_remains_report_access'))
                                    <li class="nav-item @if (Request::is('/materials/remains') || Request::is('/materials/remains/*')) active @endif">
                                        <a class="nav-link" href="{{ route('materials.remains') }}">
                                            <span class="sidebar-mini"><i class="pe-7s-note2 pe-7s-mini"></i></span>
                                            <span class="sidebar-normal">Остатки материалов</span>
                                        </a>
                                    </li>
                                @endif
                                @if(Auth::user()->can('material_accounting_objects_remains_report_access'))
                                    <li class="nav-item @if (Request::is('/materials/objects-remains') || Request::is('/materials/objects-remains/*')) active @endif">
                                        <a class="nav-link" href="{{ route('materials.objects.remains') }}">
                                            <span class="sidebar-mini"><i class="pe-7s-note2 pe-7s-mini"></i></span>
                                            <span class="sidebar-normal">Остатки на объектах</span>
                                        </a>
                                    </li>
                                @endif

                                @if(Auth::user()->can('material_accounting_materials_standards_editing'))
                                    <li class="nav-item @if (Request::is('materials/material-standard') || Request::is('materials/material-standard/*')) active @endif">
                                        <a class="nav-link" href="{{ route('materials.standards.index') }}">
                                            <span class="sidebar-mini"><i class="pe-7s-diamond pe-7s-mini"></i></span>
                                            <span class="sidebar-normal">Эталоны</span>
                                        </a>
                                    </li>
                                @endif
                                @if(Auth::user()->can('material_accounting_materials_types_editing'))
                                    <li class="nav-item @if (Request::is('materials/material-type') || Request::is('materials/material-type/*')) active @endif">
                                        <a class="nav-link" href="{{ route('materials.types.index') }}">
                                            <span class="sidebar-mini"><i class="pe-7s-menu pe-7s-mini"></i></span>
                                            <span class="sidebar-normal">Типы материалов</span>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </li>
                @endif
                <li class="nav-item @if(Request::is('building/tech_acc/technic/*') ) active @endif">
                        <a class="nav-link" data-toggle="collapse" href="#technics">
                            <i class="pe-7s-note2"></i>
                            <p>Учёт техники
                                <b class="caret"></b>
                            </p>
                        </a>
                        <div class="collapse @if(Request::is('building/tech_acc/technic/*') ) show @endif"
                            id="technics">
                            <ul class="nav">
                                <li class="nav-item @if(Request::is('building/tech_acc/technic/ourTechnicList*')) active @endif">
                                    <a class="nav-link"
                                         href="{{ route('building::tech_acc::technic::ourTechnicList::getPageCore') }}">
                                        <span class="sidebar-mini">
                                            <i class="pe-7s-mini">
                                                <img src="{{ mix('img/crane.svg') }}" alt="" width="20" class="pull-left" style="margin-bottom: 5px">
                                            </i>
                                        </span>
                                        <span class="sidebar-normal">Список техники</span>
                                    </a>
                                </li>

                                <li class="nav-item @if(Request::is('building/tech_acc/technic/technicCategory*')) active @endif">
                                    <a class="nav-link"
                                         href="{{ route('building::tech_acc::technic::technicCategory::getPageCore') }}">
                                        <span class="sidebar-mini">
                                            <i class="pe-7s-mini">
                                                <img src="{{ mix('img/crane.svg') }}" alt="" width="20" class="pull-left" style="margin-bottom: 5px">
                                            </i>
                                        </span>
                                        <span class="sidebar-normal">Категории техники</span>
                                    </a>
                                </li>

                                <li class="nav-item @if(Request::is('building/tech_acc/technic/technicBrand*')) active @endif">
                                    <a class="nav-link"
                                         href="{{ route('building::tech_acc::technic::technicBrand::getPageCore') }}">
                                        <span class="sidebar-mini">
                                            <i class="pe-7s-mini">
                                                <img src="{{ mix('img/crane.svg') }}" alt="" width="20" class="pull-left" style="margin-bottom: 5px">
                                            </i>
                                        </span>
                                        <span class="sidebar-normal">Марки техники</span>
                                    </a>
                                </li>

                                <li class="nav-item @if(Request::is('building/tech_acc/technic/technicBrandModel*')) active @endif">
                                    <a class="nav-link"
                                         href="{{ route('building::tech_acc::technic::technicBrandModel::getPageCore') }}">
                                        <span class="sidebar-mini">
                                            <i class="pe-7s-mini">
                                                <img src="{{ mix('img/crane.svg') }}" alt="" width="20" class="pull-left" style="margin-bottom: 5px">
                                            </i>
                                        </span>
                                        <span class="sidebar-normal">Модели техники</span>
                                    </a>
                                </li>

                            </ul>
                        </div>
                </li>
<!-- СТАРЫЙ РАЗДЕЛ ТЕХНИКИ
                    <li  style="background: grey;" class="nav-item active">
                        <a class="nav-link" data-toggle="collapse" href="#technics-old">
                            <i class="pe-7s-note2"></i>
                            <p>Учёт техники - OLD
                                <b class="caret"></b>
                            </p>
                        </a>
                        <div
                            class="collapse @if((Request::is('building/tech_acc/*') || Request::is('building/tech_acc/') || Request::is('building/vehicles/') || Request::is('building/vehicles/*')) && !(Request::is('building/tech_acc/fuel_tank') || Request::is('building/tech_acc/fuel_tank/*') || Request::is('building/tech_acc/fuel_tank_operations') || Request::is('building/tech_acc/fuel_tank_operations/*'))) show @endif"
                            id="technics-old">
                            <ul class="nav">
                                <li class="nav-item @if (Request::is('building/tech_acc/technic_category') || Request::is('building/tech_acc/technic_category/*')) active @endif">
                                    <a class="nav-link"
                                       href="{{ route('building::tech_acc::technic_category.index') }}">
                                        <span class="sidebar-mini"><i class="pe-7s-mini"><img
                                                    src="{{ mix('img/crane.svg') }}" alt="" width="20" class="pull-left"
                                                    style="margin-bottom: 5px"></i></span>
                                        <span class="sidebar-normal">Техника</span>
                                    </a>
                                </li>
                                <li class="nav-item @if(Request::is('building/vehicles/*') || Request::is('building/vehicles/')) active @endif">
                                    <a class="nav-link"
                                       href="{{ route('building::vehicles::vehicle_categories.index') }}">
                                        <span class="sidebar-mini"><img src="{{ mix('img/delivery-truck.svg') }}" alt=""
                                                                        width="20" class="pull-left"
                                                                        style="margin-bottom: 5px; opacity: 0.56"></span>
                                        <span class="sidebar-normal">Транспорт</span>
                                    </a>
                                </li>
                                @canany(['tech_acc_our_technic_tickets_see', 'tech_acc_see_technic_ticket_module'])
                                    <li class="nav-item @if (Request::is('building/tech_acc/our_technic_tickets') || Request::is('building/tech_acc/our_technic_tickets/*')) active @endif">
                                        <a class="nav-link"
                                           href="{{ route('building::tech_acc::our_technic_tickets.index') }}">
                                            <span class="sidebar-mini"><i class="pe-7s-config pe-7s-mini"></i></span>
                                            <span class="sidebar-normal">Заявки на технику</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('tech_acc_defects_see')
                                    <li class="nav-item @if (Request::is('building/tech_acc/defects') || Request::is('building/tech_acc/defects/*')) active @endif">
                                        <a class="nav-link"
                                           href="{{ route('building::tech_acc::defects.index') }}">
                                            <span class="sidebar-mini"><i class="pe-7s-mini"><img
                                                        src="{{ mix('img/wrench.svg') }}" alt="" width="17"
                                                        class="pull-left" style="margin-bottom: 5px"></i></span>
                                            <span class="sidebar-normal">Неисправности</span>
                                        </a>
                                    </li>

                            </ul>
                            @endcan
                        </div>
                    </li>
-->
<!-- СТРЫЙ РАЗДЕЛ ТЕХНИКИ КОНЕЦ -->
<!-- НОВЫЙ ТОПЛИВНЫЙ РАЗДЕЛ -->
            @canany([
                'fuel_tanks_access', 
                'fuel_tank_flows_access', 
                'fuel_tank_operations_report_advanced_filter_settings_access', 
                'fuel_tanks_movements_report_access'
                ])
                <li class="nav-item @if(Request::is('building/tech_acc/fuel/*') ) active @endif">
                        <a class="nav-link" data-toggle="collapse" href="#fuel">
                            <i class="pe-7s-note2"></i>
                            <p>Учёт топлива
                                <b class="caret"></b>
                            </p>
                        </a>
                        <div class="collapse @if(Request::is('building/tech_acc/fuel/*') ) show @endif"
                            id="fuel">
                            <ul class="nav">
                                @can('fuel_tanks_access')
                                    <li class="nav-item @if(Request::is('building/tech_acc/fuel/tank*')) active @endif">
                                        <a class="nav-link"
                                            href="{{ route('building::tech_acc::fuel::tanks::getPageCore') }}">
                                            <span class="sidebar-mini"><i class="pe-7s-paint-bucket pe-7s-mini"></i></span>
                                            <span class="sidebar-normal">Топливные ёмкости</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('fuel_tank_flows_access')
                                    <li class="nav-item @if (Request::is('building/tech_acc/fuel/fuelFlow*') ) active @endif">
                                        <a class="nav-link"
                                        href="{{ route('building::tech_acc::fuel::fuelFlow::getPageCore') }}">
                                            <span class="sidebar-mini"><i class="pe-7s-drop pe-7s-mini"></i></span>
                                            <span class="sidebar-normal">Топливный журнал</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('fuel_tank_operations_report_advanced_filter_settings_access')
                                    <li class="nav-item @if (Request::is('building/tech_acc/fuel/reports/fuelTankPeriodReport*') ) active @endif">
                                        <a class="nav-link"
                                        href="{{ route('building::tech_acc::fuel::reports::fuelTankPeriodReport::getPageCore') }}">
                                            <span class="sidebar-mini"><i class="pe-7s-news-paper pe-7s-mini"></i></span>
                                            <span class="sidebar-normal">Отчет по топливу</span>
                                        </a>
                                    </li>
                                @endcan

                                <!-- <li class="nav-item @if (Request::is('building/tech_acc/fuel/reports/fuelFlowMacroReport*') ) active @endif">
                                    <a class="nav-link"
                                       href="{{ route('building::tech_acc::fuel::reports::fuelFlowMacroReport::getPageCore') }}">
                                        <span class="sidebar-mini"><i class="pe-7s-news-paper pe-7s-mini"></i></span>
                                        <span class="sidebar-normal">Оборотная ведомость<br>по всем ёмкостям</span>
                                    </a>
                                </li> -->
                                @can('fuel_tanks_movements_report_access')
                                    <li class="nav-item @if (Request::is('building/tech_acc/fuel/reports/tanksMovementReport*') ) active @endif">
                                        <a class="nav-link"
                                        href="{{ route('building::tech_acc::fuel::reports::tanksMovementReport::getPageCore') }}">
                                            <span class="sidebar-mini"><i class="pe-7s-news-paper pe-7s-mini"></i></span>
                                            <span class="sidebar-normal">Перемещение ёмкостей</span>
                                        </a>
                                    </li>
                                @endcan
                                <hr>

                            </ul>
                        </div>
                </li>
            @endcanany
    <!-- НОВЫЙ ТОПЛИВНЫЙ РАЗДЕЛ -->
    <!-- СТАРЫЙ ТОПЛИВНЫЙ РАЗДЕЛ

                @can('store', 'App\Models\TechAcc\FuelTank\FuelTank')
                    <li style="background: grey;" class="nav-item @if(Request::is('building/tech_acc/fuel_tank') || Request::is('building/tech_acc/fuel_tank/*') || Request::is('building/tech_acc/fuel_tank_operations') || Request::is('building/tech_acc/fuel_tank_operations/*')) active @endif">
                        <a class="nav-link" data-toggle="collapse" href="#oldfuel">
                            <i class="pe-7s-note2"></i>
                            <p>Учёт топлива - OLD
                                <b class="caret"></b>
                            </p>
                        </a>
                        <div
                            class="collapse @if(Request::is('building/tech_acc/fuel_tank/*') || Request::is('building/tech_acc/fuel_tank') || Request::is('building/tech_acc/fuel_tank_operations') || Request::is('building/tech_acc/fuel_tank_operations/*')) show @endif"
                            id="oldfuel">
                            <ul class="nav">
                                <li class="nav-item @if(Request::is('building/tech_acc/fuel_tank') || Request::is('building/tech_acc/fuel_tank/*')) active @endif">
                                    <a class="nav-link" href="{{ route('building::tech_acc::fuel_tank.index') }}">
                                        <span class="sidebar-mini"><i class="pe-7s-paint-bucket pe-7s-mini"></i></span>
                                        <span class="sidebar-normal">Топливные ёмкости</span>
                                    </a>
                                </li>
                                <li class="nav-item @if (Request::is('building/tech_acc/fuel_tank_operations') || Request::is('building/tech_acc/fuel_tank_operations/*')) active @endif">
                                    <a class="nav-link"
                                       href="{{ route('building::tech_acc::fuel_tank_operations.index') }}">
                                        <span class="sidebar-mini"><i class="pe-7s-drop pe-7s-mini"></i></span>
                                        <span class="sidebar-normal">Топливный журнал</span>
                                    </a>
                                </li>
                                <hr>
                            </ul>
                        </div>
                    </li>
                @endcan -->
                @if(Gate::check('project_documents') || Gate::check('commercial_offers') || Gate::check('work_volumes') || Gate::check('contracts') || Gate::check('project_object_documents_access'))
                    <li class="nav-item @if(Request::is('project_documents') || Request::is('project_documents/*') || Request::is('commercial_offers') || Request::is('commercial_offers/*') || Request::is('contracts') || Request::is('contracts/*') || Request::is('work_volumes') || Request::is('work_volumes/*')) active @endif">
                        <a class="nav-link" data-toggle="collapse" href="#documentsExamples">
                            <i class="pe-7s-folder"></i>
                            <p>Документооборот
                                <b class="caret"></b>
                            </p>
                        </a>
                        <div
                            class="collapse @if(Request::is('project-object-documents') || Request::is('project_documents') || Request::is('project_documents/*') || Request::is('commercial_offers') || Request::is('commercial_offers/*') || Request::is('contracts') || Request::is('contracts/*') || Request::is('work_volumes') || Request::is('work_volumes/*')) show @endif"
                            id="documentsExamples">
                            <ul class="nav">
                                @can('project_object_documents_access')
                                    <li class="nav-item @if (Request::is('project-object-documents') || Request::is('project-object-documents/*')) active @endif">
                                        <a class="nav-link" href="{{ route('project-object-documents') }}">
                                            <span class="sidebar-mini">ПО</span>
                                            <span class="sidebar-normal">Площадка ⇆ Офис</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('project_documents')
                                    <li class="nav-item @if (Request::is('project_documents') || Request::is('project_documents/*')) active @endif">
                                        <a class="nav-link" href="{{ route('project_documents::index') }}">
                                            <span class="sidebar-mini">ПД</span>
                                            <span class="sidebar-normal">Проектная документация</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('commercial_offers')
                                    <li class="nav-item @if (Request::is('commercial_offers') || Request::is('commercial_offers/*')) active @endif">
                                        <a class="nav-link" href="{{ route('commercial_offers::index') }}">
                                            <span class="sidebar-mini">КП</span>
                                            <span class="sidebar-normal">Коммерч. предложения</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('contracts')
                                    <li class="nav-item @if (Request::is('contracts') || Request::is('contracts/*'))  active @endif">
                                        <a class="nav-link" href="{{ route('contracts::index') }}">
                                            <span class="sidebar-mini">Д</span>
                                            <span class="sidebar-normal">Договоры</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('work_volumes')
                                    <li class="nav-item @if (Request::is('work_volumes') || Request::is('work_volumes/*')) active @endif">
                                        <a class="nav-link" href="{{ route('work_volumes::index') }}">
                                            <span class="sidebar-mini">ОР</span>
                                            <span class="sidebar-normal">Объёмы работ</span>
                                        </a>
                                    </li>

                                @endcan

                                <hr>
                            </ul>
                        </div>
                    </li>
                @endif
                @if(Auth::user()->is_su ||
                    Gate::check('labor_safety_order_creation') ||
                    Gate::check('labor_safety_order_list_access') ||
                    Gate::check('labor_safety_order_types_editing'))
                    <li class="nav-item @if(Request::is('labor-safety') || Request::is('labor-safety/*')) active @endif">
                        <a class="nav-link" data-toggle="collapse" href="#labor-safety">
                            <i class="pe-7s-folder"></i>
                            <p>Охрана труда
                                <b class="caret"></b>
                            </p>
                        </a>
                        <div
                            class="collapse @if(Request::is('labor-safety/') || Request::is('labor-safety/*')) show @endif"
                            id="labor-safety">
                            <ul class="nav">
                                @can('labor_safety_order_creation')
                                    <li class="nav-item @if (Request::is('labor-safety/orders-and-requests')) active @endif">
                                        <a class="nav-link" href="{{ route('labor-safety.orders-and-requests.index') }}">
                                            <span class="sidebar-mini"><i class="fas fa-envelope"></i></span>
                                            <span class="sidebar-normal">Заявки и приказы</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('labor_safety_order_types_editing')
                                    <li class="nav-item @if (Request::is('labor-safety/templates')) active @endif">
                                        <a class="nav-link" href="{{ route('labor-safety.order-types.index') }}">
                                            <span class="sidebar-mini"><i class="fas fa-envelope"></i></span>
                                            <span class="sidebar-normal">Шаблоны приказов</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </div>
                    </li>
                @endif
                @if(Auth::user()->is_su)
                    <li class="nav-item @if(Request::is('admin') || Request::is('admin/*')) active @endif">
                        <a class="nav-link" data-toggle="collapse" href="#admin">
                            <i class="pe-7s-folder"></i>
                            <p>Администрирование
                                <b class="caret"></b>
                            </p>
                        </a>
                        <div
                            class="collapse @if(Request::is('admin/notifications')) show @endif"
                            id="admin">
                            <ul class="nav">
                                <li class="nav-item @if (Request::is('admin/notifications')) active @endif">
                                    <a class="nav-link" href="{{ route('admin.notifications') }}">
                                        <span class="sidebar-mini"><i class="fas fa-envelope"></i></span>
                                        <span class="sidebar-normal">Рассылка уведомлений</span>
                                    </a>
                                </li>
                                <li class="nav-item @if (Request::is('/admin/validate-material-accounting-data')) active @endif">
                                    <a class="nav-link" href="{{ route('admin.validate-material-accounting_data') }}">
                                        <span class="sidebar-mini"><i class="fas fa-check"></i></span>
                                        <span class="sidebar-normal">Проверка мат. учета</span>
                                    </a>
                                </li>
                                <li class="nav-item @if (Request::is('/admin/permissions')) active @endif">
                                    <a class="nav-link" href="{{ route('admin.permissions') }}">
                                        <span class="sidebar-mini"><i class="fas fa-check"></i></span>
                                        <span class="sidebar-normal">Управление доступами</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif
                {{--                    @canany(['human_resources_job_categories_view', 'human_resources_report_groups_view', 'human_resources_timecards_view'])--}}
                {{--                        <li class="nav-item @if(Request::is('human_resources') || Request::is('human_resources/*')) active @endif">--}}
                {{--                            <a class="nav-link" data-toggle="collapse" href="#humanResources">--}}
                {{--                                <i class="pe-7s-users"></i>--}}
                {{--                                <p>Человеческие ресурсы--}}
                {{--                                    <b class="caret"></b>--}}
                {{--                                </p>--}}
                {{--                            </a>--}}
                {{--                            <div class="collapse @if(Request::is('human_resources') || Request::is('human_resources/*')) show @endif" id="humanResources">--}}
                {{--                                <ul class="nav">--}}
                {{--                                    @can('human_resources_job_categories_view')--}}
                {{--                                        <li class="nav-item @if (Request::is('human_resources/job_category') || Request::is('human_resources/job_category/*')) active @endif">--}}
                {{--                                            <a class="nav-link" href="{{ route('human_resources.job_category.index') }}">--}}
                {{--                                                <span class="sidebar-mini">ДК</span>--}}
                {{--                                                <span class="sidebar-normal">Должностные категории</span>--}}
                {{--                                            </a>--}}
                {{--                                        </li>--}}
                {{--                                    @endcan--}}
                {{--                                    @can('human_resources_report_group_view')--}}
                {{--                                        <li class="nav-item @if (Request::is('human_resources/report_group') || Request::is('human_resources/report_group/*')) active @endif">--}}
                {{--                                            <a class="nav-link" href="{{ route('human_resources.report_group.index') }}">--}}
                {{--                                                <span class="sidebar-mini">ОГ</span>--}}
                {{--                                                <span class="sidebar-normal">Отчётные группы</span>--}}
                {{--                                            </a>--}}
                {{--                                        </li>--}}
                {{--                                    @endcan--}}
                {{--                                    @can('human_resources_brigade_view')--}}
                {{--                                        <li class="nav-item @if (Request::is('human_resources/brigade') || Request::is('human_resources/brigade/*')) active @endif">--}}
                {{--                                            <a class="nav-link" href="{{ route('human_resources.brigade.index') }}">--}}
                {{--                                                <span class="sidebar-mini">БР</span>--}}
                {{--                                                <span class="sidebar-normal">Бригады</span>--}}
                {{--                                            </a>--}}
                {{--                                        </li>--}}
                {{--                                    @endcan--}}
                {{--                                    @can('human_resources_pay_and_hold_see')--}}
                {{--                                        <li class="nav-item @if (Request::is('human_resources/payment') || Request::is('human_resources/payment/*')) active @endif">--}}
                {{--                                            <a class="nav-link" href="{{ route('human_resources.payment.index') }}">--}}
                {{--                                                <span class="sidebar-mini">ВУ</span>--}}
                {{--                                                <span class="sidebar-normal">Выплаты и удержания</span>--}}
                {{--                                            </a>--}}
                {{--                                        </li>--}}
                {{--                                    @endcan--}}
                {{--                                    @can('human_resources_timecards_view')--}}
                {{--                                        <li class="nav-item @if (Request::is('human_resources/report') || Request::is('human_resources/report/*')) active @endif">--}}
                {{--                                            <a class="nav-link" href="{{ route('human_resources.report.detailed_report') }}">--}}
                {{--                                                <span class="sidebar-mini"><i class="pe-7s-display2 pe-7s-mini"></i></span>--}}
                {{--                                                <span class="sidebar-normal">Табели</span>--}}
                {{--                                            </a>--}}
                {{--                                        </li>--}}
                {{--                                    @endcan--}}
                {{--                                </ul>--}}
                {{--                            </div>--}}
                {{--                        </li>--}}
                {{--                    @endcanany--}}
                @can('users')
                    <li class="nav-item @if (Request::is('users') || Request::is('users/*')) active @endif">
                        <a class="nav-link" href="{{ route('users::index') }}">
                            <i class="pe-7s-id"></i>
                            <p>Сотрудники</p>
                        </a>
                    </li>
                @endcan
            </ul>
            <ul class="nav" style="margin-top: auto; margin-bottom: 20px;">
                <li class="nav-item @if (Request::is('support') || Request::is('support/*')) active @endif"
                    style="width:100%">
                    <a class="nav-link" href="{{ route('support::index') }}">
                        <i class="pe-7s-help1"></i>
                        <p>Техническая поддержка</p>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="main-panel">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg ">
            <div class="container-fluid">
                <div class="navbar-wrapper">
                    <div class="navbar-minimize">
                        <button id="minimizeSidebar"
                                class="btn btn-warning btn-fill btn-round btn-icon d-none d-lg-block">
                            <i class="fa fa-ellipsis-v visible-on-sidebar-regular"></i>
                            <i class="fa fa-navicon visible-on-sidebar-mini"></i>
                        </button>
                    </div>
                    <a class="navbar-brand" href=@yield('url')>@yield('title')</a>
                </div>
                <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse"
                        aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-bar burger-lines"></span>
                    <span class="navbar-toggler-bar burger-lines"></span>
                    <span class="navbar-toggler-bar burger-lines"></span>
                </button>
                <div class="collapse navbar-collapse justify-content-end">
                    <ul class="nav navbar-nav mr-auto">
                    </ul>
                    <ul class="navbar-nav">
                        <li class="dropdown nav-item">
                            <a id="messages" target="_blank" href="{{ route('messages::index') }}" class="nav-link">
                                <i class="nc-icon nc-chat-round"></i>
                                <span class="d-lg-none">Диалоги</span>
                                <span-message-count v-bind:messages="messages"></span-message-count>
                            </a>
                        </li>
                        <li class="dropdown nav-item">
                            <a id="main" href="{{ route('notifications::index') }}" class="nav-link">
                                <i class="nc-icon nc-bell-55"></i>
                                <span class="d-lg-none">Оповещения</span>
                                <span-notify v-bind:notify="notify"></span-notify>
                            </a>
                        </li>
                        <li class="dropdown nav-item support-letter">
                            <a href="#" onclick="modalWork()" data-original-title="Написать в тех. поддержку"
                               class="nav-link">
                                <i class="nc-icon nc-send"></i>
                                <span class="d-lg-none">Написать в тех. поддержку</span>
                            </a>
                        </li>
                        <li class="dropdown nav-item">
                            <a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
                                {{ Auth::user()->last_name }} {{ Auth::user()->first_name }} {{ Auth::user()->patronymic }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <a href="{{ route('users::card', Auth::user()->id) }}" class="dropdown-item">
                                    <i class="nc-icon nc-single-02"></i> Профиль
                                </a>
                                <!-- <a id="messages" target="_blank" href="{{ route('messages::index') }}" class="dropdown-item" style="position:relative">
                                        <i class="nc-icon nc-chat-round"></i>
                                        <span>Диалоги</span>
                                        <span-message-count v-bind:messages="messages" style="top:5px"></span-message-count>
                                    </a> -->
                                <a href="{{ route('logout') }}" class="dropdown-item text-exit">
                                    <i class="nc-icon nc-button-power"></i> Выйти
                                </a>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <!-- End Navbar -->

        <div class="content">
            <div class="container-fluid pd-0-360">
                @yield('content')

                @include('support.support_modal')
            </div>
        </div>

        <footer class="footer" style="float: bottom;">
            <nav>
                <p class="copyright text-center">
                    <span>© {{ date('Y') }}</span>
                    <a href="https://sk-gorod.com">ООО «СК ГОРОД»</a>
                </p>
            </nav>
            <nav>
                <p class="copyright text-center">
                    <a id="modal_open" data-toggle="modal" data-target="#support_modal"
                       data-original-title="Написать в тех. поддержку" class="support-modal">Техническая поддержка</a>
                </p>
            </nav>
        </footer>

    </div>
</div>
</body>
<!--   Core JS Files   -->
<script src="{{ mix('js/core/jquery.3.2.1.min.js') }}" type="text/javascript"></script>
<script src="{{ mix('js/jquery.table.js') }}" type="text/javascript"></script>
<script src="{{ mix('js/core/popper.min.js') }}" type="text/javascript"></script>
<script src="{{ mix('js/core/bootstrap.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/common.js') }}"></script>

<script src="{{ mix('js/plugins/bootstable.js') }}"></script>
<!--  Plugin for Switches, full documentation here: http://www.jque.re/plugins/version3/bootstrap.switch/ -->
<script src="{{ mix('js/plugins/bootstrap-switch.js') }}"></script>
<!--  Google Maps Plugin    -->
<!-- <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?YOUR_KEY_HERE"></script> -->
<!--  Chartist Plugin  -->
<script src="{{ mix('js/plugins/chartist.min.js') }}"></script>
<!--  Notifications Plugin    -->
<script src="{{ mix('js/plugins/bootstrap-notify.js') }}"></script>
<!--  jVector Map  -->
<script src="{{ mix('js/plugins/jquery-jvectormap.js') }}" type="text/javascript"></script>
<!--  Plugin for Date Time Picker and Full Calendar Plugin-->
<script src="{{ mix('js/plugins/moment.min.js') }}"></script>
<script>moment.locale('ru');</script>
<!--  DatetimePicker   -->
<script src="{{ mix('js/plugins/bootstrap-datetimepicker.js') }}"></script>
<!--  Sweet Alert  -->
<script src="{{ mix('js/plugins/sweetalert2.all.min.js') }}" type="text/javascript"></script>
<!--  Tags Input  -->
<script src="{{ mix('js/plugins/bootstrap-tagsinput.js') }}" type="text/javascript"></script>
<!--  Sliders  -->
<script src="{{ mix('js/plugins/nouislider.js') }}" type="text/javascript"></script>
<!--  Bootstrap Select  -->
<script src="{{ mix('js/plugins/bootstrap-selectpicker.js') }}" type="text/javascript"></script>
<!--  jQueryValidate  -->
<script src="{{ mix('js/plugins/jquery.validate.min.js') }}" type="text/javascript"></script>
<!--  Plugin for the Wizard, full documentation here: https://github.com/VinceG/twitter-bootstrap-wizard -->
<script src="{{ mix('js/plugins/jquery.bootstrap-wizard.js') }}"></script>
<!--  Bootstrap Table Plugin -->
<script src="{{ mix('js/plugins/bootstrap-table.js') }}"></script>
<!--  DataTable Plugin -->
<script src="{{ mix('js/plugins/jquery.dataTables.min.js') }}"></script>
<!--  Full Calendar   -->
<script src="{{ mix('js/plugins/fullcalendar.min.js') }}"></script>
<!-- Control Center for Now Ui Dashboard: parallax effects, scripts for the example pages etc -->
<script src="{{ mix('js/light-bootstrap-dashboard.js') }}" type="text/javascript"></script>
<!-- Select2 Essentials and Localization -->
<script src="{{ asset('js/select2.js') }}" type="text/javascript"></script>
<!-- Light Dashboard DEMO methods, don't include it in your project! -->
<script src="{{ mix('js/demo.js') }}"></script>
<script src="{{ mix('js/plugins/bootstrap-table-mobile.js') }}"></script>
<!--Main scripts-->
<script src="{{ mix('js/modal-window.js') }}"></script>
<!-- Validation-form-ru-locale -->
<script src="{{ mix('js/form-validation.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/plugins/jquery.mask.min.js') }}"></script>
<!-- AutoNumeric -->
<script src="{{ asset('js/plugins/autonumeric.min.js') }}"></script>

<!-- Fix Multi Submit -->
<script src="{{ mix('js/fixMultiSubmit.js') }}"></script>

<!-- Vue and Pusher to get fast Notifications -->

{{--<script src="https://js.pusher.com/4.4/pusher.min.js"></script>--}}
<script src="{{ asset('js/plugins/pusher_and_vue.js') }}"></script>

<!-- import JavaScript -->
<script src="{{ asset('js/vue.js') }}"></script>
<script src="{{ asset('js/elementui.js') }}"></script>

<!-- fix select search-->
<script src="{{ mix('js/vue-router.js') }}"></script>
<script src="{{ mix('js/plugins/vee-validate.js') }}" type="text/javascript"></script>
<script src="{{ mix('js/validation-rules.js') }}" type="text/javascript"></script>
<!--  -->

<script src="{{ mix('js/axios.min.js') }}"></script>
<script src="{{ asset('js/ru-RU.js') }}"></script>

{{-- <!-- import FontAwesome (fresh version of icons) -->
<script src="{{ asset('js/92abca6220.js') }}"></script> --}}
<!-- import Lodash -->
<script src="{{ asset('js/plugins/lodash.js') }}"></script>
<script src="{{ asset('js/plugins/download.js') }}"></script>


<script src="{{ asset('js/plugins/emojione.js') }}"></script>
<script src="{{ asset('js/plugins/emojionearea.min.js') }}"></script>


<!-- DevExtreme themes -->
<link rel="stylesheet" href="{{ asset('css/devextreme/dx.common.css')}}">
@if (
        Request::is('project-object-documents')
        || Request::is('objects')
        || Request::is('building/tech_acc/technic*')
        || Request::is('building/tech_acc/fuel*')
    )
    <link rel="stylesheet" href="{{ asset('css/devextreme/dx.generic.light.css')}}">
@else
    <link rel="stylesheet" href="{{ asset('css/devextreme/dx.material.blue.light.compact.css')}}">
@endif



<!-- DevExtreme library -->
<script src="https://unpkg.com/devextreme-quill@1.5.16/dist/dx-quill.min.js"></script>
<script type="text/javascript" src="{{ asset('js/devextreme/dx.all.js')}}"></script>
<!-- DevExtreme localization -->
<script type="text/javascript" src="{{ asset('js/devextreme/dx.messages.ru.js')}}"></script>

<!-- DevExtreme localization -->
<script type="text/javascript" src="{{ asset('js/devextreme/dx.messages.ru.js')}}"></script>

<!-- lightgalleryjs.com -->
<script type="text/javascript" src="{{ asset('js/lightgallery/lightgallery.umd.js')}}"></script>
<script type="text/javascript" src="{{ asset('js/lightgallery/lg-thumbnail.umd.js')}}"></script>
<script type="text/javascript" src="{{ asset('js/lightgallery/lg-zoom.umd.js')}}"></script>
<script type="text/javascript" src="{{ asset('js/lightgallery/lg-rotate.umd.js')}}"></script>
<script type="text/javascript" src="{{ asset('js/lightgallery/lg-video.umd.js')}}"></script>

<!-- END lightgalleryjs.com -->


<meta name="csrf-token" content="{{ csrf_token() }}"/>
<script>
    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    ELEMENT.locale(ELEMENT.lang.ruRU);

    DevExpress.localization.locale(navigator.language);
    DevExpress.config({
        editorStylingMode: "outlined",
        forceIsoDateParsing: true
    })

    function iOS() {
        return [
                'iPad Simulator',
                'iPhone Simulator',
                'iPod Simulator',
                'iPad',
                'iPhone',
                'iPod'
            ].includes(navigator.platform)
            // iPad on iOS 13 detection
            || (navigator.userAgent.includes("Mac") && "ontouchend" in document)
    }

    if (iOS()) {
        ELEMENT.Select.computed.readonly = function () {
            // trade-off for IE input readonly problem: https://github.com/ElemeFE/element/issues/10403
            const isIE = !this.$isServer && !Number.isNaN(Number(document.documentMode));

            return !(this.filterable || this.multiple || !isIE) && !this.visible;
        };
    }

    setInterval(function () {
        var csrfToken = $('[name="csrf-token"]').val();
        $.ajax({
            url: '{{ route('get-new-csrf') }}',
            type: 'get'
        }).done(function (data) {
            csrfToken = data;
        });
    }, 1 * 60 * 60 * 1000);

    Vue.component('span-notify', {
        props: ['notify'],
        template: '<span class="notification" v-if="notify">@{{ notify }}</span>'
    });

    let notify = new Vue({
        el: '#main',

        data: {
            notify: {{ $notifications }}
        }
    });

    $(document).on('submit', 'form', function (event) {
        var form = $(event.currentTarget);
        if (form.hasClass('axios')) return;
        if (form.find('[type="file"]').length > 0) {

            var show_swal = false;

            form.find('[type="file"]').each(function () {
                if ($(this)[0].files.length > 0) {
                    show_swal = true;
                }
            });
            if (show_swal) {
                swal.fire({
                    title: 'Идёт загрузка',
                    html:
                        '<div class="fa-4x">\n' +
                        '<i class="fa fa-spinner fa-spin" style="width: auto"></i>\n' +
                        '</div>',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    showConfirmButton: false,
                })
            }
        }
    });

    $('input').attr('autocomplete', 'off');

    var pusher = new Pusher('13460ea482ed2f33ee58', {
        cluster: 'eu',
        forceTLS: true
    });

    var channel = pusher.subscribe('{{config('app.env')}}' + '.App.User.' + {{ Auth::user()->id }});
    channel.bind('App\\Events\\NotificationCreated', function (response) {
        notify.notify = response['notifications'];
    });

    Vue.component('span-message-count', {
        props: ['messages'],
        template: '<span class="notification" v-if="messages">@{{ messages }}</span>'
    });

    var message_notifications = new Vue({
        el: '#messages',
        data: {
            messages: {{ $messages }}
        }
    });

    channel.bind('message-stored', function (data) {
        // thread not currently opened => create notification
        $.ajax({
            url: '{{ route('messages::message_info') }}',
            type: 'POST',
            data: {
                _token: CSRF_TOKEN,
                message_id: data.messageData.message_id
            },
            dataType: 'JSON',
            success: function (response) {
                var message = '<strong>' + response.sender_name + ': </strong><br>' + response.text + (response.text.length > 50 ? '...' : '') + '<br><a href="' + response.thread_url + '" class="text-right">Просмотреть</a>';
                var thread_subject = response.thread_subject;

                // notify user
                message_notifications.$notify({
                    dangerouslyUseHTMLString: true,
                    message: 'Новое сообщение' + (thread_subject ? ' из чата ' + thread_subject : '') + '. ' + '<br>' + message,
                    type: 'info',
                    duration: 5000
                });
            }
        });

        message_notifications.messages = data.messagesCount;
    });

    channel.bind('message-deleted', function (data) {
        // -1 because event overtake message deleting
        message_notifications.messages = data.messagesCount - 1;
    });

    $(document).ready(function () {
        $('a[href$="mat_acc/operations"]').each(function () {
            const cookies = document.cookie.split(';').filter(el => el.indexOf('opsource') !== -1);
            if (cookies.length > 0) {
                $(this).attr("href", decodeURIComponent(cookies[0].split('=')[1]));
            }
        });
    });
</script>

@yield('js_footer')
@stack('js_footer')
@include('sections.yandex_metrika')

</html>
