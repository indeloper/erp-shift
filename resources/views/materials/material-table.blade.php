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

        .standard-name-cell-with-comment {
            margin: 0 -1px -1px -1px;
        }

        .operation-delimiter {
            border-top: 2px solid #a5a4a4 !important;
        }

        .operation-label {
            border: 1px solid #2a6285;
            padding: 2px 6px 2px 6px;
            background: #42a3df;
            color: aliceblue;
            border-radius: 4px;
            font-weight: bold;
        }

        .operation-label:hover {
            color: aliceblue;
            text-decoration: underline;
        }

        .operation-container {
            height: 30px;
            position: absolute;
        }

        .dx-form-group-caption-buttons {
            display: flex;
            flex-direction: row-reverse;
            align-items: flex-start;
        }

        .dx-placeholder {
            line-height: 6px;
        }

        .dx-selectbox {
            height: 29px;
            margin-left: 4px;
        }

        .main-filter-label {
            line-height: 33px;
            font-weight: bold;
        }

        .dx-datagrid-filter-panel {
            display: none !important;
        }

    </style>
@endsection

@section('content')
    <div id="formContainer"></div>
    <div id="gridContainer"></div>
    <div id="filterPopupContainer">
        <div id="filterFormContainer"></div>
    </div>
    <form id="printMaterialsTable" target="_blank" method="post" action="{{route('materials.table.print')}}">
        @csrf
        <input id="projectObjectId" type="hidden" name="projectObjectId">
        <input id="filterOptions" type="hidden" name="filterOptions">
        <input id="filterList" type="hidden" name="filterList">
    </form>
@endsection

