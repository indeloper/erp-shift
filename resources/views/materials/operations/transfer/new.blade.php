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
    <div id="validationPopoverContainer">
        <div id="validationTemplate" data-options="dxTemplate: { name: 'validationTemplate' }">

        </div>
    </div>
    <div id="materialCommentPopoverContainer">
        <div id="materialCommentTemplate" data-options="dxTemplate: { name: 'materialCommentTemplate' }">
        </div>
    </div>
    <div id="commentPopupContainer">
        <div id="commentEditForm"></div>
    </div>
    <div id="standardRemainsPopoverContainer">
        <div id="standardRemainsTemplate" data-options="dxTemplate: { name: 'standardRemainsTemplate' }">
        </div>
    </div>
@endsection

@section('js_footer')
    <script>
        $(function () {
            let sourceProjectObjectId = {{$sourceProjectObjectId}};
            let destinationProjectObjectId = {{$destinationProjectObjectId}};
            let transferOperationInitiator = "{{$transferOperationInitiator}}"; //one of "none", "source", "destination"
            let materialErrorList = [];
            let suspendSourceObjectLookupValueChanged = false;
            let isTransferMaterialStoreBeenAlreadyLoaded = false;
            let commentData = null;

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
                loadMode: "raw",
                load: function (loadOptions) {
                    return $.getJSON("{{route('materials.standards.list')}}"/*,
                        {data: JSON.stringify(loadOptions)}*/);
                },
            });

            let materialStandardsDataSource = new DevExpress.data.DataSource({
                key: "id",
                store: materialStandardsStore
            });

            //<editor-fold desc="JS: DataSources">
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

            let transferMaterialData = {!!$predefinedMaterials !!};
            let transferMaterialStore = new DevExpress.data.ArrayStore({
                key: "id",
                data: transferMaterialData,
                onLoaded: () => {
                    if (!isTransferMaterialStoreBeenAlreadyLoaded) {
                        validateMaterialList(false, false);
                        isTransferMaterialStoreBeenAlreadyLoaded = true;
                    }
                }
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

            let materialCommentEditForm = $("#commentEditForm").dxForm({
                colCount: 1,
                items: [{
                    editorType: "dxTextArea",
                    name: "materialCommentTextArea",
                    editorOptions: {
                        width: 600,
                        height: 200
                    }
                },
                {
                    itemType: "button",
                    buttonOptions: {
                        text: "ОК",
                        type: "default",
                        stylingMode: "text",
                        useSubmitBehavior: false,
                        onClick: (e) => {
                            commentData.comment = materialCommentEditForm.getEditor("materialCommentTextArea").option("value");
                            $("#commentPopupContainer").dxPopup("hide");
                            getTransferMaterialGrid().refresh();
                        }
                    }
                }]
            }).dxForm("instance");

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
                            @if ($transferOperationInitiator == 'destination')
                            dataSource: allMaterialsWithActualAmountDataSource,
                            @else
                            dataSource: availableMaterialsDataSource,
                            @endif
                            height: () => {
                                return 400;
                            },
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
                                enabled: false
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

                                    let quantity;
                                    let amount;
                                    let comment;

                                    if (transferOperationInitiator === "destination") {
                                        quantity = "";
                                        amount = "";
                                    } else {
                                        quantity = options.data.quantity ? options.data.quantity + " " : "";
                                        amount = options.data.amount ? options.data.amount + " " : "";
                                    }
                                    switch (options.data.accounting_type) {
                                        case 2:
                                            let standardNameText = options.data.standard_name +
                                                ' (' +
                                                quantity +
                                                options.data.measure_unit_value +
                                                '/' +
                                                amount +
                                                'шт)';

                                            let divStandardName = $(`<div class="standard-name">${standardNameText}</div>`)
                                                .appendTo(container);

                                            if (options.data.comment) {
                                                let divMaterialComment = $(`<div class="material-comment">${options.data.comment}</div>`)
                                                    .appendTo(container);

                                            }

                                            container.addClass("standard-name-cell-with-comment");

                                            break;
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
                                                    visible: false, //transferOperationInitiator === "destination",
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
                                height: () => {
                                    return 400;
                                },
                                width: 500,
                                itemTemplate: function (data) {
                                    let quantity = data.quantity ? data.quantity + " " : "";
                                    let amount = data.amount ? data.amount + " " : "";

                                    switch (data.accounting_type) {
                                        case 2:
                                            let standardItem = $("<div>")
                                            let standardNameText = data.standard_name +
                                                ' (' +
                                                quantity +
                                                data.measure_unit_value +
                                                '/' +
                                                amount +
                                                'шт)';

                                            let divStandardName = $(`<div class="standard-name">${standardNameText}</div>`)
                                                .appendTo(standardItem);

                                            if (data.comment) {
                                                let divMaterialComment = $(`<div class="material-comment">${data.comment}</div>`)
                                                    .appendTo(standardItem);

                                            }

                                            return standardItem;
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
                                    let amount = null;

                                    switch (material.accounting_type) {
                                        case 2:
                                            quantity = material.quantity;
                                            break;
                                        default:
                                            quantity = null;
                                    }

                                    let validationUid = getValidationUid(material.standard_id, material.accounting_type, quantity, amount, material.comment_id);

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
                                        amount: amount,
                                        comment: null,
                                        initial_comment_id: material.comment_id,
                                        initial_comment: material.comment,
                                        total_quantity: material.quantity,
                                        total_amount: material.amount,
                                        validationUid: validationUid,
                                        validationState: "unvalidated",
                                        validationResult: "none"
                                    })

                                    validateMaterialList(false, false, validationUid);
                                });

                                transferMaterialDataSource.reload();
                                $("#popupContainer").dxPopup("hide");
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

            let materialCommentPopupContainer = $("#commentPopupContainer").dxPopup({
                height: "auto",
                width: "auto",
                title: "Введите комментарий"
            });

            //<editor-fold desc="JS: Columns definition">
            let transferMaterialColumns = [
                {
                    type: "buttons",
                    width: 110,
                    buttons: [
                        {
                            template: function (container, options) {
                                let validationUid = options.data.validationUid;
                                let validationDiv = $('<div class="row-validation-indicator"/>')
                                    .attr("validation-uid", validationUid)

                                updateRowsValidationState([options.data], options.data.validationState, options.data.validationResult, validationDiv);
                                return validationDiv;
                            }
                        },
                        {
                            hint: "Удалить",
                            icon: "trash",
                            onClick: (e) => {
                                e.component.deleteRow(e.row.rowIndex);
                            }
                        },
                        {
                            hint: "Дублировать",
                            icon: "copy",
                            onClick: function (e) {
                                let clonedItemId = new DevExpress.data.Guid().toString();

                                let clonedItem = $.extend({},
                                    e.row.data, {
                                        id: clonedItemId,
                                        validationUid: getValidationUid(e.row.data.standard_id, e.row.data.accounting_type, e.row.data.quantity, e.row.data.amount, e.row.data.initial_comment_id)
                                    }
                                );

                                transferMaterialDataSource.store().insert(clonedItem).done(() => {
                                    transferMaterialDataSource.reload();
                                    validateMaterialList(false, false, clonedItem.validationUid);

                                });

                                e.event.preventDefault();
                            }
                        },
                        {
                            hint: "Комментарии",
                            icon: "fas fa-message",

                            template: (container, options) => {
                                let accountingType;

                                if (options.data.accounting_type) {
                                    accountingType = options.data.accounting_type;
                                }

                                let commentIconClass = !options.data.comment ? "far fa-comment" : "fas fa-comment";

                                let commentLink;

                                switch (accountingType) {
                                    case 2:
                                        commentLink = $("<a>")
                                            .attr("href", "#")
                                            .attr("title", "Комментарий")
                                            .addClass("dx-link dx-icon " + commentIconClass + " dx-link-icon")
                                            .click(() => {
                                                commentData = options.data;
                                                if (commentData.comment) {
                                                    materialCommentEditForm.getEditor("materialCommentTextArea").option("value", commentData.comment);
                                                } else {
                                                    if (commentData.initial_comment) {
                                                        materialCommentEditForm.getEditor("materialCommentTextArea").option("value", commentData.initial_comment);
                                                    } else {
                                                        materialCommentEditForm.getEditor("materialCommentTextArea").option("value", "");
                                                    }
                                                }
                                                $("#commentPopupContainer").dxPopup("show");
                                            })
                                            .mouseenter(function () {
                                                if (!options.data.comment) {
                                                    return;
                                                }

                                                let materialCommentPopover = $('#materialCommentTemplate');
                                                materialCommentPopover.dxPopover({
                                                    position: "top",
                                                    width: 300,
                                                    contentTemplate: options.data.comment,
                                                    hideEvent: "mouseleave",
                                                })
                                                    .dxPopover("instance")
                                                    .show($(this));
                                            });
                                        break;
                                    default:
                                        return;
                                }
                                return commentLink;
                            }
                        }]
                },
                {
                    dataField: "standard_name",
                    dataType: "string",
                    allowEditing: false,
                    width: "30%",
                    caption: "Наименование",
                    sortIndex: 0,
                    sortOrder: "asc",
                    cellTemplate: function (container, options) {
                        if (options.data.total_amount === null) {
                            $(`<div>${options.text}</div>`)
                                .appendTo(container);
                        } else {
                            let divStandardName = $(`<div class="standard-name"></div>`)
                                .appendTo(container);

                            let divStandardText = $(`<div>${options.text}</div>`)
                                .appendTo(divStandardName);

                            if (options.data.initial_comment) {
                                $(`<div class="material-comment">${options.data.initial_comment}</div>`)
                                    .appendTo(divStandardName);

                                divStandardName.addClass("standard-name-cell-with-comment");
                            }

                            let divStandardRemains = $(`<div class="standard-remains" standard-id="${options.data.standard_id}" standard-quantity="${options.data.quantity}" accounting-type="${options.data.accounting_type}" initial-comment-id="${options.data.initial_comment_id}"></div>`)
                                .appendTo(container);

                            divStandardRemains.mouseenter(function () {
                                let standardRemainsPopover = $('#standardRemainsTemplate');
                                standardRemainsPopover.dxPopover({
                                        position: "top",
                                        width: 300,
                                        contentTemplate: "Остаток материала на объекте отправления",
                                        hideEvent: "mouseleave",
                                    })
                                .dxPopover("instance")
                                .show($(this));

                                return false;
                            });
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
                        let quantity = options.data.quantity;
                        if (quantity !== null) {
                            $(`<div>${quantity} ${options.data.measure_unit_value}</div>`)
                                .appendTo(container);
                        } else {
                            $(`<div class="measure-units-only">${options.data.measure_unit_value}</div>`)
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
                        } else {
                            $(`<div class="measure-units-only">шт</div>`)
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
                paging: {
                    enabled: false
                },
                editing: {
                    mode: "cell",
                    allowUpdating: true,
                    allowDeleting: true,
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
                        cssClass: "computed-weight-total-summary",
                        customizeText: function (data) {
                            return `Итого: ${data.value.toFixed(3)} т.`
                        }
                    }]
                },

                onEditorPreparing: (e) => {
                    if (e.dataField === "quantity" && e.parentType === "dataRow" && transferOperationInitiator === "source") {
                        if (e.row.data.accounting_type === 2) {
                            e.cancel = true;
                            e.editorElement.append($(`<div>${e.row.data.quantity} ${e.row.data.measure_unit_value}</div>`))
                        }
                    }
                },
                onRowUpdating: (e) => {
                    e.newData.validationUid = getValidationUid(e.oldData.standard_id, e.oldData.accounting_type, e.oldData.quantity, e.newData.amount, e.oldData.initial_comment_id);
                    e.newData.validationState = "unvalidated";
                    e.newData.validationResult = "none";
                },
                onRowUpdated: (e) => {
                    recalculateStandardsRemains(e.key);
                    validateMaterialList(false, false, e.data.validationUid);
                },
                onRowRemoved: (e) => {
                    validateMaterialList(false, false, e.data.validationUid);
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
                        name: "sourceProjectObjectSelectBox",
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
                                function updateComponentsDataSources(sourceProjectObjectIdValue) {
                                    sourceProjectObjectId = sourceProjectObjectIdValue;
                                    transferMaterialStore.clear();
                                    transferMaterialDataSource.reload();
                                    reloadMaterialsStandardsListDataSource();
                                }

                                function reloadMaterialsStandardsListDataSource () {
                                    let dataGrid = materialsStandardsAddingForm.getEditor("materialsStandardsList")
                                    dataGrid.option("dataSource").reload();
                                }

                                if (suspendSourceObjectLookupValueChanged) {
                                    suspendSourceObjectLookupValueChanged = false;
                                    return;
                                }

                                let oldValue = e.previousValue;
                                let currentValue = e.value;

                                if (getTransferMaterialGrid().getDataSource().items().length > 0 && e.previousValue !== null) {
                                    let confirmDialog = DevExpress.ui.dialog.confirm('При смене объекта отправления будут удалены введенные данные по материалам операции.<br>Продолжить?', 'Смена объекта отправления');
                                    confirmDialog.done(function (dialogResult) {
                                        if (dialogResult) {
                                            updateComponentsDataSources(currentValue);
                                        } else {
                                            suspendSourceObjectLookupValueChanged = true;
                                            e.component.option('value', oldValue);
                                        }
                                    });
                                } else {
                                    updateComponentsDataSources(currentValue);
                                }
                            }
                        },
                        validationRules: [{
                            type: "required",
                            message: 'Поле "Объект отправления" обязательно для заполнения'
                        }]
                    },
                        {
                            name: "operationDateStartDateBox",
                            dataField: "operation_date_start",
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
                            name: "sourceResponsibleUserSelectBox",
                            colSpan: transferOperationInitiator === "destination" ? 3 : 2,
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
                        name: "destinationProjectObjectSelectBox",
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
                            name: "operationDateEndDateBox",
                            dataField: "operation_date_end",
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
                            name: "destinationResponsibleUserSelectBox",
                            colSpan: transferOperationInitiator === "destination" ? 1 : 2,
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
                            name: "consignmentNoteNumberNumberBox",
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
                            },
                            {
                                type: "async",
                                message: 'Поле "Номер ТТН" должно быть уникальным',
                                validationCallback: function(params) {
                                    return validateConsignmentNumberUnique(params.value);
                                }
                            }]
                        }]
                },
                    {
                        itemType: "group",
                        caption: "Материалы",
                        cssClass: "materials-grid",
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
                        itemType: "group",
                        caption: "Комментарий",
                        colSpan: 2,
                        items: [{
                            name: "newCommentTextArea",
                            dataField: "new_comment",
                            label: {
                                text: "Новый комментарий",
                                visible: false
                            },
                            editorType: "dxTextArea"
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
                    {
                        itemType: "button",
                        name: "createTransferOperation",
                        colSpan: 2,
                        horizontalAlignment: "right",
                        buttonOptions: {
                            text: "Создать перемещение",
                            type: "default",
                            stylingMode: "contained",
                            useSubmitBehavior: false,
                            template: function(data, container) {
                                $("<div class='button-loading-indicator'></div><span class='dx-button-text'>" + data.text + "</span>").appendTo(container);
                                let loadingIndicator = container.find(".button-loading-indicator").dxLoadIndicator({
                                    visible: false
                                }).dxLoadIndicator("instance");
                            },
                            onClick: function (e) {
                                let result = e.validationGroup.validate();
                                if (!result.isValid) {
                                    return;
                                }

                                setButtonIndicatorVisibleState("createTransferOperation", true)
                                setElementsDisabledState(true);

                                let comment = operationForm.option("formData").new_comment;
                                if (!comment) {
                                    let confirmDialog = DevExpress.ui.dialog.confirm('Вы не заполнили поле "Комментарий".<br>Продолжить без заполнения?', 'Комметарий не заполнен');
                                    confirmDialog.done(function (dialogResult) {
                                        if (dialogResult) {
                                            validateMaterialList(true, true);
                                        } else {
                                            setButtonIndicatorVisibleState("createTransferOperation", false)
                                            setElementsDisabledState(false);
                                            return;
                                        }
                                    })
                                } else {
                                    validateMaterialList(true, true);
                                }
                            }
                        }
                    }]

            }).dxForm("instance")
            //</editor-fold>

            //<editor-fold desc="JS: Toolbar configuration">
            //</editor-fold>

            function saveOperationData() {
                let transferOperationData = {};

                transferOperationData.transfer_operation_initiator = transferOperationInitiator;
                transferOperationData.source_project_object_id = operationForm.option("formData").source_project_object_id;
                transferOperationData.destination_project_object_id = operationForm.option("formData").destination_project_object_id;
                transferOperationData.new_comment = operationForm.option("formData").new_comment;
                //TODO Дата формаируется в UTC. Нужно либо учитывать это при перобразовании, либо хранить в UTC в БД
                if (transferOperationInitiator === "none" || transferOperationInitiator === "source") {
                    transferOperationData.operation_date = new Date(operationForm.option("formData").operation_date_start).toJSON().split("T")[0];
                }

                if (transferOperationInitiator === "destination") {
                    transferOperationData.operation_date = new Date(operationForm.option("formData").operation_date_end).toJSON().split("T")[0];
                }

                transferOperationData.source_responsible_user_id = operationForm.option("formData").source_responsible_user_id;
                transferOperationData.destination_responsible_user_id = operationForm.option("formData").destination_responsible_user_id;

                transferOperationData.consignment_note_number = operationForm.option("formData").consignment_note_number;

                let uploadedFiles = []
                $(".file-uploader").each(function () {
                    if ($(this).attr("uploaded-file-id") !== undefined) {
                        uploadedFiles.push($(this).attr("uploaded-file-id"));
                    }
                });

                transferOperationData.uploaded_files = uploadedFiles;
                transferOperationData.materials = transferMaterialDataSource.store().createQuery().toArray();

                postEditingData(transferOperationData);
            }

            function getValidationUid(standardId, accountingType, quantity, amount, initialCommentId){
                let filterConditions;

                switch (accountingType){
                    case 2:
                        if (!quantity || !amount) {
                            return new DevExpress.data.Guid().toString();
                        } else {
                            filterConditions = [["standard_id", "=", standardId],
                                "and",
                                ["quantity", "=", quantity],
                                "and",
                                ["amount", ">", 0],
                                "and",
                                ["initial_comment_id", "=", initialCommentId]];
                        }
                        break;
                    default:
                        filterConditions = ["standard_id", "=", standardId];
                }

                let filteredData = getTransferMaterialGrid().getDataSource().store().createQuery()
                    .filter(filterConditions)
                    .toArray();

                if (filteredData.length > 0) {
                    return filteredData[0].validationUid
                } else {
                    return new DevExpress.data.Guid().toString();
                }
            }

            function validateMaterialList(saveEditedData, showErrorWindowOnHighSeverity, validationUid) {
                let validationData;
                if (validationUid && !(saveEditedData)) {
                    validationData = transferMaterialDataSource.store().createQuery()
                        .filter(['validationUid', '=', validationUid])
                        .toArray();
                } else {
                    validationData = transferMaterialDataSource.store().createQuery()
                        .toArray();
                }

                updateRowsValidationState(validationData, "inProcess", "none")

                let transferOperationData = {
                    materials: validationData,
                    sourceProjectObjectId: sourceProjectObjectId,
                    timestamp: new Date()
                };
                $.ajax({
                    url: "{{route('materials.operations.transfer.validate-material-list')}}",
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    contentType: "json",
                    dataType: "json",
                    data: JSON.stringify(transferOperationData),
                    success: function (e) {
                        let needToShowErrorWindow = false;

                        if (!validationUid) {
                            materialErrorList = [];
                        } else {
                            delete (materialErrorList["common"]);
                        }
                        e.validationResult.forEach((validationElement) => {
                            if (materialErrorList[validationElement.validationUid]) {
                                let materialListTimestamp = new Date(materialErrorList[validationElement.validationUid].timestamp);
                                let currentResponseTimestamp = new Date(e.timestamp);

                                if (materialListTimestamp < currentResponseTimestamp) {
                                    delete(materialErrorList[validationElement.validationUid]);
                                } else {
                                    return;
                                }
                            }

                            let validatedData = transferMaterialDataSource.store().createQuery()
                                .filter(['validationUid', '=', validationElement.validationUid])
                                .toArray();

                            if (validationElement.isValid) {
                                updateRowsValidationState(validatedData, "validated", "valid");
                            } else {
                                materialErrorList[validationElement.validationUid] = {};
                                materialErrorList[validationElement.validationUid].errorList = validationElement.errorList;
                                materialErrorList[validationElement.validationUid].timestamp = e.timestamp;
                                updateRowsValidationState(validatedData, "validated", "invalid");
                            }

                            updateCommonValidationState();

                            if (!validationElement.isValid) {
                                validationElement.errorList.forEach((errorItem) => {
                                    if (showErrorWindowOnHighSeverity) {
                                        if (errorItem.severity > 500) {
                                            needToShowErrorWindow = true;
                                        }
                                    }
                                })
                            }
                        })

                        if (needToShowErrorWindow) {
                            showErrorWindow(materialErrorList);
                            setButtonIndicatorVisibleState("createTransferOperation", false)
                            setElementsDisabledState(false);
                        }

                        if (!needToShowErrorWindow){
                            if (saveEditedData) {
                                saveOperationData();
                            }
                        }
                    },
                    error: function (e) {
                        DevExpress.ui.notify("При проверке данных произошла неизвестная ошибка", "error", 5000)
                        setButtonIndicatorVisibleState("createTransferOperation", false)
                        setElementsDisabledState(false);
                    }
                });
            }

            function updateCommonValidationState() {
                if (materialErrorList["common"]) {
                    materialErrorList["common"].errorList.forEach((item) => {
                        if (item.type === "totalWeightIsTooLarge"){
                            let summary = $(".computed-weight-total-summary");
                            $('<i/>').addClass("dx-link fas fa-exclamation-triangle")
                                .attr("style", "color: #ffd358; margin-right: 4px;")
                                .attr('severity', item.severity)
                                .click((e) => {
                                    e.preventDefault();
                                })
                                .mouseenter(function () {
                                    if (!item.message) {
                                        return;
                                    }

                                    let validationDescription = $('#validationTemplate');

                                    validationDescription.dxPopover({
                                        position: "top",
                                        width: 300,
                                        contentTemplate: "<ul>" + item.message + "</ul>",
                                        hideEvent: "mouseleave",
                                    })
                                        .dxPopover("instance")
                                        .show($(this));
                                })
                                .prependTo(summary);
                        }
                    })
                }
            }

            function updateRowsValidationState(data, validationState, validationResult, validationDiv){
                data.forEach((element) => {
                    transferMaterialDataSource.store()
                        .update(element.id, {validationState: validationState, validationResult: validationResult})
                        .done((dataObj, key) => {
                            let validationIndicatorDiv;
                            if (validationDiv) {
                                validationIndicatorDiv = validationDiv;
                            } else {
                                validationIndicatorDiv = $('[validation-uid=' + dataObj.validationUid + ']');
                            }

                            validationIndicatorDiv.empty();

                            switch (dataObj.validationState) {
                                case "inProcess":
                                    let indicatorDiv = $('<div class="cell-validation-loading-indicator">');
                                    indicatorDiv.dxLoadIndicator({
                                        visible: true,
                                        width: 16,
                                        height: 16
                                    }).appendTo(validationIndicatorDiv);
                                    break;
                                case "validated":
                                    if (validationResult === "valid"){
                                        let checkIcon = $("<i/>")
                                            .addClass("dx-link dx-icon fas fa-check-circle dx-link-icon")
                                            .attr("style", "color: #8bc34a")
                                            .appendTo(validationIndicatorDiv);
                                        return;
                                    } else {
                                        let exclamationTriangle = $("<a>")
                                            .attr("href", "#")
                                            .attr("style", "display: none")
                                            .addClass("dx-link dx-icon fas fa-exclamation-triangle dx-link-icon")
                                            .appendTo(validationIndicatorDiv);

                                        if (!materialErrorList[element.validationUid].errorList){
                                            return;
                                        }

                                        let errorList = materialErrorList[element.validationUid].errorList;
                                        let maxSeverity = 0;
                                        let errorDescription = "";
                                        let exclamationTriangleStyle = "";

                                        errorList.forEach((errorItem) => {
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

                                        exclamationTriangle.attr('style', exclamationTriangleStyle);
                                        exclamationTriangle.attr('severity', maxSeverity);
                                        exclamationTriangle.click((e) => {
                                            e.preventDefault();
                                        });
                                        exclamationTriangle.mouseenter(function () {
                                            if (!errorDescription) {
                                                return;
                                            }

                                            let validationDescription = $('#validationTemplate');

                                            validationDescription.dxPopover({
                                                position: "top",
                                                width: 300,
                                                contentTemplate: "<ul>" + errorDescription + "</ul>",
                                                hideEvent: "mouseleave",
                                            })
                                                .dxPopover("instance")
                                                .show($(this));
                                        });
                                    }
                                    break;
                            }
                        });
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
                        DevExpress.ui.notify("При сохранении данных произошла ошибка", "error", 5000);
                        setButtonIndicatorVisibleState("createTransferOperation", false)
                        setElementsDisabledState(false);
                    }
                })
            }

            function recalculateStandardsRemains(editedRowKey) {
                transferMaterialStore.byKey(editedRowKey)
                    .done(function (dataItem) {
                        let calculatedQuantity = dataItem.total_quantity * dataItem.total_amount;
                        let calculatedAmount = dataItem.total_amount;
                        let initialCommentId = dataItem.initial_comment_id ? dataItem.initial_comment_id : null;

                        transferMaterialDataSource.store().createQuery().toArray().forEach((item) => {
                            if (item.standard_id === dataItem.standard_id) {
                                switch (dataItem.accounting_type) {
                                    case 2:
                                        let itemComment = item.initial_comment_id ? item.initial_comment_id : null;
                                        if (item.quantity === dataItem.quantity && itemComment === initialCommentId) {
                                            calculatedAmount = Math.round((calculatedAmount - item.amount) * 100) / 100;
                                        }
                                        break;
                                    default:
                                        calculatedQuantity = Math.round((calculatedQuantity - item.quantity * item.amount) * 100) / 100;
                                }
                            }
                        })

                        switch (dataItem.accounting_type) {
                            case 2:
                                $(`[accounting-type='${dataItem.accounting_type}'][standard-id='${dataItem.standard_id}'][standard-quantity='${dataItem.quantity}'][initial-comment-id='${dataItem.initial_comment_id}']`).each(function () {
                                    $(this).text(calculatedAmount + ' шт');
                                    if (calculatedAmount < 0){
                                        $(this).addClass("red")
                                    }
                                });
                                break;
                            default:
                                $(`[accounting-type='${dataItem.accounting_type}'][standard-id='${dataItem.standard_id}']`).each(function () {
                                    $(this).text(calculatedQuantity + ' ' + dataItem.measure_unit_value);
                                    if (calculatedAmount < 0){
                                        $(this).addClass("red")
                                    }
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

            function setElementsDisabledState(state){
                operationForm.getEditor("createTransferOperation").option("disabled", state);
                getTransferMaterialGrid().option("disabled", state);
                operationForm.getEditor("sourceProjectObjectSelectBox").option("disabled", state);
                operationForm.getEditor("operationDateStartDateBox").option("disabled", state);
                operationForm.getEditor("sourceResponsibleUserSelectBox").option("disabled", state);
                operationForm.getEditor("destinationProjectObjectSelectBox").option("disabled", state);
                //operationForm.getEditor("operationDateEndDateBox").option("disabled", state);
                operationForm.getEditor("destinationResponsibleUserSelectBox").option("disabled", state);
                operationForm.getEditor("consignmentNoteNumberNumberBox").option("disabled", state);
                operationForm.getEditor("newCommentTextArea").option("disabled", state);
            }

            function setButtonIndicatorVisibleState(buttonName, state){
                let loadingIndicator = operationForm.getEditor(buttonName).element()
                    .find(".button-loading-indicator").dxLoadIndicator("instance");
                loadingIndicator.option('visible', state);
            }

            function showErrorWindow(errorList){
                let htmlMessage = "";
                for (key in errorList) {
                    errorList[key].errorList.forEach((errorItem) => {
                        switch (errorItem.severity) {
                            case 500:
                                exclamationTriangleStyle = 'color: #ffd358';
                                break;
                            case 1000:
                                exclamationTriangleStyle = 'color: #f15a5a';
                                break;
                            default:
                                exclamationTriangleStyle = "gray";
                        }

                        htmlMessage += '<p><i class="fas fa-exclamation-triangle" style="' + exclamationTriangleStyle + '"></i>  ';
                        if (errorItem.itemName) {
                            htmlMessage += errorItem.itemName + ': ' + errorItem.message;
                        } else {
                            htmlMessage += errorItem.message;
                        }
                        htmlMessage += '</p>'
                    })
                }

                DevExpress.ui.dialog.alert(htmlMessage, "Обнаружены ошибки");
            }

            function validateConsignmentNumberUnique(value){
                return $.ajax({
                    url: "{{route('material.consignment-number.validate')}}",
                    data: {
                        consignmentNumber: value,
                    }
                })
            }

            function createAddMaterialsButton(){
                let groupCaption = $('.materials-grid').find('.dx-form-group-with-caption');
                $('<div>').addClass('dx-form-group-caption-buttons').prependTo(groupCaption);
                groupCaption.find('span').addClass('dx-form-group-caption-span-with-buttons');
                let groupCaptionButtonsDiv = groupCaption.find('.dx-form-group-caption-buttons');

                $('<div>')
                    .dxButton({
                        text: "Добавить",
                        icon: "fas fa-plus",
                        onClick: (e) => {
                            selectedMaterialStandardsListDataSource.store().clear();

                            let materialsStandardsList = materialsStandardsAddingForm.getEditor("materialsStandardsList");
                            materialsStandardsList.option("selectedRowKeys", []);

                            $("#popupContainer").dxPopup("show");
                        }
                    })
                    .addClass('dx-form-group-caption-button')
                    .prependTo(groupCaptionButtonsDiv)
            }

            function getTransferMaterialGrid() {
                return operationForm.getEditor("transferMaterialGrid");
            }

            createAddMaterialsButton();
        });
    </script>
@endsection
