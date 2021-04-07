@extends('layouts.app')

@section('title', 'Перемещение ('.$operationRouteStage.')')

@section('url', "#")

@section('css_top')
    <style>
        .initial-content {
            float: left;
            text-align: left;
            line-height: 20px;
            margin: -4px;
            padding: 2px 4px;
            border-radius: 2px;
        }

        .initial-content.equal {
            background: #c6efce;
            color: #006100;
        }

        .initial-content.equal.deleted {
            background: lightgray;
            color: white;
        }

        .initial-content.equal.deleted {
            background: lightgray;
            color: white;
        }

        .initial-content.negative {
            background: #ffc7ce;
            color: #9c0006;
        }

        .initial-content.negative.deleted {
            background: lightgray;
            color: white;
        }

        .amount-cell-content {
            float: right;
            min-width: 50%;
        }

        .quantity-cell-content {
            float: right;
            min-width: 50%;
        }

        .dx-link.dx-icon-add.dx-datagrid {
            color: #006100;
        }

        .dx-link.dx-icon-revert.deleted {
            color: lightblue;
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
    <div id="formContainer"></div>
    <div id="popupContainer">
        <div id="materialsStandardsAddingForm"></div>
    </div>
    <div id="validationPopoverContainer">
        <div id="validationTemplate" data-options="dxTemplate: { name: 'validationTemplate' }">

        </div>
    </div>
@endsection

@section('js_footer')
    <script>
        $(function () {
            //<editor-fold desc="JS: DataSources">
            let operationData = {!! $operationData !!};

            let sourceProjectObjectId = {{$sourceProjectObjectId}};
            let destinationProjectObjectId = {{$destinationProjectObjectId}};
            let transferOperationInitiator = "{{$transferOperationInitiator}}"; //one of "none", "source", "destination"
            let materialErrorList = [];

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
                loadMode: "raw",
                load: function (loadOptions) {
                    return $.getJSON("{{route('material.types.lookup-list')}}",
                        {data: JSON.stringify({dxLoadOptions: loadOptions})});
                },
            });

            let materialTypesDataSource = new DevExpress.data.DataSource({
                key: "id",
                store: materialTypesStore
            })

            let materialStandardsStore = new DevExpress.data.CustomStore({
                key: "id",
                loadMode: "processed",
                load: function (loadOptions) {
                    return $.getJSON("{{route('materials.standards.list')}}"/*,
                        {data: JSON.stringify(loadOptions)}*/);
                },
            });


            let availableMaterialsStore = new DevExpress.data.CustomStore({
                key: "id",
                loadMode: "raw",
                load: function (loadOptions) {
                    return $.getJSON("{{route('materials.actual.list')}}",
                        {project_object: sourceProjectObjectId});
                },
            });

            let availableMaterialsDataSource = new DevExpress.data.DataSource({
                key: "id",
                store: availableMaterialsStore
            });

            let allMaterialsWithActualAmountStore = new DevExpress.data.CustomStore({
                key: "id",
                loadMode: "raw",
                load: function (loadOptions) {
                    return $.getJSON("{{route('materials.all-with-actual-amount.list')}}",
                        {project_object: sourceProjectObjectId});
                },
            });

            let allMaterialsWithActualAmountDataSource = new DevExpress.data.DataSource({
                key: "id",
                store: allMaterialsWithActualAmountStore
            })

            let selectedMaterialStandardsListDataSource = new DevExpress.data.DataSource({
                store: new DevExpress.data.ArrayStore({
                    key: "id",
                    data: []
                })
            })

            let transferMaterialData = {!! $operationMaterials !!};
            let transferMaterialStore = new DevExpress.data.ArrayStore({
                key: "id",
                data: transferMaterialData,
                onLoaded: validateMaterialList(null)
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
            @if(in_array($routeStageId, [6, 25]) && ($allowEditing || $allowCancelling))
            let applyDataButtonGroup =
                {
                    itemType: "simpleItem",
                    colSpan: 2,
                    template: function (data, itemElement) {
                        @if($allowCancelling)
                        $('<div>')
                            .css('float', 'left')
                            .dxButton({
                                text: "Отменить операцию",
                                type: "danger",
                                stylingMode: "contained",
                                useSubmitBehavior: false,

                                onClick: function (e) {
                                    let confirmDialog = DevExpress.ui.dialog.confirm('Вы действительно хотите отменить операцию?', 'Отмена операции');
                                    confirmDialog.done(function (dialogResult) {
                                        if (dialogResult) {
                                            cancelOperation()
                                        } else {
                                            return;
                                        }
                                    })
                                }
                            })
                            .appendTo(itemElement)
                        @endIf
                        @if($allowEditing)
                        $('<div>')
                            .css('float', 'right')
                            .css('margin-right', '8px')
                            .dxButton({
                                text: "Подтвердить",
                                type: "default",
                                stylingMode: "contained",
                                useSubmitBehavior: false,

                                onClick: function (e) {
                                    let result = e.validationGroup.validate();
                                    if (!result.isValid) {
                                        return;
                                    }
                                    let comment = operationForm.option("formData").new_comment;
                                    if (!comment) {
                                        let confirmDialog = DevExpress.ui.dialog.confirm('Вы не заполнили поле "Комментарий".<br>Продолжить без заполнения?', 'Комметарий не заполнен');
                                        confirmDialog.done(function (dialogResult) {
                                            if (dialogResult) {
                                                saveOperationData("");
                                            } else {
                                                return;
                                            }
                                        })
                                    } else {
                                        saveOperationData("");
                                    }
                                }

                            })
                            .appendTo(itemElement)
                        @endIf
                    }
                }
            @endIf

            @if(in_array($routeStageId, [11, 30]) && ($allowEditing || $allowCancelling))
            let applyConflictButtonGroup = {
                itemType: "simpleItem",
                colSpan: 2,
                template: function (data, itemElement) {
                    @if($allowCancelling)
                    $('<div>')
                        .css('float', 'left')
                        .dxButton({
                            text: "Отменить операцию",
                            type: "danger",
                            stylingMode: "contained",
                            useSubmitBehavior: false,

                            onClick: function (e) {
                                let confirmDialog = DevExpress.ui.dialog.confirm('Вы действительно хотите отменить операцию?', 'Отмена операции');
                                confirmDialog.done(function (dialogResult) {
                                    if (dialogResult) {
                                        cancelOperation()
                                    } else {
                                        return;
                                    }
                                })
                            }
                        })
                        .appendTo(itemElement)
                    @endIf
                    @if ($allowEditing)
                    $('<div>')
                        .css('float', 'right')
                        .dxButton({
                            text: "Подтвердить изменения",
                            type: "default",
                            stylingMode: "contained",
                            useSubmitBehavior: false,

                            onClick: function (e) {
                                let result = e.validationGroup.validate();
                                if (!result.isValid) {
                                    return;
                                }

                                let comment = operationForm.option("formData").new_comment;
                                if (!comment) {
                                    let confirmDialog = DevExpress.ui.dialog.confirm('Вы не заполнили поле "Комментарий".<br>Продолжить без заполнения?', 'Комметарий не заполнен');
                                    confirmDialog.done(function (dialogResult) {
                                        if (dialogResult) {
                                            saveOperationData("forceComplete");
                                        }
                                    })
                                } else {
                                    saveOperationData("forceComplete");
                                }
                            }

                        })
                        .appendTo(itemElement)
                    $('<div>')
                        .css('float', 'right')
                        .css('margin-right', '8px')
                        .dxButton({

                            text: "Отправить руководителю",
                            type: "default",
                            stylingMode: "outlined",
                            useSubmitBehavior: false,

                            onClick: function (e) {
                                let result = e.validationGroup.validate();
                                if (!result.isValid) {
                                    return;
                                }

                                let comment = operationForm.option("formData").new_comment;
                                if (!comment) {
                                    let confirmDialog = DevExpress.ui.dialog.confirm('Вы не заполнили поле "Комментарий".<br>Продолжить без заполнения?', 'Комметарий не заполнен');
                                    confirmDialog.done(function (dialogResult) {
                                        if (dialogResult) {
                                            saveOperationData("moveToResponsibilityUser");
                                        }
                                    })
                                } else {
                                    saveOperationData("moveToResponsibilityUser");
                                }
                            }

                        })
                        .appendTo(itemElement)
                    @endIf
                }
            }
            @endif

            @if(in_array($routeStageId, [19, 38]) && ($allowEditing || $allowCancelling))
            let applyConflictByResponsibilityUserButtonGroup = {
                itemType: "simpleItem",
                colSpan: 2,
                template: function (data, itemElement) {
                    @if($allowCancelling)
                    $('<div>')
                        .css('float', 'left')
                        .dxButton({
                            text: "Отменить операцию",
                            type: "danger",
                            stylingMode: "contained",
                            useSubmitBehavior: false,

                            onClick: function (e) {
                                let confirmDialog = DevExpress.ui.dialog.confirm('Вы действительно хотите отменить операцию?', 'Отмена операции');
                                confirmDialog.done(function (dialogResult) {
                                    if (dialogResult) {
                                        cancelOperation()
                                    } else {
                                        return;
                                    }
                                })
                            }
                        })
                        .appendTo(itemElement)
                    @endIf
                    @if ($allowEditing)
                    $('<div>')
                        .css('float', 'right')
                        .dxButton({
                            text: "Подтвердить изменения",
                            type: "default",
                            stylingMode: "contained",
                            useSubmitBehavior: false,

                            onClick: function (e) {
                                let result = e.validationGroup.validate();
                                if (!result.isValid) {
                                    return;
                                }

                                let comment = operationForm.option("formData").new_comment;
                                if (!comment) {
                                    let confirmDialog = DevExpress.ui.dialog.confirm('Вы не заполнили поле "Комментарий".<br>Продолжить без заполнения?', 'Комметарий не заполнен');
                                    confirmDialog.done(function (dialogResult) {
                                        if (dialogResult) {
                                            saveOperationData("forceComplete");
                                        }
                                    })
                                } else {
                                    saveOperationData("forceComplete");
                                }
                            }
                        })
                        .appendTo(itemElement)
                    @endIf
                }
            };
            @endIf
            let materialsStandardsAddingForm = $("#materialsStandardsAddingForm").dxForm({
                colCount: 2,
                items: [{
                    itemType: "group",
                    colCount: 3,
                    caption: "Материалы",
                    items: [{
                        editorType: "dxDataGrid",
                        name: "materialsStandardsList",
                        editorOptions: {
                            dataSource: availableMaterialsDataSource,
                            height: 400,
                            width: 500,
                            showColumnHeaders: false,
                            showRowLines: false,
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
                            paging: {
                                enabled: true
                            },
                            searchPanel: {
                                visible: true,
                                searchVisibleColumnsOnly: true,
                                width: 240,
                                placeholder: "Поиск..."
                            },
                            columns: [{
                                dataField: "standard_name",
                                dataType: "string",
                                caption: "Наименование",
                                sortIndex: 0,
                                sortOrder: "asc",
                                calculateFilterExpression: function (filterValue, selectedFilterOperation, target) {
                                    if (target === "search") {
                                        let words = filterValue.split(" ");
                                        let filter = [];
                                        words.forEach(function (word) {
                                            filter.push(["standard_name", "contains", word]);
                                            filter.push("and");
                                        });
                                        filter.pop();
                                        return filter;
                                    }
                                    return this.defaultCalculateFilterExpression(filterValue, selectedFilterOperation);
                                },
                                cellTemplate: function (container, options) {
                                    if (transferOperationInitiator === "destination") {
                                        quantity = "";
                                        amount = "";
                                    } else {
                                        quantity = options.data.quantity ? options.data.quantity + " " : "";
                                        amount = options.data.amount ? options.data.amount + " " : "";
                                    }
                                    switch (options.data.accounting_type) {
                                        case 2:
                                            return $("<div>").text(options.data.standard_name +
                                                ' (' +
                                                quantity +
                                                options.data.measure_unit_value +
                                                '; ' +
                                                amount +
                                                'шт)'
                                            )
                                        default:
                                            return $("<div>").text(options.data.standard_name +
                                                ' (' +
                                                quantity +
                                                options.data.measure_unit_value +
                                                ')')
                                    }
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
                                }],
                            onSelectionChanged: function (e) {
                                selectedMaterialStandardsListDataSource.store().clear();
                                e.selectedRowsData.forEach(function (selectedRowItem) {
                                    selectedMaterialStandardsListDataSource.store().insert(selectedRowItem)
                                })

                                selectedMaterialStandardsListDataSource.reload();
                            },
                            onToolbarPreparing: function (e) {
                                let dataGrid = e.component;

                                e.toolbarOptions.items.unshift(
                                    {
                                        location: "before",
                                        template: function () {
                                            return $("<div/>")
                                                .dxCheckBox({
                                                    value: false,
                                                    width: "auto",
                                                    text: "Показать все материалы",
                                                    rtlEnabled: true,
                                                    onValueChanged: (e) => {
                                                        if (e.value) {
                                                            dataGrid.option("dataSource", allMaterialsWithActualAmountDataSource)
                                                            allMaterialsWithActualAmountDataSource.reload();
                                                        } else {
                                                            dataGrid.option("dataSource", availableMaterialsDataSource)
                                                            availableMaterialsDataSource.reload();
                                                        }
                                                    }

                                                });
                                        }
                                    });
                            }
                        }
                    }]
                },
                    {
                        itemType: "group",
                        colCount: 3,
                        caption: "Выбранные материалы",
                        items: [{
                            editorType: "dxList",
                            name: "selectedMaterialsList",
                            editorOptions: {
                                dataSource: selectedMaterialStandardsListDataSource,
                                allowItemDeleting: true,
                                itemDeleteMode: "static",
                                height: 400,
                                width: 500,
                                itemTemplate: function (data) {
                                    let quantity = data.quantity ? data.quantity + " " : "";
                                    let amount = data.amount ? data.amount + " " : "";
                                    switch (data.accounting_type) {
                                        case 2:
                                            return $("<div>").text(data.standard_name +
                                                ' (' +
                                                quantity +
                                                data.measure_unit_value +
                                                '; ' +
                                                amount +
                                                'шт)'
                                            )
                                        default:
                                            return $("<div>").text(data.standard_name +
                                                ' (' +
                                                quantity +
                                                data.measure_unit_value +
                                                ')')
                                    }
                                },
                                onItemDeleted: function (e) {
                                    let materialsStandardsList = materialsStandardsAddingForm.getEditor("materialsStandardsList");
                                    let selectedMaterialsList = materialsStandardsAddingForm.getEditor("selectedMaterialsList");
                                    let selectedRowsKeys = [];
                                    selectedMaterialsList.option("items").forEach(function (selectedItem) {
                                        selectedRowsKeys.push(selectedItem.id);
                                    });

                                    materialsStandardsList.option("selectedRowKeys", selectedRowsKeys);
                                }
                            }
                        }]
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
                                    let quantity;

                                    switch (material.accounting_type) {
                                        case 2:
                                            quantity = material.quantity;
                                            break;
                                        default:
                                            quantity = null;
                                    }

                                    transferMaterialDataSource.store().insert({
                                        id: new DevExpress.data.Guid().toString(),
                                        standard_id: material.standard_id,
                                        standard_name: material.standard_name,
                                        accounting_type: material.accounting_type,
                                        material_type: material.material_type,
                                        measure_unit: material.measure_unit,
                                        measure_unit_value: material.measure_unit_value,
                                        standard_weight: material.weight,
                                        quantity: quantity,
                                        amount: null,
                                        total_quantity: material.quantity,
                                        total_amount: material.amount,
                                        edit_states: ["addedByRecipient"]
                                    })
                                })
                                transferMaterialDataSource.reload();
                                $("#popupContainer").dxPopup("hide");
                                validateMaterialList(null);
                            }
                        }
                    }
                ]
            }).dxForm("instance");

            let popupContainer = $("#popupContainer").dxPopup({
                height: "auto",
                width: "auto",
                title: "Выберите материалы для добавления"
            });

            //<editor-fold desc="JS: Columns definition">
            let transferMaterialColumns = [
                {
                    type: "buttons",
                    width: 110,
                    buttons: [
                        {
                            template: function (container, options) {
                                let validationId = "0";
                                let standardId = "";
                                let quantity = "";

                                if (options.data.quantity) {
                                    quantity = options.data.quantity;
                                }

                                if (options.data.standard_id) {
                                    standardId = options.data.standard_id;
                                }

                                switch (options.data.accounting_type) {
                                    case 2:
                                        validationId = standardId + "-" + quantity
                                        break;
                                    default:
                                        validationId = standardId;
                                }


                                let exclamationTriangle = $("<a>")
                                    .attr("href", "#")
                                    .attr("validationId", validationId)
                                    .attr("style", "display: none")
                                    .addClass("dx-link dx-icon fas fa-exclamation-triangle dx-link-icon");

                                materialErrorList.forEach((errorElement) => {
                                    if (errorElement.validationId === validationId) {
                                        updateValidationExclamationTriangles(exclamationTriangle, errorElement);
                                    }
                                })

                                return exclamationTriangle;
                            }
                        },
                            @if ($allowEditing)
                        {
                            icon: "fas fa-plus",
                            visible: (e) => {
                                return e.row.data.edit_states.indexOf("addedByRecipient") !== -1
                            }
                        },

                        {
                            hint: "Отменить удаление",
                            icon: "dx-icon-revert deleted",
                            visible: (e) => {
                                if (e.row.data.edit_states.indexOf("deletedByRecipient") !== -1) {
                                    let rowElement = e.row.cells[0].cellElement.parent();
                                    rowElement.css("color", "lightgrey");
                                    return true
                                } else {
                                    return false
                                }

                            },
                            onClick: (e) => {
                                e.row.data.edit_states.splice(e.row.data.edit_states.indexOf("deletedByRecipient"), 1);
                                e.component.repaintRows(e.row.rowIndex);
                                e.component.refresh(true);
                                validateMaterialList(e.row);
                            }
                        },
                        {
                            hint: "Удалить",
                            icon: "trash",
                            visible: (e) => {
                                return e.row.data.edit_states.indexOf("deletedByRecipient") === -1
                            },
                            onClick: (e) => {
                                if (e.row.data.edit_states.indexOf("addedByInitiator") === -1) {
                                    e.component.deleteRow(e.row.rowIndex);
                                } else {
                                    e.row.data.edit_states.push("deletedByRecipient");
                                    e.component.repaintRows(e.row.rowIndex);
                                }
                                ;
                                e.component.refresh(true);
                                validateMaterialList(e.row);
                            }
                        },
                        {
                            hint: "Дублировать",
                            icon: "copy",
                            onClick: function (e) {
                                let clonedItem = $.extend({}, e.row.data, {
                                    id: new DevExpress.data.Guid().toString(),
                                    edit_states: ["addedByRecipient"]
                                });
                                transferMaterialDataSource.store().insert(clonedItem);
                                e.component.refresh(true);
                                validateMaterialList(e.row);
                                e.event.preventDefault();
                            }
                        }
                        @endif
                    ]
                },
                {
                    dataField: "standard_id",
                    dataType: "string",
                    allowEditing: false,
                    width: "30%",
                    caption: "Наименование",
                    sortIndex: 0,
                    sortOrder: "asc",
                    lookup: {
                        dataSource: {store: materialStandardsStore},
                        displayExpr: "name",
                        valueExpr: "id"
                    },
                    cellTemplate: function (container, options) {
                        if (options.data.edit_states.indexOf("deletedByRecipient") !== -1 || options.data.total_amount === null) {
                            $(`<div>${options.text}</div>`)
                                .appendTo(container);
                        } else {
                            $(`<div class="standard-name">${options.text}</div><div class="standard-remains" standard-id="${options.data.standard_id}" standard-quantity="${options.data.quantity}" accounting-type="${options.data.accounting_type}"></div>`)
                                .appendTo(container);
                        }

                        recalculateStandardsRemains(options.data.id);
                    },
                },
                {
                    dataField: "quantity",
                    dataType: "number",
                    caption: "Количество",
                    editorOptions: {
                        min: 0
                    },
                    showSpinButtons: false,
                    cellTemplate: function (container, options) {
                        let initialQuantity = options.data.initial_quantity;
                        let quantity = options.data.quantity;
                        if (options.data.edit_states.indexOf("addedByInitiator") !== -1 && options.data.accounting_type !== 2) {
                            let quantityDelta = quantity - initialQuantity;
                            let initialQuantityContentStyle = "initial-content";

                            if (quantityDelta === 0) {
                                quantityDelta = '='
                                initialQuantityContentStyle = initialQuantityContentStyle + " equal"
                            } else {
                                initialQuantityContentStyle = initialQuantityContentStyle + " negative"
                            }

                            if (options.data.edit_states.indexOf("deletedByRecipient") !== -1)
                                initialQuantityContentStyle = initialQuantityContentStyle + " deleted";

                            if (quantityDelta > 0) {
                                quantityDelta = '+' + quantityDelta
                            }

                            if (quantity !== null) {
                                $(`<div class="${initialQuantityContentStyle}">${initialQuantity} [${quantityDelta}]</div><div class="quantity-cell-content">${quantity} ${options.data.measure_unit_value}</div>`)
                                    .appendTo(container);
                            } else {
                                $(`<div class="measure-units-only">${options.data.measure_unit_value}</div>`)
                                    .appendTo(container);
                            }
                        } else {
                            if (quantity !== null) {
                                $(`<div class="quantity-cell-content">${quantity} ${options.data.measure_unit_value}</div>`)
                                    .appendTo(container);
                            } else {
                                $(`<div class="measure-units-only">шт</div>`)
                                    .appendTo(container);
                            }
                        }
                    }
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
                        let initialAmount = options.data.initial_amount;
                        let amount = options.data.amount;
                        if (options.data.edit_states.indexOf("addedByInitiator") !== -1) {
                            let amountDelta = amount - initialAmount;
                            let initialAmountContentStyle = "initial-content";

                            if (amountDelta === 0) {
                                amountDelta = '='
                                initialAmountContentStyle = initialAmountContentStyle + " equal"
                            } else {
                                initialAmountContentStyle = initialAmountContentStyle + " negative"
                            }

                            if (options.data.edit_states.indexOf("deletedByRecipient") !== -1) {
                                initialAmountContentStyle = initialAmountContentStyle + " deleted"
                            }

                            if (amountDelta > 0) {
                                amountDelta = '+' + amountDelta
                            }

                            if (amount !== null) {
                                $(`<div class="${initialAmountContentStyle}">${initialAmount} [${amountDelta}]</div><div class="amount-cell-content">${amount} шт</div>`)
                                    .appendTo(container);
                            } else {
                                $(`<div class="measure-units-only">шт</div>`)
                                    .appendTo(container);
                            }
                        } else {
                            if (amount !== null) {
                                $(`<div class="amount-cell-content">${amount} шт</div>`)
                                    .appendTo(container);
                            } else {
                                $(`<div class="measure-units-only">шт</div>`)
                                    .appendTo(container);
                            }
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
                            name: "totalAmountGroupSummary",
                            showInColumn: "amount",
                            summaryType: "custom",
                            showInGroupFooter: false,
                            alignByColumn: true
                        },
                        {
                            name: "totalWeightGroupSummary",
                            showInColumn: "computed_weight",
                            summaryType: "custom",
                            showInGroupFooter: false,
                            alignByColumn: true
                        }
                    ],
                    totalItems: [{
                        name: "totalWeightSummary",
                        showInColumn: "computed_weight",
                        summaryType: "custom"
                    }],
                    calculateCustomSummary: function (options) {
                        if (options.name === "totalWeightSummary" || options.name === "totalWeightGroupSummary") {
                            if (options.summaryProcess === "start") {
                                options.totalValue = 0;
                            }

                            if (options.summaryProcess === "calculate") {
                                console.log(options.value);
                                if (options.value.edit_states.indexOf("deletedByRecipient") === -1) {
                                    options.totalValue = options.totalValue + (options.value.amount * options.value.quantity * options.value.standard_weight);
                                }
                            }

                            if (options.summaryProcess === "finalize") {
                                if (options.name === "totalWeightSummary") {
                                    options.totalValue = "Итого: " + options.totalValue.toFixed(3) + " т."
                                } else {
                                    options.totalValue = "Всего: " + options.totalValue.toFixed(3) + " т."
                                }


                            }
                        }

                        if (options.name === "totalAmountGroupSummary") {
                            if (options.summaryProcess === "start") {
                                options.totalValue = 0;
                            }

                            if (options.summaryProcess === "calculate") {
                                console.log(options.value);
                                if (options.value.edit_states.indexOf("deletedByRecipient") === -1) {
                                    options.totalValue = options.totalValue + options.value.amount;
                                }
                            }

                            if (options.summaryProcess === "finalize") {
                                options.totalValue = "Всего: " + options.totalValue + " шт"
                            }
                        }
                    }
                },
                onEditorPreparing: (e) => {
                    if (e.dataField === "quantity" && e.parentType === "dataRow") {
                        if (e.row.data.accounting_type === 2) {
                            e.cancel = true;
                        }
                    }
                },
                @if($allowEditing)
                onToolbarPreparing: (e) => {
                    let dataGrid = e.component;
                    e.toolbarOptions.items.unshift(
                        {
                            location: "before",
                            widget: "dxButton",
                            options: {
                                icon: "add",
                                text: "Добавить",
                                onClick: function (e) {
                                    selectedMaterialStandardsListDataSource.store().clear();

                                    let materialsStandardsList = materialsStandardsAddingForm.getEditor("materialsStandardsList");
                                    materialsStandardsList.option("selectedRowKeys", []);

                                    $("#popupContainer").dxPopup("show")
                                }
                            }
                        }
                    );
                },
                @endif
                onEditorPreparing: (e) => {
                    if (e.dataField === "quantity" && e.parentType === "dataRow") {
                        if (e.row.data.edit_states.indexOf("addedByInitiator") !== -1) {
                            if (e.row.data.accounting_type === 2) {
                                e.cancel = true
                            }
                        }
                    }
                },
                onRowUpdating: (e) => {
                    if (e.oldData.edit_states.indexOf("editedByRecipient") === -1) {
                        e.newData.edit_states = e.oldData.edit_states;
                        e.newData.edit_states.push("editedByRecipient");
                    }

                    console.log(e.newData);

                    if (e.oldData.accounting_type === 2) {
                        if (e.newData.quantity !== undefined) {
                            e.newData.total_amount = null;
                            availableMaterialsDataSource.items().forEach((item) => {
                                console.log(item.items[0].quantity);
                                console.log(e.newData.quantity);
                                if (item.items[0].standard_id === e.oldData.standard_id && item.items[0].quantity === e.newData.quantity) {
                                    e.newData.total_amount = item.items[0].amount;
                                }
                            });
                        }
                    }
                },
                onRowUpdated: (e) => {
                    //recalculateStandardsRemains(e.key);
                    validateMaterialList(e);
                },
            };
            //</editor-fold>

            //<editor-fold desc="JS: Edit form configuration">
            let operationForm = $("#formContainer").dxForm({
                formData: operationData,
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
                        readOnly: true,
                        editorOptions: {
                            readOnly: true,
                            dataSource: {
                                store: projectObjectStore
                            },
                            displayExpr: "short_name",
                            valueExpr: "id",
                            searchEnabled: true
                        }
                    },
                        {
                            dataField: "operation_date",
                            readOnly: true,
                            colSpan: 1,
                            label: {
                                text: "Дата отправления"
                            },
                            editorType: "dxDateBox",
                            editorOptions: {
                                readOnly: true,
                            }
                        },
                        {
                            colSpan: (e) => {
                                if (transferOperationInitiator === "none" || transferOperationInitiator === "source") {
                                    return 2
                                }
                                if (transferOperationInitiator === "destination") {
                                    return 3
                                }
                            },
                            dataField: "source_responsible_user_id",
                            label: {
                                text: "Ответственный"
                            },
                            editorType: "dxSelectBox",
                            editorOptions: {
                                readOnly: true,
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
                            readOnly: true,
                            dataSource: {
                                store: projectObjectStore
                            },
                            displayExpr: "short_name",
                            valueExpr: "id",
                            searchEnabled: true
                        }
                    },
                        {
                            colSpan: (e) => {
                                if (transferOperationInitiator === "none" || transferOperationInitiator === "source") {
                                    return 2
                                }
                                if (transferOperationInitiator === "destination") {
                                    return 1
                                }
                            },
                            dataField: "destination_responsible_user_id",
                            label: {
                                text: "Ответственный"
                            },
                            editorType: "dxSelectBox",
                            editorOptions: {
                                readOnly: true,
                                dataSource: {
                                    store: usersStore
                                },
                                displayExpr: "full_name",
                                valueExpr: "id",
                                searchEnabled: true
                            }
                        },
                        {
                            colSpan: 1,
                            dataField: "consignment_note_number",
                            label: {
                                text: "Номер ТТН"
                            },
                            editorType: "dxNumberBox",
                            editorOptions: {
                                readOnly: true,
                                min: 0,
                                format: "000000",
                                showSpinButtons: false
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
                        itemType: "group",
                        caption: "Комментрий",
                        colSpan: 2,
                        items: [{
                            dataField: "new_comment",
                            label: {
                                text: "Новый комментарий",
                                visible: false
                            },
                            editorType: "dxTextArea",
                            @if (in_array($routeStageId, [11, 19, 30, 38]))
                            validationRules: [{
                                type: "required",
                                message: 'Поле "Комментарий" обязательно для заполнения'
                            }]
                            @endif
                        }
                        ]
                    },
                    {
                        itemType: "group",
                        caption: "Файлы",
                        colSpan: 2,
                        colCount: 4,
                        items: [{
                            colSpan: 1,
                            template:
                                '<div id="dropzone-external-1" class="dx-uploader-flex-box dx-theme-border-color dropzone-external">' +
                                '<img id="dropzone-image-1" class="dropzone-image" src="#" hidden alt="" />' +
                                '<div id="dropzone-text-1" class="dx-uploader-flex-box dropzone-text">' +
                                '<span class="dx-uploader-span">Фото ТТН</span>' +
                                '</div>' +
                                '<div id="upload-progress-1" class="upload-progress"></div>' +
                                '</div>' +
                                '<div class="file-uploader" purpose="consignment-note-photo" index="1"></div>'
                        },
                            {
                                colSpan: 1,
                                template: '<div id="dropzone-external-2" class="dx-uploader-flex-box dx-theme-border-color dropzone-external">' +
                                    '<img id="dropzone-image-2" class="dropzone-image" src="#" hidden alt="" />' +
                                    '<div id="dropzone-text-2" class="dx-uploader-flex-box dropzone-text">' +
                                    '<span class="dx-uploader-span">Фото машины спереди</span>' +
                                    '</div>' +
                                    '<div id="upload-progress-2" class="upload-progress"></div>' +
                                    '</div>' +
                                    '<div class="file-uploader" purpose="frontal-vehicle-photo" index="2"></div>'
                            },
                            {
                                colSpan: 1,
                                template: '<div id="dropzone-external-3" class="dx-uploader-flex-box dx-theme-border-color dropzone-external">' +
                                    '<img id="dropzone-image-3" class="dropzone-image" src="#" hidden alt="" />' +
                                    '<div id="dropzone-text-3" class="dx-uploader-flex-box dropzone-text">' +
                                    '<span class="dx-uploader-span">Фото машины сзади</span>' +
                                    '</div>' +
                                    '<div id="upload-progress-3" class="upload-progress"></div>' +
                                    '</div>' +
                                    '<div class="file-uploader" purpose="behind-vehicle-photo" index="3"></div>'
                            },
                            {
                                colSpan: 1,
                                template: '<div id="dropzone-external-4" class="dx-uploader-flex-box dx-theme-border-color dropzone-external">' +
                                    '<img id="dropzone-image-4" class="dropzone-image" src="#" hidden alt="" />' +
                                    '<div id="dropzone-text-4" class="dx-uploader-flex-box dropzone-text">' +
                                    '<span class="dx-uploader-span">Фото материалов</span>' +
                                    '</div>' +
                                    '<div id="upload-progress-4" class="upload-progress"></div>' +
                                    '</div>' +
                                    '<div class="file-uploader" purpose="materials-photo" index="4"></div>'
                            }
                        ]
                    },
                    @endif
                        @if(in_array($routeStageId, [6, 25]) && ($allowEditing || $allowCancelling))
                        applyDataButtonGroup,
                    @endif
                        @if(in_array($routeStageId, [11, 30]) && ($allowEditing || $allowCancelling))
                        applyConflictButtonGroup,
                    @endif
                        @if(in_array($routeStageId, [19, 38]) && ($allowEditing || $allowCancelling))
                        applyConflictByResponsibilityUserButtonGroup,
                    @endif
                ]

            }).dxForm("instance")
            //</editor-fold>

            //<editor-fold desc="JS: Toolbar configuration">
            //</editor-fold>

            function saveOperationData(userAction) {
                let transferOperationData = {};
                transferOperationData.operationId = operationData.id;
                transferOperationData.new_comment = operationForm.option("formData").new_comment;

                @if($allowEditing && in_array($routeStageId, [11, 19, 30, 38]))
                    transferOperationData.userAction = userAction;
                @endif

                let uploadedFiles = []
                $(".file-uploader").each(function () {
                    if ($(this).attr("uploaded-file-id") !== undefined) {
                        uploadedFiles.push($(this).attr("uploaded-file-id"));
                    }
                });

                transferOperationData.uploaded_files = uploadedFiles;
                transferOperationData.materials = transferMaterialDataSource.store().createQuery().toArray();

                console.log(transferOperationData);
                //validateMaterialList(transferOperationData);
                postEditingData(transferOperationData);
            }

            @if ($allowCancelling)
            function cancelOperation() {
                let cancelledOperationData = {};
                cancelledOperationData.operationId = operationData.id;
                cancelledOperationData.new_comment = operationForm.option("formData").new_comment;

                $.ajax({
                    url: "{{route('materials.operations.transfer.cancel')}}",
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        data: JSON.stringify(cancelledOperationData),
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
                        DevExpress.ui.notify("При отмене операции произошла ошибка", "error", 5000)
                    }
                })
            }
            @endif

            function validateMaterialList(updateInfo) {
                $.ajax({
                    dataType: "json",
                    url: "{{route('materials.operations.transfer.validate-material-list')}}",
                    data: {
                        operationId: operationData.id,
                        sourceProjectObjectId: operationData.source_project_object_id,
                        materials: transferMaterialDataSource.store().createQuery().toArray()
                    },
                    success: (e) => {
                        $('.fa-exclamation-triangle').attr('style', 'display:none');
                    },
                    error: (e) => {
                        if (e.responseJSON.result === 'error') {
                            $('.fa-exclamation-triangle').attr('style', 'display:none');
                            e.responseJSON.errors.forEach((errorElement) => {
                                updateValidationExclamationTriangles($('[validationId=' + errorElement.validationId.toString().replaceAll('.', '\\.') + ']'), errorElement);
                            })
                            materialErrorList = e.responseJSON.errors;
                            /*updateInfo.data.errors = e.responseJSON.errors;
                            updateInfo.component.repaintRows(updateInfo.component.getRowIndexByKey(updateInfo.key));*/
                        }
                    }
                });
            }

            function updateValidationExclamationTriangles(element, errorElement) {
                let maxSeverity = 0;
                let errorDescription = "";
                let exclamationTriangleStyle = ""

                errorElement.errorList.forEach((errorItem) => {
                    if (errorItem.severity > maxSeverity) {
                        maxSeverity = errorItem.severity;
                    }

                    errorDescription = errorDescription + "<li>" + errorItem.message + "</li>"
                })

                switch (maxSeverity) {
                    case 500:
                        exclamationTriangleStyle = 'color: #ffd358';
                        break;
                    case 1000:
                        exclamationTriangleStyle = 'color: #f15a5a';
                        break;
                    default:
                        exclamationTriangleStyle = "display: none";
                }

                element.attr('style', exclamationTriangleStyle);
                element.attr('severity', maxSeverity);
                element.click(function (e) {
                    e.preventDefault();

                    let validationDescription = $('#validationTemplate');

                    validationDescription.dxPopover({
                        position: "top",
                        width: 300,
                        contentTemplate: "<ul>" + errorDescription + "</ul>"
                    })
                        .dxPopover("instance")
                        .show(e.target);

                    return false;
                });
            }

            function postEditingData(transferOperationData) {
                $.ajax({
                    url: "{{route('materials.operations.transfer.update')}}",
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
                        DevExpress.ui.notify("При сохранении данных произошла ошибка", "error", 5000)
                    }
                })
            }

            function recalculateStandardsRemains(editedRowKey) {
                transferMaterialStore.byKey(editedRowKey)
                    .done(function (dataItem) {
                        let calculatedQuantity = dataItem.total_quantity * dataItem.total_amount;
                        let calculatedAmount = dataItem.total_amount;

                        transferMaterialDataSource.store().createQuery().toArray().forEach((item) => {
                            if (item.standard_id === dataItem.standard_id && item.edit_states.indexOf("deletedByRecipient") === -1) {
                                switch (dataItem.accounting_type) {
                                    case 2:
                                        if (item.quantity === dataItem.quantity) {
                                            calculatedAmount = calculatedAmount - item.amount;
                                        }
                                        break;
                                    default:
                                        calculatedQuantity = calculatedQuantity - item.quantity * item.amount;
                                }
                            }
                        })

                        switch (dataItem.accounting_type) {
                            case 2:
                                $(`[accounting-type='${dataItem.accounting_type}'][standard-id='${dataItem.standard_id}'][standard-quantity='${dataItem.quantity}']`).each(function () {
                                    $(this).text(calculatedAmount + ' шт');
                                });
                                break;
                            default:
                                $(`[accounting-type='${dataItem.accounting_type}'][standard-id='${dataItem.standard_id}']`).each(function () {
                                    $(this).text(calculatedQuantity + ' ' + dataItem.measure_unit_value);
                                });
                        }

                    })
            }

            $(".file-uploader").each(function () {
                let uploaderIndex = $(this).attr('index');
                $(this).dxFileUploader({
                    dialogTrigger: "#dropzone-external-" + uploaderIndex,
                    dropZone: "#dropzone-external-" + uploaderIndex,
                    multiple: false,
                    allowedFileExtensions: [".jpg", ".jpeg", ".gif", ".png"],
                    uploadMode: "instantly",
                    uploadHeaders: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    uploadUrl: "{{route('materials.operations.upload-file')}}",
                    uploadCustomData: {uploadPurpose: $(this).attr('purpose')},
                    visible: false,
                    onDropZoneEnter: function (e) {
                        if (e.dropZoneElement.id === "dropzone-external-" + uploaderIndex)
                            toggleDropZoneActive(e.dropZoneElement, true);
                    },
                    onDropZoneLeave: function (e) {
                        if (e.dropZoneElement.id === "dropzone-external-" + uploaderIndex)
                            toggleDropZoneActive(e.dropZoneElement, false);
                    },
                    onUploaded: function (e) {
                        const file = e.file;
                        const dropZoneText = document.getElementById("dropzone-text-" + uploaderIndex);
                        const fileReader = new FileReader();
                        fileReader.onload = function () {
                            toggleDropZoneActive(document.getElementById("dropzone-external-" + uploaderIndex), false);
                            const dropZoneImage = document.getElementById("dropzone-image-" + uploaderIndex);
                            dropZoneImage.src = fileReader.result;
                        }
                        fileReader.readAsDataURL(file);
                        dropZoneText.style.display = "none";
                        uploadProgressBar.option({
                            visible: false,
                            value: 0
                        });

                        let fileId = JSON.parse(e.request.response).id;
                        e.element.attr('uploaded-file-id', fileId);
                    },
                    onProgress: function (e) {
                        uploadProgressBar.option("value", e.bytesLoaded / e.bytesTotal * 100)

                    },
                    onUploadStarted: function () {
                        toggleImageVisible(false);
                        uploadProgressBar.option("visible", true);
                    }
                });

                let uploadProgressBar = $("#upload-progress-" + uploaderIndex).dxProgressBar({
                    min: 0,
                    max: 100,
                    width: "30%",
                    showStatus: false,
                    visible: false
                }).dxProgressBar("instance");

                function toggleDropZoneActive(dropZone, isActive) {
                    if (isActive) {
                        dropZone.classList.add("dx-theme-accent-as-border-color");
                        dropZone.classList.remove("dx-theme-border-color");
                        dropZone.classList.add("dropzone-active");
                    } else {
                        dropZone.classList.remove("dx-theme-accent-as-border-color");
                        dropZone.classList.add("dx-theme-border-color");
                        dropZone.classList.remove("dropzone-active");
                    }
                }

                function toggleImageVisible(visible) {
                    const dropZoneImage = document.getElementById("dropzone-image-" + uploaderIndex);
                    dropZoneImage.hidden = !visible;
                }

                document.getElementById("dropzone-image-" + uploaderIndex).onload = function () {
                    toggleImageVisible(true);
                };
            });
        });

    </script>
@endsection
