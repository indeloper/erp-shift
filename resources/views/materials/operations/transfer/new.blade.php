@extends('layouts.app')

@section('title', 'Новое перемещение')

@section('url', "#")

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
    </style>
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
                        {data: JSON.stringify({dxLoadOptions: loadOptions})});
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


            let sourceProjectObjectId = {{$sourceProjectObjectId}};
            let destinationProjectObjectId = {{$destinationProjectObjectId}};
            let transferOperationInitiator = "{{$transferOperationInitiator}}"; //one of "none", "source", "destination"
            let transferMaterialTempID = 0;


            //<editor-fold desc="JS: DataSources">
            let availableMaterialsStore = new DevExpress.data.CustomStore({
                key: "id",
                loadMode: "raw",
                load: function (loadOptions) {
                    return $.getJSON("{{route('materials.list')}}",
                        {project_object: sourceProjectObjectId});
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

            let transferMaterialData = {!! $predefinedMaterials !!};
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
                                case 2:
                                    return $("<div>").text(data.standard_name +
                                        ' (' +
                                        data.quantity +
                                        ' ' +
                                        data.measure_unit_value +
                                        '; ' +
                                        data.amount +
                                        ' шт)'
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
                                console.log(addedItem);
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
                                    case 2:
                                        return $("<div>").text(data.standard_name +
                                            ' (' +
                                            data.quantity +
                                            ' ' +
                                            data.measure_unit_value +
                                            '; ' +
                                            data.amount +
                                            ' шт)'
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
                                console.log(e);
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
                                    console.log(material);
                                    transferMaterialDataSource.store().insert({
                                        id: material.id,
                                        standard_id: material.standard_id,
                                        standard_name: material.standard_name,
                                        accounting_type: material.accounting_type,
                                        material_type: material.material_type,
                                        measure_unit: material.measure_unit,
                                        measure_unit_value: material.measure_unit_value,
                                        standard_weight: material.weight,
                                        quantity: material.quantity,
                                        amount: material.amount
                                    })
                                })
                                transferMaterialDataSource.reload();
                                console.log(transferMaterialDataSource.store());
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
                    buttons: ["delete"]
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
                    alignment: "right",
                    lookup: {
                        dataSource: {store: measureUnitsStore},
                        displayExpr: "value",
                        valueExpr: "id"
                    }
                },
                {
                    dataField: "quantity",
                    dataType: "number",
                    caption: "Количество",
                    allowEditing: false,
                    showSpinButtons: false,
                    cellTemplate: function (container, options) {
                        let quantity = options.data.quantity;
                        console.log(options.data);
                        if (quantity !== null) {
                            $(`<div>${quantity} ${options.data.measure_unit_value}</div>`)
                                .appendTo(container);
                        }
                    },
                },
                {
                    dataField: "amount",
                    dataType: "number",
                    caption: "Количество (шт)",
                    editorOptions: {
                        min: 0,
                        format: "#"
                    },
                    cellTemplate: function (container, options) {
                        let amount = options.data.amount;
                        if (amount !== null) {
                            $(`<div>${amount} шт</div>`)
                                .appendTo(container);
                        }
                    }
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
                                return `Всего: ${data.value} шт`
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
                        //displayFormat: "Итого: {0} т.",
                        customizeText: function (data) {
                            return "Итого: " + data.value.toFixed(3) + " т."
                        }
                    }]
                },
                onEditorPreparing: (e) => {
                    if (e.dataField === "quantity" && e.parentType === "dataRow") {
                        if (e.row.data.accounting_type === 2) {
                            e.cancel = true
                        }
                    }
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
                                onClick: function (e) {
                                    $("#popupContainer").dxPopup("show")
                                }
                            }
                        }
                    );
                },
                onRowDblClick: function (e) {
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
                            searchEnabled: true,
                            value: transferOperationInitiator === "source" ? sourceProjectObjectId : null,
                            onValueChanged: function (e) {
                                if (operationForm.getEditor("transferMaterialGrid").option("dataSource").items().length > 0) {
                                    let confirmDialog = DevExpress.ui.dialog.confirm('При смене объекта отправления будут удалены введенные данные по материалам операции.<br>Продолжить?', 'Смена объекта отправления');
                                    confirmDialog.done(function (dialogResult) {
                                        if (dialogResult) {
                                            sourceProjectObjectId = e.value;
                                            transferMaterialStore.clear();
                                            transferMaterialDataSource.reload();
                                            availableMaterialsDataSource.reload();
                                        } else {
                                            e.component.off("onValueChanged");
                                            e.component.option("value", e.previousValue)
                                            e.component.on("onValueChanged");
                                        }
                                    });
                                } else {
                                    sourceProjectObjectId = e.value;
                                    transferMaterialData = [];
                                    transferMaterialDataSource.reload();
                                    availableMaterialsDataSource.reload();
                                }
                            }
                        },
                        validationRules: [{
                            type: "required",
                            message: 'Поле "Объект отправления" обязательно для заполнения'
                        }]
                    },
                        {
                            dataField: "date_start",
                            colSpan: 1,
                            visible: transferOperationInitiator !== "destination",
                            label: {
                                text: "Дата отправления"
                            },
                            editorType: "dxDateBox",
                            editorOptions: {
                                value: transferOperationInitiator === "source" ? Date.now() : null,
                            },
                            validationRules: [{
                                type: transferOperationInitiator !== "destination" ? "required" : "",
                                message: 'Поле "Дата получения" обязательно для заполнения'
                            }]
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
                                searchEnabled: true,
                                value: transferOperationInitiator === "source" ? {{$currentUserId}} : null
                            },
                            validationRules: [{
                                type: "required",
                                message: 'Поле "Ответственный" обязательно для заполнения'
                            }]
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
                            searchEnabled: true,
                            value: transferOperationInitiator === "destination" ? destinationProjectObjectId : null,
                        },
                        validationRules: [{
                            type: "required",
                            message: 'Поле "Объект получения" обязательно для заполнения'
                        }]
                    },
                        {
                            dataField: "date_end",
                            colSpan: 1,
                            visible: transferOperationInitiator === "destination",
                            label: {
                                text: "Дата получения"
                            },
                            editorType: "dxDateBox",
                            editorOptions: {
                                value: transferOperationInitiator === "destination" ? Date.now() : null,
                            },
                            validationRules: [{
                                type: transferOperationInitiator === "destination" ? "required" : "",
                                message: 'Поле "Дата получения" обязательно для заполнения'
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
                                dataSource: {
                                    store: usersStore
                                },
                                displayExpr: "full_name",
                                valueExpr: "id",
                                searchEnabled: true,
                                value: transferOperationInitiator === "destination" ? {{$currentUserId}} : null
                            },
                            validationRules: [{
                                type: "required",
                                message: 'Поле "Ответственный" обязательно для заполнения'
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
                            name: "transferMaterialGrid",
                            editorOptions: transferMaterialGridConfiguration
                        }
                        ]

                    },
                    {
                        itemType: "button",
                        colSpan: 2,
                        horizontalAlignment: "right",
                        buttonOptions: {
                            text: "Создать перемещение",
                            type: "default",
                            stylingMode: "contained",
                            useSubmitBehavior: false,

                            onClick: function (e) {
                                let transferOperationData = {};

                                let result = e.validationGroup.validate();
                                if (!result.isValid) {
                                    return;
                                }

                                transferOperationData.transfer_operation_initiator = transferOperationInitiator;
                                transferOperationData.source_project_object_id = operationForm.option("formData").source_project_object_id;
                                transferOperationData.destination_project_object_id = operationForm.option("formData").destination_project_object_id;
                                //TODO Дата формаируется в UTC. Нужно либо учитывать это при перобразовании, либо хранить в UTC в БД
                                if (transferOperationInitiator === "none" || transferOperationInitiator === "source") {
                                    transferOperationData.date_start = new Date(operationForm.option("formData").date_start).toJSON().split("T")[0];
                                    transferOperationData.date_end = null;
                                }

                                if (transferOperationInitiator === "destination") {
                                    transferOperationData.date_start = null;
                                    transferOperationData.date_end = new Date(operationForm.option("formData").date_start).toJSON().split("T")[0];
                                }

                                transferOperationData.source_responsible_user_id = operationForm.option("formData").source_responsible_user_id;
                                transferOperationData.destination_responsible_user_id = operationForm.option("formData").destination_responsible_user_id;

                                transferOperationData.consignment_note_number = operationForm.option("formData").consignment_note_number;

                                transferOperationData.materials = transferMaterialData;

                                console.log(operationForm.option("formData"));
                                console.log(transferOperationData);
                                console.log(JSON.stringify(transferOperationData));
                                validateMaterialList(transferOperationData, false);
                            }
                        }
                    }]

            }).dxForm("instance")
            //</editor-fold>

            //<editor-fold desc="JS: Toolbar configuration">
            //</editor-fold>

            function validateMaterialList(transferOperationData, forcePostData) {
                $.ajax({
                    url: "{{route('materials.operations.transfer.new.validate-material-list')}}",
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        data: JSON.stringify(transferOperationData)
                    },

                    success: function (data, textStatus, jqXHR) {
                        postEditingData(transferOperationData)
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        if (forcePostData) {
                            postEditingData(transferOperationData)
                        }
                        DevExpress.ui.notify("При сохранении данных произошла ошибка<br>Список ошибок", "error", 5000)
                    }
                })
            }

            function postEditingData(transferOperationData) {
                $.ajax({
                    url: "{{route('materials.operations.transfer.new')}}",
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
                            window.location.href = '{{route('materials.index')}}/?project_object=' + sourceProjectObjectId
                        }
                        if (transferOperationInitiator === "destination") {
                            window.location.href = '{{route('materials.index')}}/?project_object=' + destinationProjectObjectId
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        DevExpress.ui.notify("При сохранении данных произошли ошибки - список ошибок", "error", 5000)
                    }
                })
            }
        });

    </script>
@endsection
