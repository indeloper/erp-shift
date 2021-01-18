@extends('layouts.app')

@section('title', 'Материалы')

@section('url', route('materials.index'))

@section('css_top')
    <link rel="stylesheet" href="{{asset('css/plugins/codyhouse-horizontal-timeline-2.0/style.css')}}">

    <style>
        td.dx-command-select {
            border-right: none !important;
        }

        .dx-command-expand {
            border-left: none !important;
        }

        /*Snapshot tiles*/
        .dx-item-content .dx-tile-content {
            display: flex;
        }

        .snapshot-tile-icon {
            width: 20%;
            height: 100%;
            border-right: 1px lightgray dashed;

            text-align: center;
        }

        .snapshot-tile-icon > i {
            line-height: 40px;

            font-size: x-large;
            font-weight: bold;
        }

        .snapshot-tile-icon > i.fa-sign-out-alt {
            font-size: large;
        }

        .snapshot-tile-icon > i.fa-sign-in-alt {
            font-size: large;
        }

        i.dx-icon-plus {
            color: green;
        }

        i.fas.fa-sign-out-alt {
            color: indianred;
        }

        i.fas.fa-sign-in-alt {
            color: green;
        }

        .snapshot-tile-content {
            width: 80%;
            height: 100%;
            padding: 4px;
            text-align: right;
        }

        .dx-form-group {
            background-color: #fff;
            border: 1px solid #cfcfcf;
            border-radius: 1px;
            box-shadow: 0 1px 4px 0 rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .dx-layout-manager .dx-field-item:not(.dx-first-col) {
            padding-left: 0px !important;
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
            let projectObject = {{$projectObjectId}};
            let snapshotId = null;

            let operationRoutesStore = new DevExpress.data.CustomStore({
                key: "id",
                loadMode: "raw",
                load: function (loadOptions) {
                    return $.getJSON("{{route('material.operation.routes.list')}}",
                        {data: JSON.stringify(loadOptions)});
                },
            });

            let operationRouteStagesStore = new DevExpress.data.CustomStore({
                key: "id",
                loadMode: "processed",
                load: function (loadOptions) {
                    return $.getJSON("{{route('material.operation.route-stages.list')}}",
                        {data: JSON.stringify(loadOptions)});
                },
            });

            let projectObjectsData = new DevExpress.data.DataSource({
                reshapeOnPush: true,
                store: new DevExpress.data.CustomStore({
                    key: "id",
                    loadMode: "raw",
                    load: function (loadOptions) {
                        return $.getJSON("{{route('project-objects.list')}}",
                            {data: JSON.stringify(loadOptions)});
                    },
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
                            return $.getJSON("{{route('materials.snapshots-materials.list')}}",
                                {
                                    snapshotId: snapshotId
                                });
                        }
                    }
                })
            });

            let materialSnapshotsStore = new DevExpress.data.CustomStore({
                key: "id",
                loadMode: "raw",
                load: function (loadOptions) {
                    return $.getJSON("{{route('materials.snapshots.list')}}",
                        {projectObjectId: projectObject});
                },
            });

            let materialSnapshotsDataSource = new DevExpress.data.DataSource({
                store: materialSnapshotsStore
            })

            let projectObjectActiveOperationsStore = new DevExpress.data.CustomStore({
                key: "id",
                loadMode: "raw",
                load: function (loadOptions) {
                    return $.getJSON("{{route('materials.operations.list.project-object.active')}}",
                        {projectObjectId: projectObject});
                },
            });

            let projectObjectActiveOperationsDataSource = new DevExpress.data.DataSource({
                store: projectObjectActiveOperationsStore
            })


            //</editor-fold>

            //<editor-fold desc="JS: Info form configuration">
            let projectObjectInfoForm = $('#projectObjectForm').dxForm({
                formData: [],
                colCount: 2,
                items: [
                    {
                        itemType: "group",
                        caption: "Объект",
                        height: "400px",
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
                                        materialSnapshotsDataSource.reload();
                                        projectObjectActiveOperationsDataSource.reload();
                                        window.history.pushState("", "", "?project_object=" + projectObject)
                                    }
                                }
                            },
                            {
                                label: {
                                    visible: true,
                                    text: "Полное наименование"
                                },
                                template: '<div id="projectObjectFullName"></div>'
                            },
                            {
                                label: {
                                    visible: true,
                                    text: "Адрес"
                                },
                                template: '<div id="projectObjectAddress"></div>'
                            }
                        ]
                    },
                    {
                        itemType: "group",
                        caption: "Активные операции",
                        cssClass: "some-css-class",
                        items: [{
                            editorType: "dxDataGrid",
                            editorOptions: {
                                dataSource: projectObjectActiveOperationsDataSource,
                                focusedRowEnabled: false,
                                hoverStateEnabled: true,
                                columnAutoWidth: false,
                                showBorders: true,
                                showColumnLines: true,
                                height: 183,
                                columns: [
                                    {
                                        dataField: "id",
                                        dataType: "number",
                                        caption: "Операция",
                                        showSpinButtons: true,
                                        cellTemplate: function (container, options) {
                                            let operationId = options.data.id;

                                            $(`<div><a href={{route('materials.operations.transfer.view')}}/?operationId=${operationId}>Операция #${operationId}</a></div>`)
                                                .appendTo(container);
                                        }
                                    },
                                    {
                                        dataField: "operation_route_id",
                                        dataType: "number",
                                        caption: "Тип операции",
                                        lookup: {
                                            dataSource: {
                                                paginate: true,
                                                pageSize: 25,
                                                store: operationRoutesStore
                                            },
                                            displayExpr: "name",
                                            valueExpr: "id"
                                        }
                                    },
                                    {
                                        dataField: "operation_route_stage_id",
                                        dataType: "number",
                                        caption: "Этап",
                                        lookup: {
                                            dataSource: {
                                                paginate: true,
                                                pageSize: 25,
                                                store: operationRouteStagesStore
                                            },
                                            displayExpr: "name",
                                            valueExpr: "id"
                                        }
                                    },
                                ]
                            }
                        }]
                    },
                    {
                        itemType: "group",
                        colSpan: 2,
                        caption: "История операций",
                        items: [{
                            editorType: "dxTileView",
                            editorOptions: {
                                height: 50,
                                baseItemHeight: 40,
                                baseItemWidth: 140,
                                itemMargin: 10,
                                direction: "horizontal",
                                showScrollbar: true,
                                dataSource: materialSnapshotsDataSource,
                                onItemClick: function (e) {
                                    snapshotId = e.itemData.id;
                                    $("#gridContainer").dxDataGrid("instance").refresh();
                                },
                                itemTemplate: function (itemData, _, itemElement) {
                                    let operationIcon;
                                    let operationCaption;

                                    switch (itemData.operation_route_id) {
                                        case 1:
                                            operationCaption = "Поставка";
                                        case 2:
                                            operationCaption = "Перемещение";
                                    }

                                    operationIcon = getOperationRouteIcon(itemData.operation_route_id, itemData.source_project_object_id, itemData.destination_project_object_id);

                                    itemElement.append('<div class="snapshot-tile-icon">' +
                                        '<i class="' + operationIcon + '"></i>' +
                                        '</div>');

                                    createdDate = new Intl.DateTimeFormat('ru-RU', {
                                        dateStyle: 'short',
                                        timeStyle: 'short'
                                    }).format(new Date(itemData.created_at));

                                    createdDate = createdDate.replaceAll(',', ' ');

                                    itemElement.append('<div class="snapshot-tile-content">' +
                                        createdDate +
                                        '<br>' +
                                        operationCaption +
                                        '</div>');
                                    console.log(itemData);
                                }
                            }
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
                    }
                },
                {
                    dataField: "measure_unit",
                    dataType: "number",
                    caption: "Ед. изм.",
                    alignment: "right",
                    lookup: {
                        dataSource: measureUnitsData,
                        displayExpr: "value",
                        valueExpr: "id"
                    }
                },
                {
                    dataField: "quantity",
                    dataType: "number",
                    caption: "Количество",
                    showSpinButtons: true,
                    cellTemplate: function (container, options) {
                        let quantity = options.data.quantity;
                        let measureUnit = options.data.measure_unit_value;

                        $(`<div>${quantity} ${measureUnit}</div>`)
                            .appendTo(container);
                    }
                },
                {
                    dataField: "amount",
                    dataType: "number",
                    caption: "Количество (шт)",
                    cellTemplate: function (container, options) {
                        let amount = options.data.amount;
                        if (options.data.from_operation === 1) {
                            amount = amount * options.data.amount_modifier;
                        }
                        $(`<div>${amount} шт</div>`)
                            .appendTo(container);
                    }
                },
                {
                    dataField: "computed_weight",
                    dataType: "number",
                    caption: "Вес",
                    calculateCellValue: function (rowData) {
                        let amount = rowData.amount;
                        if (rowData.from_operation === 1) {
                            amount = amount * rowData.amount_modifier;
                        }

                        let weight = amount * rowData.quantity * rowData.weight;
                        console.log(rowData);
                        if (isNaN(weight)) {
                            weight = 0;
                        } else {
                            weight = weight.toFixed(3)
                        }

                        rowData.computed_weight = weight;
                        return weight;
                    },
                    cellTemplate: function (container, options) {
                        let weight = options.data.computed_weight;

                        $(`<div>${weight} т.</div>`)
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
                    allowSelectAll: true,
                    deferred: false,
                    mode: "multiple",
                    selectAllMode: "allPages",
                    showCheckBoxesMode: "always"
                },
                columns: materialColumns,
                onRowPrepared: function (e) {
                    console.log("Row prepared");
                    console.log(e);
                    if (e.rowType === "data") {
                        if (e.data.from_operation === 1) {
                            e.rowElement.find(".dx-datagrid-group-closed")
                                .replaceWith('<i class="fas fa-lock"><i>')
                            e.rowElement.find(".dx-select-checkbox").remove();

                            e.rowElement.css("color", "gray");
                        }
                    }
                },
                summary: {
                    groupItems: [{
                        column: "standard_id",
                        summaryType: "count",
                        displayFormat: "Количество: {0}",
                    },
                        {
                            column: "amount",
                            summaryType: "sum",
                            displayFormat: "Всего: {0} шт",
                            showInGroupFooter: false,
                            alignByColumn: true
                        },
                        {
                            column: "computed_weight",
                            summaryType: "sum",
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
                masterDetail: {
                    enabled: true,
                    template: function(container, options) {
                        var currentMaterialData = options.data;

                        $("<div>")
                            .addClass("master-detail-caption")
                            .text("История материала")
                            .appendTo(container);

                        $("<div>")
                            .dxDataGrid({
                                columnAutoWidth: true,
                                showBorders: true,
                                columns: [
                                    {
                                        dataField: "operation_route_id",
                                        caption: "",
                                        dataType: "number",
                                        width: 24,
                                        cellTemplate: function (container, options) {
                                            let operationIcon = getOperationRouteIcon(options.data.operation_route_id, options.data.source_project_object_id, options.data.destination_project_object_id);

                                            $(`<div><i class="${operationIcon}"></i></div>`)
                                                .appendTo(container);
                                        }
                                    },
                                    {
                                        dataField: "operation_date",
                                        caption: "Дата",
                                        dataType: "datetime",
                                        cellTemplate: function (container, options) {
                                            let operationDate = options.text.replaceAll(',', ' ');

                                            $(`<div>${operationDate}</div>`)
                                                .appendTo(container);
                                        }
                                    },
                                    {
                                        dataField: "quantity",
                                        caption: "Количество",
                                        dataType: "number",
                                        cellTemplate: function (container, options) {
                                            let quantity = options.data.quantity;
                                            let measureUnit = options.data.measure_unit_value;

                                            $(`<div>${quantity} ${measureUnit}</div>`)
                                                .appendTo(container);
                                        }
                                    },
                                    {
                                        dataField: "amount",
                                        caption: "Количество (шт)",
                                        dataType: "number",
                                        cellTemplate: function (container, options) {
                                            let amount = options.data.quantity;

                                            $(`<div>${amount} шт</div>`)
                                                .appendTo(container);
                                        }
                                    }
                                ],
                                dataSource: new DevExpress.data.DataSource({
                                    store: new DevExpress.data.CustomStore({
                                        key: "id",
                                        loadMode: "raw",
                                        load: function (loadOptions) {
                                            return $.getJSON("{{route('materials.standard-history.list')}}",
                                                {
                                                    projectObjectId: projectObject,
                                                    materialStandardId: currentMaterialData.standard_id
                                                });
                                        },
                                    }),

                                })
                            }).appendTo(container);
                    }
                },
                onToolbarPreparing: function (e) {
                    e.toolbarOptions.items.unshift(
                        {
                            location: "after",
                            widget: "dxDropDownButton",
                            options: {
                                text: "Операции",
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
                    let name = dataItem.name === undefined ? '<Не указано>' : dataItem.name;
                    let address = dataItem.address === undefined ? '<Не указан>' : dataItem.address;

                    $('#projectObjectFullName').html(`${name}`)
                    $('#projectObjectAddress').html(`${address}`)
                })
            }

            updateProjectObjectDetailInfo(projectObject);

            function getOperationRouteIcon(operationRouteId, sourceProjectObjectId, destinationProjectObjectId) {
                switch (operationRouteId) {
                    case 1:
                        return 'dx-icon-plus';
                        break;
                    case 2:
                        if (projectObject === sourceProjectObjectId) {
                            return 'fas fa-sign-out-alt'
                        }

                        if (projectObject === destinationProjectObjectId) {
                            return 'fas fa-sign-in-alt'
                        }

                        break;
                }
            }
        });


    </script>
@endsection
