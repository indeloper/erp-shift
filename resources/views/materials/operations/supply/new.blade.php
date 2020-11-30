@extends('layouts.app')

@section('title', 'Новая поставка')

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
            let measureUnitData = {!!$measureUnits ?? ''!!};
            let projectObject = {{$projectObjectId}};
            let materialStandardsData = {!!$materialStandards!!};
            let materialTypesData = {!!$materialTypes!!};

            let supplyMaterialTempID = 0;

            //<editor-fold desc="JS: DataSources">
            let materialsStandardsListDataSource = new DevExpress.data.DataSource({
                group: "material_type_name",
                store: new DevExpress.data.ArrayStore({
                    key: "id",
                    data: materialStandardsData
                })
            })

            let selectedMaterialStandardsListDataSource = new DevExpress.data.DataSource ({
                store: new DevExpress.data.ArrayStore({
                    key: "id",
                    data: []
                })
            })

            let supplyMaterialData = [];

            let supplyMaterialStore = new DevExpress.data.ArrayStore({
                key: "id",
                data: supplyMaterialData
            })

            let supplyMaterialDataSource = new DevExpress.data.DataSource({
                reshapeOnPush: true,
                store: supplyMaterialStore
            })

            let projectObjectsData = new DevExpress.data.DataSource({
                reshapeOnPush: true,
                store: new DevExpress.data.ArrayStore({
                    key: "id",
                    data: {!! $projectObjects !!}
                })
            });

            let usersData = new DevExpress.data.DataSource({
                reshapeOnPush: true,
                store: new DevExpress.data.ArrayStore({
                    key: "id",
                    data: {!!$users!!}
                })
            });
            //</editor-fold>

            let materialsStandardsAddingForm = $("#materialsStandardsAddingForm").dxForm({
                colCount: 2,
                items: [{
                    editorType: "dxList",
                    name: "materialsStandardsList",
                    editorOptions: {
                        height: 400,
                        width: 500,
                        dataSource: materialsStandardsListDataSource,
                        showSelectionControls: true,
                        selectionMode: "multiply",
                        searchEnabled: true,
                        searchExpr: "name",
                        grouped: true,
                        collapsibleGroups: true,
                        itemTemplate: function (data) {
                            return $("<div>").text(data.name)
                        },
                        onSelectionChanged: function(data) {
                            data.addedItems.forEach(function (addedItem){
                                console.log(addedItem);
                                selectedMaterialStandardsListDataSource.store().insert({
                                    id: addedItem.id,
                                    name: addedItem.name,
                                    accounting_type: addedItem.accounting_type,
                                    material_type: addedItem.material_type,
                                    measure_unit: addedItem.measure_unit,
                                    weight: addedItem.weight
                                })
                            })

                            data.removedItems.forEach(function (removedItem){
                                selectedMaterialStandardsListDataSource.store().remove(removedItem.id)
                            })

                            selectedMaterialStandardsListDataSource.reload();
                        },


                        /*,
                        groupTemplate: function(data)*/
                    }
                },
                    {
                        editorType: "dxList",
                        name: "selectedMaterialsStandardsList",
                        editorOptions: {
                            dataSource: selectedMaterialStandardsListDataSource,
                            allowItemDeleting: true,
                            itemDeleteMode: "static",
                            height: 400,
                            width: 500,
                            itemTemplate: function (data) {
                                return $("<div>").text(data.name)
                            },

                            onItemDeleted: function (e) {
                                console.log(e);
                                let materialsStandardsList = materialsStandardsAddingForm.getEditor("materialsStandardsList");
                                let selectedMaterialsStandardsList = materialsStandardsAddingForm.getEditor("selectedMaterialsStandardsList");

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
                            let selectedMaterialsData = materialsStandardsAddingForm.getEditor("selectedMaterialsStandardsList").option("items");

                            selectedMaterialsData.forEach(function(materialStandard){
                                supplyMaterialDataSource.store().insert({
                                    id: ++supplyMaterialTempID,
                                    standard_id: materialStandard.id,
                                    standard_name: materialStandard.name,
                                    accounting_type: materialStandard.accounting_type,
                                    material_type: materialStandard.material_type,
                                    measure_unit: materialStandard.measure_unit,
                                    standard_weight: materialStandard.weight
                                })
                            })
                            supplyMaterialDataSource.reload();
                            console.log(supplyMaterialDataSource.store());
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
            let supplyMaterialColumns = [
                {
                    type: "buttons",
                    width: 110,
                    buttons: ["delete", {
                        hint: "Копировать",
                        icon: "copy",
                        onClick: function(e) {
                            let clonedItem = $.extend({}, e.row.data, {id: ++supplyMaterialTempID});
                            console.log(supplyMaterialData);
                            supplyMaterialData.splice(e.row.rowIndex, 0, clonedItem);
                            e.component.refresh(true);
                            e.event.preventDefault();
                        }
                    }]
                },
                {
                    dataField: "standard_id",
                    dataType: "string",
                    allowEditing: false,
                    caption: "Наименование",
                    lookup: {
                        dataSource: materialStandardsData,
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
                        dataSource: measureUnitData,
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
                        console.log(rowData);

                        let weight;
                        if (rowData.accounting_type === 1){
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
                        dataSource: materialTypesData,
                        displayExpr: "name",
                        valueExpr: "id"
                    }
                },
            ];
            //</editor-fold>

            //<editor-fold desc="JS: Grid configuration">
            let supplyMaterialGridConfiguration = {
                dataSource: supplyMaterialDataSource,
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
                    allowUpdating: true,
                    allowDeleting: true,
                    selectTextOnEditStart: false,
                    startEditAction: "click"
                },
                columns: supplyMaterialColumns,
                summary: {
                    groupItems: [{
                        column: "standard_id",
                        summaryType: "count",
                        displayFormat: "Количество: {0}",
                    },
                        {
                            column: "length_quantity",
                            summaryType: "sum",
                            customizeText: function (data) {
                                return "Всего: " + data.value.toFixed(3)
                            },
                            showInGroupFooter: false,
                            alignByColumn: true
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

                onToolbarPreparing: function (e) {
                    let dataGrid = e.component;
                    e.toolbarOptions.items.unshift(
                        {
                            location: "before",
                            widget: "dxButton",
                            options: {
                                icon: "add",
                                text: "Добавить",
                                onClick: function (e){
                                    $("#popupContainer").dxPopup("show")
                                }
                            }
                        }
                    );
                },
                onRowDblClick: function(e){
                    console.log(e);
                }
            };
            //</editor-fold>

            //<editor-fold desc="JS: Edit form configuration">
            let operationForm = $("#formContainer").dxForm({
                formData: [],
                colCount: 2,
                items: [{
                    itemType: "group",
                    colCount: 3,
                    caption: "Поставка",
                    items: [{
                        colSpan: 3,
                        dataField: "project_object_id",
                        label: {
                            text: "Объект"
                        },
                        editorType: "dxSelectBox",
                        editorOptions: {
                            dataSource: projectObjectsData,
                            displayExpr: "name",
                            valueExpr: "id",
                            searchEnabled: true,
                            value: projectObject,
                            onValueChanged: function(e){
                                projectObject = e.value;
                            }
                        }
                    },
                    {
                        dataField: "date_start",
                        colSpan: 1,
                        label: {
                            text: "Дата поставки"
                        },
                        editorType: "dxDateBox",
                        editorOptions: {
                            value: Date.now()
                        }
                    },
                        {
                            colSpan: 2,
                            dataField: "destination_responsible_user_id",
                            label: {
                                text: "Ответственный"
                            },
                            editorType: "dxSelectBox",
                            editorOptions: {
                                dataSource: usersData,
                                displayExpr: "full_name",
                                valueExpr: "id",
                                searchEnabled: true,
                                value: {{$currentUserId}}
                        }
                    }]
                },{
                    itemType: "group",
                    caption: "Контрагент",
                    items: [{
                        dataField: "contractor_id",
                        label: {
                            text: "Поставщик"
                        },
                        editorType: "dxSelectBox",
                        editorOptions: {

                        }
                    },
                    {
                        dataField: "contract_id",
                        label: {
                            text: "Договор"
                        },
                        editorType: "dxSelectBox",
                        editorOptions: {

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
                        editorOptions: supplyMaterialGridConfiguration
                    }
                    ]

                },
                {
                    itemType: "button",
                    colSpan: 2,
                    horizontalAlignment: "right",
                    buttonOptions: {
                        text: "Создать поставку",
                        type: "default",
                        stylingMode: "contained",
                        useSubmitBehavior: false,

                        onClick: function () {
                            let supplyOperationData = {};

                            supplyOperationData.project_object_id = operationForm.option("formData").project_object_id;
                            //TODO Дата формаируется в UTC. Нужно либо учитывать это при перобразовании, либо хранить в UTC в БД
                            supplyOperationData.date_start = new Date(operationForm.option("formData").date_start).toJSON().split("T")[0];
                            supplyOperationData.destination_responsible_user_id = operationForm.option("formData").destination_responsible_user_id;
                            supplyOperationData.contractor_id = operationForm.option("formData").contractor_id;
                            supplyOperationData.contract_id = operationForm.option("formData").contract_id;

                            supplyOperationData.materials = supplyMaterialData;

                            console.log(operationForm.option("formData"));
                            console.log(supplyOperationData);
                            console.log(JSON.stringify(supplyOperationData));

                            $.ajax({
                                url: "{{route('materials.operations.supply.new')}}",
                                method: "POST",
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                data: {
                                    data: JSON.stringify(supplyOperationData),
                                    options: null
                                },

                                success: function (data, textStatus, jqXHR){
                                    window.location.href = '{{route('materials.index')}}/?project_object=' + projectObject
                                },
                                error: function(jqXHR, textStatus, errorThrown) {
                                    DevExpress.ui.notify("При сохранении данных произошла ошибка", "error", 5000)
                                }
                            })
                        }
                    }
                }]

            }).dxForm("instance")
            //</editor-fold>

            //<editor-fold desc="JS: Toolbar configuration">
            //</editor-fold>


        });

    </script>
@endsection
