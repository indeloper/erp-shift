@extends('layouts.app')

@section('title', 'Новое списание')

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
            let measureUnitData = {!!$measureUnits ?? ''!!};
            let projectObject = {{$projectObjectId}};
            let materialStandardsData = {!!$materialStandards!!};
            let materialTypesData = {!!$materialTypes!!};
            let materialErrorList = [];
            let suspendSourceObjectLookupValueChanged = false;
            let commentData = null;
            let isTWriteOffMaterialStoreBeenAlreadyLoaded = false;

            let files = [
                {
                    colSpan: 1,
                    dropzoneRole: "addNewFileUploader"
                }
            ];

            //<editor-fold desc="JS: DataSources">
            let materialsListStore = new DevExpress.data.CustomStore({
                key: "id",
                loadMode: "processed",
                load: function (loadOptions) {
                    return $.getJSON("{{route('materials.actual.list')}}",
                        {project_object: projectObject});
                },
            });


            let materialsListDataSource = new DevExpress.data.DataSource({
                //group: "key",
                store: materialsListStore
            })

            let selectedMaterialStandardsListDataSource = new DevExpress.data.DataSource({
                store: new DevExpress.data.ArrayStore({
                    key: "id",
                    data: []
                })
            })

            let writeOffMaterialData = {!! $predefinedMaterials !!};

            let writeOffMaterialStore = new DevExpress.data.ArrayStore({
                key: "id",
                data: writeOffMaterialData,
                onLoaded: () => {
                    if (!isTWriteOffMaterialStoreBeenAlreadyLoaded) {
                        validateMaterialList(false, false);
                        isTWriteOffMaterialStoreBeenAlreadyLoaded = true;
                    }
                }
            })

            let writeOffMaterialDataSource = new DevExpress.data.DataSource({
                reshapeOnPush: true,
                store: writeOffMaterialStore
            })

            let projectObjectsListWhichParticipatesInMaterialAccountingDataSource = new DevExpress.data.DataSource({
                reshapeOnPush: true,
                store: new DevExpress.data.CustomStore({
                    key: "id",
                    loadMode: "raw",
                    load: function (loadOptions) {
                        return $.getJSON("{{route('project-objects.which-participates-in-material-accounting.list')}}",
                            {data: JSON.stringify(loadOptions)});
                    }
                })

            });

            let usersWithMaterialListAccessStore = new DevExpress.data.CustomStore({
                key: "id",
                loadMode: "raw",
                load: function (loadOptions) {
                    return $.getJSON("{{route('users-with-material-list-access.list')}}",
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
                            getWriteOffMaterialGrid().refresh();
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
                        name: "materialsList",
                        editorOptions: {
                            dataSource: materialsListDataSource,
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
                                    let quantity;
                                    let amount;

                                    quantity = options.data.quantity ? Math.round(options.data.quantity * 100) / 100 + " " : "";
                                    amount = options.data.amount ? options.data.amount + " " : "";

                                    switch (options.data.accounting_type) {
                                        case 1:
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
                                        dataSource: materialTypesData,
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
                                    return 400/*$(document).height() - ($(document).height()/100*20)*/;
                                },
                                width: 500,
                                itemTemplate: function (data) {
                                    let quantity = data.quantity ? Math.round(data.quantity * 100) / 100 + " " : "";
                                    let amount = data.amount ? data.amount + " " : "";

                                    switch (data.accounting_type) {
                                        case 1:
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
                                    let materialsList = materialsStandardsAddingForm.getEditor("materialsList");
                                    let selectedMaterialsList = materialsStandardsAddingForm.getEditor("selectedMaterialsList");
                                    let selectedRowsKeys = [];
                                    selectedMaterialsList.option("items").forEach(function (selectedItem) {
                                        selectedRowsKeys.push(selectedItem.id);
                                    });

                                    materialsList.option("selectedRowKeys", selectedRowsKeys);
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
                                writeOffMaterialDataSource.store().insert({
                                    id: new DevExpress.data.Guid().toString(),
                                    standard_id: material.standard_id,
                                    standard_name: material.standard_name,
                                    accounting_type: material.accounting_type,
                                    material_type: material.material_type,
                                    measure_unit: material.measure_unit,
                                    measure_unit_value: material.measure_unit_value,
                                    standard_weight: material.weight,
                                    quantity: Math.round(material.quantity * 100) / 100,
                                    amount: material.amount,
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
                            })
                            writeOffMaterialDataSource.reload();
                            $("#popupContainer").dxPopup("hide")
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
            let writeOffMaterialColumns = [
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
                            console.log('standard_name', options.text);
                            let divStandardName = $(`<div class="standard-name"></div>`)
                                .appendTo(container);

                            let divStandardText = $(`<div>${options.text}</div>`)
                                .appendTo(divStandardName);

                            if (options.data.initial_comment) {
                                $(`<div class="material-comment">${options.data.initial_comment}</div>`)
                                    .appendTo(divStandardName);

                                divStandardName.addClass("standard-name-cell-with-comment");
                            }

                            let divStandardRemains = $(`<div class="standard-remains" standard-id="${options.data.standard_id}" standard-quantity="${Math.round(options.data.quantity * 100) / 100}" accounting-type="${options.data.accounting_type}" initial-comment-id="${options.data.initial_comment_id}"></div>`)
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
                        let quantity = Math.round(options.data.quantity * 100) / 100;
                        if (quantity) {
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
                        dataSource: materialTypesData,
                        displayExpr: "name",
                        valueExpr: "id"
                    }
                },
            ];
            //</editor-fold>

            //<editor-fold desc="JS: Grid configuration">
            let writeOffMaterialGridConfiguration = {
                dataSource: writeOffMaterialDataSource,
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
                columns: writeOffMaterialColumns,
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
                    if (e.dataField === "quantity" && e.parentType === "dataRow") {
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
                    caption: "Списание",
                    items: [{
                        name: "projectObjectSelectBox",
                        colSpan: 3,
                        dataField: "project_object_id",
                        label: {
                            text: "Объект"
                        },
                        editorType: "dxSelectBox",
                        editorOptions: {
                            dataSource: projectObjectsListWhichParticipatesInMaterialAccountingDataSource,
                            displayExpr: "short_name",
                            valueExpr: "id",
                            searchEnabled: true,
                            value: projectObject,
                            onValueChanged: function (e) {
                                function updateComponentsDataSources(projectObjectIdValue) {
                                    projectObject = projectObjectIdValue;
                                    writeOffMaterialStore.clear();
                                    writeOffMaterialDataSource.reload();
                                    reloadMaterialsListDataSource();
                                }

                                function reloadMaterialsListDataSource () {
                                    let dataGrid = materialsStandardsAddingForm.getEditor("materialsList")
                                    dataGrid.option("dataSource").reload();
                                }

                                if (suspendSourceObjectLookupValueChanged) {
                                    suspendSourceObjectLookupValueChanged = false;
                                    return;
                                }

                                let oldValue = e.previousValue;
                                let currentValue = e.value;

                                if (getWriteOffMaterialGrid().getDataSource().items().length > 0 && e.previousValue !== null) {
                                    let confirmDialog = DevExpress.ui.dialog.confirm('При смене объекта будут удалены введенные данные по материалам операции.<br>Продолжить?', 'Смена объекта');
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
                            message: 'Поле "Объект" обязательно для заполнения'
                        }]
                    },
                        {
                            name: "operationDateDateBox",
                            dataField: "operation_date",
                            colSpan: 1,
                            label: {
                                text: "Дата списания"
                            },
                            editorType: "dxDateBox",
                            editorOptions: {
                                value: Date.now()
                            },
                            validationRules: [{
                                type: "required",
                                message: 'Поле "Дата списания" обязательно для заполнения'
                            }]
                        },
                        {
                            name: "destinationResponsibleUserSelectBox",
                            colSpan: 2,
                            dataField: "responsible_user_id",
                            label: {
                                text: "Ответственный"
                            },
                            editorType: "dxSelectBox",
                            editorOptions: {
                                dataSource: {
                                    store: usersWithMaterialListAccessStore
                                },
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
                    caption: "Комментарий",
                    items: [{
                        name: "newCommentTextArea",
                        dataField: "new_comment",
                        label: {
                            text: "Новый комментарий",
                            visible: false
                        },
                        editorType: "dxTextArea",
                        editorOptions: {
                            height: 160,
                        },
                        validationRules: [{
                            type: "required",
                            message: 'Поле "Комментарий" обязательно для заполнения'
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
                            name: "writeOffMaterialGrid",
                            editorType: "dxDataGrid",
                            editorOptions: writeOffMaterialGridConfiguration
                        }
                        ]
                    },
                    {
                        itemType: "group",
                        caption: "Файлы",
                        name: "fileUploaderGroup",
                        colSpan: 2,
                        colCount: 4,
                        items: getFileOptions()
                    },
                    {
                        itemType: "button",
                        name: "createWriteOffOperation",
                        colSpan: 2,
                        horizontalAlignment: "right",
                        buttonOptions: {
                            text: "Создать списание",
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
                                getWriteOffMaterialGrid().closeEditCell();

                                let result = e.validationGroup.validate();
                                if (!result.isValid) {
                                    return;
                                }

                                setButtonIndicatorVisibleState("createWriteOffOperation", true)
                                setElementsDisabledState(true);

                                let comment = operationForm.option("formData").new_comment;
                                if (!comment) {
                                    let confirmDialog = DevExpress.ui.dialog.confirm('Вы не заполнили поле "Комментарий".<br>Продолжить без заполнения?', 'Комметарий не заполнен');
                                    confirmDialog.done(function (dialogResult) {
                                        if (dialogResult) {
                                            validateMaterialList(true, true);
                                        } else {
                                            setButtonIndicatorVisibleState("createWriteOffOperation", false)
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
                let writeOffOperationData = {};

                writeOffOperationData.project_object_id = operationForm.option("formData").project_object_id;
                //TODO Дата формируется в UTC. Нужно либо учитывать это при перобразовании, либо хранить в UTC в БД
                writeOffOperationData.operation_date = new Date(operationForm.option("formData").operation_date).toJSON().split("T")[0];
                writeOffOperationData.responsible_user_id = operationForm.option("formData").responsible_user_id;
                writeOffOperationData.new_comment = operationForm.option("formData").new_comment;

                let uploadedFiles = []

                files.forEach((item) => {
                    if (item.uploadedFileId) {
                        uploadedFiles.push(item.uploadedFileId);
                    }
                })

                writeOffOperationData.uploaded_files = uploadedFiles;
                writeOffOperationData.materials = writeOffMaterialData;

                postEditingData(writeOffOperationData);
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

                let filteredData = getWriteOffMaterialGrid().getDataSource().store().createQuery()
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
                    validationData = writeOffMaterialDataSource.store().createQuery()
                        .filter(['validationUid', '=', validationUid])
                        .toArray();
                } else {
                    validationData = writeOffMaterialDataSource.store().createQuery()
                        .toArray();
                }

                updateRowsValidationState(validationData, "inProcess", "none")

                let writeOffOperationData = {
                    materials: validationData,
                    projectObjectId: operationForm.option("formData").project_object_id,
                    timestamp: new Date()
                };
                $.ajax({
                    url: "{{route('materials.operations.write-off.new.validate-material-list')}}",
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    contentType: "json",
                    dataType: "json",
                    data: JSON.stringify(writeOffOperationData),
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

                            let validatedData = writeOffMaterialDataSource.store().createQuery()
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
                            setButtonIndicatorVisibleState("createWriteOffOperation", false)
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
                        setButtonIndicatorVisibleState("createWriteOffOperation", false)
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
                    writeOffMaterialDataSource.store()
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

            function postEditingData(writeOffOperationData) {
                $.ajax({
                    url: "{{route('materials.operations.write-off.new')}}",
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        data: JSON.stringify(writeOffOperationData)
                    },

                    success: function (data, textStatus, jqXHR) {
                        window.location.href = '{{route('materials.index')}}/?project_object=' + projectObject
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        DevExpress.ui.notify("При сохранении данных произошла ошибка", "error", 5000);
                        setButtonIndicatorVisibleState("createWriteOffOperation", false)
                        setElementsDisabledState(false);
                    }
                })
            }

            function recalculateStandardsRemains(editedRowKey) {
                writeOffMaterialStore.byKey(editedRowKey)
                    .done(function (dataItem) {
                        let calculatedQuantity = dataItem.total_quantity * dataItem.total_amount;
                        let calculatedAmount = dataItem.total_amount;
                        let initialCommentId = dataItem.initial_comment_id ? dataItem.initial_comment_id : null;

                        writeOffMaterialDataSource.store().createQuery().toArray().forEach((item) => {
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

            function setElementsDisabledState(state){
                operationForm.getEditor("createWriteOffOperation").option("disabled", state);
                operationForm.getEditor("writeOffMaterialGrid").option("disabled", state);
                operationForm.getEditor("destinationResponsibleUserSelectBox").option("disabled", state);
                operationForm.getEditor("projectObjectSelectBox").option("disabled", state);
                operationForm.getEditor("operationDateDateBox").option("disabled", state);
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

                            let materialsList = materialsStandardsAddingForm.getEditor("materialsList");
                            materialsList.option("selectedRowKeys", []);

                            $("#popupContainer").dxPopup("show");
                        }
                    })
                    .addClass('dx-form-group-caption-button')
                    .prependTo(groupCaptionButtonsDiv)
            }

            function getWriteOffMaterialGrid() {
                return operationForm.getEditor("writeOffMaterialGrid");
            }

            function getFileOptions() {
                let options = [];
                files.forEach((item) => {
                    let optionElement = {}
                    optionElement.colSpan = 1;
                    optionElement.template = (() => {

                        if (item.dropzoneRole === "addNewFileUploader") {
                            let addDropzoneDiv = $('<div id="dropzone-external-add" class="dx-uploader-flex-box dx-theme-border-color dropzone-external">' +
                                '<div id="dropzone-text-add" class="dx-uploader-flex-box dropzone-text">' +
                                '<i class="fas fa-plus dx-uploader-icon"></i>' +
                                '</div>' +
                                '</div>');
                            addDropzoneDiv.click(() => {
                                createFileUploaderElement();
                            })
                            return addDropzoneDiv;
                        } else {
                            let imageContainerSrc = item.src ? item.src : "#";
                            let dropzoneContainer = $('<div/>');
                            let dropzoneExternalContainer = $(`<div id="dropzone-external-${item.itemIndex}" class="dx-uploader-flex-box dx-theme-border-color dropzone-external">`)
                                .appendTo(dropzoneContainer);

                            let dropzoneImageContainer = $(`<img id="dropzone-image-${item.itemIndex}" class="dropzone-image" src="${imageContainerSrc}" alt="" style="display:none"/>`)
                                .appendTo(dropzoneExternalContainer);

                            toggleImageVisible(imageContainerSrc !== '#')

                            let dropzoneTextContainer = $(`<div id="dropzone-text-${item.itemIndex}" class="dx-uploader-flex-box dropzone-text">` +
                                                     `<span class="dx-uploader-span">Выберите файл для загрузки</span>` +
                                                 `</div>`)
                                .appendTo(dropzoneExternalContainer);

                            if (imageContainerSrc !== "#") {
                                dropzoneTextContainer.attr('style', 'display:none')
                            }

                            let uploadProgressBarContainer = $(`<div id="upload-progress-${item.itemIndex}" class="upload-progress"/>`)
                                .appendTo(dropzoneExternalContainer)

                            let fileUploader = $(`<div class="file-uploader" purpose="custom" index="${item.itemIndex}">` +
                                `</div>`).appendTo(dropzoneContainer);

                            fileUploader.dxFileUploader({
                                dialogTrigger: dropzoneExternalContainer,
                                dropZone: dropzoneExternalContainer,
                                multiple: false,
                                allowedFileExtensions: [".jpg", ".jpeg", ".gif", ".png"],
                                uploadMode: "instantly",
                                uploadHeaders: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                uploadUrl: "{{route('materials.operations.upload-file')}}",
                                uploadCustomData: {uploadPurpose: fileUploader.attr('purpose')},
                                visible: false,
                                onDropZoneEnter: function (e) {
                                    if (e.dropZoneElement.id === "dropzone-external-" + item.itemIndex)
                                        toggleDropZoneActive(e.dropZoneElement, true);
                                },
                                onDropZoneLeave: function (e) {
                                    if (e.dropZoneElement.id === "dropzone-external-" + item.itemIndex)
                                        toggleDropZoneActive(e.dropZoneElement, false);
                                },
                                onUploaded: function (e) {
                                    const file = e.file;
                                    const dropZoneText = dropzoneTextContainer;
                                    const fileReader = new FileReader();
                                    fileReader.onload = function () {
                                        toggleDropZoneActive(document.getElementById("dropzone-external-" + item.itemIndex), false);
                                        const dropZoneImage = document.getElementById("dropzone-image-" + item.itemIndex);
                                        dropZoneImage.src = fileReader.result;
                                        files[item.itemIndex].src = dropZoneImage.src;
                                    }
                                    fileReader.readAsDataURL(file);
                                    dropZoneText.attr("style", "display:none")
                                    uploadProgressBar.option({
                                        visible: false,
                                        value: 0
                                    });

                                    let fileId = JSON.parse(e.request.response).id;
                                    e.element.attr('uploaded-file-id', fileId);
                                    files[item.itemIndex].uploadedFileId = fileId;
                                    toggleImageVisible("true");
                                },
                                onProgress: function (e) {
                                    uploadProgressBar.option("value", e.bytesLoaded / e.bytesTotal * 100)
                                },
                                onUploadStarted: function () {
                                    toggleImageVisible(false);
                                    uploadProgressBar.option("visible", true);
                                }
                            });

                            let uploadProgressBar = uploadProgressBarContainer.dxProgressBar({
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
                                if (visible) {
                                    dropzoneImageContainer.show();
                                } else {
                                    dropzoneImageContainer.hide()
                                }
                            }

                            return dropzoneContainer;
                        }
                    })

                    options.push(optionElement)
                })
                return options;
            }

            function createFileUploaderElement() {
                let fileIndex = files.length - 1;
                let fileUploader = {};
                fileUploader.itemIndex = fileIndex;

                files.splice(fileIndex, 0, fileUploader);
                operationForm.itemOption("fileUploaderGroup", "items", getFileOptions());
            }

            createAddMaterialsButton();
            createFileUploaderElement();
        });
    </script>
@endsection
