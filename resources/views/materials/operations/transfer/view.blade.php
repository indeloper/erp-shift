@extends('layouts.app')

@section('title', 'Перемещение')

@section('url', "#")

@section('css_top')

@endsection

@section('content')
    <div id="formContainer"></div>
    <div id="popupContainer">
        <div id="materialsStandardsAddingForm"></div>
    </div>
@endsection

@section('js_footer')
    <script>
        $(function () {
            let operationData = {!! $operationData !!};

            let measureUnitsStore = new DevExpress.data.CustomStore({
                key: "id",
                loadMode: "raw",
                load: function (loadOptions) {
                    return $.getJSON("{{route('material.measure-units.list')}}",
                        {data: JSON.stringify(loadOptions)});
                },
            });
            let materialTypesStore = new DevExpress.data.CustomStore({
                key: "id",
                loadMode: "processed",
                load: function (loadOptions) {
                    return $.getJSON("{{route('materials.types.list')}}",
                        {data: JSON.stringify({filterOptions: loadOptions})});
                },
            });
            let materialStandardsStore = new DevExpress.data.CustomStore({
                key: "id",
                loadMode: "raw",
                load: function (loadOptions) {
                    return $.getJSON("{{route('materials.standards.list')}}"/*,
                        {data: JSON.stringify(loadOptions)}*/);
                },
            });

            //<editor-fold desc="JS: DataSources">
            let availableMaterialsStore = new DevExpress.data.CustomStore({
                key: "id",
                loadMode: "raw",
                load: function (loadOptions) {
                    return $.getJSON("{{route('materials.list')}}",
                        {/*project_object: sourceProjectObjectId*/});
                },
            });

            let availableMaterialsDataSource = new DevExpress.data.DataSource({
                group: "material_type_name",
                store: availableMaterialsStore
            })

            let selectedMaterialsDataSource = new DevExpress.data.DataSource({
                store: new DevExpress.data.ArrayStore({
                    key: "id",
                    data: []
                })
            })

            let transferMaterialData = {!! $operationMaterials !!};
            let transferMaterialStore = new DevExpress.data.ArrayStore({
                key: "id",
                data: transferMaterialData
            })
            let transferMaterialDataSource = new DevExpress.data.DataSource({
                reshapeOnPush: true,
                store: transferMaterialStore
            })

            let projectObjectStore = new DevExpress.data.CustomStore({
                key: "id",
                loadMode: "raw",
                load: function (loadOptions) {
                    return $.getJSON("{{route('project-objects.list')}}",
                        {data: JSON.stringify(loadOptions)});
                },
            });

            let usersStore = new DevExpress.data.CustomStore({
                key: "id",
                loadMode: "raw",
                load: function (loadOptions) {
                    return $.getJSON("{{route('users.list')}}",
                        {data: JSON.stringify(loadOptions)});
                },
            });
            //</editor-fold>

            let materialsStandardsAddingForm = $("#materialsStandardsAddingForm").dxForm({
                colCount: 2,
                items: [{
                    editorType: "dxList",
                    name: "availableMaterialsList",
                    editorOptions: {
                        height: 400,
                        width: 500,
                        dataSource: availableMaterialsDataSource,
                        showSelectionControls: true,
                        selectionMode: "multiply",
                        searchEnabled: true,
                        searchExpr: "name",
                        grouped: true,
                        collapsibleGroups: true,
                        itemTemplate: function (data) {
                            switch (data.accounting_type) {
                                case 1:
                                    return $("<div>").text(data.standard_name +
                                        ' (' +
                                        data.quantity +
                                        ' ' +
                                        data.measure_unit_value +
                                        '; ' +
                                        data.amount +
                                        ' шт.)'
                                    )
                                default:
                                    return $("<div>").text(data.standard_name +
                                        ' (' +
                                        data.quantity +
                                        ' ' +
                                        data.measure_unit_value +
                                        ')')
                            }
                        },
                        onSelectionChanged: function (data) {
                            data.addedItems.forEach(function (addedItem) {
                                selectedMaterialsDataSource.store().insert(addedItem)
                            })

                            data.removedItems.forEach(function (removedItem) {
                                selectedMaterialsDataSource.store().remove(removedItem.id)
                            })

                            selectedMaterialsDataSource.reload();
                        },


                        /*,
                        groupTemplate: function(data)*/
                    }
                },
                    {
                        editorType: "dxList",
                        name: "selectedMaterialsList",
                        editorOptions: {
                            dataSource: selectedMaterialsDataSource,
                            allowItemDeleting: true,
                            itemDeleteMode: "static",
                            height: 400,
                            width: 500,
                            itemTemplate: function (data) {
                                switch (data.accounting_type) {
                                    case 1:
                                        return $("<div>").text(data.standard_name +
                                            ' (' +
                                            data.quantity +
                                            ' ' +
                                            data.measure_unit_value +
                                            '; ' +
                                            data.amount +
                                            ' шт.)'
                                        )
                                    default:
                                        return $("<div>").text(data.standard_name +
                                            ' (' +
                                            data.quantity +
                                            ' ' +
                                            data.measure_unit_value +
                                            ')')
                                }
                            },
                            onItemDeleted: function (e) {
                                let materialsStandardsList = materialsStandardsAddingForm.getEditor("availableMaterialsDataSource");
                                let selectedMaterialsStandardsList = materialsStandardsAddingForm.getEditor("selectedMaterialsList");

                                materialsStandardsList.option("selectedItems", selectedMaterialsStandardsList.option("items"));
                            }
                        }
                    },
                    {
                        itemType: "button",
                        colSpan: 2,
                        horizontalAlignment: "right",
                        buttonOptions: {
                            text: "Добавить",
                            type: "default",
                            stylingMode: "text",
                            useSubmitBehavior: false,

                            onClick: function () {
                                let selectedMaterialsData = materialsStandardsAddingForm.getEditor("selectedMaterialsList").option("items");

                                selectedMaterialsData.forEach(function (material) {
                                    transferMaterialDataSource.store().insert({
                                        id: material.id,
                                        standard_id: material.standard_id,
                                        standard_name: material.standard_name,
                                        accounting_type: material.accounting_type,
                                        material_type: material.material_type,
                                        measure_unit: material.measure_unit,
                                        standard_weight: material.weight,
                                        length_quantity: material.accounting_type === 1 ? material.quantity : material.amount
                                    })
                                })
                                transferMaterialDataSource.reload();
                                $("#popupContainer").dxPopup("hide")
                            }
                        }
                    }
                ]
            }).dxForm("instance");

            let popupContainer = $("#popupContainer").dxPopup({
                height: "auto",
                width: "auto"
            });

            //<editor-fold desc="JS: Columns definition">
            let transferMaterialColumns = [
                {
                    type: "buttons",
                    width: 110,
                    buttons: [
                        {
                            icon: "warning",
                            style: "color: red",
                            visible: (e) => {
                                return typeof e.row.data.errors !== "undefined"
                            }
                        },
                        "delete"

                    ]
                },
                {
                    dataField: "standard_id",
                    dataType: "string",
                    allowEditing: false,
                    caption: "Наименование",
                    lookup: {
                        dataSource: {store: materialStandardsStore},
                        displayExpr: "name",
                        valueExpr: "id"
                    }
                },
                {
                    dataField: "measure_unit",
                    dataType: "number",
                    allowEditing: false,
                    caption: "Единица измерения",
                    lookup: {
                        dataSource: {store: measureUnitsStore},
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
                    dataField: "material_quantity",
                    dataType: "number",
                    caption: "Количество",
                },
                {
                    dataField: "computed_weight",
                    dataType: "number",
                    allowEditing: false,
                    caption: "Вес",
                    calculateCellValue: function (rowData) {
                        let weight;
                        if (rowData.accounting_type === 1) {
                            weight = rowData.material_quantity * rowData.length_quantity * rowData.standard_weight;
                        } else {
                            weight = rowData.material_quantity * rowData.standard_weight;
                        }

                        if (isNaN(weight)) {
                            weight = 0;
                        } else {
                            weight = weight.toFixed(3)
                        }

                        rowData.computed_weight = weight;
                        return weight;

                    }
                },
                {
                    dataField: "material_type",
                    dataType: "number",
                    caption: "Тип материала",
                    groupIndex: 0,
                    lookup: {
                        dataSource: {store: materialTypesStore},
                        displayExpr: "name",
                        valueExpr: "id"
                    }
                },
            ];
            //</editor-fold>

            //<editor-fold desc="JS: Grid configuration">
            let transferMaterialGridConfiguration = {
                dataSource: transferMaterialDataSource,
                focusedRowEnabled: false,
                hoverStateEnabled: true,
                columnAutoWidth: false,
                showBorders: true,
                showColumnLines: true,
                grouping: {
                    autoExpandAll: true,
                },
                groupPanel: {
                    visible: false
                },
                editing: {
                    mode: "cell",
                    @if ($allowEditing)
                    allowUpdating: true,
                    allowDeleting: true,
                    @endif
                    selectTextOnEditStart: false,
                    startEditAction: "click"
                },
                columns: transferMaterialColumns,
                summary: {
                    groupItems: [{
                        column: "standard_id",
                        summaryType: "count",
                        displayFormat: "Количество: {0}",
                    },
                        {
                            column: "material_quantity",
                            summaryType: "sum",
                            customizeText: function (data) {
                                return "Всего: " + data.value.toFixed(3)
                            },
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
                        //displayFormat: "Итого: {0} т.",
                        customizeText: function (data) {
                            return "Итого: " + data.value.toFixed(3) + " т."
                        }
                    }]
                },
                @if($allowEditing)
                onToolbarPreparing: function (e) {
                    let dataGrid = e.component;
                    e.toolbarOptions.items.unshift(
                        {
                            location: "before",
                            widget: "dxButton",
                            options: {
                                icon: "add",
                                text: "Добавить",
                                onClick: function (e) {
                                    $("#popupContainer").dxPopup("show")
                                }
                            }
                        }
                    );
                },
                @endif
                onRowUpdated: validateSingleMaterial
            };
            //</editor-fold>

            //<editor-fold desc="JS: Edit form configuration">
            let operationForm = $("#formContainer").dxForm({
                formData: operationData,
                readOnly: true,
                colCount: 2,
                items: [{
                    itemType: "group",
                    colCount: 3,
                    caption: "Отправление",
                    items: [{
                        colSpan: 3,
                        dataField: "source_project_object_id",
                        label: {
                            text: "Объект отправления"
                        },
                        editorType: "dxSelectBox",
                        editorOptions: {
                            dataSource: {
                                store: projectObjectStore
                            },
                            displayExpr: "short_name",
                            valueExpr: "id",
                            searchEnabled: true
                        }
                    },
                        {
                            dataField: "date_start",
                            colSpan: 1,
                            label: {
                                text: "Дата отправления"
                            },
                            editorType: "dxDateBox"
                        },
                        {
                            colSpan: 2,
                            dataField: "source_responsible_user_id",
                            label: {
                                text: "Ответственный"
                            },
                            editorType: "dxSelectBox",
                            editorOptions: {
                                dataSource: {
                                    store: usersStore
                                },
                                displayExpr: "full_name",
                                valueExpr: "id",
                                searchEnabled: true
                            }
                        }]
                }, {
                    itemType: "group",
                    colCount: 3,
                    caption: "Получение",
                    items: [{
                        colSpan: 3,
                        dataField: "destination_project_object_id",
                        label: {
                            text: "Объект получения"
                        },
                        editorType: "dxSelectBox",
                        editorOptions: {
                            dataSource: {
                                store: projectObjectStore
                            },
                            displayExpr: "short_name",
                            valueExpr: "id",
                            searchEnabled: true
                        }
                    },
                        {
                            colSpan: 3,
                            dataField: "destination_responsible_user_id",
                            label: {
                                text: "Ответственный"
                            },
                            editorType: "dxSelectBox",
                            editorOptions: {
                                dataSource: {
                                    store: usersStore
                                },
                                displayExpr: "full_name",
                                valueExpr: "id",
                                searchEnabled: true
                            }
                        }]
                },
                    {
                        itemType: "group",
                        caption: "Материалы",
                        colSpan: 2,
                        items: [{
                            dataField: "",
                            editorType: "dxDataGrid",
                            name: "transferMaterialGrid",
                            editorOptions: transferMaterialGridConfiguration
                        }
                        ]

                    },
                        @if($allowEditing)
                    {
                        itemType: "button",
                        colSpan: 1,
                        horizontalAlignment: "left",
                        buttonOptions: {
                            text: "Конфликт?",
                            type: "danger",
                            stylingMode: "contained",
                            useSubmitBehavior: false,

                            onClick: function () {
                                //
                            }
                        }
                    },
                    {
                        itemType: "button",
                        colSpan: 1,
                        horizontalAlignment: "right",
                        buttonOptions: {
                            text: "Подтвердить",
                            type: "default",
                            stylingMode: "contained",
                            useSubmitBehavior: false,

                            onClick: function () {
                                let transferOperationData = {};
                                transferOperationData.operationId = operationData.id;
                                transferOperationData.materials = transferMaterialData;

                                console.log(transferOperationData);

                                $.ajax({
                                    url: "{{route('materials.operations.transfer.move')}}",
                                    method: "POST",
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    },
                                    data: {
                                        data: JSON.stringify(transferOperationData),
                                        options: null
                                    },

                                    success: function (data, textStatus, jqXHR) {
                                        if (transferOperationInitiator === "none" || transferOperationInitiator === "source") {
                                            //window.location.href = '{{route('materials.index')}}/?project_object=' + sourceProjectObjectId
                                        }
                                        if (transferOperationInitiator === "destination") {
                                            //window.location.href = '{{route('materials.index')}}/?project_object=' + destinationProjectObjectId
                                        }
                                    },
                                    error: function (jqXHR, textStatus, errorThrown) {
                                        DevExpress.ui.notify("При сохранении данных произошла ошибка", "error", 5000)
                                    }
                                })
                            }
                        }
                    }
                    @endif
                ]

            }).dxForm("instance")
            //</editor-fold>

            //<editor-fold desc="JS: Toolbar configuration">
            //</editor-fold>

            function validateSingleMaterial(updateInfo) {
                $.ajax({
                    dataType: "json",
                    url: "{{route('materials.operations.transfer.validate-single-material')}}",
                    data: {
                        operationId: operationData.id,
                        sourceProjectObjectId: operationData.source_project_object_id,
                        material: {
                            standardID: updateInfo.data.standard_id,
                            lengthQuantity: updateInfo.data.length_quantity,
                            quantity: updateInfo.data.material_quantity
                        }
                    },
                    success: (e) => {
                        delete updateInfo.data.errors;
                        updateInfo.component.repaintRows(updateInfo.component.getRowIndexByKey(updateInfo.key));
                    },
                    error: (e) => {
                        if (e.responseJSON.result === 'error') {
                            updateInfo.data.errors = e.responseJSON.errors;
                            updateInfo.component.repaintRows(updateInfo.component.getRowIndexByKey(updateInfo.key));
                        }
                    }
                });
            }
        });
    </script>
@endsection
