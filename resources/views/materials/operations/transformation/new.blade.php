@extends('layouts.app')

@section('title', 'Новое преобразование')

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
            padding-left: 0 !important;
        }

        .transformation-type-selector {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            flex-direction: row;
            align-content: space-between;
        }

        .transformation-type-item {
            width: 120px;
            height: 120px;
            margin: 32px;
            background-color: rgba(183, 183, 183, 0.1);
            border-width: 2px;
            border-style: solid;
            border-color: rgba(183, 183, 183, 0.7);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .transformation-type-text {
            font-weight: 500;
            opacity: 0.8;
            text-align: center;
        }

        .transformation-type-item:hover {
            background-color: aliceblue;
            border-color: #03a9f4a6;
            cursor: pointer;
        }

        .without-box-shadow {
            box-shadow: none !important;
        }

        .form-container .dx-numberbox {
            float: right;
        }

        .transformation-header {
            border-bottom: #e0e0e0 solid 2px !important;
            background-color: #f7f7f7;
        }

        .transformation-header-caption {
            float: left;
            line-height: 28px;
            font-weight: bold;
            color: #717171;
        }

        .transformation-header-button {
            float: right;
            margin-left: 8px;
        }
        .command-row-buttons {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .dx-datagrid-rowsview .dx-texteditor.dx-editor-outlined .dx-texteditor-input {
            text-align: right;
            padding-right: 0;
        }

        .dx-datagrid-rowsview .dx-placeholder {
            text-align: right;
            width: 100%;
        }

        .dx-datagrid-rowsview .dx-texteditor.dx-editor-outlined .dx-placeholder::before {
            padding-right: 0;
        }

        .computed-weight, .quantity-total-summary, .amount-total-summary, .weight-total-summary {
            text-align: right;
        }

        div.footer-row-validation {
            display: flex;
            align-items: center;
        }

        .footer-row-validation-indicator {
            float: left;
            margin-right: 8px;
        }

        .footer-row-validation-message {
            font-weight: 500;
        }
    </style>
@endsection

@section('content')
    <div id="formContainer"></div>

    <div id="popupContainer">
        <div id="materialsStandardsAddingForm"></div>
    </div>

    <div id="validationPopoverContainer">
        <div id="validationTemplate" data-options="dxTemplate: { name: 'validationTemplate' }"></div>
    </div>

    <div id="validationPopoverContainer">
        <div id="validationTemplate" data-options="dxTemplate: { name: 'validationTemplate' }"></div>
    </div>
@endsection

@section('js_footer')
    <script>
        $(function () {
            const transformationStages = Object.freeze({
                transformationTypesSelection: Symbol("transformationTypesSelection"),
                fillingMaterialsToTransform: Symbol("fillingMaterialsToTransform"),
                fillingMaterialsAfterTransform: Symbol("fillingMaterialsAfterTransform"),
                fillingMaterialsRemains: Symbol("fillingMaterialsRemains"),
                fillingMaterialsTechnologicalLosses: Symbol("fillingMaterialsTechnologicalLosses"),
            });

            const rowTypes = Object.freeze({
                rowHeader: Symbol("rowHeader"),
                rowFooter: Symbol("rowFooter"),
                rowData: Symbol("fillingMaterialsTechnologicalLosses"),
            });

            let materialTypesData = {!!$materialTypes!!};

            let projectObjectId = {{$projectObjectId}};

            let currentTransformationStage = transformationStages.transformationTypesSelection;
            let currentTransformationType = "";

            let transformationData = new DevExpress.data.DataSource({
                store: new DevExpress.data.ArrayStore({
                    key: "id",
                    data: []
                }),
                sort: ["sortIndex"]
            })

            let suspendSourceObjectLookupValueChanged = false;
            //<editor-fold desc="JS: DataSources">

            let availableMaterialsStore = new DevExpress.data.CustomStore({
                key: "id",
                loadMode: "raw",
                load: function (loadOptions) {
                    return $.getJSON("{{route('materials.actual.list')}}",
                        {project_object: projectObjectId});
                },
            });

            let availableMaterialsDataSource = new DevExpress.data.DataSource({
                key: "id",
                store: availableMaterialsStore
            });

            let materialStandardsListStore = new DevExpress.data.CustomStore({
                key: "id",
                loadMode: "raw",
                load: function (loadOptions) {
                    return $.getJSON("{{route('materials.standards.listex')}}",
                        {data: JSON.stringify({dxLoadOptions: loadOptions})});
                },
            });

            let materialStandardsListDataSource = new DevExpress.data.DataSource({
                key: "id",
                store: materialStandardsListStore
            })

            let selectedMaterialStandardsListDataSource = new DevExpress.data.DataSource({
                store: new DevExpress.data.ArrayStore({
                    key: "id",
                    data: []
                })
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

            let materialTransformationTypesDataSource = new DevExpress.data.DataSource({
                reshapeOnPush: true,
                store: new DevExpress.data.CustomStore({
                    key: "id",
                    loadMode: "raw",
                    load: function (loadOptions) {
                        return $.getJSON("{{route('material.transformation-types.lookup-list')}}",
                            {data: JSON.stringify(loadOptions)});
                    }
                }),
                onChanged: function () {
                    repaintGUI();
                }
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

            let materialsStandardsAddingForm = $("#materialsStandardsAddingForm").dxForm({
                colCount: 2,
                items: [{
                    itemType: "group",
                    colCount: 3,
                    caption: "Эталоны",
                    items: [{
                        editorType: "dxDataGrid",
                        name: "materialsStandardsList",
                        editorOptions: {
                            dataSource: null,
                            height: "50vh",
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
                            scrolling: {
                                mode: 'virtual'
                            },
                            searchPanel: {
                                visible: true,
                                searchVisibleColumnsOnly: true,
                                width: 240,
                                placeholder: "Поиск..."
                            },
                            columns: [
                                {
                                dataField: "standard_name",
                                dataType: "string",
                                caption: "Наименование",
                                calculateFilterExpression: function (filterValue, selectedFilterOperation, target) {
                                    if (target === "search") {
                                        let columnsNames = ["standard_name", "comment"]

                                        let words = filterValue.split(" ");
                                        let filter = [];

                                        columnsNames.forEach(function (column) {
                                            filter.push([]);
                                            words.forEach(function (word) {
                                                filter[filter.length - 1].push([column, "contains", word]);
                                                filter[filter.length - 1].push("and");
                                            });

                                            filter[filter.length - 1].pop();
                                            filter.push("or");
                                        })
                                        filter.pop();
                                        return filter;
                                    }
                                    return this.defaultCalculateFilterExpression(filterValue, selectedFilterOperation);
                                },
                                cellTemplate: function (container, options) {
                                    let quantity;
                                    let amount;

                                    quantity = options.data.quantity ? options.data.quantity + " " : "";
                                    amount = options.data.amount ? options.data.amount + " " : "";

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
                                height: "50vh",
                                width: 500,
                                itemTemplate: function (data) {
                                    let quantity = data.quantity ? data.quantity + " " : "";
                                    let amount = data.amount ? data.amount + " " : "";
                                    let container = $('<div class="standard-name-cell-with-comment"></div>')

                                    switch (data.accounting_type) {
                                        case 2:
                                            let standardNameText = data.standard_name +
                                                ' (' +
                                                quantity +
                                                data.measure_unit_value +
                                                '/' +
                                                amount +
                                                'шт)';

                                            let divStandardName = $(`<div class="standard-name">${standardNameText}</div>`)
                                                .appendTo(container);

                                            if (data.comment) {
                                                let divMaterialComment = $(`<div class="material-comment">${data.comment}</div>`)
                                                    .appendTo(container);

                                            }

                                            return container;
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
                                    switch(currentTransformationStage) {
                                        case transformationStages.fillingMaterialsToTransform:
                                        case transformationStages.fillingMaterialsAfterTransform:
                                            let rowType;
                                            let sortIndex;
                                            switch (currentTransformationStage) {
                                                case transformationStages.fillingMaterialsToTransform:
                                                    rowType = rowTypes.rowData;
                                                    sortIndex = 2;
                                                    break;
                                                case transformationStages.fillingMaterialsAfterTransform:
                                                    rowType = rowTypes.rowData;
                                                    sortIndex = 5;
                                                    break;
                                            }
                                            let validationUid = getValidationUid(material);

                                            let data = {
                                                id: "uid-" + new DevExpress.data.Guid().toString(),
                                                material_id: material.id,
                                                standard_id: material.standard_id,
                                                standard_name: material.standard_name,
                                                accounting_type: material.accounting_type,
                                                material_type: material.material_type,
                                                measure_unit: material.measure_unit,
                                                measure_unit_value: material.measure_unit_value,
                                                standard_weight: material.weight,
                                                quantity: material.quantity,
                                                amount: material.amount,
                                                comment: material.comment,
                                                initial_comment_id: material.comment_id,
                                                initial_comment: material.comment,
                                                total_quantity: material.quantity,
                                                total_amount: material.amount,
                                                brands: material.standard_brands,
                                                rowType: rowType,
                                                rowTransformationStage: currentTransformationStage,
                                                sortIndex: sortIndex,
                                                validationUid: validationUid,
                                                validationState: "unvalidated",
                                                validationResult: "none",
                                                errorMessage: ""
                                            };

                                            insertTransformationRow(data, currentTransformationStage);

                                            validateMaterialList(data.validationUid);
                                            validateStages(null);
                                            break;
                                        /*case "fillingMaterialsRemains":
                                            break;*/
                                    }
                                })

                                //updateRowFooter();

                                /*switch(currentTransformationStage) {
                                    case "fillingMaterialsToTransform":
                                        repaintMaterialsToTransformLayer();
                                        break;
                                    case "fillingMaterialsAfterTransform":
                                        repaintMaterialsAfterTransformLayer();
                                        break;
                                    case "fillingMaterialsRemains":
                                        break;
                                }*/
                                $("#popupContainer").dxPopup("hide");
                            }
                        }
                    }
                ]
            }).dxForm("instance");

            let popupContainer = $("#popupContainer").dxPopup({
                showCloseButton: true,
                height: "auto",
                width: "auto",
                title: "Выберите материалы для добавления"
            });

            //<editor-fold desc="JS: Edit form configuration">
            let operationForm = $("#formContainer").dxForm({
                formData: [],
                colCount: 2,
                items: [{
                    itemType: "group",
                    colCount: 3,
                    caption: "Преобразование",
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
                            value: projectObjectId,
                            onValueChanged: function (e) {
                                function updateComponentsDataSources(projectObjectIdValue) {
                                    projectObjectId = projectObjectIdValue;
                                    availableMaterialsDataSource.reload();
                                }

                                if (suspendSourceObjectLookupValueChanged) {
                                    suspendSourceObjectLookupValueChanged = false;
                                    return;
                                }

                                let oldValue = e.previousValue;
                                let currentValue = e.value;

                                 if (materialsToTransform.length > 0 && e.previousValue !== null) {
                                     let confirmDialog = DevExpress.ui.dialog.confirm('При смене объекта будут удалены введенные данные по материалам операции.<br>Продолжить?', 'Смена объекта');
                                     confirmDialog.done(function (dialogResult) {
                                         if (dialogResult) {
                                             /*updateComponentsDataSources(currentValue);

                                             currentTransformationStage = "fillingMaterialsToTransform";
                                             materialsToTransform = [];
                                             materialsAfterTransform = [];
                                             materialsRemains = [];

                                             repaintMaterialsToTransformLayer();
                                             repaintMaterialsAfterTransformLayer();
                                             repaintMaterialRemains();*/
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
                            text: "Дата преобразования"
                        },
                        editorType: "dxDateBox",
                        editorOptions: {
                            value: Date.now(),
                            max: Date.now(),
                            min: getMinDate(),
                            readOnly: false
                        },
                        validationRules: [{
                            type: "required",
                            message: 'Поле "Дата преобразования" обязательно для заполнения'
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
                },
                    {
                        itemType: "group",
                        caption: "Комментарий",
                        items: [{
                            name: "newCommentTextArea",
                            dataField: "new_comment",
                            label: {
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
                        colSpan: 2,
                        items: [
                            {
                                dataField: "",
                                name: "materialsGroup",
                                itemType: "tabbed",
                                tabs: [
                                    {
                                        title: "Выбор типа преобразования",
                                        name: "transformationTypeSelector",
                                        items: [
                                            {
                                                itemType: "simple",
                                                template: function (data, itemElement) {
                                                    itemElement.append( $(`<div class="transformation-type-selector"/>`));
                                                }
                                            }
                                        ]

                                    },
                                    {
                                        title: "Преобразование",
                                        name: "transformation-tab",
                                        items: [
                                            {
                                                editorType: "dxDataGrid",
                                                name: "transformationGrid",
                                                editorOptions: {
                                                    dataSource: transformationData,
                                                    focusedRowEnabled: false,
                                                    hoverStateEnabled: true,
                                                    columnAutoWidth: false,
                                                    showBorders: true,
                                                    showColumnLines: true,
                                                    paging: {
                                                        enabled: false
                                                    },
                                                    editing: {
                                                        allowUpdating: true,
                                                        mode: "cell",
                                                        selectTextOnEditStart: false,
                                                        startEditAction: "click",
                                                        useIcons: true
                                                    },
                                                    dataRowTemplate(container, item) {
                                                        let markup = getRowMarkup(item.rowIndex, item.data);

                                                        container.append(markup);
                                                    },
                                                    columns: [
                                                        {
                                                            dataField: "sortIndex",
                                                            dataType: "integer",
                                                            sortIndex: 0,
                                                            visible: false
                                                        },
                                                        {
                                                            type: "buttons",
                                                            width: 130,
                                                            allowSorting: false
                                                        },
                                                        {
                                                            dataField: "standard_name",
                                                            dataType: "string",
                                                            allowEditing: false,
                                                            width: "30%",
                                                            caption: "Наименование",
                                                            allowSorting: false
                                                        },
                                                        {
                                                            dataField: "quantity",
                                                            dataType: "number",
                                                            caption: "Количество",
                                                            allowSorting: false,
                                                            editorOptions: {
                                                                min: 0
                                                            },
                                                            showSpinButtons: false
                                                        },
                                                        {
                                                            dataField: "amount",
                                                            dataType: "number",
                                                            caption: "Количество (шт)",
                                                            allowSorting: false,
                                                            editorOptions: {
                                                                min: 0,
                                                                format: "#"
                                                            }
                                                        },
                                                        {
                                                            dataField: "computed_weight",
                                                            dataType: "number",
                                                            allowEditing: false,
                                                            allowSorting: false,
                                                            caption: "Вес"
                                                        }
                                                    ]
                                                }
                                            },

                                        ]

                                    }
                                ]
                            }
                        ]
                    }],
                onContentReady: () => {
                    materialTransformationTypesDataSource.load();
                }

            }).dxForm("instance")
            //</editor-fold>

            function repaintGUI(){
                switch (currentTransformationStage) {
                    case transformationStages.transformationTypesSelection:
                        repaintTransformationTypeSelectionLayer();
                        break;
                }
            }

            function repaintTransformationTypeSelectionLayer(){
                let transformTypeLayer = $('.transformation-type-selector');
                let transformTypes = materialTransformationTypesDataSource.items();

                transformTypes.forEach(element => {
                    let transformationTypeLayer = $(`<div class="transformation-type-item" transformation-type-name="${element.value}" transformation-type-codename="${element.codename}"/>`).append(
                        $(`<div class="transformation-type-text">${element.value}</div>`)
                    )

                    transformationTypeLayer.click(() => {
                        let data = {
                            name: `Шаг 1: Добавьте материалы для преобразования «${element.value}»`,
                            rowType: rowTypes.rowHeader,
                            sortIndex: 1
                        }
                        insertTransformationRow(data, transformationStages.fillingMaterialsToTransform);

                        currentTransformationType = element.codename;
                    })

                    transformTypeLayer.append(transformationTypeLayer)
                })
            }

            function getRowMarkup(rowIndex, data) {
                let markup;

                let row = $(`<tr>`);
                switch (data.rowType) {
                    case rowTypes.rowHeader:
                        markup = $(`<td colspan="5" class="transformation-header">`);
                        let caption = $(`<div class="transformation-header-caption">${data.name}</div>`);
                        markup.append(caption);

                        let appendMaterialButton;
                        let nextStageButton;

                        switch (currentTransformationStage) {
                            case transformationStages.fillingMaterialsToTransform:
                                appendMaterialButton = $(`<div class="transformation-header-button">`).dxButton({
                                    text: `Добавить материал`,
                                    type: `normal`,
                                    onClick: () => {
                                        showMaterialsAddingForm();
                                    }
                                });

                                nextStageButton = $(`<div class="transformation-header-button">`).dxButton({
                                    text: `Далее`,
                                    type: `normal`,
                                    onClick: () => {
                                        let data = {
                                            name: `Шаг 2: Добавьте материалы после преобразования`,
                                            rowType: rowTypes.rowHeader,
                                            sortIndex: 4
                                        }
                                        insertTransformationRow(data, transformationStages.fillingMaterialsAfterTransform);
                                    }
                                });

                                markup.append(nextStageButton);
                                markup.append(appendMaterialButton);

                                break;
                            case transformationStages.fillingMaterialsAfterTransform:
                                appendMaterialButton = $(`<div class="transformation-header-button">`).dxButton({
                                    text: `Добавить материал`,
                                    type: `normal`,
                                    onClick: () => {
                                        showMaterialsAddingForm();
                                    }
                                });

                                nextStageButton = $(`<div class="transformation-header-button">`).dxButton({
                                    text: `Далее`,
                                    type: `normal`,
                                    onClick: () => {
                                        let data = {
                                            name: `Шаг 3: Укажите остатки матералов`,
                                            rowType: rowTypes.rowHeader,
                                            sortIndex: 7
                                        }
                                        insertTransformationRow(data, transformationStages.fillingMaterialsRemains);
                                        insertMaterialsRemains();

                                        data = {
                                            name: `Шаг 4: Укажите технологические потери (из-за резки или торцовки материала)`,
                                            rowType: rowTypes.rowHeader,
                                            sortIndex: 10
                                        }
                                        insertTransformationRow(data, transformationStages.fillingMaterialsTechnologicalLosses);
                                        insertMaterialsTechnologicalLosses()
                                    }
                                });

                                markup.append(nextStageButton);
                                markup.append(appendMaterialButton);

                                break;
                        }
                        row.append(markup);
                        break;

                    case rowTypes.rowData:
                        row.addClass("dx-row")
                            .addClass("dx-data-row")
                            .addClass("dx-row-lines")
                            .addClass("dx-column-lines");
                        let controlRow = $(`<td class="dx-command-edit dx-command-edit-with-icons dx-cell-focus-disabled"/>`).append(getControlRowLayer(rowIndex, data));
                        let standardName = $("<td/>").append(getStandardNameLayer(rowIndex, data));
                        let quantity = $(`<td aria-describedby="dx-col-2" aria-selected="false" role="gridcell" aria-colindex="2" style="text-align: right;"/>`).append(getQuantityLayer(rowIndex, data));
                        let amount = $(`<td aria-describedby="dx-col-3" aria-selected="false" role="gridcell" aria-colindex="3" style="text-align: right;"/>`).append(getAmountLayer(rowIndex, data));
                        let computedWeight = $(`<td class="computed-weight" rowIndex="${rowIndex}"/>`).append(getComputedWeightLayer(rowIndex, data));

                        row.append(controlRow);
                        row.append(standardName);
                        row.append(quantity);
                        row.append(amount);
                        row.append(computedWeight);

                        break;

                    case rowTypes.rowFooter:
                        let footerStageErrorLayer = $(`<td colspan ="2" class="footer-validation-cell" rowIndex="${rowIndex}"/>`).append(getFooterStageErrorLayer(rowIndex, data));

                        row.addClass("dx-row")
                            .addClass("dx-footer-row")
                            .addClass("dx-row-lines")
                            .addClass("dx-column-lines")
                            .attr("uid", data.id)
                            .append(footerStageErrorLayer)
                            .append($(`<td><div class="dx-datagrid-summary-item dx-datagrid-text-content quantity-total-summary">${data.quantity} ${data.measure_unit_value}</div></td>`))
                            .append($(`<td><div class="dx-datagrid-summary-item dx-datagrid-text-content amount-total-summary">${data.amount} шт</div></td>`))
                            .append($(`<td><div class="dx-datagrid-summary-item dx-datagrid-text-content weight-total-summary">${data.weight} т</div></td>`))
                        break;
                }

                return row;
            }

            function showMaterialsAddingForm() {
                let dataSource = materialStandardsListDataSource;

                switch (currentTransformationStage) {
                    case transformationStages.fillingMaterialsToTransform:
                        dataSource = availableMaterialsDataSource
                        break;
                    case transformationStages.fillingMaterialsAfterTransform:
                        dataSource = materialStandardsListDataSource;
                        break;
                }

                dataSource.filter(getMaterialAddingFormFilter())

                let materialsList = materialsStandardsAddingForm.getEditor("materialsStandardsList");
                materialsList.option("dataSource", dataSource);
                dataSource.reload();
                materialsList.option("selectedRowKeys", []);

                $("#popupContainer").dxPopup("show")
            }

            function insertTransformationRow(dataToInsert, transformationStage) {
                transformationData.store().insert(dataToInsert).done(() => {
                    currentTransformationStage = transformationStage;
                    transformationData.reload();

                    if (dataToInsert.rowType === rowTypes.rowFooter) {
                        validateStages(null);
                    }

                    if (dataToInsert.rowType === rowTypes.rowData) {
                        updateRowFooter();
                    }
                })
            }

            function getStandardNameLayer(rowIndex, data){
                let divStandardName = $(`<div class="standard-name"></div>`)

                $(`<div>${data.standard_name}</div>`)
                    .appendTo(divStandardName);

                if (data.comment) {
                    $(`<div class="material-comment">${data.comment}</div>`)
                        .appendTo(divStandardName);

                    divStandardName.addClass("standard-name-cell-with-comment");
                }

                return divStandardName;
            }

            function getQuantityLayer(rowIndex, data){
            let isReadOnly = false;

            switch (data.rowTransformationStage) {
                case transformationStages.fillingMaterialsToTransform:
                    isReadOnly = true;
                    break;//data.rowType === rowTypes.rowMaterialsToTransform;
            }

                let quantity = Math.round(data.quantity * 100) / 100;

                if (isReadOnly) {
                    if (quantity) {
                        return $(`<div class="transformation-quantity"><span>${quantity} ${data.measure_unit_value}</span></div>`)
                    } else {
                        return $(`<div class="transformation-quantity measure-units-only"><span>${data.measure_unit_value}</span></div>`)
                    }
                } else {
                    let quantityLayer = $(
                        `<div class="measure-units-only without-box-shadow dx-show-invalid-badge dx-numberbox dx-texteditor dx-editor-outlined dx-texteditor-empty dx-widget">` +
                        `</div>`
                    );

                    quantityLayer.dxNumberBox({
                        min: 0,
                        value: quantity,
                        format: "#0.## " + data.measure_unit_value,
                        placeholder: data.measure_unit_value,
                        mode: "number",
                        onValueChanged: (e) => {
                            e.component.option("format", "#0.## " + data.measure_unit_value);
                            transformationData.store()
                                .update(data.id, {quantity: e.value})
                                .done(() => {
                                    updateComputedWeightLayer(rowIndex, data);
                                    validateMaterialList(data.validationUid);
                                    validateStages(null);
                                });
                            operationForm.getEditor("transformationGrid").endUpdate();
                        },
                        onFocusIn: (e) => {
                            e.component.option("format", "");
                        },
                        onFocusOut: (e) => {
                            e.component.option("format", "#0.## " + data.measure_unit_value);
                        },
                    });

                    return quantityLayer;
                }
            }

            function getAmountLayer(rowIndex, data){
                let isReadOnly = false;

                let amount = data.amount;

                if (isReadOnly) {
                    if (amount) {
                        return $(`<div>${amount} шт</div>`)
                    } else {
                        return $(`<div class="measure-units-only">шт</div>`)
                    }
                } else {
                    let amountLayer = $(
                        `<div class="measure-units-only without-box-shadow dx-show-invalid-badge dx-numberbox dx-texteditor dx-editor-outlined dx-texteditor-empty dx-widget">` +
                        `</div>`
                    );

                    amountLayer.dxNumberBox({
                        min: 0,
                        value: amount,
                        format: "#0 шт",
                        placeholder: "шт",
                        mode: "number",
                        onValueChanged: (e) => {
                            e.component.option("format", "#0 шт");
                            transformationData.store()
                                .update(data.id, {amount: e.value})
                                .done(() => {
                                    updateComputedWeightLayer(rowIndex, data);
                                    validateMaterialList(data.validationUid);
                                    validateStages(null);
                                });

                        },
                        onFocusIn: (e) => {
                            e.component.option("format", "");
                        },
                        onFocusOut: (e) => {
                            e.component.option("format", "# шт");
                        },
                    });

                    return amountLayer;
                }
            }

            function getComputedWeightLayer(rowIndex, data) {
                let weight = data.quantity * data.amount * data.standard_weight;

                if (weight) {
                    weight = Math.round(weight * 1000) / 1000
                } else {
                    weight = 0;
                }

                return $(`<div>${weight} т</div>`)
            }

            function updateComputedWeightLayer(rowIndex, data) {
                $(`.computed-weight[rowIndex=${rowIndex}]`).html(getComputedWeightLayer(rowIndex, data));
            }

            function getControlRowLayer(rowIndex, data) {
                let controlRowLayer = $('<div class="command-row-buttons"/>');
                let validationUid = data.validationUid;
                let validationDiv = $(`<div class="row-validation-indicator"/>`)
                    .attr("validation-uid", validationUid)

                switch(data.validationResult) {
                    case "valid":
                        let checkIcon = $(`<i/>`)
                            .addClass(`dx-link dx-icon fas fa-check-circle dx-link-icon`)
                            .attr(`style`, `color: #8bc34a`)
                            .appendTo(validationDiv);
                        break;
                    case "invalid":
                        let exclamationTriangle = $(`<i/>`).addClass(`dx-link fas fa-exclamation-triangle`)
                            .attr(`style`, `color: #f15a5a`)
                            .mouseenter(function () {
                                if (!data.errorMessage) {
                                    return;
                                }

                                let validationDescription = $('#validationTemplate');

                                validationDescription.dxPopover({
                                    position: "top",
                                    width: 300,
                                    contentTemplate: data.errorMessage,
                                    hideEvent: "mouseleave",
                                })
                                    .dxPopover("instance")
                                    .show($(this));
                            })
                            .appendTo(validationDiv);
                        break;
                }

                let deleteRowDiv = $(`<div row-index="${rowIndex}"><a href="#" class="dx-link dx-icon-trash dx-link-icon" title="Удалить" row-index="${rowIndex}"></a></div>`)
                    .attr("validation-uid", validationUid);

                deleteRowDiv.click((e) => {
                    e.preventDefault();

                    operationForm.getEditor("transformationGrid").deleteRow(rowIndex);

                    validateMaterialList(data.validationUid);
                    validateStages(null);
                    })

                let duplicateRowDiv = $(`<div class=""><a href="#" class="dx-link dx-icon-copy dx-link-icon" title="Дублировать"></a></div>`)
                    .attr("validation-uid", validationUid)
                    .click((e) => {
                        e.preventDefault();

                        let clonedItemId = "uid-" + new DevExpress.data.Guid().toString();

                        let clonedItem = $.extend({},
                            data, {
                                id: clonedItemId,
                                edit_states: ["addedByRecipient"],
                                validationUid: getValidationUid(data),
                            }
                        );

                        transformationData.store().insert(clonedItem).done(() => {
                            transformationData.reload();
                            validateMaterialList(data.validationUid);
                            validateStages(null);
                        });
                    })

                controlRowLayer.append(validationDiv);
                controlRowLayer.append(deleteRowDiv);
                controlRowLayer.append(duplicateRowDiv);

                return controlRowLayer;
            }

            function getFooterStageErrorLayer(rowIndex, data){
                let validationUid = data.validationUid;
                let validationDiv = $(`<div class="footer-row-validation"/>`).attr("validation-uid", validationUid);
                let validationIconDiv = $(`<div class="footer-row-validation-indicator"/>`);


                let validationMessageDiv = $(`<div class="footer-row-validation-message"/>`);

                switch(data.validationResult) {
                    case "valid":
                        let checkIcon = $(`<i/>`)
                            .addClass(`dx-icon fas fa-check-circle`)
                            .attr(`style`, `color: #8bc34a;font-size: 16px;`)
                            .appendTo(validationIconDiv);

                        validationMessageDiv.html("Проблемы не обнаружены");
                        break;
                    case "invalid":
                        let exclamationTriangle = $(`<i/>`).addClass(`dx-icon fas fa-exclamation-triangle`)
                            .attr(`style`, `color: #f15a5a;font-size: 16px;`)
                            .appendTo(validationIconDiv);
                            validationMessageDiv.html(data.errorMessage);
                        break;
                }
                validationDiv.append(validationIconDiv);
                validationDiv.append(validationMessageDiv);
                return validationDiv;
            }

            function getValidationUid(material) {
                let filterConditions;

                switch (material.accounting_type) {
                    case 2:
                        if (!material.quantity || !material.amount) {
                            return "uid-" + new DevExpress.data.Guid().toString();
                        } else {
                            filterConditions = [["standard_id", "=", material.standard_id],
                                "and",
                                ["quantity", "=", material.quantity],
                                "and",
                                ["amount", ">", 0],
                                "and",
                                ["initial_comment_id", "=", material.initial_comment_id],
                                "and",
                                ["rowType", "=", material.rowType]];
                        }
                        break;
                    default:
                        filterConditions = [["standard_id", "=", material.standard_id],
                            "and",
                            ["initial_comment_id", "=", material.initial_comment_id],
                            "and",
                            ["rowType", "=", material.rowType]];
                }

                let filteredData = transformationData.store().createQuery()
                    .filter(filterConditions)
                    .toArray();

                if (filteredData.length > 0) {
                    return filteredData[0].validationUid
                } else {
                    return "uid-" + new DevExpress.data.Guid().toString();
                }
            }

            function validateMaterialList(validationUid) {
                function validateQuantity(material) {
                    if (material.rowType === rowTypes.rowData) {
                        if (!material.quantity) {
                            return ({
                                severity: 1000,
                                codename: "null_quantity",
                                message: "Количество в единицах измерения не заполнено",
                                standard_name: material.standard_name
                            })
                        }
                    }
                }

                function validateAmount(material) {
                    if (material.rowType === rowTypes.rowData) {
                        if (!material.amount) {
                            return ({
                                severity: 1000,
                                codename: "null_amount",
                                message: "Количество в штуках не заполнено",
                                standard_name: material.standard_name
                            })
                        }
                    }
                }

                function validateTotalRemains(material) {
                    if (material.rowType !== rowTypes.rowData) {
                        return;
                    }

                    let filterArray = [];
                    filterArray.push(["rowType", "=", rowTypes.rowData]);
                    filterArray.push("and");
                    filterArray.push(["rowTransformationStage", "=", material.rowTransformationStage])
                    filterArray.push("and");
                    filterArray.push(["standard_id", "=", material.standard_id]);
                    filterArray.push("and");
                    filterArray.push(["initial_comment_id", "=", material.initial_comment_id]);

                    switch (material.accounting_type) {
                        case 2:
                            filterArray.push("and");
                            filterArray.push(["quantity", "=", material.quantity]);
                            break;
                        default:
                    }

                    let materialsToCalculateTotalAmount = transformationData.store().createQuery()
                        .filter(filterArray)
                        .toArray();

                    let materialToTransferTotalAmount = 0;

                    materialsToCalculateTotalAmount.forEach((materialToCalculateTotalAmount) => {
                        switch (materialToCalculateTotalAmount.accounting_type) {
                            case 2:
                                materialToTransferTotalAmount += materialToCalculateTotalAmount.amount;
                                break;
                            default:
                                materialToTransferTotalAmount += Math.round(materialToCalculateTotalAmount.quantity * materialToCalculateTotalAmount.amount / 100) * 100;
                        }
                    })

                    if (material.total_amount < materialToTransferTotalAmount) {
                        return ({
                            severity: 1000,
                            codename: "amount_is_larger_than_total_amount",
                            message: "На объекте недостаточно материала",
                            standard_name: material.standard_name
                        })
                    }
                }

                function updateValidationResult(validationData, validationResult, validationFunction) {
                    validationData.forEach((material) => {
                        let validationResponse = validationFunction(material);
                        if (validationResponse) {
                            validationResult.validationInfo.push(validationResponse);
                        }
                    })
                }

                let validationData;

                if (validationUid) {
                    validationData = transformationData.store().createQuery()
                        .filter(['validationUid', '=', validationUid])
                        .toArray();
                } else {
                    validationData = transformationData.store().createQuery()
                        .toArray();
                }

                let validationResult = {validationUid: validationUid, validationInfo: []};

                console.log('validationData', validationData);


                if (validationData.rowTransformationStage === transformationStages.fillingMaterialsToTransform || validationData.rowTransformationStage === transformationStages.fillingMaterialsAfterTransform) {
                    updateValidationResult(validationData, validationResult, validateQuantity);
                    updateValidationResult(validationData, validationResult, validateAmount);
                }

                updateValidationResult(validationData, validationResult, validateTotalRemains);

                validationResult.isValid = validationResult.validationInfo.length === 0;

                if (!validationResult.isValid) {
                    let validationErrors = [];

                    validationResult.validationInfo.forEach((validationError) => {
                        validationErrors.push(validationError.message);
                    })

                    validationResult.errorMessage = (Array.from(new Set(validationErrors))).join("<br>");
                }

                console.log("validationResult", validationResult);

                updateValidationData(validationResult);
            }

            function validateStages() {
                function getStageFooterValidationUid(transformationStage){
                    let footerData = transformationData.store().createQuery()
                        .filter([["rowTransformationStage", "=", transformationStage],
                            "and",
                            ["rowType", "=", rowTypes.rowFooter]])
                        .toArray();

                    if (footerData.length > 0) {
                        return footerData[0].validationUid;
                    }
                }

                function validateMaterialToTransformStage() {
                    let footerValidationUid = getStageFooterValidationUid(transformationStages.fillingMaterialsToTransform);

                    if (!footerValidationUid) {
                        return;
                    }

                    let validationResult = {validationUid: footerValidationUid, validationInfo: []};

                    let summary = calculateMaterialSummariesByStage(transformationStages.fillingMaterialsToTransform, "brands");

                    let materialsToTransformMaterials = getMaterialsByStage(transformationStages.fillingMaterialsToTransform);

                    let isAnyOfMaterialInvalid = false;

                    materialsToTransformMaterials.forEach((material) => {
                        if (material.validationResult === "invalid") {
                            isAnyOfMaterialInvalid = true;
                        }
                    })

                    if (isAnyOfMaterialInvalid) {
                        validationResult.validationInfo.push({
                            severity: 1000,
                            codename: "some_materials_in_stage_invalid",
                            message: `Данные у некоторых материалов введены некорректно`,
                            //standard_name: material.standard_name
                        })
                    }

                    validationResult.isValid = validationResult.validationInfo.length === 0;

                    if (!validationResult.isValid) {
                        let validationErrors = [];

                        validationResult.validationInfo.forEach((validationError) => {
                            validationErrors.push(validationError.message);
                        })

                        validationResult.errorMessage = (Array.from(new Set(validationErrors))).join("<br>");
                    }

                    updateValidationData(validationResult);

                    updateSummaries(calculateMaterialSummariesByStage(transformationStages.fillingMaterialsToTransform, "brands"), transformationStages.fillingMaterialsToTransform);
                }

                function validateMaterialAfterTransformStage() {
                    let footerValidationUid = getStageFooterValidationUid(transformationStages.fillingMaterialsAfterTransform);

                    if (!footerValidationUid) {
                        return;
                    }

                    let validationResult = {validationUid: footerValidationUid, validationInfo: []};

                    let materialsToTransform = getMaterialsByStage(transformationStages.fillingMaterialsToTransform, "brands");
                    let materialsAfterTransform = getMaterialsByStage(transformationStages.fillingMaterialsAfterTransform, "brands");

                    materialsToTransform.forEach((toSummaryBrand) => {
                        let isBrandFound = false;

                        materialsAfterTransform.forEach((afterSummaryBrand) => {
                            if (toSummaryBrand.key === afterSummaryBrand.key) {
                                isBrandFound = true;
                            }
                        })

                        if (!isBrandFound) {
                            validationResult.validationInfo.push({
                                severity: 1000,
                                codename: "some_brands_not_found",
                                message: `Не все марки материалов добавлены в список`,
                                //standard_name: material.standard_name
                            })
                        }
                    })

                    let isAnyOfMaterialInvalid = false;

                    materialsAfterTransform = getMaterialsByStage(transformationStages.fillingMaterialsAfterTransform);

                    materialsAfterTransform.forEach((material) => {
                        if (material.validationResult === "invalid") {
                            isAnyOfMaterialInvalid = true;
                        }
                    })

                    if (isAnyOfMaterialInvalid) {
                        validationResult.validationInfo.push({
                            severity: 1000,
                            codename: "some_materials_in_stage_invalid",
                            message: `Данные у некоторых материалов введены некорректно`,
                            //standard_name: material.standard_name
                        })
                    }

                    validationResult.isValid = validationResult.validationInfo.length === 0;

                    if (!validationResult.isValid) {
                        let validationErrors = [];

                        validationResult.validationInfo.forEach((validationError) => {
                            validationErrors.push(validationError.message);
                        })

                        validationResult.errorMessage = (Array.from(new Set(validationErrors))).join("<br>");
                    }

                    updateValidationData(validationResult);

                    updateSummaries(calculateMaterialSummariesByStage(transformationStages.fillingMaterialsAfterTransform, "brands"), transformationStages.fillingMaterialsAfterTransform);
                }

                function validateFillingRemainsTransformStage() {
                    let footerValidationUid = getStageFooterValidationUid(transformationStages.fillingMaterialsRemains);

                    if (!footerValidationUid) {
                        return;
                    }

                    let validationResult = {validationUid: footerValidationUid, validationInfo: []};

                    let materialsToTransform = getMaterialsByStage(transformationStages.fillingMaterialsToTransform, "brands");
                    let materialsAfterTransform = getMaterialsByStage(transformationStages.fillingMaterialsAfterTransform, "brands");
                    let materialsRemains = getMaterialsByStage(transformationStages.fillingMaterialsRemains, "brands");
                    let materialsTechnologicalLosses = getMaterialsByStage(transformationStages.fillingMaterialsTechnologicalLosses, "brands");

                    checkTotalMaterialSummaryAfterTransformation();

                    let isAnyOfMaterialInvalid = false;


                    let materialsToTransformSummary = calculateMaterialSummariesByStage(transformationStages.fillingMaterialsToTransform, "brands");
                    let materialsAfterTransformSummary = calculateMaterialSummariesByStage(transformationStages.fillingMaterialsAfterTransform, "brands");
                    let materialsRemainsSummary = calculateMaterialSummariesByStage(transformationStages.fillingMaterialsRemains, "brands");
                    let materialsTechnologicalLossesSummary = calculateMaterialSummariesByStage(transformationStages.fillingMaterialsTechnologicalLosses, "brands")


                    console.log(`materialsToTransformSummary`, materialsToTransformSummary);
                    console.log(`materialsRemainsSummary`, materialsRemainsSummary);

                    let Total

                    if (isAnyOfMaterialInvalid) {
                        validationResult.validationInfo.push({
                            severity: 1000,
                            codename: "some_materials_in_stage_invalid",
                            message: `Данные у некоторых материалов введены некорректно`,
                            //standard_name: material.standard_name
                        })
                    }

                    validationResult.isValid = validationResult.validationInfo.length === 0;

                    if (!validationResult.isValid) {
                        let validationErrors = [];

                        validationResult.validationInfo.forEach((validationError) => {
                            validationErrors.push(validationError.message);
                        })

                        validationResult.errorMessage = (Array.from(new Set(validationErrors))).join("<br>");
                    }

                    updateValidationData(validationResult);

                    updateSummaries(calculateMaterialSummariesByStage(transformationStages.fillingMaterialsRemains, "brands"), transformationStages.fillingMaterialsRemains);
                }

                function validateTechnologicalLossesTransformStage() {
                    let footerValidationUid = getStageFooterValidationUid(transformationStages.fillingMaterialsTechnologicalLosses);

                    if (!footerValidationUid) {
                        return;
                    }

                    let validationResult = {validationUid: footerValidationUid, validationInfo: []};

                    let materialsToTransform = getMaterialsByStage(transformationStages.fillingMaterialsToTransform, "brands");
                    let materialsAfterTransform = getMaterialsByStage(transformationStages.fillingMaterialsAfterTransform, "brands");
                    let materialsRemains = getMaterialsByStage(transformationStages.fillingMaterialsRemains, "brands");
                    let materialsTechnologicalLosses = getMaterialsByStage(transformationStages.fillingMaterialsTechnologicalLosses, "brands");

                    let materialsTotalSummary = [];

                    materialsAfterTransform.forEach((item) => {
                        let isBrandFound = false;
                        materialsTotalSummary.forEach((totalSummaryItem) => {
                            if (totalSummaryItem.brand_id === item.brand_id) {
                                isBrandFound = true;
                                totalSummaryItem.quantity += item.quantity;
                                totalSummaryItem.weight += item.weight;
                            }
                        })

                        if (!isBrandFound) {
                            materialsTotalSummary.push({quantity: item.quantity, weight: item.weight});
                        }
                    })

                    console.log(`totalSummaryItem`, materialsTotalSummary)

                    let isAnyOfMaterialInvalid = false;

                    materialsTechnologicalLosses = getMaterialsByStage(transformationStages.fillingMaterialsTechnologicalLosses);

                    materialsTechnologicalLosses.forEach((material) => {
                        if (material.validationResult === "invalid") {
                            isAnyOfMaterialInvalid = true;
                        }
                    })

                    if (isAnyOfMaterialInvalid) {
                        validationResult.validationInfo.push({
                            severity: 1000,
                            codename: "some_materials_in_stage_invalid",
                            message: `Данные у некоторых материалов введены некорректно`,
                            //standard_name: material.standard_name
                        })
                    }

                    validationResult.isValid = validationResult.validationInfo.length === 0;

                    if (!validationResult.isValid) {
                        let validationErrors = [];

                        validationResult.validationInfo.forEach((validationError) => {
                            validationErrors.push(validationError.message);
                        })

                        validationResult.errorMessage = (Array.from(new Set(validationErrors))).join("<br>");
                    }

                    updateValidationData(validationResult);

                    updateSummaries(calculateMaterialSummariesByStage(transformationStages.fillingMaterialsTechnologicalLosses, "brands"), transformationStages.fillingMaterialsTechnologicalLosses);
                }

                function checkTotalMaterialSummaryAfterTransformation() {
                    let totalSummary = [];

                    function addToTotalSummary(summaryArray) {
                        summaryArray.forEach((itemAfterTransform) => {
                            let isBrandFound = false;
                            totalSummary.forEach((totalSummaryItem) => {
                                if (totalSummaryItem.brands === itemAfterTransform.brands) {
                                    isBrandFound = true;
                                    totalSummaryItem.quantity += itemAfterTransform.quantity;
                                    totalSummaryItem.weight += itemAfterTransform.weight;
                                }
                            })

                            if (!isBrandFound) {
                                totalSummary.push({quantity: itemAfterTransform.quantity, weight: itemAfterTransform.weight, brands: itemAfterTransform.brands});
                            }
                        })
                    }

                    let materialsToTransform = calculateMaterialSummariesByStage(transformationStages.fillingMaterialsToTransform, "brands");
                    let materialsAfterTransform = calculateMaterialSummariesByStage(transformationStages.fillingMaterialsAfterTransform, "brands");
                    let materialsRemains = calculateMaterialSummariesByStage(transformationStages.fillingMaterialsRemains, "brands");
                    let materialsTechnologicalLosses = calculateMaterialSummariesByStage(transformationStages.fillingMaterialsTechnologicalLosses, "brands");

                    addToTotalSummary(materialsAfterTransform);
                    addToTotalSummary(materialsRemains);
                    addToTotalSummary(materialsTechnologicalLosses);

                    materialsToTransform.forEach((item) => {
                        totalSummary.forEach((totalSummaryItem) => {

                        })
                    })

                    console.log(`addToTotalSummary`, totalSummary);


                    return null;
                }

                function updateSummaries(summary, transformationStage) {
                    let data = transformationData.store().createQuery()
                        .filter([["rowTransformationStage", "=", transformationStage],
                            "and",
                            ["rowType", "=", rowTypes.rowFooter]])
                        .toArray();

                    if (data.length === 1){
                        let totalStageSummary = {quantity: 0, amount: 0, weight: 0};
                        summary.forEach((item) => {
                            totalStageSummary.quantity += item.quantity;
                            totalStageSummary.amount += item.amount;
                            totalStageSummary.weight += item.weight;
                        })

                        if (!totalStageSummary.quantity) {
                            totalStageSummary.quantity = 0;
                        } else {
                            totalStageSummary.quantity = Math.round(totalStageSummary.quantity * 100) / 100;
                        }

                        if (!totalStageSummary.amount) {
                            totalStageSummary.amount = 0;
                        }

                        if (!totalStageSummary.weight) {
                            totalStageSummary.weight = 0;
                        } else {
                            totalStageSummary.weight = Math.round(totalStageSummary.weight * 1000) / 1000;
                        }

                        transformationData.store().update(
                            data[0].id,
                            {
                                quantity: totalStageSummary.quantity,
                                amount: totalStageSummary.amount,
                                weight: totalStageSummary.weight
                            });
                    }
                }

                function getMaterialsByStage(transformationStage, groupBy){
                    let filterArray = [["rowTransformationStage", "=", transformationStage],
                        "and",
                        ["rowType", "=", rowTypes.rowData]];

                    let dataToCalculate = transformationData.store().createQuery()
                        .filter(filterArray);

                    if (groupBy) {
                        dataToCalculate = dataToCalculate.groupBy(groupBy)
                    }

                    return dataToCalculate.toArray();
                }

                function calculateMaterialSummariesByStage(transformationStage, groupBy) {
                    let dataToCalculate = getMaterialsByStage(transformationStage, groupBy);

                    let summaryResult = [];

                    dataToCalculate.forEach((data) => {
                        let summaryStructure = {quantity: 0, amount: 0, weight: 0}
                        data.items.forEach((item) => {
                            if (item.quantity && item.amount) {
                                summaryStructure.quantity += item.quantity * item.amount;
                            }

                            if (item.amount) {
                                summaryStructure.amount += item.amount;
                            }

                            if (item.quantity && item.amount) {
                                summaryStructure.weight += item.quantity * item.amount * item.standard_weight;
                            }

                            if (groupBy) {
                                summaryStructure[groupBy] = item[groupBy];
                            }
                        })

                        summaryResult.push(summaryStructure);
                    })

                    return summaryResult;
                }

                validateMaterialToTransformStage();
                validateMaterialAfterTransformStage();
                validateFillingRemainsTransformStage();
                validateTechnologicalLossesTransformStage();
            }

            function updateValidationData(validationData) {
                let validatedData = transformationData.store().createQuery()
                    .filter(["validationUid", "=", validationData.validationUid])
                    .toArray();

                validatedData.forEach((material) => {
                    let validationState = "validated";
                    let validationResult;

                    if (validationData.isValid) {
                        validationResult = "valid"
                    } else {
                        validationResult = "invalid"
                    }

                    transformationData.store().update(material.id, {validationState: validationState, validationResult: validationResult, errorMessage: validationData.errorMessage})
                        .done(() => {
                            transformationData.reload();
                        });
                })
            }

            function getMinDate() {
                let minDate = new Date();

                return minDate.setDate(minDate.getDate() - 3);
            }

            function getMaterialAddingFormFilter() {
                let filterArray = [];
                switch (currentTransformationStage) {
                    case transformationStages.fillingMaterialsToTransform:
                        switch (currentTransformationType) {
                            case "CUTTING":
                                filterArray = [ "accounting_type", "=", "2" ]
                                break;
                            default:
                                filterArray = null
                        }
                        break;
                    case transformationStages.fillingMaterialsAfterTransform:
                        switch (currentTransformationType) {
                            case "CUTTING":
                                let transformationDataArray = transformationData.store().createQuery()
                                    .filter(['standard_id', '>', 0])
                                    .toArray();

                                let brands = [];
                                let uniqueBrands = [];
                                let brandsFilterArray = []
                                transformationDataArray.forEach((item) => {
                                    brands.push(item.brands)
                                });

                                uniqueBrands = Array.from(new Set(brands));

                                uniqueBrands.forEach((item) => {
                                    brandsFilterArray.push([
                                        'standard_brands',
                                        '=',
                                        item
                                    ]);
                                    brandsFilterArray.push('or');
                                })

                                brandsFilterArray.pop();

                                if (brandsFilterArray.length > 0) {
                                    filterArray.push(brandsFilterArray);
                                    filterArray.push("and");
                                }

                                filterArray.push(["standard_properties", "=", null])
                                filterArray.push("and")
                                filterArray.push(["accounting_type", "=", "2"])

                                console.log('filterArray', filterArray);
                                break;
                            default:
                                filterArray = null
                        }
                        break;
                }
                return filterArray;
            }

            function insertMaterialsRemains() {
                let materialsRemains = [];
                let standardFound = false;
                let materialsToTransform = transformationData.store().createQuery()
                    .filter(["rowType", "=", rowTypes.rowData],
                        "and",
                        ["rowTransformationStage", "=", transformationStages.fillingMaterialsToTransform])
                    .toArray()

                materialsToTransform.forEach((material) => {
                    materialsRemains.forEach((remainMaterial) => {
                        if (remainMaterial.standard_id === material.standard_id) {
                            standardFound = true;
                        }
                    });

                    if (!standardFound) {
                        let data = {
                            id: "uid-" + new DevExpress.data.Guid().toString(),
                            standard_id: material.standard_id,
                            standard_name: material.standard_name,
                            accounting_type: material.accounting_type,
                            material_type: material.material_type,
                            measure_unit: material.measure_unit,
                            measure_unit_value: material.measure_unit_value,
                            standard_weight: material.standard_weight,
                            quantity: 0,
                            amount: 0,
                            comment: null,
                            initial_comment_id: null,
                            initial_comment: null,
                            total_quantity: material.quantity,
                            total_amount: material.amount,
                            brands: material.brands,
                            validationUid: "uid-" + new DevExpress.data.Guid().toString(),
                            validationState: "unvalidated",
                            validationResult: "none",
                            rowType: rowTypes.rowData,
                            rowTransformationStage: transformationStages.fillingMaterialsRemains,
                            sortIndex: 8
                        }

                        materialsRemains.push(data);
                        insertTransformationRow(data, transformationStages.fillingMaterialsRemains);
                        standardFound = false;
                        validateMaterialList(data.validationUid);
                    }
                })
            }

            function insertMaterialsTechnologicalLosses() {
                let materialsTechnologicalLosses = [];
                let standardFound = false;
                let materialsToTransfer = transformationData.store().createQuery()
                    .filter(["rowType", "=", rowTypes.rowData],
                        "and",
                        ["rowTransformationStage", "=", transformationStages.fillingMaterialsToTransform])
                    .toArray()

                materialsToTransfer.forEach((material) => {
                    materialsTechnologicalLosses.forEach((technologicalLossesMaterial) => {
                        if (technologicalLossesMaterial.standard_id === material.standard_id) {
                            standardFound = true;
                        }
                    });

                    if (!standardFound) {
                        let data = {
                            id: "uid-" + new DevExpress.data.Guid().toString(),
                            standard_id: material.standard_id,
                            standard_name: material.standard_name,
                            accounting_type: material.accounting_type,
                            material_type: material.material_type,
                            measure_unit: material.measure_unit,
                            measure_unit_value: material.measure_unit_value,
                            standard_weight: material.standard_weight,
                            quantity: 0,
                            amount: 0,
                            comment: null,
                            initial_comment_id: null,
                            initial_comment: null,
                            total_quantity: material.quantity,
                            total_amount: material.amount,
                            brands: material.brands,
                            validationUid: "uid-" + new DevExpress.data.Guid().toString(),
                            validationState: "unvalidated",
                            validationResult: "none",
                            rowType: rowTypes.rowData,
                            rowTransformationStage: transformationStages.fillingMaterialsTechnologicalLosses,
                            sortIndex: 11
                        }

                        materialsTechnologicalLosses.push(data);
                        insertTransformationRow(data, transformationStages.fillingMaterialsTechnologicalLosses);
                        validateMaterialList(data.validationUid);
                        standardFound = false;
                    }
                })
            }

            function updateRowFooter() {
                console.log("updateRowFooter transformationData", transformationData.store().createQuery().toArray());

                let data = transformationData.store().createQuery()
                    .filter([["rowType", "=", rowTypes.rowData],
                        "and",
                        ["rowTransformationStage", "=", currentTransformationStage]
                    ])
                    .toArray();

                let footerData = transformationData.store().createQuery()
                    .filter([["rowType", "=", rowTypes.rowFooter],
                        "and",
                        ["rowTransformationStage", "=", currentTransformationStage]
                    ])
                    .toArray();

                let isFooterAlreadyInserted = footerData.length !== 0;

                if (data.length === 0) {
                    deleteRowFooter()
                } else {
                    if (!isFooterAlreadyInserted) {
                        insertFooterRow();
                    }
                }
            }

            function insertFooterRow() {
                let sortIndex = 0;
                switch (currentTransformationStage) {
                    case transformationStages.fillingMaterialsToTransform:
                        sortIndex = 3;
                        break;
                    case transformationStages.fillingMaterialsAfterTransform:
                        sortIndex = 6;
                        break;
                    case transformationStages.fillingMaterialsRemains:
                        sortIndex = 9;
                        break;
                    case transformationStages.fillingMaterialsTechnologicalLosses:
                        sortIndex = 12;
                        break;
                }

                let data = {
                    id: "uid-" + new DevExpress.data.Guid().toString(),
                    rowType: rowTypes.rowFooter,
                    quantity: 0,
                    amount: 0,
                    weight: 0,
                    measure_unit_value: 'м.п',
                    rowTransformationStage: currentTransformationStage,
                    sortIndex: sortIndex,
                    validationUid: "uid-" + new DevExpress.data.Guid().toString(),
                    validationState: "unvalidated",
                    validationResult: "none",
                    errorMessage: ""
                }

                insertTransformationRow(data, currentTransformationStage);

                console.log('transformationData array', transformationData.store().createQuery().toArray());
            }

            function deleteRowFooter() {

            }
        });
    </script>
@endsection