@section('js_footer')
    <script>

        let measureUnitsData = {!!$measureUnits!!};
        let materialTypesData = {!!$materialTypes!!};

        let previousOperationId = 0;

        let projectObject = {{$projectObjectId}};

        let filterText = '';

        let dataSourceLoadOptions = {};

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
                store: new DevExpress.data.CustomStore({
                    loadMode: "processed",
                    load: function (loadOptions) {
                        dataSourceLoadOptions = loadOptions;

                        return $.getJSON("{{route('materials.table.list')}}",
                            {data: JSON.stringify(loadOptions), projectObjectId: projectObject});
                    },
                }),
                onChanged: (e) => {
                    previousOperationId = 0;
                }
            });

            let materialGridForm = $("#formContainer").dxForm({
                formData: {
                    currentSelectedFilterGroup: null
                },
                items: [
                    {
                        itemType: "group",
                        caption: "Табель учета материалов",
                        cssClass: "material-snapshot-grid",
                        items: [{
                            name: "materialsTableGrid",
                            editorType: "dxDataGrid",
                            editorOptions: {
                                dataSource: materialsTableDataSource,
                                remoteOperations: true,
                                focusedRowEnabled: false,
                                hoverStateEnabled: true,
                                columnAutoWidth: false,
                                showBorders: true,
                                showColumnLines: true,
                                filterRow: {
                                    visible: true,
                                    applyFilter: "auto"
                                },
                                filterPanel: {
                                    visible: true,
                                    customizeText: (e) => {
                                        filterText = e.text;
                                    }
                                },
                                grouping: {
                                    autoExpandAll: true,
                                },
                                groupPanel: {
                                    visible: false
                                },
                                scrolling: {
                                    mode: "virtual",
                                    rowRenderingMode: "virtual",
                                },
                                paging: {
                                    enabled: true,
                                    pageSize: 100
                                },
                                columns: [
                                    {
                                        dataField: "operation_date",
                                        dataType: "date",
                                        caption: "Дата",
                                        width: 90,
                                        cellTemplate: (container, options) => {
                                            if (previousOperationId === 0) {
                                                previousOperationId = options.data.id;
                                                let operationLabel = $(`<div class='operation-container'><a class="operation-label" target="_blank" href="${options.data.url}">Операция №${options.data.id}</a></div>`)
                                                    .appendTo(container);
                                                $(`<div><br>${options.text}</div>`)
                                                    .appendTo(container);

                                                operationLabel.offset({top: container.parent().offset().top + 8, left: container.parent().offset().left + 8})
                                            } else {
                                                if (options.data.id !== previousOperationId) {
                                                    container.parent().addClass('operation-delimiter');
                                                    previousOperationId = options.data.id;

                                                    let operationLabel = $(`<div class='operation-container'><a class="operation-label" target="_blank" href="${options.data.url}">Операция №${options.data.id}</a></div>`)
                                                        .appendTo(container);

                                                    operationLabel.offset({
                                                        top: container.parent().offset().top - 8,
                                                        left: container.parent().offset().left + 8
                                                    })
                                                }

                                                $(`<div>${options.text}</div>`)
                                                    .appendTo(container);
                                            }


                                        }
                                    },
                                    {
                                        dataField: "route_name",
                                        dataType: "string",
                                        caption: "Вид работ",
                                        width: 155,
                                        cellTemplate: (container, options) => {
                                            let workTypeName = options.data.route_name;
                                            let operationIcon = getOperationRouteIcon(options.data.operation_route_id,
                                                options.data.source_project_object_id,
                                                options.data.destination_project_object_id,
                                                options.data.transform_operation_stage_id
                                            );

                                            $(`<div><i class="${operationIcon}"></i> ${workTypeName}</div>`)
                                                .appendTo(container);
                                        }
                                    },
                                    {
                                        dataField: "standard_name",
                                        dataType: "string",
                                        caption: "Наименование",
                                        width: 250,
                                        cellTemplate: (container, options) => {
                                            $(`<div class="standard-name">${options.text}</div>`)
                                                .appendTo(container);

                                            if (options.data.comment) {
                                                $(`<div class="material-comment">${options.data.comment}</div>`)
                                                    .appendTo(container);

                                                container.addClass("standard-name-cell-with-comment");
                                            }
                                        }
                                    },
                                    {
                                        dataField: "quantity",
                                        dataType: "number",
                                        caption: "Количество",
                                        width: 100,
                                        showSpinButtons: true,
                                        cellTemplate: function (container, options) {
                                            let quantity = options.data.quantity;
                                            let measureUnit = options.data.measure_unit_value;

                                            $(`<div>${Math.round(quantity * 100) / 100} ${measureUnit}</div>`)
                                                .appendTo(container);
                                        }
                                    },
                                    {
                                        dataField: "amount",
                                        dataType: "number",
                                        caption: "Количество (шт)",
                                        width: 125,
                                        cellTemplate: function (container, options) {
                                            let amount = options.data.amount;
                                            $(`<div>${amount} шт</div>`)
                                                .appendTo(container);
                                        }
                                    },
                                    {
                                        dataField: "total_quantity",
                                        dataType: "number",
                                        caption: "Π ед.изм./шт",
                                        width: 100,
                                        cellTemplate: function (container, options) {
                                            let totalQuantity = Math.round(options.data.total_quantity * 100) / 100;
                                            let measureUnit = options.data.measure_unit_value;

                                            $(`<div>${totalQuantity + ' ' + measureUnit}</div>`)
                                                .appendTo(container);
                                        }
                                    },
                                    {
                                        dataField: "weight",
                                        dataType: "number",
                                        caption: "Вес",
                                        width: 100,
                                        cellTemplate: function (container, options) {
                                            let weight = Math.round(options.data.weight * 1000) / 1000;
                                            $(`<div>${weight} т</div>`)
                                                .appendTo(container);
                                        }
                                    },
                                    {
                                        dataField: "coming_from_project_object",
                                        dataType: "string",
                                        caption: "Приход"
                                    },
                                    {
                                        dataField: "outgoing_to_project_object",
                                        dataType: "string",
                                        caption: "Уход"
                                    },
                                    {
                                        dataField: "item_transport_consignment_note_number",
                                        dataType: "string",
                                        caption: "№ ТТН",
                                        width: 80
                                    },
                                    {
                                        dataField: "consignment_note_number",
                                        dataType: "string",
                                        caption: "№ ТН",
                                        width: 80
                                    },
                                ]
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
                materialGridForm.itemOption("filterGroup.snapshotDateFilterGroup", "visible", false);

                materialGridForm.itemOption(visibleFilterGroupName, "visible", true);

                repaintFilterTagBox();
            }

            let filterForm = $("#filterFormContainer").dxForm({
                name: "filterGroup",
                colCount: 4,
                items: [
                    {
                        dataField: 'operationTypeCheckBox',
                        dataType: 'boolean',
                        caption: "Тип операции",
                        value: true,
                        label: {
                            visible: false
                        },
                        editorType: "dxCheckBox",
                        editorOptions: {
                            value: false,
                            text: "Тип операции"
                        }
                    },
                    {
                        colSpan: 3,
                        dataField: 'operationTypeTagBox',
                        dataType: 'integer',
                        caption: "Тип операции",
                        value: true,
                        label: {
                            visible: false
                        },
                        editorType: "dxTagBox",
                        editorOptions: {
                            displayExpr: "name",
                            valueExpr: "id",
                            dataSource: {
                                paginate: true,
                                pageSize: 25,
                                store: new DevExpress.data.CustomStore({
                                    key: "id",
                                    loadMode: "raw",
                                    load: function (loadOptions) {
                                        return $.getJSON("{{route('material.operation.routes.list')}}",
                                            {data: JSON.stringify(loadOptions)});
                                    }
                                })
                            },
                            searchEnabled: true,
                            showSelectionControls: true
                        }
                    },
                    {
                        dataField: 'materialStandardCheckBox',
                        dataType: 'boolean',
                        caption: "Эталон",
                        value: true,
                        label: {
                            visible: false
                        },
                        editorType: "dxCheckBox",
                        editorOptions: {
                            value: false,
                            text: "Эталон"
                        }
                    },
                    {
                        colSpan: 3,
                        dataField: 'materialStandardTagBox',
                        dataType: 'integer',
                        caption: "Эталон",
                        value: true,
                        label: {
                            visible: false
                        },
                        editorType: "dxTagBox",
                        editorOptions: {
                            displayExpr: "name",
                            valueExpr: "id",
                            dataSource: {
                                paginate: true,
                                pageSize: 25,
                                store: new DevExpress.data.CustomStore({
                                    key: "id",
                                    loadMode: "raw",
                                    load: function (loadOptions) {
                                        return $.getJSON("{{route('materials.standards.list')}}",
                                            {data: JSON.stringify(loadOptions)});
                                    }
                                })
                            },
                            searchEnabled: true,
                            showSelectionControls: true
                        }
                    },
                    {
                        itemType: "empty"
                    },
                    {
                        itemType: "button",
                        colSpan: 3,
                        buttonOptions: {
                            text: "ОК",
                            onClick: () => {
                                let selectedReportType = getReportType(filterTasksReportForm.option('formData'));
                                let filterExpression = generateFilterExpression(filterTasksReportForm.option('formData'));
                                $('#filterOptions').val(JSON.stringify({filter: filterExpression}));
                                $('#reportType').val(JSON.stringify({reportType: selectedReportType}));
                                $('#filterTasksReport').get(0).submit();
                            }
                        }
                    }
                ]
            }).dxForm("instance");

            let filterPopup = $("#filterPopupContainer").dxPopup({

            }).dxPopup("instance");

            function createGridReportButtons(){
                let groupCaption = $('.material-snapshot-grid').find('.dx-form-group-with-caption');
                $('<div>').addClass('dx-form-group-caption-buttons').prependTo(groupCaption);
                groupCaption.find('span').addClass('dx-form-group-caption-span-with-buttons');
                let groupCaptionButtonsDiv = groupCaption.find('.dx-form-group-caption-buttons');

                $('<div>')
                    .text("Объект:")
                    .addClass('main-filter-label')
                    .prependTo(groupCaptionButtonsDiv)

                $('<div>')
                    .dxSelectBox({
                        width: 280,
                        dataSource: new DevExpress.data.DataSource({
                            store: new DevExpress.data.CustomStore({
                                key: "id",
                                loadMode: "raw",
                                load: function (loadOptions) {
                                    return $.getJSON("{{route('project-objects.which-participates-in-material-accounting.list')}}",
                                        {data: JSON.stringify(loadOptions)});
                                },
                            })
                        }),
                        displayExpr: "short_name",
                        valueExpr: "id",
                        searchEnabled: true,
                        searchExpr: "short_name",
                        value: projectObject,
                        onValueChanged: (e) => {
                            projectObject = e.value;
                            previousOperationId = 0;
                            materialsTableDataSource.reload();
                            window.history.pushState("", "", "?project_object=" + projectObject);
                        }
                    })
                    .addClass('dx-form-group-caption-button')
                    .prependTo(groupCaptionButtonsDiv)

                $('<div>')
                    .dxButton({
                        text: "Скачать",
                        icon: "fa fa-download",
                        onClick: (e) => {
                            delete dataSourceLoadOptions.skip;
                            delete dataSourceLoadOptions.take;

                            console.log(materialGridForm.getEditor("materialsTableGrid").option("filterValue"));

                            $('#projectObjectId').val(JSON.stringify(projectObject));
                            $('#filterList').val(JSON.stringify(filterText));
                            $('#filterOptions').val(JSON.stringify(dataSourceLoadOptions));
                            $('#printMaterialsTable').get(0).submit();
                        }
                    })
                    .addClass('dx-form-group-caption-button')
                    .prependTo(groupCaptionButtonsDiv)
            }

            createGridReportButtons();

            function getOperationRouteIcon(operationRouteId, sourceProjectObjectId, destinationProjectObjectId, transformStageId) {
                switch (operationRouteId) {
                    case 1:
                        return 'fas fa-plus';
                    case 2:
                        if (projectObject === sourceProjectObjectId) {
                            return 'fas fa-sign-out-alt'
                        }

                        if (projectObject === destinationProjectObjectId) {
                            return 'fas fa-sign-in-alt'
                        }

                        break;
                    case 3:
                        switch (transformStageId) {
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
        });
    </script>
@endsection
