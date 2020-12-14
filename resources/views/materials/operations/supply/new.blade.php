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
            let contractorsStore = new DevExpress.data.CustomStore({
                key: "id",
                loadMode: "raw",
                load: function (loadOptions) {
                    return $.getJSON("{{route('contractors.list')}}",
                        {data: JSON.stringify({dxLoadOptions: loadOptions})});
                },
            });

            let contractorsDataSource = new DevExpress.data.DataSource({
                store: contractorsStore
            });


            let materialsStandardsListDataSource = new DevExpress.data.DataSource({
                group: "material_type_name",
                store: new DevExpress.data.ArrayStore({
                    key: "id",
                    data: materialStandardsData
                })
            })

            let selectedMaterialStandardsListDataSource = new DevExpress.data.DataSource({
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

            let projectObjectsDataSource = new DevExpress.data.DataSource({
                reshapeOnPush: true,
                store: new DevExpress.data.CustomStore({
                    key: "id",
                    loadMode: "raw",
                    load: function (loadOptions) {
                        return $.getJSON("{{route('project-objects.list')}}",
                            {data: JSON.stringify(loadOptions)});
                    }
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
                        onSelectionChanged: function (data) {
                            data.addedItems.forEach(function (addedItem) {
                                console.log(addedItem);
                                selectedMaterialStandardsListDataSource.store().insert({
                                    id: addedItem.id,
                                    name: addedItem.name,
                                    accounting_type: addedItem.accounting_type,
                                    material_type: addedItem.material_type,
                                    measure_unit: addedItem.measure_unit,
                                    measure_unit_value: addedItem.measure_unit_value,
                                    weight: addedItem.weight
                                })
                            })

                            data.removedItems.forEach(function (removedItem) {
                                selectedMaterialStandardsListDataSource.store().remove(removedItem.id)
                            })

                            selectedMaterialStandardsListDataSource.reload();
                        },
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

                            selectedMaterialsData.forEach(function (materialStandard) {
                                supplyMaterialDataSource.store().insert({
                                    id: ++supplyMaterialTempID,
                                    standard_id: materialStandard.id,
                                    standard_name: materialStandard.name,
                                    accounting_type: materialStandard.accounting_type,
                                    material_type: materialStandard.material_type,
                                    measure_unit: materialStandard.measure_unit,
                                    measure_unit_value: materialStandard.measure_unit_value,
                                    standard_weight: materialStandard.weight,
                                    quantity: null,
                                    amount: null
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
                        hint: "Дублировать",
                        icon: "copy",
                        onClick: function (e) {
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
                    alignment: "right",
                    allowEditing: false,
                    caption: "Единица измерения",
                    lookup: {
                        dataSource: measureUnitData,
                        displayExpr: "value",
                        valueExpr: "id"
                    }
                },
                {
                    dataField: "quantity",
                    dataType: "number",
                    caption: "Количество",
                    editorOptions: {
                        min: 0
                    },
                    cellTemplate: function (container, options) {
                        let quantity = options.data.quantity;
                        console.log(options.data);
                        if (quantity !== null) {
                            $(`<div>${quantity} ${options.data.measure_unit_value}</div>`)
                                .appendTo(container);
                        }
                    },
                    validationRules: [{type: "required"}]
                },
                {
                    dataField: "amount",
                    dataType: "number",
                    caption: "Количество (шт.)",
                    editorOptions: {
                        min: 0,
                        format: "#"
                    },
                    cellTemplate: function (container, options) {
                        let amount = options.data.amount;
                        if (amount !== null) {
                            $(`<div>${amount} шт.</div>`)
                                .appendTo(container);
                        }
                    },
                    validationRules: [{type: "required"}]
                },
                {
                    dataField: "computed_weight",
                    dataType: "number",
                    allowEditing: false,
                    caption: "Вес",
                    calculateCellValue: function (rowData) {
                        let weight = rowData.quantity * rowData.amount * rowData.standard_weight;

                        if (isNaN(weight)) {
                            weight = 0;
                        } else {
                            weight = weight.toFixed(3)
                        }

                        rowData.computed_weight = weight;
                        return weight;

                    },
                    cellTemplate: function (container, options) {
                        let computed_weight = options.data.computed_weight;
                        if (computed_weight !== null) {
                            $(`<div>${computed_weight} т.</div>`)
                                .appendTo(container);
                        }
                    },

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
                            column: "amount",
                            summaryType: "sum",
                            customizeText: function (data) {
                                return `Всего: ${data.value} шт.`
                            },
                            showInGroupFooter: false,
                            alignByColumn: true
                        },
                        {
                            column: "computed_weight",
                            summaryType: "sum",
                            customizeText: function (data) {
                                return `Всего: ${data.value.toFixed(3)} т.`
                            },
                            showInGroupFooter: false,
                            alignByColumn: true
                        }],
                    totalItems: [{
                        column: "computed_weight",
                        summaryType: "sum",
                        customizeText: function (data) {
                            return `Итого: ${data.value.toFixed(3)} т.`
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
                            dataSource: projectObjectsDataSource,
                            displayExpr: "short_name",
                            valueExpr: "id",
                            searchEnabled: true,
                            value: projectObject,
                            onValueChanged: function (e) {
                                projectObject = e.value;
                            }
                        },
                        validationRules: [{
                            type: "required",
                            message: 'Поле "Объект" обязательно для заполнения'
                        }]
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
                            },
                            validationRules: [{
                                type: "required",
                                message: 'Поле "Дата поставки" обязательно для заполнения'
                            }]
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
                            },
                            validationRules: [{
                                type: "required",
                                message: 'Поле "Ответственный" обязательно для заполнения'
                            }]

                        }]
                }, {
                    itemType: "group",
                    caption: "Поставщик",
                    items: [{
                        dataField: "contractor_id",
                        label: {
                            text: "Поставщик"
                        },
                        editorType: "dxSelectBox",
                        editorOptions: {
                            dataSource: contractorsDataSource,
                            displayExpr: "short_name",
                            valueExpr: "id",
                            searchEnabled: true
                        },
                        validationRules: [{
                            type: "required",
                            message: 'Поле "Поставщик" обязательно для заполнения'
                        }]
                    },
                        {
                            dataField: "consignment_note_number",
                            label: {
                                text: "Номер ТТН"
                            },
                            editorType: "dxNumberBox",
                            editorOptions: {
                                min: 0,
                                format: "000000",
                                showSpinButtons: false,
                                value: null
                            },
                            validationRules: [{
                                type: "required",
                                message: 'Поле "Номер ТТН" обязательно для заполнения'
                            }]
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

                            onClick: function (e) {
                                let supplyOperationData = {};

                                let result = e.validationGroup.validate();
                                if (!result.isValid) {
                                    return;
                                }

                                supplyOperationData.project_object_id = operationForm.option("formData").project_object_id;
                                //TODO Дата формаируется в UTC. Нужно либо учитывать это при перобразовании, либо хранить в UTC в БД
                                supplyOperationData.date_start = new Date(operationForm.option("formData").date_start).toJSON().split("T")[0];
                                supplyOperationData.destination_responsible_user_id = operationForm.option("formData").destination_responsible_user_id;
                                supplyOperationData.contractor_id = operationForm.option("formData").contractor_id;
                                supplyOperationData.contract_id = operationForm.option("formData").contract_id;
                                supplyOperationData.consignment_note_number = operationForm.option("formData").consignment_note_number;

                                supplyOperationData.materials = supplyMaterialData;

                                console.log(operationForm.option("formData"));
                                console.log(supplyOperationData);
                                console.log(JSON.stringify(supplyOperationData));
                                validateMaterialList(supplyOperationData, false);
                            }
                        }
                    }]

            }).dxForm("instance")
            //</editor-fold>

            //<editor-fold desc="JS: Toolbar configuration">
            //</editor-fold>

            function validateMaterialList(supplyOperationData, forcePostData) {
                $.ajax({
                    url: "{{route('materials.operations.supply.new.validate-material-list')}}",
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        data: JSON.stringify(supplyOperationData)
                    },
                    success: function (data, textStatus, jqXHR) {
                        postEditingData(supplyOperationData)
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        if (forcePostData) {
                            postEditingData(supplyOperationData)
                        }
                        DevExpress.ui.notify("При сохранении данных произошла ошибка<br>Список ошибок", "error", 5000)
                    }
                })
            }

            function postEditingData(supplyOperationData) {
                $.ajax({
                    url: "{{route('materials.operations.supply.new')}}",
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        data: JSON.stringify(supplyOperationData)
                    },

                    success: function (data, textStatus, jqXHR) {
                        window.location.href = '{{route('materials.index')}}/?project_object=' + projectObject
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        DevExpress.ui.notify("При сохранении данных произошла ошибка", "error", 5000)
                    }
                })
            }
        });

    </script>
@endsection
