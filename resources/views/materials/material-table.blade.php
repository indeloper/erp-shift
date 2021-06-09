@extends('layouts.app')

@section('title', 'Табель учета')

@section('url', route('materials.table'))

@section('css_top')
    <style>
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

        .dx-form-group.dx-group-no-border {
            border: 0;
            border-radius: 0;
        }
    </style>
@endsection

@section('content')
    <div id="formContainer"></div>
    <div id="gridContainer"></div>
    <form id="printMaterialsTable" target="_blank" multisumit="true" method="post" action="{{route('materials.table.print')}}">
        @csrf
        <input id="filterOptions" type="hidden" name="filterOptions">
        <input id="filterList" type="hidden" name="filterList">
    </form>
@endsection

@section('js_footer')
    <script>

        let measureUnitsData = {!!$measureUnits!!};
        let materialTypesData = {!!$materialTypes!!};

        let filterOptions = [
            {
                name: "Объект",
                filterType: "lookup",
                groupName: "projectObjectFilterGroup"
            },
            {
                name: "Эталон",
                filterType: "lookup",
                groupName: "standardFilterGroup"
            },
        ]

        let filterList = [];

        let dataSourceLoadOptions = {};

        $(function () {
            $("div.content").children(".container-fluid.pd-0-360").removeClass();
        });

        $(function () {
            //<editor-fold desc="JS: DataSources">
            let projectObjectsStore = new DevExpress.data.CustomStore({
                key: "id",
                loadMode: "processed",
                load: function (loadOptions) {
                    return $.getJSON("{{route('project-objects.list')}}",
                        {data: JSON.stringify(loadOptions)});
                }
            });

            let materialsTableDataSource = new DevExpress.data.DataSource({
                reshapeOnPush: true,
                store: new DevExpress.data.CustomStore({
                    key: "id",
                    load: function (loadOptions) {
                        loadOptions.filter = getLoadOptionsFilterArray();
                        dataSourceLoadOptions = loadOptions;

                        return $.getJSON("{{route('materials.table.list')}}",
                            {data: JSON.stringify(loadOptions)});
                    },
                })
            });

            let materialGridForm = $("#formContainer").dxForm({
                formData: {
                    currentSelectedFilterGroup: null
                },
                items: [
                    {
                        itemType: "group",
                        caption: "Фильтрация",
                        name: "filterGroup",
                        items: [
                            {
                                dataField: "currentSelectedFilterGroup",
                                name: "fieldSelectorLookup",
                                label: {visible: false},
                                editorType: "dxLookup",
                                editorOptions: {
                                    dataSource: filterOptions,
                                    displayExpr: "name",
                                    valueExpr: "groupName",
                                    onValueChanged: (e) => {
                                        changeFilterGroupVisibility("filterGroup." + e.value);
                                    }
                                },
                            },
                            {
                                itemType: "group",
                                name: "projectObjectFilterGroup",
                                colCount: 2,
                                visible: false,
                                cssClass: "dx-group-no-border",
                                items: [
                                    {
                                        name: "projectObjectFilterLookup",
                                        editorType: "dxLookup",
                                        editorOptions: {
                                            dataSource: new DevExpress.data.DataSource({
                                                store: new DevExpress.data.CustomStore({
                                                    key: "id",
                                                    //loadMode: "raw",
                                                    load: function (loadOptions) {
                                                        return $.getJSON("{{route('project-objects.list')}}",
                                                            {data: JSON.stringify(loadOptions)});
                                                    },
                                                })
                                            }),
                                            displayExpr: "short_name",
                                            valueExpr: "id",
                                            searchEnabled: true,
                                            searchExpr: "short_name",
                                        },
                                    },
                                    {
                                        editorType: "dxButton",
                                        editorOptions: {
                                            text: "Добавить",
                                            icon:"check",
                                            type:"default",
                                            height: 40,
                                            onClick: (e) => {
                                                let filterElement = materialGridForm.getEditor("projectObjectFilterLookup");
                                                if (filterElement.option("value")) {
                                                    filterList.push(
                                                        {
                                                            id: new DevExpress.data.Guid().toString(),
                                                            fieldName: "project_object_id",
                                                            operation: "=",
                                                            value: filterElement.option("value"),
                                                            text: 'Объект: ' + filterElement.option("text")
                                                        }
                                                    )
                                                }
                                                repaintFilterTagBox();
                                                materialsTableDataSource.reload();
                                            }
                                        }
                                    }
                                ]
                            },
                            {
                                itemType: "group",
                                name: "standardFilterGroup",
                                colCount: 2,
                                visible: false,
                                cssClass: "dx-group-no-border",
                                items: [
                                    {
                                        name: "standardFilterLookup",
                                        editorType: "dxLookup",
                                        editorOptions: {
                                            dataSource: [{id: 1, name: "Да"}, {id: 0, name: "Нет"}],
                                            displayExpr: "name",
                                            valueExpr: "id"
                                        },
                                    },
                                    {
                                        name: "standardFilterRangeSlider",
                                        editorType: "dxRangeSlider",
                                        editorOptions: {
                                            min: 0,
                                            max: 100,
                                            start: 0,
                                            end: 65,
                                            tooltip: {
                                                enabled: true,
                                                format: function (value) {
                                                    return value + "...";
                                                },
                                                showMode: "always",
                                                position: "bottom"
                                            }
                                        }
                                    },
                                    {
                                        editorType: "dxButton",
                                        editorOptions: {
                                            text: "Добавить",
                                            icon:"check",
                                            type:"default",
                                            height: 40,
                                            onClick: (e) => {
                                                let filterElement = materialGridForm.getEditor("haveConflictFilterLookup");
                                                if (filterElement.option("value")) {
                                                    filterList.push(
                                                        {
                                                            id: new DevExpress.data.Guid().toString(),
                                                            fieldName: "have_conflict",
                                                            operation: "=",
                                                            value: filterElement.option("value"),
                                                            text: 'Наличие конфликта: ' + filterElement.option("text")
                                                        }
                                                    )
                                                }
                                                repaintFilterTagBox();
                                                materialsTableDataSource.reload();
                                            }
                                        }
                                    }
                                ]
                            },
                            {
                                itemType: "empty",
                                name: "selectedFilterOperations",
                                cssClass: "selected-filter-operations"
                            }
                        ]
                    },
                    {
                        itemType: "group",
                        caption: "Табель учета материалов",
                        cssClass: "material-snapshot-grid",
                        name: "materialsTableGrid",
                        items: [{
                            name: "reservedMaterialsGrid",
                            editorType: "dxDataGrid",
                            editorOptions: {
                                dataSource: materialsTableDataSource,
                                focusedRowEnabled: false,
                                hoverStateEnabled: true,
                                columnAutoWidth: false,
                                showBorders: true,
                                showColumnLines: true,
                                filterRow: {
                                    visible: false,
                                    applyFilter: "auto"
                                },
                                grouping: {
                                    autoExpandAll: true,
                                },
                                groupPanel: {
                                    visible: false
                                },
                                paging: {
                                    enabled: false
                                },
                                columns: [
                                    {
                                        dataField: "project_object_short_name",
                                        dataType: "string",
                                        caption: "Объект",
                                        width: 500,
                                        sortOrder: "asc",
                                        groupIndex: 0
                                    },
                                    {
                                        dataField: "standard_name",
                                        dataType: "string",
                                        caption: "Наименование",
                                        width: 500,
                                        sortIndex: 0,
                                        sortOrder: "asc"
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
                                        sortIndex: 1,
                                        sortOrder: "asc",
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
                                        sortIndex: 2,
                                        sortOrder: "asc",
                                        cellTemplate: function (container, options) {
                                            let amount = options.data.amount;
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
                                            let weight = amount * rowData.quantity * rowData.weight;

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
                                        groupIndex: 1,
                                        lookup: {
                                            dataSource: materialTypesData,
                                            displayExpr: "name",
                                            valueExpr: "id"
                                        }
                                    }
                                ],
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
                            }
                        }]
                    }
                ]
            }).dxForm("instance");

            function repaintFilterTagBox() {
                let selectedFilterOperationsDiv = $(".selected-filter-operations");
                selectedFilterOperationsDiv.empty();
                selectedFilterOperationsDiv.append(getFilterTagBoxTemplate());

                $( ".dx-tag-remove-button" ).click(function() {
                    let filterId = $(this).parent().parent().attr("filter-id");
                    let filterItemIndex = null;
                    filterList.forEach((item, index) => {
                        if (item.id === filterId) {
                            filterItemIndex = index;
                        }
                    });

                    if (filterItemIndex !== null){
                        filterList.splice(filterItemIndex, 1);
                        $(this).parent().parent().remove();
                        materialsTableDataSource.reload();
                    }
                });
            }

            function getFilterTagBoxTemplate() {
                let result = "";

                filterList.forEach((item) => {
                    //result = result + '<div class="filter-operation-box">' + item.text + '</div>'
                    result = result + '<div class="dx-tag" filter-id="' + item.id + '">' +
                                        '<div class="dx-tag-content">' +
                                            '<span>' + item.text + '</span>' +
                                            '<div class="dx-tag-remove-button">' +
                                            '</div>' +
                                        '</div>' +
                                      '</div>'
                })

                return result;
            }

            function getLoadOptionsFilterArray() {
                let filterArray = [];
                filterList.forEach((item, index) => {
                    filterArray.push([item.fieldName, item.operation, item.value]);

                    if (filterList.length - 1 !== index) {
                        filterArray.push('and');
                    }
                })
                return filterArray;
            }

            function changeFilterGroupVisibility(visibleFilterGroupName) {
                materialGridForm.itemOption("filterGroup.projectObjectFilterGroup", "visible", false);
                materialGridForm.itemOption("filterGroup.standardFilterGroup", "visible", false);

                materialGridForm.itemOption(visibleFilterGroupName, "visible", true);

                repaintFilterTagBox();
            }

            function createGridReportButtons(){
                let groupCaption = $('.material-snapshot-grid').find('.dx-form-group-with-caption');
                $('<div>').addClass('dx-form-group-caption-buttons').prependTo(groupCaption);
                groupCaption.find('span').addClass('dx-form-group-caption-span-with-buttons');
                let groupCaptionButtonsDiv = groupCaption.find('.dx-form-group-caption-buttons');

                $('<div>')
                    .dxButton({
                        text: "Отчет",
                        icon: "fa fa-download"
                    })
                    .addClass('dx-form-group-caption-button')
                    .prependTo(groupCaptionButtonsDiv)

                $('<div>')
                    .dxButton({
                        text: "Печать",
                        icon: "fa fa-print",
                        onClick: (e) => {
                            delete dataSourceLoadOptions.skip;
                            delete dataSourceLoadOptions.take;

                            $('#filterList').val(JSON.stringify(filterList));
                            $('#filterOptions').val(JSON.stringify(dataSourceLoadOptions));
                            $('#printMaterialsTable').submit();
                        }
                    })
                    .addClass('dx-form-group-caption-button')
                    .prependTo(groupCaptionButtonsDiv)
            }

            createGridReportButtons();
        });
    </script>
@endsection
