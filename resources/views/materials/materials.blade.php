@extends('layouts.app')

@section('title', 'Материалы')

@section('url', route('materials.index'))

@section('css_top')
    <style>
        td.dx-command-select {
            border-right: none !important;
        }

        .dx-command-expand {
            border-left: none !important;
        }

        .dx-form-group, .dx-tabpanel {
            background-color: #fff;
            border: 1px solid #cfcfcf;
            border-radius: 1px;
            box-shadow: 0 1px 4px 0 rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .dx-tabpanel {
            padding: 0px;
        }

        .dx-layout-manager .dx-field-item:not(.dx-first-col) {
            padding-left: 0px !important;
        }

        .dx-master-detail-cell {
            padding: 0 !important;
            padding-left: 60px !important;
        }

        .tab-wrapper-button {
            float: right;
            margin-top: 4px;
            margin-right: 4px;
            background-color: white !important;
        }
    </style>
@endsection

@section('content')
    <div id="projectObjectForm"></div>
    <div
        id="gridContainer"
        style="height: 100%; display: none"
    ></div>
    <div id="supplyTypePopup"></div>
    <div id="commentPopup"></div>
@endsection

@section('js_footer')
    <script>
        $(function () {
            //<editor-fold desc="JS: DataSources">
            let measureUnitsData = {!!$measureUnits!!};
            let materialTypesData = {!!$materialTypes!!};
            let projectObject = {{$projectObjectId}};
            let snapshotId = null;
            let isStore = null;

            let operationRoutesStore = new DevExpress.data.CustomStore({
                key: 'id',
                loadMode: 'raw',
                load: function (loadOptions) {
                    return $.getJSON("{{route('material.operation.routes.list')}}",
                        {data: JSON.stringify(loadOptions)});
                },
            });

            let operationRouteStagesStore = new DevExpress.data.CustomStore({
                key: 'id',
                loadMode: 'processed',
                load: function (loadOptions) {
                    return $.getJSON("{{route('material.operation.route-stages.list')}}",
                        {data: JSON.stringify(loadOptions)});
                },
            });

            let projectObjectsData = new DevExpress.data.DataSource({
                reshapeOnPush: true,
                store: new DevExpress.data.CustomStore({
                    key: 'id',
                    loadMode: 'processed',
                    loadMode: 'raw',
                    load: function (loadOptions) {
                        return $.getJSON("{{route('project-objects.list')}}",
                            {data: JSON.stringify(loadOptions)});
                    },
                }),
            });

            let projectObjectsListWhichParticipatesInMaterialAccountingData = new DevExpress.data.DataSource({
                reshapeOnPush: true,
                store: new DevExpress.data.CustomStore({
                    key: 'id',
                    loadMode: 'processed',
                    loadMode: 'raw',
                    load: function (loadOptions) {
                        return $.getJSON("{{route('project-objects.which-participates-in-material-accounting.list')}}",
                            {data: JSON.stringify(loadOptions)});
                    },
                }),
            });

            let actualMaterialsDataSource = new DevExpress.data.DataSource({
                reshapeOnPush: true,
                store: new DevExpress.data.CustomStore({
                    key: 'id',
                    load: function () {
                        if (isNullOrUndefined(snapshotId)) {
                            return $.getJSON("{{route('materials.list')}}",
                                {
                                    project_object: projectObject,
                                });
                        } else {
                            return $.getJSON("{{route('materials.snapshots-materials.list')}}",
                                {
                                    snapshotId: snapshotId,
                                });
                        }
                    },
                }),
            });

            let reservedMaterialsDataSource = new DevExpress.data.DataSource({
                reshapeOnPush: true,
                store: new DevExpress.data.CustomStore({
                    key: 'id',
                    load: function () {
                        return $.getJSON("{{route('materials.reserved.list')}}",
                            {
                                project_object: projectObject,
                            });
                    },
                }),
            });

            let materialSnapshotsStore = new DevExpress.data.CustomStore({
                key: 'id',
                load: function (loadOptions) {
                    let params = {
                        projectObjectId: projectObject,
                        skip: loadOptions.skip || 0,
                        take: loadOptions.take || 10,
                        sort: JSON.stringify(loadOptions.sort || null),
                        filter: JSON.stringify(loadOptions.filter || null)
                    };

                    return $.getJSON("{{route('materials.snapshots.list')}}", params).then(response => {
                        return {
                            data: response.items,
                            totalCount: response.totalCount,
                        };
                    });
                }
            });

            let materialSnapshotsDataSource = new DevExpress.data.DataSource({
                store: materialSnapshotsStore,
                paginate: true,
                pageSize: 10
            });

            let projectObjectActiveOperationsStore = new DevExpress.data.CustomStore({
                key: 'id',
                loadMode: 'raw',
                load: function (loadOptions) {
                    return $.getJSON("{{route('materials.operations.list.project-object.active')}}",
                        {projectObjectId: projectObject});
                },
            });

            let projectObjectActiveOperationsDataSource = new DevExpress.data.DataSource({
                store: projectObjectActiveOperationsStore,
            });
            //</editor-fold>

            //<editor-fold desc="JS: Columns definition">
            let materialColumns = [
                {
                    dataField: 'standard_name',
                    dataType: 'string',
                    caption: 'Наименование',
                    width: 500,
                    sortIndex: 0,
                    sortOrder: 'asc',
                    cellTemplate: function (container, options) {
                        $(`<div class="standard-name">${options.text}</div>`)
                            .appendTo(container);

                        if (options.data.comment) {
                            $(`<div class="material-comment">${options.data.comment}</div>`)
                                .appendTo(container);

                            container.addClass('standard-name-cell-with-comment');
                        }
                    },
                    calculateFilterExpression: function (filterValue, selectedFilterOperation, target) {
                        if (['contains', 'notcontains'].indexOf(selectedFilterOperation) !== -1) {
                            let columnsNames = ['standard_name', 'comment'];

                            let words = filterValue.split(' ');
                            let filter = [];

                            columnsNames.forEach(function (column, index) {
                                filter.push([]);
                                words.forEach(function (word) {
                                    filter[filter.length - 1].push([column, selectedFilterOperation, word]);
                                    filter[filter.length - 1].push('and');
                                });

                                filter[filter.length - 1].pop();
                                filter.push('or');
                            });

                            filter.pop();
                            return filter;
                        }
                        return this.defaultCalculateFilterExpression(filterValue, selectedFilterOperation);
                    },
                },
                {
                    dataField: 'measure_unit',
                    dataType: 'number',
                    caption: 'Ед. изм.',
                    alignment: 'right',
                    lookup: {
                        dataSource: measureUnitsData,
                        displayExpr: 'value',
                        valueExpr: 'id',
                    },
                },
                {
                    dataField: 'quantity',
                    dataType: 'number',
                    caption: 'Количество',
                    sortIndex: 1,
                    sortOrder: 'asc',
                    showSpinButtons: true,
                    cellTemplate: function (container, options) {
                        let quantity = Math.round(options.data.quantity * 100) / 100;
                        let measureUnit = options.data.measure_unit_value;

                        $(`<div>${quantity} ${measureUnit}</div>`)
                            .appendTo(container);
                    },
                },
                {
                    dataField: 'amount',
                    dataType: 'number',
                    caption: 'Количество (шт)',
                    sortIndex: 2,
                    sortOrder: 'asc',
                    cellTemplate: function (container, options) {
                        let amount = options.data.amount;
                        $(`<div>${amount} шт</div>`)
                            .appendTo(container);
                    },
                },
                {
                    dataField: 'computed_weight',
                    dataType: 'number',
                    caption: 'Вес',
                    calculateCellValue: function (rowData) {
                        let amount = rowData.amount;
                        let weight = amount * rowData.quantity * rowData.weight;

                        if (isNaN(weight)) {
                            weight = 0;
                        } else {
                            weight = Math.round(weight * 1000) / 1000;
                        }

                        rowData.computed_weight = weight;
                        return weight;
                    },
                    cellTemplate: function (container, options) {
                        let weight = options.data.computed_weight;

                        $(`<div>${weight} т</div>`)
                            .appendTo(container);
                    },
                },
                {
                    dataField: 'material_type',
                    dataType: 'number',
                    caption: 'Тип материала',
                    groupIndex: 0,
                    lookup: {
                        dataSource: materialTypesData,
                        displayExpr: 'name',
                        valueExpr: 'id',
                    },
                },
            ];
            //</editor-fold>

            //<editor-fold desc="JS: Info form configuration">
            let projectObjectInfoForm = $('#projectObjectForm').dxForm({
                formData: [],
                colCount: 2,
                items: [
                    {
                        itemType: 'group',
                        name: 'projectObjectGroup',
                        caption: 'Объект',
                        cssClass: 'project-object-group',
                        items: [
                            {
                                dataField: 'project_object_id',
                                label: {
                                    visible: false,
                                    text: 'Объект',
                                },
                                editorType: 'dxSelectBox',
                                editorOptions: {
                                    dataSource: projectObjectsListWhichParticipatesInMaterialAccountingData,
                                    displayExpr: function (data) {
                                        if (isNullOrUndefined(data.short_name)) {
                                            return data.name;
                                        } else {
                                            return data.short_name;
                                        }
                                    },
                                    valueExpr: 'id',
                                    searchEnabled: true,
                                    value: projectObject,
                                    onValueChanged: function (e) {
                                        projectObject = e.value;
                                        snapshotId = null;

                                        updateProjectObjectDetailInfo(e.value);

                                        materialSnapshotsDataSource.reload();
                                        reservedMaterialsDataSource.reload();
                                        projectObjectActiveOperationsDataSource.reload();
                                        projectObjectInfoForm.getEditor('materialDataGrid').refresh();
                                        console.log('projectObjectInfoForm.getEditor(reservedMaterialsGrid)', projectObjectInfoForm.getEditor('reservedMaterialsGrid'));
                                        projectObjectInfoForm.getEditor('reservedMaterialsGrid').refresh();

                                        window.history.pushState('', '', '?project_object=' + projectObject);
                                    },
                                },
                            },
                            {
                                label: {
                                    visible: true,
                                    text: 'Полное наименование',
                                },
                                template: '<div id="projectObjectFullName"></div>',
                            },
                            {
                                label: {
                                    visible: true,
                                    text: 'Адрес',
                                },
                                template: '<div id="projectObjectAddress"></div>',
                            },
                        ],
                    },
                    {
                        itemType: 'group',
                        cssClass: 'active-operations-group',
                        caption: 'Активные операции',
                        name: 'activeOperationGroup',
                        items: [
                            {
                                editorType: 'dxDataGrid',
                                editorOptions: {
                                    dataSource: projectObjectActiveOperationsDataSource,
                                    focusedRowEnabled: false,
                                    hoverStateEnabled: true,
                                    columnAutoWidth: false,
                                    showBorders: true,
                                    showColumnLines: true,
                                    noDataText: 'Активные операции отсутствуют',
                                    height: 244,
                                    columns: [
                                        {
                                            dataField: 'id',
                                            dataType: 'number',
                                            caption: 'Операция',
                                            width: '20%',
                                            showSpinButtons: true,

                                            cellTemplate: function (container, options) {
                                                let operationId = options.data.id;
                                                let operationUrl = options.data.url;

                                                $(`<div><a href="${operationUrl}">Операция #${operationId}</a></div>`)
                                                    .appendTo(container);
                                            },
                                        },
                                        {
                                            dataField: 'operation_route_id',
                                            dataType: 'number',
                                            caption: 'Тип операции',
                                            width: '20%',
                                            lookup: {
                                                dataSource: {
                                                    paginate: true,
                                                    pageSize: 25,
                                                    store: operationRoutesStore,
                                                },
                                                displayExpr: 'name',
                                                valueExpr: 'id',
                                            },
                                        },
                                        {
                                            dataField: 'operation_route_stage_id',
                                            dataType: 'number',
                                            caption: 'Статус',
                                            width: '60%',
                                            lookup: {
                                                dataSource: {
                                                    paginate: true,
                                                    pageSize: 25,
                                                    store: operationRouteStagesStore,
                                                },
                                                displayExpr: 'name',
                                                valueExpr: 'id',
                                            },
                                            cellTemplate: (container, options) => {
                                                console.log('options', options);
                                                if (options.data.expected_users_names) {
                                                    return $(`<div class="cell-end-ellipses">${options.displayValue} (${options.data.expected_users_names})</div>`);
                                                } else {
                                                    return $(`<div class="cell-end-ellipses">${options.displayValue}</div>`);
                                                }
                                            },
                                        },
                                    ],
                                    onRowPrepared: (e) => {
                                        if (e.rowType === 'data') {
                                            if (e.data.have_conflict) {
                                                e.rowElement.addClass('row-conflict-operation');
                                            }
                                        }
                                    },
                                },
                            },
                        ],
                    },
                    {
                        itemType: 'group',
                        colSpan: 2,
                        caption: 'История операций',
                        items: [
                            {
                                editorType: 'dxDataGrid',
                                editorOptions: {
                                    dataSource: materialSnapshotsDataSource,
                                    remoteOperations: true,
                                    focusedRowEnabled: false,
                                    hoverStateEnabled: true,
                                    columnAutoWidth: false,
                                    showBorders: true,
                                    showColumnLines: true,
                                    noDataText: 'История операций отсутствует',
                                    columns: [
                                        {
                                            dataField: 'id',
                                            dataType: 'number',
                                            caption: 'Операция',
                                            width: '20%',
                                            showSpinButtons: true,
                                            cellTemplate: function (container, options) {
                                                let operationId = options.data.id;
                                                let operationUrl = options.data.url;

                                                $(`<div><a href="${operationUrl}">Операция #${operationId}</a></div>`)
                                                    .appendTo(container);
                                            },
                                        },
                                        {
                                            dataField: 'created_at',
                                            dataType: 'date',
                                            caption: 'Дата создания',
                                            width: '20%',
                                        },
                                        {
                                            dataField: 'operation_route_id',
                                            dataType: 'number',
                                            caption: 'Тип операции',
                                            width: '20%',
                                            lookup: {
                                                dataSource: {
                                                    paginate: true,
                                                    pageSize: 25,
                                                    store: operationRoutesStore,
                                                },
                                                displayExpr: 'name',
                                                valueExpr: 'id',
                                            },
                                        },
                                        {
                                            dataField: 'operation_route_stage_id',
                                            dataType: 'number',
                                            caption: 'Статус',
                                            width: '60%',
                                            lookup: {
                                                dataSource: {
                                                    paginate: true,
                                                    pageSize: 25,
                                                    store: operationRouteStagesStore,
                                                },
                                                displayExpr: 'name',
                                                valueExpr: 'id',
                                            },
                                            cellTemplate: (container, options) => {
                                                console.log('options', options);
                                                if (options.data.expected_users_names) {
                                                    return $(`<div class="cell-end-ellipses">${options.displayValue} (${options.data.expected_users_names})</div>`);
                                                } else {
                                                    return $(`<div class="cell-end-ellipses">${options.displayValue}</div>`);
                                                }
                                            },
                                        },
                                    ],
                                    pager: {
                                        showPageSizeSelector: true,
                                        allowedPageSizes: [10, 20, 50],
                                        showInfo: true,
                                        showNavigationButtons: true
                                    },
                                    paging: {
                                        pageSize: 10
                                    },
                                    onRowPrepared: (e) => {
                                        if (e.rowType === 'data') {
                                            if (e.data.have_conflict) {
                                                e.rowElement.addClass('row-conflict-operation');
                                            }
                                        }
                                    },
                                },
                            },
                        ],
                    },
                    {
                        itemType: 'tabbed',
                        tabPanelOptions: {
                            deferRendering: false,
                        },
                        cssClass: 'actual-materials-grid',
                        colSpan: 2,
                        tabs: [
                            {
                                tabTemplate: '<div>Материалы на объекте</div>',
                                items: [
                                    {
                                        name: 'materialDataGrid',
                                        editorType: 'dxDataGrid',
                                        editorOptions: {
                                            dataSource: actualMaterialsDataSource,
                                            focusedRowEnabled: false,
                                            hoverStateEnabled: true,
                                            columnAutoWidth: false,
                                            showBorders: true,
                                            showColumnLines: true,
                                            filterRow: {
                                                visible: true,
                                                applyFilter: 'auto',
                                            },
                                            grouping: {
                                                autoExpandAll: true,
                                            },
                                            groupPanel: {
                                                visible: false,
                                            },
                                            selection: {
                                                allowSelectAll: true,
                                                deferred: false,
                                                mode: 'multiple',
                                                selectAllMode: 'allPages',
                                                showCheckBoxesMode: 'always',
                                            },
                                            paging: {
                                                enabled: false,
                                            },
                                            scrolling: {
                                                mode: 'virtual',
                                            },
                                            columns: materialColumns,
                                            onRowPrepared: function (e) {
                                                if (e.rowType === 'data') {
                                                    if (e.data.from_operation === 1) {
                                                        e.rowElement.find('.dx-datagrid-group-closed')
                                                            .replaceWith('<i class="fas fa-lock"><i>');
                                                        e.rowElement.find('.dx-select-checkbox').remove();

                                                        e.rowElement.css('color', 'gray');
                                                    }
                                                }
                                            },
                                            onSelectionChanged: (e) => {
                                                e.component.refresh();
                                            },
                                            summary: {
                                                groupItems: [
                                                    {
                                                        column: 'standard_id',
                                                        summaryType: 'count',
                                                        displayFormat: 'Количество: {0}',
                                                    },
                                                    {
                                                        summaryType: 'custom',
                                                        name: 'totalQuantitySummary',
                                                        showInGroupFooter: false,
                                                        alignByColumn: true,
                                                        showInColumn: 'quantity',
                                                    },
                                                    {
                                                        showInColumn: 'amount',
                                                        name: 'amountSummary',
                                                        summaryType: 'custom',
                                                        showInGroupFooter: false,
                                                        alignByColumn: true,
                                                    },
                                                    {
                                                        showInColumn: 'computed_weight',
                                                        summaryType: 'custom',
                                                        name: 'computedWeightSummary',
                                                        showInGroupFooter: false,
                                                        alignByColumn: true,
                                                    },
                                                ],
                                                totalItems: [
                                                    {
                                                        showInColumn: 'computed_weight',
                                                        summaryType: 'custom',
                                                        name: 'totalComputedWeightSummary',
                                                    },
                                                ],
                                                calculateCustomSummary: (options) => {
                                                    if (options.name === 'totalQuantitySummary' ||
                                                        options.name === 'amountSummary' ||
                                                        options.name === 'computedWeightSummary' ||
                                                        options.name === 'totalComputedWeightSummary') {
                                                        if (options.summaryProcess === 'start') {
                                                            options.totalValue = 0;
                                                            options.selectedValue = 0;
                                                        }

                                                        if (options.summaryProcess === 'calculate') {
                                                            switch (options.name) {
                                                                case 'totalQuantitySummary':
                                                                    options.totalValue += options.value.amount * options.value.quantity;
                                                                    break;
                                                                case 'amountSummary':
                                                                    options.totalValue += options.value.amount;
                                                                    break;
                                                                case 'computedWeightSummary':
                                                                case 'totalComputedWeightSummary':
                                                                    options.totalValue += options.value.weight * options.value.amount * options.value.quantity;
                                                                    break;
                                                            }
                                                            options.measureUnit = options.value.measure_unit_value;

                                                            if (options.component.getSelectedRowKeys().includes(options.value.id)) {
                                                                switch (options.name) {
                                                                    case 'totalQuantitySummary':
                                                                        options.selectedValue += options.value.amount * options.value.quantity;
                                                                        break;
                                                                    case 'amountSummary':
                                                                        options.selectedValue += options.value.amount;
                                                                        break;
                                                                    case 'computedWeightSummary':
                                                                    case 'totalComputedWeightSummary':
                                                                        options.selectedValue += options.value.weight * options.value.amount * options.value.quantity;
                                                                        break;
                                                                }
                                                            }
                                                        }

                                                        if (options.summaryProcess === 'finalize') {
                                                            let roundModificator = 100;

                                                            if (options.name === 'computedWeightSummary' || options.name === 'totalComputedWeightSummary') {
                                                                roundModificator = 1000;
                                                            }

                                                            options.totalValue = Math.round(options.totalValue * roundModificator) / roundModificator;
                                                            options.selectedValue = Math.round(options.selectedValue * roundModificator) / roundModificator;

                                                            options.totalValue = new Intl.NumberFormat('ru-RU').format(options.totalValue);

                                                            if (options.selectedValue === 0) {
                                                                switch (options.name) {
                                                                    case 'totalQuantitySummary':
                                                                        options.totalValue = `Всего: ${options.totalValue} ${options.measureUnit}`;
                                                                        break;
                                                                    case 'amountSummary':
                                                                        options.totalValue = `Всего: ${options.totalValue} шт`;
                                                                        break;
                                                                    case 'computedWeightSummary':
                                                                    case 'totalComputedWeightSummary':
                                                                        options.totalValue = `Всего: ${options.totalValue} т`;
                                                                        break;
                                                                }
                                                            } else {
                                                                switch (options.name) {
                                                                    case 'totalQuantitySummary':
                                                                        options.totalValue = `Выбрано: ${new Intl.NumberFormat('ru-RU').format(options.selectedValue)} из ${options.totalValue} ${options.measureUnit}`;
                                                                        break;
                                                                    case 'amountSummary':
                                                                        options.totalValue = `Выбрано: ${options.selectedValue} из ${options.totalValue} шт`;
                                                                        break;
                                                                    case 'computedWeightSummary':
                                                                    case 'totalComputedWeightSummary':
                                                                        options.totalValue = `Выбрано: ${new Intl.NumberFormat('ru-RU').format(options.selectedValue)} из ${options.totalValue} т`;
                                                                        break;
                                                                }

                                                            }
                                                        }
                                                    }
                                                },
                                            },
                                            masterDetail: {
                                                enabled: true,
                                                template: function (container, options) {
                                                    let currentMaterialData = options.data;
                                                    let operationRouteIconWidth = 24;
                                                    let dxCommandSelectWidth = $('.dx-command-select').outerWidth();
                                                    let dxCommandExpandWidth = $('.dx-command-expand.dx-datagrid-group-space').outerWidth();
                                                    let materialDateWidth = $('[aria-label$=Наименование]').outerWidth() + $('[aria-label$="Ед. изм."]').outerWidth() - dxCommandSelectWidth - operationRouteIconWidth;
                                                    console.log('materialDateWidth', materialDateWidth);

                                                    $('<div>')
                                                        .dxDataGrid({
                                                            columnAutoWidth: false,
                                                            showBorders: true,
                                                            showColumnHeaders: false,
                                                            selection: {
                                                                allowSelectAll: false,
                                                                deferred: false,
                                                                /*mode: (e) => {
                                                                    if (isStore) {
                                                                        return "multiple"
                                                                    } else {
                                                                        return "none"
                                                                    }
                                                                },*/
                                                                mode: 'multiple',
                                                                selectAllMode: 'allPages',
                                                                //showCheckBoxesMode: "always"
                                                            },
                                                            columns: [
                                                                {
                                                                    dataField: 'operation_route_id',
                                                                    caption: '',
                                                                    dataType: 'number',
                                                                    width: operationRouteIconWidth,
                                                                    cellTemplate: function (container, options) {
                                                                        let operationIcon = getOperationRouteIcon(options.data.operation_route_id,
                                                                            options.data.source_project_object_id,
                                                                            options.data.destination_project_object_id,
                                                                            options.data.transform_operation_stage_id,
                                                                        );

                                                                        $(`<div><i class="${operationIcon}"></i></div>`)
                                                                            .appendTo(container);
                                                                    },
                                                                },
                                                                {
                                                                    dataField: 'operation_date',
                                                                    caption: 'Дата',
                                                                    dataType: 'datetime',
                                                                    width: materialDateWidth,
                                                                    cellTemplate: function (container, options) {
                                                                        let operationDate = options.text.replaceAll(',', ' ');

                                                                        $(`<div>${operationDate}</div>`)
                                                                            .appendTo(container);
                                                                    },
                                                                },
                                                                {
                                                                    dataField: 'quantity',
                                                                    caption: 'Количество',
                                                                    dataType: 'number',
                                                                    cellTemplate: function (container, options) {
                                                                        let quantity = options.data.quantity;
                                                                        let measureUnit = options.data.measure_unit_value;

                                                                        $(`<div>${quantity} ${measureUnit}</div>`)
                                                                            .appendTo(container);
                                                                    },
                                                                },
                                                                {
                                                                    dataField: 'amount',
                                                                    caption: 'Количество (шт)',
                                                                    dataType: 'number',
                                                                    cellTemplate: function (container, options) {
                                                                        let amount = options.data.amount;

                                                                        $(`<div>${amount} шт</div>`)
                                                                            .appendTo(container);
                                                                    },
                                                                },
                                                                {
                                                                    dataField: 'weight',
                                                                    caption: 'Вес',
                                                                    dataType: 'number',
                                                                    calculateCellValue: function (rowData) {
                                                                        let amount = rowData.amount;
                                                                        let weight = amount * rowData.quantity * rowData.weight;

                                                                        if (isNaN(weight)) {
                                                                            weight = 0;
                                                                        } else {
                                                                            weight = Math.round(weight * 1000) / 1000;
                                                                        }

                                                                        rowData.computed_weight = weight;
                                                                        return weight;
                                                                    },
                                                                    cellTemplate: function (container, options) {
                                                                        let weight = options.data.computed_weight;

                                                                        $(`<div>${weight} т</div>`)
                                                                            .appendTo(container);
                                                                    },
                                                                },
                                                                {
                                                                    dataField: 'id',
                                                                    dataType: 'number',
                                                                    caption: 'Номер операции',
                                                                    groupIndex: 0,
                                                                    sortOrder: 'desc',
                                                                    groupCellTemplate: function (container, options) {
                                                                        console.log(options);
                                                                        let data;

                                                                        if (options.data.items) {
                                                                            data = options.data.items[0];
                                                                        } else {
                                                                            data = options.data.collapsedItems[0];
                                                                        }

                                                                        let operationId = options.text;
                                                                        let operationUrl = data.url;
                                                                        let routeName = data.route_name;
                                                                        let postfix = '';

                                                                        switch (data.operation_route_id) {
                                                                            case 1:
                                                                                postfix = data.contractor_short_name;
                                                                                break;
                                                                            case 2:
                                                                                postfix = `${data.source_project_object_name} ➞ ${data.destination_project_object_name}`;
                                                                                break;
                                                                            case 3:
                                                                                routeName = data.transformation_type_value;
                                                                                break;
                                                                        }

                                                                        if (postfix) {
                                                                            postfix = `(${postfix})`;
                                                                        }

                                                                        $(`<div><a href="${operationUrl}" target="_blank">Операция #${operationId}</a> — ${routeName} ${postfix}</div>`)
                                                                            .appendTo(container);
                                                                    },
                                                                },
                                                            ],
                                                            dataSource: new DevExpress.data.DataSource({
                                                                store: new DevExpress.data.CustomStore({
                                                                    key: 'id',
                                                                    loadMode: 'raw',
                                                                    load: function (loadOptions) {
                                                                        let loadParameters = {
                                                                            projectObjectId: projectObject,
                                                                            materialStandardId: currentMaterialData.standard_id,
                                                                            materialQuantity: currentMaterialData.quantity,
                                                                        };

                                                                        if (currentMaterialData.comment) {
                                                                            loadParameters.commentId = currentMaterialData.comment_id;
                                                                        }
                                                                        return $.getJSON("{{route('materials.standard-history.list')}}",
                                                                            loadParameters);
                                                                    },
                                                                }),
                                                            }),
                                                            onRowPrepared: (e) => {
                                                                e.rowElement.addClass('material-history-detail-row');
                                                            },
                                                        }).appendTo(container);
                                                },
                                            },
                                        },
                                    },
                                ],
                            },
                            {
                                tabTemplate: '<div>Материалы в резерве</div>',
                                items: [
                                    {
                                        name: 'reservedMaterialsGrid',
                                        editorType: 'dxDataGrid',
                                        editorOptions: {
                                            dataSource: reservedMaterialsDataSource,
                                            focusedRowEnabled: false,
                                            hoverStateEnabled: true,
                                            columnAutoWidth: false,
                                            showBorders: true,
                                            showColumnLines: true,
                                            filterRow: {
                                                visible: true,
                                                applyFilter: 'auto',
                                            },
                                            grouping: {
                                                autoExpandAll: true,
                                            },
                                            groupPanel: {
                                                visible: false,
                                            },
                                            paging: {
                                                enabled: false,
                                            },
                                            columns: materialColumns,
                                            summary: {
                                                groupItems: [
                                                    {
                                                        column: 'standard_id',
                                                        summaryType: 'count',
                                                        displayFormat: 'Количество: {0}',
                                                    },
                                                    {
                                                        column: 'amount',
                                                        summaryType: 'sum',
                                                        displayFormat: 'Всего: {0} шт',
                                                        showInGroupFooter: false,
                                                        alignByColumn: true,
                                                    },
                                                    {
                                                        column: 'computed_weight',
                                                        summaryType: 'sum',
                                                        customizeText: function (data) {
                                                            return 'Всего: ' + Math.round(data.value * 1000) / 1000 + ' т';
                                                        },
                                                        showInGroupFooter: false,
                                                        alignByColumn: true,
                                                    },
                                                ],
                                                totalItems: [
                                                    {
                                                        column: 'computed_weight',
                                                        summaryType: 'sum',
                                                        customizeText: function (data) {
                                                            return 'Итого: ' + Math.round(data.value * 1000) / 1000 + ' т';
                                                        },
                                                    },
                                                ],
                                            },
                                        },
                                    },
                                ],
                            },
                        ],
                    },
                ],

            }).dxForm('instance');
            //</editor-fold>

            //<editor-fold desc="JS: Toolbar configuration">
            //</editor-fold>

            function transferMaterials() {
                let materialsToTransferArray = projectObjectInfoForm.getEditor('materialDataGrid').getSelectedRowKeys();
                let transferParams = 'sourceProjectObjectId=' + projectObject;

                if (materialsToTransferArray.length !== 0) {
                    transferParams = transferParams + '&materialsToTransfer=' + encodeURIComponent(materialsToTransferArray.join('+'));
                }

                document.location.href = "{{route('materials.operations.transfer.new')}}" + '/?' + transferParams;
            }

            function transformMaterials() {
                let materialsToTransformArray = projectObjectInfoForm.getEditor('materialDataGrid').getSelectedRowKeys();
                let transformParams = 'projectObjectId=' + projectObject;

                if (materialsToTransformArray.length !== 0) {
                    transformParams = transformParams + '&materialsToTransform=' + encodeURIComponent(materialsToTransformArray.join('+'));
                }

                document.location.href = "{{route('materials.operations.transformation.new')}}" + '/?' + transformParams;
            }

            function writeOffMaterials() {
                let materialsToWriteOffArray = projectObjectInfoForm.getEditor('materialDataGrid').getSelectedRowKeys();
                let writeOffParams = 'project_object=' + projectObject;

                if (materialsToWriteOffArray.length !== 0) {
                    writeOffParams = writeOffParams + '&materialsToWriteOff=' + encodeURIComponent(materialsToWriteOffArray.join('+'));
                }

                document.location.href = "{{route('materials.operations.write-off.new')}}" + '/?' + writeOffParams;
            }

            function updateProjectObjectDetailInfo(projectObjectID) {
                projectObjectsData.store().byKey(projectObjectID).done(function (dataItem) {
                    isStore = dataItem.material_accounting_type === 2;

                    let name = dataItem.name === undefined ? '<Не указано>' : dataItem.name;
                    let address = dataItem.address === undefined ? '<Не указан>' : dataItem.address;

                    $('#projectObjectFullName').html(`${name}`);
                    $('#projectObjectAddress').html(`${address}`);
                });
            }

            updateProjectObjectDetailInfo(projectObject);

            function getOperationRouteIcon(operationRouteId, sourceProjectObjectId, destinationProjectObjectId, transferStageId) {
                switch (operationRouteId) {
                    case 1:
                        return 'fas fa-plus';
                    case 2:
                        if (projectObject === sourceProjectObjectId) {
                            return 'fas fa-sign-out-alt';
                        }

                        if (projectObject === destinationProjectObjectId) {
                            return 'fas fa-sign-in-alt';
                        }

                        break;
                    case 3:
                        switch (transferStageId) {
                            case 1:
                                return 'fas fa-random minus';
                            case 2:
                            case 3:
                                return 'fas fa-random plus';
                            default:
                                return 'fas fa-random';
                        }
                    case 4:
                        return 'fas fa-minus';
                }
            }

            function recalculateGUISizes() {
                let topContainersHeight = 280;

                $('.project-object-group ').find('.dx-form-group-with-caption').height(topContainersHeight);
                $('.active-operations-group').find('.dx-form-group-with-caption').height(topContainersHeight);
                /*let projectObjectGroupInnerContentHeight = $(".project-object-group ").find('.dx-layout-manager.dx-widget').height()
      //                console.log("projectObjectGroupInnerContentHeight", projectObjectGroupInnerContentHeight);
                $(".active-operations-group").find(".dx-datagrid").height(projectObjectGroupInnerContentHeight);
                projectObjectInfoForm.refresh();*/
                //$(".active-operations-group").find(".dx-datagrid").attr("style", "height: " + projectObjectGroupInnerContentHeight + "px");
            }

            function createOperationButtons() {
                let groupTabs = $('.actual-materials-grid').find('.dx-tabpanel-tabs');
                let tabList = groupTabs.find('.dx-tabs');
                console.log(tabList);

                $('<div class="tab-wrapper-button">')
                    .dxButton(
                        {
                            text: 'Комментировать',
                            stylingMode: 'outlined',
                            disabled: true,
                            onClick: (e) => {
                                let popupWindow = $('#commentPopup')
                                    .dxPopup(
                                        {
                                            width: 900,
                                            height: 400,
                                            title: 'Комментарии материалов',
                                            contentTemplate: () => {
                                                let selectedData = projectObjectInfoForm.getEditor('materialDataGrid').getSelectedRowsData();
                                                let container = $('<div>');
                                                let scrollView = $('<div>').appendTo(container);

                                                let dataGrid = $('<div>').dxDataGrid(
                                                    {
                                                        dataSource: {
                                                            store: new DevExpress.data.ArrayStore(
                                                                {
                                                                    key: 'id',
                                                                    data: selectedData,
                                                                },
                                                            ),
                                                        },
                                                        focusedRowEnabled: false,
                                                        hoverStateEnabled: true,
                                                        columnAutoWidth: false,
                                                        showBorders: true,
                                                        showColumnLines: true,
                                                        filterRow: {
                                                            visible: false,
                                                            applyFilter: 'auto',
                                                        },
                                                        grouping: {
                                                            autoExpandAll: true,
                                                        },
                                                        groupPanel: {
                                                            visible: false,
                                                        },
                                                        paging: {
                                                            enabled: false,
                                                        },
                                                        columns: materialColumns,
                                                        masterDetail: {
                                                            enabled: true,
                                                            template: function (container, options) {
                                                                let currentMaterialData = options.data;
                                                                let materialCommentArray = [];

                                                                for (let i = 0; i < currentMaterialData.amount; i++) {
                                                                    materialCommentArray.push(
                                                                        {
                                                                            id: i + 1,
                                                                            comment: currentMaterialData.comment,
                                                                        },
                                                                    );
                                                                }

                                                                $('<div>')
                                                                    .dxDataGrid({
                                                                        columnAutoWidth: true,
                                                                        showBorders: true,
                                                                        showColumnHeaders: false,
                                                                        editing: {
                                                                            allowUpdating: true,
                                                                            mode: 'cell',
                                                                        },
                                                                        columns: [
                                                                            {
                                                                                dataField: 'id',
                                                                                caption: 'Номер',
                                                                                dataType: 'number',
                                                                                width: 34,
                                                                                allowEditing: false,
                                                                            },
                                                                            {
                                                                                dataField: 'comment',
                                                                                caption: 'Комментарий',
                                                                                dataType: 'string',
                                                                            },
                                                                        ],
                                                                        dataSource: new DevExpress.data.DataSource({
                                                                            store: new DevExpress.data.ArrayStore({
                                                                                key: 'id',
                                                                                data: materialCommentArray,
                                                                            }),
                                                                        }),
                                                                    }).appendTo(container);
                                                            },
                                                        },
                                                    },
                                                );

                                                scrollView.append(dataGrid);

                                                scrollView.dxScrollView({
                                                    width: '100%',
                                                    height: '100%',
                                                });

                                                return scrollView;

                                            },
                                        },
                                    ).dxPopup('instance');

                                popupWindow.show();
                            },
                        },
                    )
                    .prependTo(tabList);

                $('<div class="tab-wrapper-button">')
                    .dxDropDownButton({
                        text: 'Операции',
                        dropDownOptions: {
                            width: 230,
                        },
                        onItemClick: function (e) {
                            if (e.itemData === 'Поставка') {
                                let popupWindow = $('#supplyTypePopup')
                                    .dxPopup({
                                        width: 'auto',
                                        height: 'auto',
                                        title: 'Выберите тип поставки',
                                        contentTemplate: function () {
                                            return $('<div>').dxForm({
                                                items: [
                                                    {
                                                        itemType: 'button',
                                                        horizontalAlignment: 'center',
                                                        buttonOptions: {
                                                            text: 'Материал от поставщика',
                                                            type: 'normal',
                                                            stylingMode: 'outlined',
                                                            onClick: () => {
                                                                document.location.href = "{{route('materials.operations.supply.new')}}" + '/?project_object=' + projectObject;
                                                            },
                                                        },
                                                    },
                                                    {
                                                        itemType: 'button',
                                                        horizontalAlignment: 'center',
                                                        buttonOptions: {
                                                            text: 'Материал с другого объекта',
                                                            type: 'normal',
                                                            stylingMode: 'outlined',
                                                            onClick: () => {
                                                                document.location.href = "{{route('materials.operations.transfer.new')}}" + '/?destinationProjectObjectId=' + projectObject;
                                                            },
                                                        },
                                                    },
                                                ],
                                            });
                                        },
                                    })
                                    .dxPopup('instance');

                                popupWindow.show();
                                //document.location.href = "{{route('materials.operations.supply.new')}}" + "/?project_object=" + projectObject;
                            }

                            if (e.itemData === 'Перемещение') {
                                transferMaterials();
                            }

                            if (e.itemData === 'Преобразование') {
                                transformMaterials();
                            }

                            if (e.itemData === 'Списание') {
                                writeOffMaterials();
                            }
                        },
                        items: ['Поставка', 'Перемещение', 'Преобразование', 'Списание'],
                    })
                    .prependTo(tabList);

            }

            createOperationButtons();
            recalculateGUISizes();

        });

    </script>
@endsection
