@extends('layouts.app')

@section('title', 'Операции')

@section('url', route('materials.operations.index'))

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
    <form id="printAllOperations" target="_blank" multisumit="true" method="post" action="{{route('materials.operations.print')}}">
        @csrf
        <input id="filterOptions" type="hidden" name="filterOptions">
        <input id="filterList" type="hidden" name="filterList">
    </form>
@endsection

@section('js_footer')
    <script>

        let filterOptions = [
            {
                name: "Объект",
                filterType: "lookup",
                groupName: "projectObjectFilterGroup"
            },
            {
                name: "Тип операции",
                filterType: "lookup",
                groupName: "operationTypeFilterGroup"
            },
            {
                name: "Статус",
                filterType: "lookup",
                groupName: "operationStageFilterGroup"
            },
            {
                name: "Автор",
                filterType: "lookup",
                groupName: "operationAuthorFilterGroup"
            },
            {
                name: "Наличие конфликта",
                filterType: "lookup",
                groupName: "haveConflictFilterGroup"
            },
            /*{
                name: "Эталон",
                filterType: "range",
                groupName: "standardFilterGroup"
            },*/
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

            let operationRoutesStore = new DevExpress.data.CustomStore({
                key: "id",
                loadMode: "processed",
                useDefaultSearch: true,
                load: function (loadOptions) {
                    return $.getJSON("{{route('material.operation.routes.list')}}",
                        {data: JSON.stringify(loadOptions)});
                },
            });

            let operationsDataSource = new DevExpress.data.DataSource({
                reshapeOnPush: true,
                store: new DevExpress.data.CustomStore({
                    key: "id",
                    load: function (loadOptions) {
                        loadOptions.filter = getLoadOptionsFilterArray();
                        dataSourceLoadOptions = loadOptions;
                        console.log(loadOptions);

                        return $.getJSON("{{route('materials.operations.list')}}",
                            {data: JSON.stringify(loadOptions)});
                    },
                })
            });

            //</editor-fold>

            //<editor-fold desc="JS: Edit form configuration">

            //</editor-fold>

            //<editor-fold desc="JS: Columns definition">
            let materialTypesColumns = [
                {
                    dataField: "id",
                    caption: "Номер",
                    dataType: "number",
                    width: 70,
                    cellTemplate: function (container, options) {
                        container.html('<a href="{{route('materials.operations.transfer.view')}}/?operationId=' + options.displayValue + '">' + options.displayValue + '</a>');
                    }
                },
                {
                    dataField: "operation_route_id",
                    dataType: "number",
                    caption: "Тип",
                    lookup: {
                        dataSource: {
                            paginate: true,
                            pageSize: 25,
                            store: operationRoutesStore
                        },
                        displayExpr: "name",
                        valueExpr: "id",
                    }
                },
                {
                    dataField: "material_types_info",
                    dataType: "string",
                    caption: "Материалы",
                    cellTemplate: function (container, options) {
                        container.html(options.displayValue);
                    }
                },
                {
                    dataField: "source_project_object_id",
                    dataType: "number",
                    caption: "Объект отправления",
                    lookup: {
                        dataSource: {
                            paginate: true,
                            pageSize: 25,
                            store: projectObjectsStore
                        },
                        displayExpr: "short_name",
                        valueExpr: "id"
                    }
                },
                {
                    dataField: "destination_project_object_id",
                    dataType: "number",
                    caption: "Объект назначения",
                    lookup: {
                        dataSource: {
                            paginate: true,
                            pageSize: 25,
                            store: projectObjectsStore
                        },
                        displayExpr: "short_name",
                        valueExpr: "id"
                    }
                },
                {
                    dataField: "operation_date",
                    dataType: "date",
                    caption: "Дата операции"
                },
                {
                    dataField: "operation_route_stage_name",
                    dataType: "string",
                    caption: "Статус"
                }
            ];
            //</editor-fold>

            //<editor-fold desc="JS: Grid configuration">
            let operationsGridOptions = {
                dataSource: operationsDataSource,
                remoteOperations: true,
                scrolling: {
                    mode: "virtual",
                    rowRenderingMode: "virtual",
                    useNative: false,
                    scrollByContent: true,
                    scrollByThumb: true,
                    showScrollbar: "onHover"
                },
                paging: {
                    pageSize: 100
                },
                height: function () {
                    return $("div.content").height()
                },
                showColumnLines: true,
                focusedRowEnabled: true,
                hoverStateEnabled: true,
                columnAutoWidth: false,
                showBorders: true,
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
                columns: materialTypesColumns,
                onRowPrepared: (e) => {
                    if (e.rowType === "data") {
                        if (e.data.have_conflict) {
                            e.rowElement.addClass("row-conflict-operation")
                        }
                    }
                }
            };
            //</editor-fold>

            let operationGridForm = $("#formContainer").dxForm({
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
                                                    loadMode: "processed",
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
                                                let filterElement = operationGridForm.getEditor("projectObjectFilterLookup");
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
                                                operationsDataSource.reload();
                                            }
                                        }
                                    }
                                ]
                            },
                            {
                                itemType: "group",
                                name: "operationTypeFilterGroup",
                                colCount: 2,
                                visible: false,
                                cssClass: "dx-group-no-border",
                                items: [
                                    {
                                        name: "operationTypeFilterLookup",
                                        editorType: "dxLookup",
                                        editorOptions: {
                                            dataSource: new DevExpress.data.DataSource({
                                                store: new DevExpress.data.CustomStore({
                                                    key: "id",
                                                    loadMode: "processed",
                                                    load: function (loadOptions) {
                                                        return $.getJSON("{{route('material.operation.routes.list')}}",
                                                            {data: JSON.stringify(loadOptions)});
                                                    },
                                                })
                                            }),
                                            displayExpr: "name",
                                            valueExpr: "id",
                                            searchEnabled: true,
                                            searchExpr: "name",
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
                                                let filterElement = operationGridForm.getEditor("operationTypeFilterLookup");
                                                if (filterElement.option("value")) {
                                                    filterList.push(
                                                        {
                                                            id: new DevExpress.data.Guid().toString(),
                                                            fieldName: "q3w_material_operations.operation_route_id",
                                                            operation: "=",
                                                            value: filterElement.option("value"),
                                                            text: 'Тип операции: ' + filterElement.option("text")
                                                        }
                                                    )
                                                }
                                                repaintFilterTagBox();
                                                operationsDataSource.reload();
                                            }
                                        }
                                    }
                                ]
                            },
                            {
                                itemType: "group",
                                name: "operationStageFilterGroup",
                                colCount: 2,
                                visible: false,
                                cssClass: "dx-group-no-border",
                                items: [
                                    {
                                        name: "operationStageFilterLookup",
                                        editorType: "dxLookup",
                                        editorOptions: {
                                            dataSource: new DevExpress.data.DataSource({
                                                store: new DevExpress.data.CustomStore({
                                                    key: "id",
                                                    loadMode: "processed",
                                                    load: function (loadOptions) {
                                                        return $.getJSON("{{route('material.operation.route-stages-without-notifications.list')}}",
                                                            {data: JSON.stringify(loadOptions)});
                                                    },
                                                })
                                            }),
                                            displayExpr: "name",
                                            valueExpr: "name",
                                            searchEnabled: true,
                                            searchExpr: "name",
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
                                                let filterElement = operationGridForm.getEditor("operationStageFilterLookup");
                                                if (filterElement.option("value")) {
                                                    filterList.push(
                                                        {
                                                            id: new DevExpress.data.Guid().toString(),
                                                            fieldName: "q3w_operation_route_stages.name",
                                                            operation: "=",
                                                            value: filterElement.option("value"),
                                                            text: 'Статус: ' + filterElement.option("text")
                                                        }
                                                    )
                                                }
                                                repaintFilterTagBox();
                                                operationsDataSource.reload();
                                            }
                                        }
                                    }
                                ]
                            },
                            {
                                itemType: "group",
                                name: "operationAuthorFilterGroup",
                                colCount: 2,
                                visible: false,
                                cssClass: "dx-group-no-border",
                                items: [
                                    {
                                        name: "operationAuthorFilterLookup",
                                        editorType: "dxLookup",
                                        editorOptions: {
                                            dataSource: new DevExpress.data.DataSource({
                                                store: new DevExpress.data.CustomStore({
                                                    key: "id",
                                                    loadMode: "processed",
                                                    load: function (loadOptions) {
                                                        return $.getJSON("{{route('users.list')}}",
                                                            {data: JSON.stringify(loadOptions)});
                                                    },
                                                })
                                            }),
                                            displayExpr: "full_name",
                                            valueExpr: "id",
                                            searchEnabled: true,
                                            searchExpr: "last_name",
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
                                                let filterElement = operationGridForm.getEditor("operationAuthorFilterLookup");
                                                if (filterElement.option("value")) {
                                                    filterList.push(
                                                        {
                                                            id: new DevExpress.data.Guid().toString(),
                                                            fieldName: "q3w_material_operations.creator_user_id",
                                                            operation: "=",
                                                            value: filterElement.option("value"),
                                                            text: 'Автор: ' + filterElement.option("text")
                                                        }
                                                    )
                                                }
                                                repaintFilterTagBox();
                                                operationsDataSource.reload();
                                            }
                                        }
                                    }
                                ]
                            },
                            {
                                itemType: "group",
                                name: "haveConflictFilterGroup",
                                colCount: 2,
                                visible: false,
                                cssClass: "dx-group-no-border",
                                items: [
                                    {
                                        name: "haveConflictFilterLookup",
                                        editorType: "dxLookup",
                                        editorOptions: {
                                            dataSource: [{id: 1, name: "Да"}, {id: 0, name: "Нет"}],
                                            displayExpr: "name",
                                            valueExpr: "id"
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
                                                let filterElement = operationGridForm.getEditor("haveConflictFilterLookup");
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
                                                operationsDataSource.reload();
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
                                            valueExpr: "id",
                                            searchEnabled: true,
                                            searchExpr: "name",
                                        },
                                    },
                                    {
                                        name: "standardFilterRangeSlider",
                                        editorType: "dxRangeSlider",
                                        editorOptions: {
                                            min: 0,
                                            max: 100,
                                            start: 15,
                                            end: 65,
                                            tooltip: {
                                                enabled: true,
                                                format: function (value) {
                                                    return value + "м.п.";
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
                                                let filterElement = operationGridForm.getEditor("haveConflictFilterLookup");
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
                                                operationsDataSource.reload();
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
                        caption: "Список операций",
                        cssClass: "all-operations-grid",
                        items: [
                            {
                                editorType: "dxDataGrid",
                                name: "operationsGrid",
                                editorOptions: operationsGridOptions
                            }
                        ]
                    }
                ]
            }).dxForm("instance");

            //<editor-fold desc="JS: Toolbar configuration">
            //</editor-fold>

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
                        operationsDataSource.reload();
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

                    if (item.fieldName === "project_object_id") {
                        let projectObjectFilterArray = [];
                        projectObjectFilterArray.push(['source_project_object_id', item.operation, item.value]);
                        projectObjectFilterArray.push('or');
                        projectObjectFilterArray.push(['destination_project_object_id', item.operation, item.value]);
                        filterArray.push(projectObjectFilterArray);
                    } else
                    if (item.fieldName === "have_conflict") {
                        let haveConflictFilterArray = [];
                        let operation = "<>";
                        if  (item.value) {
                            operation = "=";
                        }

                        haveConflictFilterArray.push(['q3w_operation_route_stages.id', operation, 11]);
                        haveConflictFilterArray.push('or');
                        haveConflictFilterArray.push(['q3w_operation_route_stages.id', operation, 19]);
                        haveConflictFilterArray.push('or');
                        haveConflictFilterArray.push(['q3w_operation_route_stages.id', operation, 30]);
                        haveConflictFilterArray.push('or');
                        haveConflictFilterArray.push(['q3w_operation_route_stages.id', operation, 38]);

                        filterArray.push(haveConflictFilterArray);
                    } else {
                        filterArray.push([item.fieldName, item.operation, item.value]);
                    }

                    if (filterList.length - 1 !== index) {
                        filterArray.push('and');
                    }
                })
                console.log(filterArray);
                return filterArray;
            }

            function changeFilterGroupVisibility(visibleFilterGroupName) {
                operationGridForm.itemOption("filterGroup.projectObjectFilterGroup", "visible", false);
                operationGridForm.itemOption("filterGroup.operationTypeFilterGroup", "visible", false);
                operationGridForm.itemOption("filterGroup.operationStageFilterGroup", "visible", false);
                operationGridForm.itemOption("filterGroup.operationAuthorFilterGroup", "visible", false);
                operationGridForm.itemOption("filterGroup.haveConflictFilterGroup", "visible", false);
                operationGridForm.itemOption("filterGroup.standardFilterGroup", "visible", false);

                operationGridForm.itemOption(visibleFilterGroupName, "visible", true);

                repaintFilterTagBox();
            }

            function createGridReportButtons(){
                let groupCaption = $('.all-operations-grid').find('.dx-form-group-with-caption');
                $('<div>').addClass('dx-form-group-caption-buttons').prependTo(groupCaption);
                groupCaption.find('span').addClass('dx-form-group-caption-span-with-buttons');
                let groupCaptionButtonsDiv = groupCaption.find('.dx-form-group-caption-buttons');

                $('<div>')
                    .dxButton({
                        text: "Печать",
                        icon: "fa fa-print",
                        onClick: (e) => {
                            delete dataSourceLoadOptions.skip;
                            delete dataSourceLoadOptions.take;

                            $('#filterList').val(JSON.stringify(filterList));
                            $('#filterOptions').val(JSON.stringify(dataSourceLoadOptions));
                            console.log(dataSourceLoadOptions);
                            $('#printAllOperations').submit();
                        }
                    })
                    .addClass('dx-form-group-caption-button')
                    .prependTo(groupCaptionButtonsDiv)
            }

            createGridReportButtons();
        });
    </script>
@endsection
