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
                            return $.getJSON("{{route('materials.snapshots.list')}}",
                                {
                                    snapshotId: snapshotId
                                });
                        }
                    }
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
                    caption: "Количество (шт.)",
                    cellTemplate: function (container, options) {
                        let amount = options.data.amount;

                        $(`<div>${amount} шт.</div>`)
                            .appendTo(container);
                    }
                },
                {
                    dataField: "computed_weight",
                    dataType: "number",
                    caption: "Вес",
                    calculateCellValue: function (rowData) {
                        let weight = rowData.amount * rowData.quantity * rowData.weight;
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
                        let weight = options.data.weight;

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
                    allowSelectAll:true,
                    deferred:false,
                    mode:"multiple",
                    selectAllMode:"allPages",
                    showCheckBoxesMode: "always"
                },
                columns: materialColumns,
                summary: {
                    groupItems: [{
                        column: "standard_id",
                        summaryType: "count",
                        displayFormat: "Количество: {0}",
                    },
                        {
                            column: "amount",
                            summaryType: "sum",
                            displayFormat: "Всего: {0}",
                            showInGroupFooter: false,
                            alignByColumn: true
                        },
                        {
                            column: "quantity",
                            summaryType: "sum",
                            displayFormat: "Всего: {0}",
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

                    let name = dataItem.name === undefined ? '<Не указано>' : dataItem.name;
                    let address = dataItem.address === undefined ? '<Не указан>' : dataItem.address;

                    $('#projectObjectDetailInfo').html(`Полное наименование: ${name}<br>Адрес: ${address}`)
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
    </script>
@endsection
