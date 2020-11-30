@extends('layouts.app')

@section('title', 'Материалы')

@section('url', route('materials.index'))

@section('css_top')
    <style>
        .dx-command-expand {
            border-left: none !important;
        }

        .cd-horizontal-timeline {
            opacity: 0;
            margin: 2em auto;
            -webkit-transition: opacity 0.2s;
            -moz-transition: opacity 0.2s;
            transition: opacity 0.2s;
        }

        .cd-horizontal-timeline::before {
            /* never visible - this is used in jQuery to check the current MQ */
            content: 'mobile';
            display: none;
        }

        .cd-horizontal-timeline.loaded {
            /* show the timeline after events position has been set (using JavaScript) */
            opacity: 1;
        }

        .cd-horizontal-timeline .timeline {
            position: relative;
            height: 100px;
            width: 90%;
            max-width: 800px;
            margin: 0 auto;
        }

        .cd-horizontal-timeline .events-wrapper {
            position: relative;
            height: 100%;
            margin: 0 40px;
            overflow: hidden;
        }

        .cd-horizontal-timeline .events-wrapper::after, .cd-horizontal-timeline .events-wrapper::before {
            /* these are used to create a shadow effect at the sides of the timeline */
            content: '';
            position: absolute;
            z-index: 2;
            top: 0;
            height: 100%;
            width: 20px;
        }

        .cd-horizontal-timeline .events-wrapper::before {
            left: 0;
            background-image: -webkit-linear-gradient(left, #f8f8f8, rgba(248, 248, 248, 0));
            background-image: linear-gradient(to right, #f8f8f8, rgba(248, 248, 248, 0));
        }

        .cd-horizontal-timeline .events-wrapper::after {
            right: 0;
            background-image: -webkit-linear-gradient(right, #f8f8f8, rgba(248, 248, 248, 0));
            background-image: linear-gradient(to left, #f8f8f8, rgba(248, 248, 248, 0));
        }

        .cd-horizontal-timeline .events {
            /* this is the grey line/timeline */
            position: absolute;
            z-index: 1;
            left: 0;
            top: 49px;
            height: 2px;
            /* width will be set using JavaScript */
            background: #dfdfdf;
            -webkit-transition: -webkit-transform 0.4s;
            -moz-transition: -moz-transform 0.4s;
            transition: transform 0.4s;
        }

        .cd-horizontal-timeline .filling-line {
            /* this is used to create the green line filling the timeline */
            position: absolute;
            z-index: 1;
            left: 0;
            top: 0;
            height: 100%;
            width: 100%;
            background-color: #7b9d6f;
            -webkit-transform: scaleX(0);
            -moz-transform: scaleX(0);
            -ms-transform: scaleX(0);
            -o-transform: scaleX(0);
            transform: scaleX(0);
            -webkit-transform-origin: left center;
            -moz-transform-origin: left center;
            -ms-transform-origin: left center;
            -o-transform-origin: left center;
            transform-origin: left center;
            -webkit-transition: -webkit-transform 0.3s;
            -moz-transition: -moz-transform 0.3s;
            transition: transform 0.3s;
        }

        .cd-horizontal-timeline .events a {
            position: absolute;
            bottom: 0;
            z-index: 2;
            text-align: center;
            font-size: 1.3rem;
            padding-bottom: 15px;
            color: #383838;
            /* fix bug on Safari - text flickering while timeline translates */
            -webkit-transform: translateZ(0);
            -moz-transform: translateZ(0);
            -ms-transform: translateZ(0);
            -o-transform: translateZ(0);
            transform: translateZ(0);
        }

        .cd-horizontal-timeline .events a::after {
            /* this is used to create the event spot */
            content: '';
            position: absolute;
            left: 50%;
            right: auto;
            -webkit-transform: translateX(-50%);
            -moz-transform: translateX(-50%);
            -ms-transform: translateX(-50%);
            -o-transform: translateX(-50%);
            transform: translateX(-50%);
            bottom: -5px;
            height: 12px;
            width: 12px;
            border-radius: 50%;
            border: 2px solid #dfdfdf;
            background-color: #f8f8f8;
            -webkit-transition: background-color 0.3s, border-color 0.3s;
            -moz-transition: background-color 0.3s, border-color 0.3s;
            transition: background-color 0.3s, border-color 0.3s;
        }

        .no-touch .cd-horizontal-timeline .events a:hover::after {
            background-color: #7b9d6f;
            border-color: #7b9d6f;
        }

        .cd-horizontal-timeline .events a.selected {
            pointer-events: none;
        }

        .cd-horizontal-timeline .events a.selected::after {
            background-color: #7b9d6f;
            border-color: #7b9d6f;
        }

        .cd-horizontal-timeline .events a.older-event::after {
            border-color: #7b9d6f;
        }

        @media only screen and (min-width: 1100px) {
            .cd-horizontal-timeline {
                margin: 0em auto;
            }

            .cd-horizontal-timeline::before {
                /* never visible - this is used in jQuery to check the current MQ */
                content: 'desktop';
            }
        }

        .cd-timeline-navigation a {
            /* these are the left/right arrows to navigate the timeline */
            position: absolute;
            z-index: 1;
            top: 50%;
            bottom: auto;
            -webkit-transform: translateY(-50%);
            -moz-transform: translateY(-50%);
            -ms-transform: translateY(-50%);
            -o-transform: translateY(-50%);
            transform: translateY(-50%);
            height: 34px;
            width: 34px;
            border-radius: 50%;
            border: 2px solid #dfdfdf;
            /* replace text with an icon */
            overflow: hidden;
            color: transparent;
            text-indent: 100%;
            white-space: nowrap;
            -webkit-transition: border-color 0.3s;
            -moz-transition: border-color 0.3s;
            transition: border-color 0.3s;
        }

        .cd-timeline-navigation a::after {
            /* arrow icon */
            content: '';
            position: absolute;
            height: 16px;
            width: 16px;
            left: 50%;
            top: 50%;
            bottom: auto;
            right: auto;
            -webkit-transform: translateX(-50%) translateY(-50%);
            -moz-transform: translateX(-50%) translateY(-50%);
            -ms-transform: translateX(-50%) translateY(-50%);
            -o-transform: translateX(-50%) translateY(-50%);
            transform: translateX(-50%) translateY(-50%);
            background: url(../img/cd-arrow.svg) no-repeat 0 0;
        }

        .cd-timeline-navigation a.prev {
            left: 0;
            -webkit-transform: translateY(-50%) rotate(180deg);
            -moz-transform: translateY(-50%) rotate(180deg);
            -ms-transform: translateY(-50%) rotate(180deg);
            -o-transform: translateY(-50%) rotate(180deg);
            transform: translateY(-50%) rotate(180deg);
        }

        .cd-timeline-navigation a.next {
            right: 0;
        }

        .no-touch .cd-timeline-navigation a:hover {
            border-color: #7b9d6f;
        }

        .cd-timeline-navigation a.inactive {
            cursor: not-allowed;
        }

        .cd-timeline-navigation a.inactive::after {
            background-position: 0 -16px;
        }

        .no-touch .cd-timeline-navigation a.inactive:hover {
            border-color: #dfdfdf;
        }

        .cd-horizontal-timeline .events-content {
            position: relative;
            width: 100%;
            margin: 2em 0;
            overflow: hidden;
            -webkit-transition: height 0.4s;
            -moz-transition: height 0.4s;
            transition: height 0.4s;
        }

        .cd-horizontal-timeline .events-content li {
            position: absolute;
            z-index: 1;
            width: 100%;
            left: 0;
            top: 0;
            -webkit-transform: translateX(-100%);
            -moz-transform: translateX(-100%);
            -ms-transform: translateX(-100%);
            -o-transform: translateX(-100%);
            transform: translateX(-100%);
            padding: 0 5%;
            opacity: 0;
            -webkit-animation-duration: 0.4s;
            -moz-animation-duration: 0.4s;
            animation-duration: 0.4s;
            -webkit-animation-timing-function: ease-in-out;
            -moz-animation-timing-function: ease-in-out;
            animation-timing-function: ease-in-out;
        }

        .cd-horizontal-timeline .events-content li.selected {
            /* visible event content */
            position: relative;
            z-index: 2;
            opacity: 1;
            -webkit-transform: translateX(0);
            -moz-transform: translateX(0);
            -ms-transform: translateX(0);
            -o-transform: translateX(0);
            transform: translateX(0);
        }

        .cd-horizontal-timeline .events-content li.enter-right, .cd-horizontal-timeline .events-content li.leave-right {
            -webkit-animation-name: cd-enter-right;
            -moz-animation-name: cd-enter-right;
            animation-name: cd-enter-right;
        }

        .cd-horizontal-timeline .events-content li.enter-left, .cd-horizontal-timeline .events-content li.leave-left {
            -webkit-animation-name: cd-enter-left;
            -moz-animation-name: cd-enter-left;
            animation-name: cd-enter-left;
        }

        .cd-horizontal-timeline .events-content li.leave-right, .cd-horizontal-timeline .events-content li.leave-left {
            -webkit-animation-direction: reverse;
            -moz-animation-direction: reverse;
            animation-direction: reverse;
        }

        .cd-horizontal-timeline .events-content li > * {
            max-width: 800px;
            margin: 0 auto;
        }

        .cd-horizontal-timeline .events-content h2 {
            font-weight: bold;
            font-size: 2.6rem;
            font-family: "Playfair Display", serif;
            font-weight: 700;
            line-height: 1.2;
        }

        .cd-horizontal-timeline .events-content em {
            display: block;
            font-style: italic;
            margin: 10px auto;
        }

        .cd-horizontal-timeline .events-content em::before {
            content: '- ';
        }

        .cd-horizontal-timeline .events-content p {
            font-size: 1.4rem;
            color: #959595;
        }

        .cd-horizontal-timeline .events-content em, .cd-horizontal-timeline .events-content p {
            line-height: 1.6;
        }

        @media only screen and (min-width: 768px) {
            .cd-horizontal-timeline .events-content h2 {
                font-size: 7rem;
            }

            .cd-horizontal-timeline .events-content em {
                font-size: 2rem;
            }

            .cd-horizontal-timeline .events-content p {
                font-size: 1.8rem;
            }
        }

        @-webkit-keyframes cd-enter-right {
            0% {
                opacity: 0;
                -webkit-transform: translateX(100%);
            }
            100% {
                opacity: 1;
                -webkit-transform: translateX(0%);
            }
        }

        @-moz-keyframes cd-enter-right {
            0% {
                opacity: 0;
                -moz-transform: translateX(100%);
            }
            100% {
                opacity: 1;
                -moz-transform: translateX(0%);
            }
        }

        @keyframes cd-enter-right {
            0% {
                opacity: 0;
                -webkit-transform: translateX(100%);
                -moz-transform: translateX(100%);
                -ms-transform: translateX(100%);
                -o-transform: translateX(100%);
                transform: translateX(100%);
            }
            100% {
                opacity: 1;
                -webkit-transform: translateX(0%);
                -moz-transform: translateX(0%);
                -ms-transform: translateX(0%);
                -o-transform: translateX(0%);
                transform: translateX(0%);
            }
        }

        @-webkit-keyframes cd-enter-left {
            0% {
                opacity: 0;
                -webkit-transform: translateX(-100%);
            }
            100% {
                opacity: 1;
                -webkit-transform: translateX(0%);
            }
        }

        @-moz-keyframes cd-enter-left {
            0% {
                opacity: 0;
                -moz-transform: translateX(-100%);
            }
            100% {
                opacity: 1;
                -moz-transform: translateX(0%);
            }
        }

        @keyframes cd-enter-left {
            0% {
                opacity: 0;
                -webkit-transform: translateX(-100%);
                -moz-transform: translateX(-100%);
                -ms-transform: translateX(-100%);
                -o-transform: translateX(-100%);
                transform: translateX(-100%);
            }
            100% {
                opacity: 1;
                -webkit-transform: translateX(0%);
                -moz-transform: translateX(0%);
                -ms-transform: translateX(0%);
                -o-transform: translateX(0%);
                transform: translateX(0%);
            }
        }
    </style>
@endsection

@section('content')
    <div id="projectObjectForm"></div>
    <div id="gridContainer" style="height: 100%"></div>
@endsection

@section('js_footer')
    <script>
        $(function () {
            //<editor-fold desc="JS: DataSources">
            let measureUnitsData = {!!$measureUnits!!};
            let accountingTypesData = {!!$accountingTypes!!};
            let materialTypesData = {!!$materialTypes!!};
            let materialStandardsData = {!!$materialStandards!!};
            let snapshotsData = {!!$snapshots!!};
            let projectObject = {{$projectObjectId}};
            let snapshotId = null;

            let projectObjectsData = new DevExpress.data.DataSource({
                reshapeOnPush: true,
                store: new DevExpress.data.ArrayStore({
                    key: "id",
                    data: {!! $projectObjects !!}
                })
            });

            let materialStandardsDataSource = new DevExpress.data.DataSource({
                reshapeOnPush: true,
                store: new DevExpress.data.CustomStore({
                    key: "id",
                    load: function () {
                        if (isNullOrUndefined(snapshotId)) {
                            return $.getJSON("{{route('materials.list')}}",
                                {
                                    project_object: projectObject
                                });
                        } else {
                            return $.getJSON("{{route('materials.snapshots.list')}}",
                                {
                                    snapshotId: snapshotId
                                });
                        }
                    }
                    /*byKey: function (key, extraOptions){

                    }*/
                })
            });

            //</editor-fold>

            //<editor-fold desc="JS: Info form configuration">
            let projectObjectInfoForm = $('#projectObjectForm').dxForm({
                formData: [],
                colCount: 2,
                items: [
                    {
                        itemType: "group",
                        caption: "Объект",
                        items: [
                            {
                                dataField: "project_object_id",
                                label: {
                                    visible: false,
                                    text: "Объект"
                                },
                                editorType: "dxSelectBox",
                                editorOptions: {
                                    dataSource: projectObjectsData,
                                    displayExpr: function (data) {
                                        if (isNullOrUndefined(data.short_name)) {
                                            return data.name
                                        } else {
                                            return data.short_name
                                        }
                                    },
                                    valueExpr: "id",
                                    searchEnabled: true,
                                    value: projectObject,
                                    onValueChanged: function (e) {
                                        projectObject = e.value;
                                        updateProjectObjectDetailInfo(e.value);
                                        $("#gridContainer").dxDataGrid("instance").refresh();
                                        window.history.pushState("", "", "?project_object=" + projectObject)
                                    }
                                }
                            },
                            {
                                template: '<div id="projectObjectDetailInfo"></div>'
                            }
                        ]
                    },
                    {
                        itemType: "group",
                        caption: "Активные операции"
                    },
                    {
                        itemType: "group",
                        colSpan: 2,
                        caption: "История операций",
                        items: [{
                            template: '<div id="snapshotsTimeline"></div>'
                        }]
                    }
                ]

            }).dxForm("instance")
            //</editor-fold>

            //<editor-fold desc="JS: Columns definition">
            let materialColumns = [
                {
                    dataField: "standard_id",
                    dataType: "string",
                    caption: "Наименование",
                    width: 500,
                    lookup: {
                        dataSource: materialStandardsData,
                        displayExpr: "name",
                        valueExpr: "id"
                    },
                    cellTemplate: function (container, options) {
                        let data = options.data;
                        let materialName = data.standard_name;
                        if (data.accounting_type === 1) {
                            materialName += " (" + data.quantity + " " + data.measure_unit_value + ")";
                        }
                        $("<div>" + materialName + "</div>")
                            .appendTo(container);
                    }
                },
                {
                    dataField: "measure_unit",
                    dataType: "number",
                    caption: "Ед. изм.",
                    lookup: {
                        dataSource: measureUnitsData,
                        displayExpr: "value",
                        valueExpr: "id"
                    }
                },
                {
                    dataField: "length_quantity",
                    dataType: "number",
                    caption: "Метраж",
                    showSpinButtons: true
                },
                {
                    dataField: "computed_quantity",
                    dataType: "number",
                    caption: "Количество",
                    cellTemplate: function (container, options) {
                        let data = options.data;
                        let computedQuantity;

                        if (data.accounting_type === 1) {
                            computedQuantity = data.computed_quantity + " шт.";
                        } else {
                            computedQuantity = data.computed_quantity + " " + data.measure_unit_value;
                        }

                        $("<div>" + computedQuantity + "</div>")
                            .appendTo(container);
                    }
                },
                {
                    dataField: "computed_weight",
                    dataType: "number",
                    caption: "Вес",
                    cellTemplate: function (container, options) {
                        $("<div>" + options.data.computed_weight.toFixed(3) + " т.</div>")
                            .appendTo(container);
                    }
                },
                {
                    dataField: "material_type",
                    dataType: "number",
                    caption: "Тип материала",
                    groupIndex: 0,
                    lookup: {
                        dataSource: materialTypesData,
                        displayExpr: "name",
                        valueExpr: "id"
                    }
                }
            ];
            //</editor-fold>

            //<editor-fold desc="JS: Grid configuration">
            let materialsDataGrid = $("#gridContainer").dxDataGrid({
                dataSource: materialStandardsDataSource,
                focusedRowEnabled: false,
                hoverStateEnabled: true,
                columnAutoWidth: false,
                showBorders: true,
                showColumnLines: true,
                filterRow: {
                    visible: true,
                    applyFilter: "auto"
                },
                grouping: {
                    autoExpandAll: true,
                },
                groupPanel: {
                    visible: false
                },
                selection: {
                    allowSelectAll:true,
                    deferred:false,
                    mode:"multiple",
                    selectAllMode:"allPages",
                    showCheckBoxesMode:"always"
                },
                columns: materialColumns,
                summary: {
                    groupItems: [{
                        column: "standard_id",
                        summaryType: "count",
                        displayFormat: "Количество: {0}",
                    },
                    {
                        column: "computed_quantity",
                        summaryType: "sum",
                        displayFormat: "Всего: {0}",
                        showInGroupFooter: false,
                        alignByColumn: true
                    },
                    {
                        column: "computed_weight",
                        summaryType: "sum",
                        //displayFormat: "Всего: {0} т.",
                        customizeText: function (data) {
                            return "Всего: " + data.value.toFixed(3) + " т."
                        },
                        showInGroupFooter: false,
                        alignByColumn: true
                    }],
                    totalItems: [{
                        column: "computed_weight",
                        summaryType: "sum",
                        customizeText: function (data) {
                            return "Итого: " + data.value.toFixed(3) + " т."
                        }
                    }]
                },

                onToolbarPreparing: function(e) {
                    e.toolbarOptions.items.unshift(
                        {
                            location: "after",
                            widget: "dxDropDownButton",
                            options: {
                                text: "Операции",
                                //icon: "save",
                                dropDownOptions: {
                                    width: 230
                                },
                                onItemClick: function(e) {
                                    if (e.itemData === "Поставка") {
                                        document.location.href = "{{route('materials.operations.supply.new')}}" + "/?project_object=" + projectObject;
                                    }

                                    if (e.itemData === "Перемещение") {
                                        transferMaterials();
                                    }
                                },

                                items: ["Поставка", "Перемещение", "Производство", "Списание"]
                            }
                        }
                    );
                },
                onRowDblClick: function (e) {
                    console.log(e);
                }
            }).dxDataGrid("instance");
            //</editor-fold>

            //<editor-fold desc="JS: Toolbar configuration">
            //</editor-fold>

            function transferMaterials() {
                let materialsToTransferArray = materialsDataGrid.getSelectedRowKeys();
                let transferParams = "sourceProjectObjectId=" + projectObject;

                if (materialsToTransferArray.length !== 0) {
                    transferParams = transferParams + "&materialsToTransfer=" + encodeURIComponent(materialsToTransferArray.join('+'));
                }

                document.location.href = "{{route('materials.operations.transfer.new')}}" + "/?" + transferParams;
            }

            function updateProjectObjectDetailInfo(projectObjectID) {
                projectObjectsData.store().byKey(projectObjectID).done(function (dataItem) {
                    console.log(dataItem);
                    $('#projectObjectDetailInfo').html(`Полное наименование: ${dataItem.name}<br>Адрес: ${dataItem.address}`)
                })
            }

            updateProjectObjectDetailInfo(projectObject);

            function updateTimeline() {
                $('#snapshotsTimeline')
                    .append('<section class="cd-horizontal-timeline">' +
                        '<div class="timeline">' +
                        '   <div class="events-wrapper">' +
                        '        <div class="events">' +
                        '            <ol id="timelineItems" style="list-style-type: none;">' +
                        '            </ol>' +
                        '            <span class="filling-line" aria-hidden="true"></span>' +
                        '        </div>' +
                        '    </div>' +
                        '    <ul class="cd-timeline-navigation" style="list-style-type: none;">' +
                        '        <li><a href="#0" class="prev inactive"></a></li>' +
                        '        <li><a href="#0" class="next"></a></li>' +
                        '    </ul>' +
                        '</div> ')

                snapshotsData.forEach(function (item) {
                    /*<li><a href="#0" data-date="16/01/2014" class="selected">16 Jan</a></li>*/
                    console.log(item);
                    let $li = $('<li><a href="#" class="" style="font-size: 8pt;" data-date="' + item.created_at + '">' + item.created_at + '</a></li>').appendTo($("#timelineItems"));
                    $li.click(function () {
                        snapshotId = item.id;
                        $("#gridContainer").dxDataGrid("instance").refresh();
                    });
                })

                if (!isNullOrUndefined($li)) {
                    $li.addClass("selected")
                }
            }

            updateTimeline();

        });

        /*Timeline - https://codepen.io/mishamean/pen/PEoWrK/*/
        jQuery(document).ready(function ($) {
            var timelines = $('.cd-horizontal-timeline'),
                eventsMinDistance = 120,
                eventsMaxDistance = 120;

            (timelines.length > 0) && initTimeline(timelines);

            function initTimeline(timelines) {
                timelines.each(function () {
                    var timeline = $(this),
                        timelineComponents = {};
                    //cache timeline components
                    timelineComponents['timelineWrapper'] = timeline.find('.events-wrapper');
                    timelineComponents['eventsWrapper'] = timelineComponents['timelineWrapper'].children('.events');
                    timelineComponents['fillingLine'] = timelineComponents['eventsWrapper'].children('.filling-line');
                    timelineComponents['timelineEvents'] = timelineComponents['eventsWrapper'].find('a');
                    timelineComponents['timelineDates'] = parseDate3(timelineComponents['timelineEvents']);
                    timelineComponents['eventsMinLapse'] = minLapse(timelineComponents['timelineDates']);
                    timelineComponents['timelineNavigation'] = timeline.find('.cd-timeline-navigation');
                    timelineComponents['eventsContent'] = timeline.children('.events-content');

                    //assign a left postion to the single events along the timeline
                    setDatePosition(timelineComponents, eventsMinDistance, eventsMaxDistance);
                    //assign a width to the timeline
                    var timelineTotWidth = setTimelineWidth(timelineComponents, eventsMinDistance);
                    //the timeline has been initialize - show it
                    timeline.addClass('loaded');
                    //detect click on the next arrow
                    timelineComponents['timelineNavigation'].on('click', '.next', function (event) {
                        event.preventDefault();
                        updateSlide(timelineComponents, timelineTotWidth, 'next');
                    });
                    //detect click on the prev arrow
                    timelineComponents['timelineNavigation'].on('click', '.prev', function (event) {
                        event.preventDefault();
                        updateSlide(timelineComponents, timelineTotWidth, 'prev');
                    });
                    //detect click on the a single event - show new event content
                    timelineComponents['eventsWrapper'].on('click', 'a', function (event) {
                        event.preventDefault();
                        timelineComponents['timelineEvents'].removeClass('selected');
                        $(this).addClass('selected');
                        updateOlderEvents($(this));
                        updateFilling($(this), timelineComponents['fillingLine'], timelineTotWidth);
                        updateVisibleContent($(this), timelineComponents['eventsContent']);
                    });

                    //on swipe, show next/prev event content
                    timelineComponents['eventsContent'].on('swipeleft', function () {
                        var mq = checkMQ();
                        (mq == 'mobile') && showNewContent(timelineComponents, timelineTotWidth, 'next');
                    });
                    timelineComponents['eventsContent'].on('swiperight', function () {
                        var mq = checkMQ();
                        (mq == 'mobile') && showNewContent(timelineComponents, timelineTotWidth, 'prev');
                    });

                    //keyboard navigation
                    $(document).keyup(function (event) {
                        if (event.which == '37' && elementInViewport(timeline.get(0))) {
                            showNewContent(timelineComponents, timelineTotWidth, 'prev');
                        } else if (event.which == '39' && elementInViewport(timeline.get(0))) {
                            showNewContent(timelineComponents, timelineTotWidth, 'next');
                        }
                    });
                });
            }

            function updateSlide(timelineComponents, timelineTotWidth, string) {
                //retrieve translateX value of timelineComponents['eventsWrapper']
                var translateValue = getTranslateValue(timelineComponents['eventsWrapper']),
                    wrapperWidth = Number(timelineComponents['timelineWrapper'].css('width').replace('px', ''));
                //translate the timeline to the left('next')/right('prev')
                (string == 'next')
                    ? translateTimeline(timelineComponents, translateValue - wrapperWidth + eventsMinDistance, wrapperWidth - timelineTotWidth)
                    : translateTimeline(timelineComponents, translateValue + wrapperWidth - eventsMinDistance);
            }

            function showNewContent(timelineComponents, timelineTotWidth, string) {
                //go from one event to the next/previous one
                var visibleContent = timelineComponents['eventsContent'].find('.selected'),
                    newContent = (string == 'next') ? visibleContent.next() : visibleContent.prev();

                if (newContent.length > 0) { //if there's a next/prev event - show it
                    var selectedDate = timelineComponents['eventsWrapper'].find('.selected'),
                        newEvent = (string == 'next') ? selectedDate.parent('li').next('li').children('a') : selectedDate.parent('li').prev('li').children('a');

                    updateFilling(newEvent, timelineComponents['fillingLine'], timelineTotWidth);
                    updateVisibleContent(newEvent, timelineComponents['eventsContent']);
                    newEvent.addClass('selected');
                    selectedDate.removeClass('selected');
                    updateOlderEvents(newEvent);
                    updateTimelinePosition(string, newEvent, timelineComponents, timelineTotWidth);
                }
            }

            function updateTimelinePosition(string, event, timelineComponents, timelineTotWidth) {
                //translate timeline to the left/right according to the position of the selected event
                var eventStyle = window.getComputedStyle(event.get(0), null),
                    eventLeft = Number(eventStyle.getPropertyValue("left").replace('px', '')),
                    timelineWidth = Number(timelineComponents['timelineWrapper'].css('width').replace('px', '')),
                    timelineTotWidth = Number(timelineComponents['eventsWrapper'].css('width').replace('px', ''));
                var timelineTranslate = getTranslateValue(timelineComponents['eventsWrapper']);

                if ((string == 'next' && eventLeft > timelineWidth - timelineTranslate) || (string == 'prev' && eventLeft < -timelineTranslate)) {
                    translateTimeline(timelineComponents, -eventLeft + timelineWidth / 2, timelineWidth - timelineTotWidth);
                }
            }

            function translateTimeline(timelineComponents, value, totWidth) {
                var eventsWrapper = timelineComponents['eventsWrapper'].get(0);
                value = (value > 0) ? 0 : value; //only negative translate value
                value = (!(typeof totWidth === 'undefined') && value < totWidth) ? totWidth : value; //do not translate more than timeline width
                setTransformValue(eventsWrapper, 'translateX', value + 'px');
                //update navigation arrows visibility
                (value == 0) ? timelineComponents['timelineNavigation'].find('.prev').addClass('inactive') : timelineComponents['timelineNavigation'].find('.prev').removeClass('inactive');
                (value == totWidth) ? timelineComponents['timelineNavigation'].find('.next').addClass('inactive') : timelineComponents['timelineNavigation'].find('.next').removeClass('inactive');
            }

            function updateFilling(selectedEvent, filling, totWidth) {
                //change .filling-line length according to the selected event
                var eventStyle = window.getComputedStyle(selectedEvent.get(0), null),
                    eventLeft = eventStyle.getPropertyValue("left"),
                    eventWidth = eventStyle.getPropertyValue("width");
                eventLeft = Number(eventLeft.replace('px', '')) + Number(eventWidth.replace('px', '')) / 2;
                var scaleValue = eventLeft / totWidth;
                setTransformValue(filling.get(0), 'scaleX', scaleValue);
            }

            function setDatePosition(timelineComponents, min, max) {
                for (i = 0; i < timelineComponents['timelineDates'].length; i++) {
                    var distance = daydiff(timelineComponents['timelineDates'][0], timelineComponents['timelineDates'][i]),
                        distanceNorm = Math.round(distance / timelineComponents['eventsMinLapse']) + 2;
                    timelineComponents['timelineEvents'].eq(i).css('left', (distanceNorm + 4) * min + 'px');
                }
            }

            function setTimelineWidth(timelineComponents, width) {
                var timeSpan = daydiff(timelineComponents['timelineDates'][0], timelineComponents['timelineDates'][timelineComponents['timelineDates'].length - 1]),
                    timeSpanNorm = timeSpan / timelineComponents['eventsMinLapse'],
                    timeSpanNorm = Math.round(timeSpanNorm) + 4,
                    totalWidth = timeSpanNorm * width;
                timelineComponents['eventsWrapper'].css('width', totalWidth + 'px');
                updateFilling(timelineComponents['timelineEvents'].eq(0), timelineComponents['fillingLine'], totalWidth);

                return totalWidth;
            }

            function q3wSetDatePosition(timelineComponents, min, max) {
                for (i = 0; i < timelineComponents['timelineDates'].length; i++) {
                    timelineComponents['timelineEvents'].eq(i).css('left', (i + 1) * min + 'px');
                }
            }

            function q3wSetTimelineWidth(timelineComponents, width) {
                totalWidth = timelineComponents['timelineDates'].length * width;
                timelineComponents['eventsWrapper'].css('width', totalWidth + 'px');
                updateFilling(timelineComponents['timelineEvents'].eq(0), timelineComponents['fillingLine'], totalWidth);

                return totalWidth;
            }

            function updateVisibleContent(event, eventsContent) {
                var eventDate = event.data('date'),
                    visibleContent = eventsContent.find('.selected'),
                    selectedContent = eventsContent.find('[data-date="' + eventDate + '"]'),
                    selectedContentHeight = selectedContent.height();

                if (selectedContent.index() > visibleContent.index()) {
                    var classEnetering = 'selected enter-right',
                        classLeaving = 'leave-left';
                } else {
                    var classEnetering = 'selected enter-left',
                        classLeaving = 'leave-right';
                }

                selectedContent.attr('class', classEnetering);
                visibleContent.attr('class', classLeaving).one('webkitAnimationEnd oanimationend msAnimationEnd animationend', function () {
                    visibleContent.removeClass('leave-right leave-left');
                    selectedContent.removeClass('enter-left enter-right');
                });
                eventsContent.css('height', selectedContentHeight + 'px');
            }

            function updateOlderEvents(event) {
                event.parent('li').prevAll('li').children('a').addClass('older-event').end().end().nextAll('li').children('a').removeClass('older-event');
            }

            function getTranslateValue(timeline) {
                var timelineStyle = window.getComputedStyle(timeline.get(0), null),
                    timelineTranslate = timelineStyle.getPropertyValue("-webkit-transform") ||
                        timelineStyle.getPropertyValue("-moz-transform") ||
                        timelineStyle.getPropertyValue("-ms-transform") ||
                        timelineStyle.getPropertyValue("-o-transform") ||
                        timelineStyle.getPropertyValue("transform");

                if (timelineTranslate.indexOf('(') >= 0) {
                    var timelineTranslate = timelineTranslate.split('(')[1];
                    timelineTranslate = timelineTranslate.split(')')[0];
                    timelineTranslate = timelineTranslate.split(',');
                    var translateValue = timelineTranslate[4];
                } else {
                    var translateValue = 0;
                }

                return Number(translateValue);
            }

            function setTransformValue(element, property, value) {
                element.style["-webkit-transform"] = property + "(" + value + ")";
                element.style["-moz-transform"] = property + "(" + value + ")";
                element.style["-ms-transform"] = property + "(" + value + ")";
                element.style["-o-transform"] = property + "(" + value + ")";
                element.style["transform"] = property + "(" + value + ")";
            }

            //based on http://stackoverflow.com/questions/542938/how-do-i-get-the-number-of-days-between-two-dates-in-javascript
            function parseDate(events) {
                var dateArrays = [];
                events.each(function () {
                    var dateComp = $(this).data('date').split('/'),
                        newDate = new Date(dateComp[2], dateComp[1] - 1, dateComp[0]);
                    dateArrays.push(newDate);
                });
                return dateArrays;
            }

            function parseDate2(events) {
                var dateArrays = [];
                events.each(function () {
                    var singleDate = $(this),
                        dateComp = singleDate.data('date').split('T');
                    if (dateComp.length > 1) { //both DD/MM/YEAR and time are provided
                        var dayComp = dateComp[0].split('/'),
                            timeComp = dateComp[1].split(':');
                    } else if (dateComp[0].indexOf(':') >= 0) { //only time is provide
                        var dayComp = ["2000", "0", "0"],
                            timeComp = dateComp[0].split(':');
                    } else { //only DD/MM/YEAR
                        var dayComp = dateComp[0].split('/'),
                            timeComp = ["0", "0"];
                    }
                    var newDate = new Date(dayComp[2], dayComp[1] - 1, dayComp[0], timeComp[0], timeComp[1]);
                    dateArrays.push(newDate);
                });
                return dateArrays;
            }

            function parseDate3(events) {
                var dateArrays = [];
                events.each(function () {
                    var singleDate = $(this),
                        dateComp = singleDate.data('date').split(' ');
                    if (dateComp.length > 1) { //both DD/MM/YEAR and time are provided
                        var dayComp = dateComp[0].split('-'),
                            timeComp = dateComp[1].split(':');
                    } else if (dateComp[0].indexOf(':') >= 0) { //only time is provide
                        var dayComp = ["2000", "0", "0"],
                            timeComp = dateComp[0].split(':');
                    } else { //only DD/MM/YEAR
                        var dayComp = dateComp[0].split('-'),
                            timeComp = ["0", "0"];
                    }
                    var newDate = new Date(dayComp[0], dayComp[1] - 1, dayComp[2], timeComp[0], timeComp[1]);
                    dateArrays.push(newDate);
                });
                return dateArrays;
            }

            function daydiff(first, second) {
                let dayDiff = Math.round((second - first))
                return 100 * 60 * 60;
                return dayDiff;//8640000; //day in milliseconds
            }

            function minLapse(dates) {
                //determine the minimum distance among events
                var dateDistances = [];
                for (i = 1; i < dates.length; i++) {
                    var distance = daydiff(dates[i - 1], dates[i]);
                    dateDistances.push(distance);
                }
                return Math.min.apply(null, dateDistances);
            }

            /*
                How to tell if a DOM element is visible in the current viewport?
                http://stackoverflow.com/questions/123999/how-to-tell-if-a-dom-element-is-visible-in-the-current-viewport
            */
            function elementInViewport(el) {
                var top = el.offsetTop;
                var left = el.offsetLeft;
                var width = el.offsetWidth;
                var height = el.offsetHeight;

                while (el.offsetParent) {
                    el = el.offsetParent;
                    top += el.offsetTop;
                    left += el.offsetLeft;
                }

                return (
                    top < (window.pageYOffset + window.innerHeight) &&
                    left < (window.pageXOffset + window.innerWidth) &&
                    (top + height) > window.pageYOffset &&
                    (left + width) > window.pageXOffset
                );
            }

            function checkMQ() {
                //check if mobile or desktop device
                return window.getComputedStyle(document.querySelector('.cd-horizontal-timeline'), '::before').getPropertyValue('content').replace(/'/g, "").replace(/"/g, "");
            }
        });
    </script>
@endsection
