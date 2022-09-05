@extends('layouts.app')

@section('title', 'Планирование поставок материалов')

@section('url', route('materials.supply-planning.index'))

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

        .dx-form-group-caption-buttons {
            display: flex;
            flex-direction: row-reverse;
            align-items: flex-start;
        }

        .dx-placeholder {
            line-height: 6px;
        }

        .supply-planning-details {
            border: 1px solid #e0e0e0;
        }

        .dx-datagrid-borders > .dx-datagrid-header-panel {
            border-bottom: 0;
            border-top: 1px solid #e0e0e0;
            border-left: 1px solid #e0e0e0;
            border-right: 1px solid #e0e0e0;
        }

        .dx-datagrid-header-panel .dx-button {
            margin-top: 6px;
            margin-right: 8px;
        }

        .supply-planning-materials {
            margin-bottom: 16px;
        }
    </style>
@endsection

@section('content')
    <div id="formContainer"></div>
    <div id="gridContainer"></div>
@endsection

@section('js_footer')
    <script>
        let dataSourceLoadOptions = {};

        $(function () {
            $("div.content").children(".container-fluid.pd-0-360").removeClass();
        });

        $(function () {
            //<editor-fold desc="JS: DataSources">
            let projectObjectsStore = new DevExpress.data.CustomStore({
                key: "id",
                loadMode: "raw",
                load: function (loadOptions) {
                    return $.getJSON("{{route('project-objects.list')}}",
                        {data: JSON.stringify(loadOptions)});
                },
            })

            let projectObjectsDataSource = new DevExpress.data.DataSource({
                store: projectObjectsStore
            });

            let brandsStore = new DevExpress.data.CustomStore({
                key: "id",
                loadMode: "raw",
                load: function (loadOptions) {
                    return $.getJSON("{{route('materials.brands.list')}}",
                        {data: JSON.stringify(loadOptions)});
                }
            })

            let brandsDataSource = new DevExpress.data.DataSource({
                store: brandsStore
            })

            let contractorsStore = new DevExpress.data.CustomStore({
                key: "id",
                loadMode: "raw",
                load: function (loadOptions) {
                    return $.getJSON("{{route('contractors.list')}}",
                        {data: JSON.stringify({dxLoadOptions: loadOptions})});
                },
            })

            let contractorsDataSource = new DevExpress.data.DataSource({
                store: contractorsStore
            })

            let materialsSupplyPlanningSource = new DevExpress.data.DataSource({
                store: new DevExpress.data.CustomStore({
                    key: "id",
                    load: function (loadOptions) {
                        dataSourceLoadOptions = loadOptions;
                        return $.getJSON("{{route('materials.supply-planning.list')}}",
                            {
                                loadOptions: JSON.stringify(loadOptions),
                            });
                    },
                    insert: function (values) {
                        return $.ajax({
                            url: "{{route('materials.supply-planning.store')}}",
                            method: "POST",
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: {
                                data: JSON.stringify(values),
                                options: null
                            },
                            success: function (data, textStatus, jqXHR) {
                                DevExpress.ui.notify("Данные успешно добавлены", "success", 1000)
                            },
                        })
                    },
                    update: function (key, values) {
                        return $.ajax({
                            url: "{{route('materials.supply-planning.update')}}",
                            method: "PUT",
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: {
                                key: key,
                                modifiedData: JSON.stringify(values)
                            },
                            success: function (data, textStatus, jqXHR) {
                                DevExpress.ui.notify("Данные успешно изменены", "success", 1000)
                            }
                        });
                    },
                    remove: function (key) {
                        return $.ajax({
                            url: "{{route('materials.supply-planning.delete')}}",
                            method: "DELETE",
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: {
                                key: key
                            },
                            success: function (data, textStatus, jqXHR) {
                                DevExpress.ui.notify("Данные успешно удалены", "success", 1000)
                            },

                            error: function (jqXHR, textStatus, errorThrown) {
                                DevExpress.ui.notify("При удалении данных произошла ошибка", "error", 5000)
                            }
                        })
                    }
                })
            });

            function getMaterialWeight(data, weightColumnName = 'weight') {
                let amount = data.amount;
                let weight = amount * data.quantity * data[weightColumnName];

                if (isNaN(weight)) {
                    weight = 0;
                } else {
                    weight = Math.round(weight * 1000) / 1000;
                }

                data.computed_weight = weight;
                return weight;
            }

            let supplyPlanningForm = $("#formContainer").dxForm({
                items: [
                    {
                        itemType: "group",
                        caption: "Планирование поставок материалов",
                        cssClass: "material-supply-planning-grid",
                        items: [{
                            name: "materialsSupplyPlanningGrid",
                            editorType: "dxDataGrid",
                            editorOptions: {
                                dataSource: materialsSupplyPlanningSource,
                                focusedRowEnabled: false,
                                hoverStateEnabled: true,
                                columnAutoWidth: false,
                                showBorders: true,
                                showColumnLines: true,
                                filterRow: {
                                    visible: true,
                                    applyFilter: "auto"
                                },
                                toolbar: {
                                    visible: false
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
                                editing: {
                                    mode: 'row',
                                    @if(Auth::user()->can('material_supply_planning_editing'))
                                    allowUpdating: true,
                                    allowAdding: false,
                                    allowDeleting: true,
                                    @endcan
                                    selectTextOnEditStart: true,
                                    startEditAction: 'click'
                                },
                                columns: [
                                    {
                                        caption: "Объект",
                                        dataType: "number",
                                        dataField: "project_object_id",
                                        width: 450,
                                        sortIndex: 0,
                                        sortOrder: "asc",
                                        lookup: {
                                            dataSource: {
                                                store: projectObjectsStore,
                                                paginate: true,
                                                pageSize: 25,
                                            },
                                            displayExpr: 'short_name',
                                            valueExpr: 'id',
                                        },
                                        validationRules: [{type: "required"}]
                                    },
                                    {
                                        caption: "Планирование",
                                        alignment: 'center',
                                        columns: [
                                            {
                                                caption: "Марка материала",
                                                dataType: "number",
                                                dataField: "brand_id",
                                                lookup: {
                                                    dataSource: {
                                                        store: brandsStore,
                                                        paginate: true,
                                                        pageSize: 25,
                                                        filter: ["material_type_id", "=", 1]
                                                    },
                                                    displayExpr: 'name',
                                                    valueExpr: 'id'
                                                },
                                                validationRules: [{type: "required"}]
                                            },
                                            {
                                                caption: "Длина (м.п)",
                                                dataType: "number",
                                                dataField: "quantity",
                                                validationRules: [{type: "required"}]
                                            },
                                            {
                                                caption: "Кол-во (шт)",
                                                dataType: "number",
                                                dataField: "amount",
                                                validationRules: [{type: "required"}]
                                            },
                                            {
                                                caption: "Вес по проекту (т)",
                                                dataField: "planned_project_weight",
                                                allowEditing: false,
                                                validationRules: [{type: "required"}],
                                                calculateCellValue: function (rowData) {
                                                    rowData.planned_project_weight = getMaterialWeight(rowData, 'standard_weight');
                                                    return rowData.planned_project_weight;
                                                }
                                            }
                                        ]
                                    },
                                    {
                                        caption: "Потребности",
                                        alignment: 'center',
                                        columns: [
                                            {
                                                caption: "Завезено (т)",
                                                dataField: "remains_weight_with_percent",
                                                allowEditing: false,
                                                alignment: 'right',
                                                calculateCellValue: function (rowData) {
                                                    let amount = rowData.amount;
                                                    let weight = amount * rowData.quantity * rowData.standard_weight;

                                                    if (isNaN(weight) || isNaN(rowData.remains_weight / (weight / 100))) {
                                                        rowData.remains_weight_with_percent = 0
                                                    } else {
                                                        weight = Math.round(weight * 1000) / 1000;
                                                        rowData.remains_weight_with_percent = `${rowData.remains_weight} (${Math.round(rowData.remains_weight / (weight / 100))}%)`;
                                                    }
                                                    return rowData.remains_weight_with_percent;
                                                }
                                            },
                                            {
                                                caption: "Л5 УМ (т)", //brand_type_id = 2
                                                allowEditing: false,
                                                alignment: 'right',
                                                calculateCellValue: function (rowData) {
                                                    if (rowData.brand_type_id === 2 && rowData.remains_weight < rowData.planned_project_weight) {
                                                        return calculateNeededWeight(rowData);
                                                    }
                                                }
                                            },
                                            {
                                                caption: "GU/PU/VL (т)", //brand_type_id = 1
                                                allowEditing: false,
                                                alignment: 'right',
                                                calculateCellValue: function (rowData) {
                                                    if (rowData.brand_type_id === 1 && rowData.remains_weight < rowData.planned_project_weight) {
                                                        return calculateNeededWeight(rowData);
                                                    }
                                                }
                                            },
                                            {
                                                caption: "AZ (т)", //brand_type_id = 3
                                                allowEditing: false,
                                                alignment: 'right',
                                                calculateCellValue: function (rowData) {
                                                    if (rowData.brand_type_id === 3 && rowData.remains_weight < rowData.planned_project_weight) {
                                                        return calculateNeededWeight(rowData);
                                                    }
                                                }
                                            }
                                        ]
                                    }
                                ],
                                masterDetail: {
                                    enabled: true,
                                    autoExpandAll: false,
                                    template: function (container, info) {
                                        let supplyMaterialsGrid = $(`<div class="supply-planning-materials" supply-planning-id="${info.data.id}">`).dxDataGrid({
                                            editing: {
                                                mode: 'row',
                                                @if(Auth::user()->can('material_supply_planning_editing'))
                                                allowUpdating: true,
                                                allowAdding: false,
                                                allowDeleting: false,
                                                @endcan
                                                selectTextOnEditStart: true,
                                                startEditAction: 'click',
                                            },
                                            dataSource: new DevExpress.data.DataSource({
                                                store: new DevExpress.data.CustomStore({
                                                    key: "id",
                                                    loadMode: "raw",
                                                    load: function (loadOptions) {
                                                        let loadParameters = {
                                                            loadOptions: loadOptions,
                                                            materialSupplyPlanningId: info.data.id,
                                                            detailType: "supply_materials"
                                                        }

                                                        return $.getJSON("{{route('materials.supply-planning.materials.list')}}",
                                                            loadParameters);
                                                    },
                                                    insert: function (values) {
                                                        values.supply_planning_id = info.data.id;
                                                        return $.ajax({
                                                            url: "{{route('materials.supply-planning.materials.store')}}",
                                                            method: "POST",
                                                            headers: {
                                                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                                            },
                                                            data: {
                                                                data: JSON.stringify(values),
                                                                options: null
                                                            },
                                                            success: function (data, textStatus, jqXHR) {
                                                                DevExpress.ui.notify("Данные успешно добавлены", "success", 1000)
                                                            },
                                                        })
                                                    },
                                                    update: function (key, values) {
                                                        values.supply_planning_id = info.data.id;
                                                        return $.ajax({
                                                            url: "{{route('materials.supply-planning.materials.update')}}",
                                                            method: "PUT",
                                                            headers: {
                                                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                                            },
                                                            data: {
                                                                key: key,
                                                                modifiedData: JSON.stringify(values)
                                                            },
                                                            success: function (data, textStatus, jqXHR) {
                                                                DevExpress.ui.notify("Данные успешно изменены", "success", 1000)
                                                            }
                                                        });
                                                    },
                                                    remove: function (key) {
                                                        return $.ajax({
                                                            url: "{{route('materials.supply-planning.materials.delete')}}",
                                                            method: "DELETE",
                                                            headers: {
                                                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                                            },
                                                            data: {
                                                                key: key
                                                            },
                                                            success: function (data, textStatus, jqXHR) {
                                                                DevExpress.ui.notify("Данные успешно удалены", "success", 1000)
                                                            },

                                                            error: function (jqXHR, textStatus, errorThrown) {
                                                                DevExpress.ui.notify("При удалении данных произошла ошибка", "error", 5000)
                                                            }
                                                        })
                                                    }
                                                }),
                                            }),
                                            showBorders: true,
                                            showColumnLines: true,
                                            filterRow: {
                                                visible: true,
                                                applyFilter: "auto"
                                            },
                                            grouping: {
                                                autoExpandAll: false,
                                            },
                                            groupPanel: {
                                                visible: false
                                            },
                                            /*selection: {
                                                allowSelectAll: true,
                                                deferred: false,
                                                mode: "multiple",
                                                selectAllMode: "allPages",
                                                showCheckBoxesMode: "always"
                                            },*/
                                            paging: {
                                                enabled: false
                                            },
                                            summary: {
                                                groupItems: [
                                                    {
                                                        column: "standard_name",
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
                                                            return "Всего: " + Math.round(data.value * 1000) / 1000 + " т"
                                                        },
                                                        showInGroupFooter: false,
                                                        alignByColumn: true
                                                    }
                                                ],
                                                calculateCustomSummary: (options) => {

                                                    if (options.name === 'totalAmountSummary' || options.name === 'totalComputedWeightSummary') {
                                                        let measureUnit;
                                                        let computedPropertyName;
                                                        switch (options.name) {
                                                            case 'totalAmountSummary':
                                                                computedPropertyName = 'amount';
                                                                measureUnit = 'шт';
                                                                break;
                                                            case 'totalComputedWeightSummary':
                                                                computedPropertyName = 'computed_weight';
                                                                measureUnit = 'т'
                                                                break;
                                                        }

                                                        if (options.summaryProcess === 'start') {
                                                            options.totalValue = 0;
                                                            options.selectedTotalValue = 0;
                                                        }
                                                        if (options.summaryProcess === 'calculate') {
                                                            if (computedPropertyName === 'computed_weight') {
                                                                console.log(options.value);
                                                                options.computed_weight = getMaterialWeight(options.value, "standard_weight");
                                                            }

                                                            options.totalValue += options.value[computedPropertyName];
                                                            if (options.component.isRowSelected(options.value.id)) {
                                                                options.selectedTotalValue += options.value[computedPropertyName];
                                                            }
                                                        }

                                                        if (options.summaryProcess === 'finalize') {
                                                            if (computedPropertyName === 'computed_weight') {
                                                                options.totalValue = Math.round(options.totalValue * 1000) / 1000;
                                                                options.selectedTotalValue = Math.round(options.selectedTotalValue * 1000) / 1000;
                                                            }

                                                            options.totalValue = `Всего: ${options.totalValue} ${measureUnit}`;

                                                            if (options.selectedTotalValue > 0) {
                                                                options.totalValue = `${options.totalValue}; Выбрано: ${options.selectedTotalValue} ${measureUnit}`
                                                            }
                                                        }
                                                    }
                                                },
                                                totalItems: [
                                                    {
                                                        name: "totalComputedWeightSummary",
                                                        showInColumn: "computed_weight",
                                                        column: "computed_weight",
                                                        summaryType: "sum",
                                                        displayFormat: "Итого: {0} т",
                                                        showInGroupFooter: false,
                                                        alignByColumn: true
                                                    }
                                                ]
                                            },
                                            onSelectionChanged(e) {
                                                e.component.refresh(true);
                                            },
                                            columns: [
                                                {
                                                    caption: "Объект",
                                                    dataType: "number",
                                                    dataField: "project_object",
                                                    allowEditing: false,
                                                    lookup: {
                                                        dataSource: {
                                                            store: projectObjectsStore,
                                                            paginate: true,
                                                            pageSize: 25
                                                        },
                                                        displayExpr: 'short_name',
                                                        valueExpr: 'id'
                                                    },
                                                    validationRules: [{type: "required"}]
                                                },
                                                {
                                                    dataField: "standard_name",
                                                    dataType: "string",
                                                    caption: "Наименование",
                                                    width: 500,
                                                    allowEditing: false,
                                                    cellTemplate: function (container, options) {
                                                        $(`<div class="standard-name">${options.text}</div>`)
                                                            .appendTo(container);

                                                        if (options.data.comment) {
                                                            $(`<div class="material-comment">${options.data.comment}</div>`)
                                                                .appendTo(container);

                                                            container.addClass("standard-name-cell-with-comment");
                                                        }
                                                    },
                                                    calculateFilterExpression: function (filterValue, selectedFilterOperation, target) {
                                                        if (["contains", "notcontains"].indexOf(selectedFilterOperation) !== -1) {
                                                            let columnsNames = ["standard_name", "comment"]

                                                            let words = filterValue.split(" ");
                                                            let filter = [];

                                                            columnsNames.forEach(function (column, index) {
                                                                filter.push([]);
                                                                words.forEach(function (word) {
                                                                    filter[filter.length - 1].push([column, selectedFilterOperation, word]);
                                                                    filter[filter.length - 1].push("and");
                                                                });

                                                                filter[filter.length - 1].pop();
                                                                filter.push("or");
                                                            })

                                                            filter.pop();
                                                            return filter;
                                                        }
                                                        return this.defaultCalculateFilterExpression(filterValue, selectedFilterOperation);
                                                    }
                                                },
                                                {
                                                    dataField: "quantity",
                                                    dataType: "number",
                                                    caption: "Длина (м.п)",
                                                    showSpinButtons: true,
                                                    allowEditing: false,
                                                    cellTemplate: function (container, options) {
                                                        let quantity = Math.round(options.data.quantity * 100) / 100;
                                                        let measureUnit = 'м.п';

                                                        $(`<div>${quantity} ${measureUnit}</div>`)
                                                            .appendTo(container);
                                                    },
                                                    validationRules: [{type: "required"}]
                                                },
                                                {
                                                    dataField: "amount",
                                                    dataType: "number",
                                                    caption: "Количество (шт)",
                                                    cellTemplate: function (container, options) {
                                                        let amount = options.data.amount;
                                                        $(`<div>${amount} шт</div>`)
                                                            .appendTo(container);
                                                    },
                                                    validationRules: [{type: "required"}]
                                                },
                                                {
                                                    dataField: "computed_weight",
                                                    dataType: "number",
                                                    caption: "Вес",
                                                    allowEditing: false,
                                                    calculateCellValue: function (rowData) {
                                                        console.log("rowData", rowData);
                                                        return getMaterialWeight(rowData);
                                                    },
                                                    cellTemplate: function (container, options) {
                                                        let weight = options.data.computed_weight;

                                                        $(`<div>${weight} т</div>`)
                                                            .appendTo(container);
                                                    }
                                                }
                                            ]
                                        })
                                        container.append(supplyMaterialsGrid);

                                        container.append(
                                            $('<div class="supply-planning-details">').dxTabPanel({
                                                items: [
                                                    {
                                                        title: "Наличие на объектах",
                                                        template: () => {
                                                            let isFirstLoad = true;
                                                            let materialsForSupplyPlanning = $(`<div class="supply-planning-details-tab-grid">`).dxDataGrid({
                                                                dataSource: new DevExpress.data.DataSource({
                                                                    store: new DevExpress.data.CustomStore({
                                                                        key: "id",
                                                                        loadMode: "raw",
                                                                        load: function (loadOptions) {
                                                                            let loadParameters = {
                                                                                loadOptions: loadOptions,
                                                                                projectObjectId: info.data.project_object_id,
                                                                                brandId: info.data.brand_id,
                                                                                quantity: info.data.quantity,
                                                                                detailType: "otherRemains"
                                                                            }

                                                                            return $.getJSON("{{route('materials.supply-planning.get-materials-for-supply-planning-details')}}",
                                                                                loadParameters)
                                                                        },
                                                                        onLoaded: (data) => {
                                                                            if (isFirstLoad) {
                                                                                isFirstLoad = false;
                                                                                let selectedRows = [];
                                                                                data.forEach((rowData) => {
                                                                                    if (rowData.id === rowData.supply_material_id) {
                                                                                        selectedRows.push(rowData.id)
                                                                                    }

                                                                                    materialsForSupplyPlanning.dxDataGrid("instance").option("selectedRowKeys", selectedRows);
                                                                                })
                                                                            }
                                                                        }
                                                                    }),
                                                                }),
                                                                showBorders: true,
                                                                showColumnLines: true,
                                                                filterRow: {
                                                                    visible: true,
                                                                    applyFilter: "auto"
                                                                },
                                                                grouping: {
                                                                    autoExpandAll: false,
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
                                                                paging: {
                                                                    enabled: false
                                                                },
                                                                summary: {
                                                                    groupItems: [
                                                                        {
                                                                            column: "standard_name",
                                                                            summaryType: "count",
                                                                            displayFormat: "Количество: {0}",
                                                                        },
                                                                        {
                                                                            name: "totalAmountGroupSummary",
                                                                            showInColumn: "amount",
                                                                            summaryType: "custom",
                                                                            showInGroupFooter: false,
                                                                            alignByColumn: true
                                                                        },
                                                                        {
                                                                            name: "totalComputedWeightGroupSummary",
                                                                            showInColumn: "computed_weight",
                                                                            summaryType: "custom",
                                                                            showInGroupFooter: false,
                                                                            alignByColumn: true
                                                                        }],
                                                                    calculateCustomSummary: (options) => {
                                                                        if (options.name === 'totalAmountGroupSummary' || options.name === 'totalAmountSummary' || options.name === 'totalComputedWeightGroupSummary' || options.name === 'totalComputedWeightSummary') {
                                                                            let measureUnit;
                                                                            let computedPropertyName;
                                                                            switch (options.name) {
                                                                                case 'totalAmountGroupSummary':
                                                                                case 'totalAmountSummary':
                                                                                    computedPropertyName = 'amount';
                                                                                    measureUnit = 'шт';
                                                                                    break;
                                                                                case 'totalComputedWeightGroupSummary':
                                                                                case 'totalComputedWeightSummary':
                                                                                    computedPropertyName = 'computed_weight';
                                                                                    measureUnit = 'т'
                                                                                    break;
                                                                            }

                                                                            if (options.summaryProcess === 'start') {
                                                                                options.totalValue = 0;
                                                                                options.selectedTotalValue = 0;
                                                                            }
                                                                            if (options.summaryProcess === 'calculate') {
                                                                                if (computedPropertyName === 'computed_weight') {
                                                                                    options.computed_weight = getMaterialWeight(options.value);
                                                                                }

                                                                                options.totalValue += options.value[computedPropertyName];
                                                                                if (options.component.isRowSelected(options.value.id)) {
                                                                                    options.selectedTotalValue += options.value[computedPropertyName];
                                                                                }
                                                                            }

                                                                            if (options.summaryProcess === 'finalize') {
                                                                                if (computedPropertyName === 'computed_weight') {
                                                                                    options.totalValue = Math.round(options.totalValue * 1000) / 1000;
                                                                                    options.selectedTotalValue = Math.round(options.selectedTotalValue * 1000) / 1000;
                                                                                }

                                                                                options.totalValue = `Всего: ${options.totalValue} ${measureUnit}`;

                                                                                if (options.selectedTotalValue > 0) {
                                                                                    options.totalValue = `${options.totalValue}; Выбрано: ${options.selectedTotalValue} ${measureUnit}`
                                                                                }
                                                                            }
                                                                        }
                                                                    },
                                                                    totalItems: [
                                                                        {
                                                                            name: "totalComputedWeightSummary",
                                                                            showInColumn: "computed_weight",
                                                                            summaryType: "custom",
                                                                            showInGroupFooter: false,
                                                                            alignByColumn: true
                                                                        },
                                                                        {
                                                                            name: "totalAmountSummary",
                                                                            showInColumn: "amount",
                                                                            summaryType: "custom",
                                                                            showInGroupFooter: false,
                                                                            alignByColumn: true
                                                                        }
                                                                    ],
                                                                },
                                                                columns: [
                                                                    {
                                                                        dataField: "standard_name",
                                                                        dataType: "string",
                                                                        caption: "Наименование",
                                                                        width: 500,
                                                                        cellTemplate: function (container, options) {
                                                                            $(`<div class="standard-name">${options.text}</div>`)
                                                                                .appendTo(container);

                                                                            if (options.data.comment) {
                                                                                $(`<div class="material-comment">${options.data.comment}</div>`)
                                                                                    .appendTo(container);

                                                                                container.addClass("standard-name-cell-with-comment");
                                                                            }
                                                                        },
                                                                        calculateFilterExpression: function (filterValue, selectedFilterOperation, target) {
                                                                            if (["contains", "notcontains"].indexOf(selectedFilterOperation) !== -1) {
                                                                                let columnsNames = ["standard_name", "comment"]

                                                                                let words = filterValue.split(" ");
                                                                                let filter = [];

                                                                                columnsNames.forEach(function (column, index) {
                                                                                    filter.push([]);
                                                                                    words.forEach(function (word) {
                                                                                        filter[filter.length - 1].push([column, selectedFilterOperation, word]);
                                                                                        filter[filter.length - 1].push("and");
                                                                                    });

                                                                                    filter[filter.length - 1].pop();
                                                                                    filter.push("or");
                                                                                })

                                                                                filter.pop();
                                                                                return filter;
                                                                            }
                                                                            return this.defaultCalculateFilterExpression(filterValue, selectedFilterOperation);
                                                                        }
                                                                    },
                                                                    {
                                                                        dataField: "quantity",
                                                                        dataType: "number",
                                                                        caption: "Длина (м.п)",
                                                                        showSpinButtons: true,
                                                                        cellTemplate: function (container, options) {
                                                                            let quantity = Math.round(options.data.quantity * 100) / 100;
                                                                            let measureUnit = 'м.п';

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
                                                                            $(`<div>${amount} шт</div>`)
                                                                                .appendTo(container);
                                                                        }
                                                                    },
                                                                    {
                                                                        dataField: "computed_weight",
                                                                        dataType: "number",
                                                                        caption: "Вес",
                                                                        calculateCellValue: function (rowData) {
                                                                            return getMaterialWeight(rowData);
                                                                        },
                                                                        cellTemplate: function (container, options) {
                                                                            let weight = options.data.computed_weight;

                                                                            $(`<div>${weight} т</div>`)
                                                                                .appendTo(container);
                                                                        }
                                                                    },
                                                                    {
                                                                        dataField: "project_object",
                                                                        dataType: "number",
                                                                        caption: "Объект",
                                                                        groupIndex: 0,
                                                                        lookup: {
                                                                            dataSource: {
                                                                                store: projectObjectsStore,
                                                                                paginate: true,
                                                                                pageSize: 25
                                                                            },
                                                                            displayExpr: "short_name",
                                                                            valueExpr: "id"
                                                                        }
                                                                    }
                                                                ],
                                                                onSelectionChanged(e) {
                                                                    let selectedDataArray = [];
                                                                    let deselectedDataArray = [];
                                                                    e.currentSelectedRowKeys.forEach((selectedItem) => {
                                                                        selectedDataArray.push({
                                                                            material_id: selectedItem,
                                                                            supply_planning_id: info.data.id
                                                                        })
                                                                    });

                                                                    if (selectedDataArray.length > 0) {
                                                                        $.ajax({
                                                                            url: "{{route('materials.supply-planning.materials.store')}}",
                                                                            method: "POST",
                                                                            headers: {
                                                                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                                                            },
                                                                            data: {
                                                                                data: JSON.stringify(selectedDataArray)
                                                                            },

                                                                            success: function (data, textStatus, jqXHR) {
                                                                                //
                                                                            },
                                                                            error: function (jqXHR, textStatus, errorThrown) {
                                                                                DevExpress.ui.notify("При обновлении данных произошла ошибка", "error", 5000);
                                                                            }
                                                                        })
                                                                    }

                                                                    e.currentDeselectedRowKeys.forEach((deselectedItem) => {
                                                                        deselectedDataArray.push({
                                                                            material_id: deselectedItem,
                                                                            supply_planning_id: info.data.id
                                                                        })
                                                                    });

                                                                    if (deselectedDataArray.length > 0) {
                                                                        $.ajax({
                                                                            url: "{{route('materials.supply-planning.materials.delete')}}",
                                                                            method: "DELETE",
                                                                            headers: {
                                                                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                                                            },
                                                                            data: {
                                                                                data: JSON.stringify(deselectedDataArray)
                                                                            },

                                                                            success: function (data, textStatus, jqXHR) {
                                                                                //
                                                                            },
                                                                            error: function (jqXHR, textStatus, errorThrown) {
                                                                                DevExpress.ui.notify("При обновлении данных произошла ошибка", "error", 5000);
                                                                            }
                                                                        })
                                                                    }

                                                                    supplyMaterialsGrid.dxDataGrid("instance").getDataSource().reload();

                                                                    e.component.refresh(true);
                                                                },
                                                            });

                                                            return materialsForSupplyPlanning;
                                                        }
                                                    },
                                                    {
                                                        title: "Завезенные на объект",
                                                        template: () => {
                                                            return $(`<div class="supply-planning-details-tab-grid">`).dxDataGrid({
                                                                dataSource: new DevExpress.data.DataSource({
                                                                    store: new DevExpress.data.CustomStore({
                                                                        key: "id",
                                                                        loadMode: "raw",
                                                                        load: function (loadOptions) {
                                                                            let loadParameters = {
                                                                                loadOptions: loadOptions,
                                                                                projectObjectId: info.data.project_object_id,
                                                                                brandId: info.data.brand_id,
                                                                                quantity: info.data.quantity,
                                                                                detailType: "selfRemains"
                                                                            }

                                                                            return $.getJSON("{{route('materials.supply-planning.get-materials-for-supply-planning-details')}}",
                                                                                loadParameters);
                                                                        },
                                                                    }),
                                                                }),
                                                                showBorders: true,
                                                                showColumnLines: true,
                                                                filterRow: {
                                                                    visible: true,
                                                                    applyFilter: "auto"
                                                                },
                                                                grouping: {
                                                                    autoExpandAll: false,
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
                                                                paging: {
                                                                    enabled: false
                                                                },
                                                                summary: {
                                                                    groupItems: [
                                                                        {
                                                                            column: "standard_name",
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
                                                                                return "Всего: " + Math.round(data.value * 1000) / 1000 + " т"
                                                                            },
                                                                            showInGroupFooter: false,
                                                                            alignByColumn: true
                                                                        }
                                                                    ],
                                                                    calculateCustomSummary: (options) => {
                                                                        if (options.name === 'totalAmountSummary' || options.name === 'totalComputedWeightSummary') {
                                                                            let measureUnit;
                                                                            let computedPropertyName;
                                                                            switch (options.name) {
                                                                                case 'totalAmountSummary':
                                                                                    computedPropertyName = 'amount';
                                                                                    measureUnit = 'шт';
                                                                                    break;
                                                                                case 'totalComputedWeightSummary':
                                                                                    computedPropertyName = 'computed_weight';
                                                                                    measureUnit = 'т'
                                                                                    break;
                                                                            }

                                                                            if (options.summaryProcess === 'start') {
                                                                                options.totalValue = 0;
                                                                                options.selectedTotalValue = 0;
                                                                            }
                                                                            if (options.summaryProcess === 'calculate') {
                                                                                if (computedPropertyName === 'computed_weight') {
                                                                                    options.computed_weight = getMaterialWeight(options.value);
                                                                                }

                                                                                options.totalValue += options.value[computedPropertyName];
                                                                                if (options.component.isRowSelected(options.value.id)) {
                                                                                    options.selectedTotalValue += options.value[computedPropertyName];
                                                                                }
                                                                            }

                                                                            if (options.summaryProcess === 'finalize') {
                                                                                if (computedPropertyName === 'computed_weight') {
                                                                                    options.totalValue = Math.round(options.totalValue * 1000) / 1000;
                                                                                    options.selectedTotalValue = Math.round(options.selectedTotalValue * 1000) / 1000;
                                                                                }

                                                                                options.totalValue = `Всего: ${options.totalValue} ${measureUnit}`;

                                                                                if (options.selectedTotalValue > 0) {
                                                                                    options.totalValue = `${options.totalValue}; Выбрано: ${options.selectedTotalValue} ${measureUnit}`
                                                                                }
                                                                            }
                                                                        }
                                                                    },
                                                                    totalItems: [
                                                                        {
                                                                            name: "totalComputedWeightSummary",
                                                                            showInColumn: "computed_weight",
                                                                            summaryType: "custom",
                                                                            showInGroupFooter: false,
                                                                            alignByColumn: true
                                                                        },
                                                                        {
                                                                            name: "totalAmountSummary",
                                                                            showInColumn: "amount",
                                                                            summaryType: "custom",
                                                                            showInGroupFooter: false,
                                                                            alignByColumn: true
                                                                        }
                                                                    ]
                                                                },
                                                                onSelectionChanged(e) {
                                                                    e.component.refresh(true);
                                                                },
                                                                columns: [
                                                                    {
                                                                        dataField: "standard_name",
                                                                        dataType: "string",
                                                                        caption: "Наименование",
                                                                        width: 500,
                                                                        cellTemplate: function (container, options) {
                                                                            $(`<div class="standard-name">${options.text}</div>`)
                                                                                .appendTo(container);

                                                                            if (options.data.comment) {
                                                                                $(`<div class="material-comment">${options.data.comment}</div>`)
                                                                                    .appendTo(container);

                                                                                container.addClass("standard-name-cell-with-comment");
                                                                            }
                                                                        },
                                                                        calculateFilterExpression: function (filterValue, selectedFilterOperation, target) {
                                                                            if (["contains", "notcontains"].indexOf(selectedFilterOperation) !== -1) {
                                                                                let columnsNames = ["standard_name", "comment"]

                                                                                let words = filterValue.split(" ");
                                                                                let filter = [];

                                                                                columnsNames.forEach(function (column, index) {
                                                                                    filter.push([]);
                                                                                    words.forEach(function (word) {
                                                                                        filter[filter.length - 1].push([column, selectedFilterOperation, word]);
                                                                                        filter[filter.length - 1].push("and");
                                                                                    });

                                                                                    filter[filter.length - 1].pop();
                                                                                    filter.push("or");
                                                                                })

                                                                                filter.pop();
                                                                                return filter;
                                                                            }
                                                                            return this.defaultCalculateFilterExpression(filterValue, selectedFilterOperation);
                                                                        }
                                                                    },
                                                                    {
                                                                        dataField: "quantity",
                                                                        dataType: "number",
                                                                        caption: "Длина (м.п)",
                                                                        showSpinButtons: true,
                                                                        cellTemplate: function (container, options) {
                                                                            let quantity = Math.round(options.data.quantity * 100) / 100;
                                                                            let measureUnit = 'м.п';

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
                                                                            $(`<div>${amount} шт</div>`)
                                                                                .appendTo(container);
                                                                        }
                                                                    },
                                                                    {
                                                                        dataField: "computed_weight",
                                                                        dataType: "number",
                                                                        caption: "Вес",
                                                                        calculateCellValue: function (rowData) {
                                                                            return getMaterialWeight(rowData);
                                                                        },
                                                                        cellTemplate: function (container, options) {
                                                                            let weight = options.data.computed_weight;

                                                                            $(`<div>${weight} т</div>`)
                                                                                .appendTo(container);
                                                                        }
                                                                    }
                                                                ]
                                                            });
                                                        }
                                                    },
                                                    {
                                                        title: "Ожидаемые поставки",
                                                        template: () => {
                                                            let dataGrid =  $(`<div class="supply-planning-details-tab-grid" supply-planning-id="${info.data.id}">`).dxDataGrid({
                                                                editing: {
                                                                    mode: 'row',
                                                                    @if(Auth::user()->can('material_supply_planning_editing'))
                                                                    allowUpdating: true,
                                                                    allowAdding: false,
                                                                    allowDeleting: true,
                                                                    @endcan
                                                                    selectTextOnEditStart: true,
                                                                    startEditAction: 'click',
                                                                },
                                                                @if(Auth::user()->can('material_supply_planning_editing'))
                                                                toolbar: {
                                                                    visible: true,
                                                                    items: [
                                                                        {
                                                                            location: 'after',
                                                                            widget: 'dxButton',
                                                                            options: {
                                                                                text: 'Добавить',
                                                                                icon: "fas fa-plus",
                                                                                stylingMode: 'contained',
                                                                                type: 'normal',
                                                                                onClick: (e) => {
                                                                                    console.log(info.data.id);
                                                                                    dataGrid.dxDataGrid("instance").addRow();
                                                                                }
                                                                            },
                                                                        }
                                                                    ]
                                                                },
                                                                @endcan
                                                                dataSource: new DevExpress.data.DataSource({
                                                                    store: new DevExpress.data.CustomStore({
                                                                        key: "id",
                                                                        loadMode: "raw",
                                                                        load: function (loadOptions) {
                                                                            let loadParameters = {
                                                                                loadOptions: loadOptions,
                                                                                materialSupplyPlanningId: info.data.id,
                                                                                detailType: "expectedDelivery"
                                                                            }

                                                                            return $.getJSON("{{route('materials.supply-planning.expected-delivery.list')}}",
                                                                                loadParameters);
                                                                        },
                                                                        insert: function (values) {
                                                                            values.supply_planning_id = info.data.id;
                                                                            return $.ajax({
                                                                                url: "{{route('materials.supply-planning.expected-delivery.store')}}",
                                                                                method: "POST",
                                                                                headers: {
                                                                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                                                                },
                                                                                data: {
                                                                                    data: JSON.stringify(values),
                                                                                    options: null
                                                                                },
                                                                                success: function (data, textStatus, jqXHR) {
                                                                                    DevExpress.ui.notify("Данные успешно добавлены", "success", 1000)
                                                                                },
                                                                            })
                                                                        },
                                                                        update: function (key, values) {
                                                                            values.supply_planning_id = info.data.id;
                                                                            delete values.computed_weight;
                                                                            return $.ajax({
                                                                                url: "{{route('materials.supply-planning.expected-delivery.update')}}",
                                                                                method: "PUT",
                                                                                headers: {
                                                                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                                                                },
                                                                                data: {
                                                                                    key: key,
                                                                                    modifiedData: JSON.stringify(values)
                                                                                },
                                                                                success: function (data, textStatus, jqXHR) {
                                                                                    DevExpress.ui.notify("Данные успешно изменены", "success", 1000)
                                                                                }
                                                                            });
                                                                        },
                                                                        remove: function (key) {
                                                                            return $.ajax({
                                                                                url: "{{route('materials.supply-planning.expected-delivery.delete')}}",
                                                                                method: "DELETE",
                                                                                headers: {
                                                                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                                                                },
                                                                                data: {
                                                                                    key: key
                                                                                },
                                                                                success: function (data, textStatus, jqXHR) {
                                                                                    DevExpress.ui.notify("Данные успешно удалены", "success", 1000)
                                                                                },

                                                                                error: function (jqXHR, textStatus, errorThrown) {
                                                                                    DevExpress.ui.notify("При удалении данных произошла ошибка", "error", 5000)
                                                                                }
                                                                            })
                                                                        }
                                                                    }),
                                                                }),
                                                                showBorders: true,
                                                                showColumnLines: true,
                                                                filterRow: {
                                                                    visible: true,
                                                                    applyFilter: "auto"
                                                                },
                                                                grouping: {
                                                                    autoExpandAll: false,
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
                                                                paging: {
                                                                    enabled: false
                                                                },
                                                                summary: {
                                                                    groupItems: [
                                                                        {
                                                                            column: "standard_name",
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
                                                                                return "Всего: " + Math.round(data.value * 1000) / 1000 + " т"
                                                                            },
                                                                            showInGroupFooter: false,
                                                                            alignByColumn: true
                                                                        }
                                                                    ],
                                                                    calculateCustomSummary: (options) => {

                                                                        if (options.name === 'totalAmountSummary' || options.name === 'totalComputedWeightSummary') {
                                                                            let measureUnit;
                                                                            let computedPropertyName;
                                                                            switch (options.name) {
                                                                                case 'totalAmountSummary':
                                                                                    computedPropertyName = 'amount';
                                                                                    measureUnit = 'шт';
                                                                                    break;
                                                                                case 'totalComputedWeightSummary':
                                                                                    computedPropertyName = 'computed_weight';
                                                                                    measureUnit = 'т'
                                                                                    break;
                                                                            }

                                                                            if (options.summaryProcess === 'start') {
                                                                                options.totalValue = 0;
                                                                                options.selectedTotalValue = 0;
                                                                            }
                                                                            if (options.summaryProcess === 'calculate') {
                                                                                if (computedPropertyName === 'computed_weight') {
                                                                                    console.log(options.value);
                                                                                    options.computed_weight = getMaterialWeight(options.value, "standard_weight");
                                                                                }

                                                                                options.totalValue += options.value[computedPropertyName];
                                                                                if (options.component.isRowSelected(options.value.id)) {
                                                                                    options.selectedTotalValue += options.value[computedPropertyName];
                                                                                }
                                                                            }

                                                                            if (options.summaryProcess === 'finalize') {
                                                                                if (computedPropertyName === 'computed_weight') {
                                                                                    options.totalValue = Math.round(options.totalValue * 1000) / 1000;
                                                                                    options.selectedTotalValue = Math.round(options.selectedTotalValue * 1000) / 1000;
                                                                                }

                                                                                options.totalValue = `Всего: ${options.totalValue} ${measureUnit}`;

                                                                                if (options.selectedTotalValue > 0) {
                                                                                    options.totalValue = `${options.totalValue}; Выбрано: ${options.selectedTotalValue} ${measureUnit}`
                                                                                }
                                                                            }
                                                                        }
                                                                    },
                                                                    totalItems: [
                                                                        {
                                                                            name: "totalComputedWeightSummary",
                                                                            showInColumn: "computed_weight",
                                                                            summaryType: "custom",
                                                                            showInGroupFooter: false,
                                                                            alignByColumn: true
                                                                        },
                                                                        {
                                                                            name: "totalAmountSummary",
                                                                            showInColumn: "amount",
                                                                            summaryType: "custom",
                                                                            showInGroupFooter: false,
                                                                            alignByColumn: true
                                                                        }
                                                                    ]
                                                                },
                                                                onSelectionChanged(e) {
                                                                    e.component.refresh(true);
                                                                },
                                                                columns: [
                                                                    {
                                                                        caption: "Поставщик",
                                                                        dataType: "number",
                                                                        dataField: "contractor_id",
                                                                        lookup: {
                                                                            dataSource: {
                                                                                store: contractorsStore,
                                                                                paginate: true,
                                                                                pageSize: 25
                                                                            },
                                                                            displayExpr: 'short_name',
                                                                            valueExpr: 'id'
                                                                        },
                                                                        validationRules: [{type: "required"}]
                                                                    },
                                                                    {
                                                                        dataField: "quantity",
                                                                        dataType: "number",
                                                                        caption: "Длина (м.п)",
                                                                        showSpinButtons: true,
                                                                        cellTemplate: function (container, options) {
                                                                            let quantity = Math.round(options.data.quantity * 100) / 100;
                                                                            let measureUnit = 'м.п';

                                                                            $(`<div>${quantity} ${measureUnit}</div>`)
                                                                                .appendTo(container);
                                                                        },
                                                                        validationRules: [{type: "required"}]
                                                                    },
                                                                    {
                                                                        dataField: "amount",
                                                                        dataType: "number",
                                                                        caption: "Количество (шт)",
                                                                        cellTemplate: function (container, options) {
                                                                            let amount = options.data.amount;
                                                                            $(`<div>${amount} шт</div>`)
                                                                                .appendTo(container);
                                                                        },
                                                                        validationRules: [{type: "required"}]
                                                                    },
                                                                    {
                                                                        dataField: "computed_weight",
                                                                        dataType: "number",
                                                                        caption: "Вес",
                                                                        allowEditing: false,
                                                                        calculateCellValue: function (rowData) {
                                                                            return getMaterialWeight(rowData, 'standard_weight');
                                                                        },
                                                                        cellTemplate: function (container, options) {
                                                                            let weight = options.data.computed_weight;

                                                                            $(`<div>${weight} т</div>`)
                                                                                .appendTo(container);
                                                                        }
                                                                    }
                                                                ]
                                                            });

                                                            return dataGrid;
                                                        }
                                                    }
                                                ]
                                            })
                                        );
                                    }
                                }
                            }
                        }]
                    }
                ]
            }).dxForm('instance')

            function createGridGroupHeaderButtons() {
                let groupCaption = $('.material-supply-planning-grid').find('.dx-form-group-with-caption');
                $('<div>').addClass('dx-form-group-caption-buttons').prependTo(groupCaption);
                groupCaption.find('span').addClass('dx-form-group-caption-span-with-buttons');
                let groupCaptionButtonsDiv = groupCaption.find('.dx-form-group-caption-buttons');

                $('<div>')
                    .dxButton({
                        text: "Добавить",
                        icon: "fas fa-plus",
                        onClick: (e) => {
                            supplyPlanningForm.getEditor("materialsSupplyPlanningGrid").addRow();
                        }
                    })
                    .addClass('dx-form-group-caption-button')
                    .prependTo(groupCaptionButtonsDiv)
            }

            @if(Auth::user()->can('material_supply_planning_editing'))
            createGridGroupHeaderButtons();
            @endcan

            function calculateNeededWeight(rowData) {
                let weight = rowData.amount * rowData.quantity * rowData.standard_weight;

                if (isNaN(weight)) {
                    weight = 0;
                }

                if (rowData.remains_weight <= weight) {
                    return Math.round((weight - rowData.remains_weight) * 1000) / 1000;
                }
            }
        });
    </script>
@endsection
