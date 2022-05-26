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
            const transformationStages = Object.freeze({
                transformationTypesSelection: Symbol("transformationTypesSelection"),
                fillingMaterialsToTransform: Symbol("fillingMaterialsToTransform"),
                fillingMaterialsAfterTransform: Symbol("fillingMaterialsAfterTransform"),
                fillingMaterialsRemainsAndTechnologicalLosses: Symbol("fillingMaterialsRemainsAndTechnologicalLosses"),
            });

            const rowTypes = Object.freeze({
                rowHeader: Symbol("rowHeader"),
                rowFooter: Symbol("rowHeader"),
                rowMaterialsToTransform: Symbol("rowMaterialsToTransform"),
                rowMaterialsAfterTransform: Symbol("rowMaterialsToTransform"),
                rowMaterialsRemains: Symbol("rowMaterialsRemains"),
                rowMaterialsTechnologicalLosses: Symbol("rowMaterialsTechnologicalLosses")
            });

            let materialTypesData = {!!$materialTypes!!};

            let projectObjectId = {{$projectObjectId}};

            let currentTransformationStage = transformationStages.transformationTypesSelection;
            let currentTransformationType = "";

            let transformationData = new DevExpress.data.DataSource({
                store: new DevExpress.data.ArrayStore({
                    key: "id",
                    data: []
                })
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

                                        columnsNames.forEach(function (column, index) {
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
                                            switch (currentTransformationStage) {
                                                case transformationStages.fillingMaterialsToTransform:
                                                    rowType = rowTypes.rowMaterialsToTransform;
                                                    break;
                                                case transformationStages.fillingMaterialsAfterTransform:
                                                    rowType = rowTypes.rowMaterialsAfterTransform;
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
                                                validationUid: validationUid,
                                                validationState: "unvalidated",
                                                validationResult: "none",
                                            };

                                            insertTransformationRow(data, currentTransformationStage)
                                            break;
                                        /*case "fillingMaterialsRemains":
                                            break;*/
                                    }
                                })


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
                                /*validateMaterialList(false, false);*/
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
                                                            type: "buttons",
                                                            width: 130,
                                                        },
                                                        {
                                                            dataField: "standard_name",
                                                            dataType: "string",
                                                            allowEditing: false,
                                                            width: "30%",
                                                            caption: "Наименование"
                                                        },
                                                        {
                                                            dataField: "quantity",
                                                            dataType: "number",
                                                            caption: "Количество",
                                                            editorOptions: {
                                                                min: 0
                                                            },
                                                            showSpinButtons: false
                                                        },
                                                        {
                                                            dataField: "amount",
                                                            dataType: "number",
                                                            caption: "Количество (шт)",
                                                            editorOptions: {
                                                                min: 0,
                                                                format: "#"
                                                            }
                                                        },
                                                        {
                                                            dataField: "computed_weight",
                                                            dataType: "number",
                                                            allowEditing: false,
                                                            caption: "Вес"
                                                        }
                                                    ],
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
                                                    }
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
                            rowType: rowTypes.rowHeader
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
                                            rowType: rowTypes.rowHeader
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
                                            rowType: rowTypes.rowHeader
                                        }
                                        insertTransformationRow(data, transformationStages.fillingMaterialsRemainsAndTechnologicalLosses);
                                        insertMaterialsRemains();

                                        data = {
                                            name: `Шаг 4: Укажите технологические потери`,
                                            rowType: rowTypes.rowHeader
                                        }
                                        insertTransformationRow(data, transformationStages.fillingMaterialsRemainsAndTechnologicalLosses);
                                        insertMaterialsTechnologicalLosses()
                                    }
                                });

                                markup.append(nextStageButton);
                                markup.append(appendMaterialButton);

                                break;
                        }
                        row.append(markup);
                        break;

                    case rowTypes.rowMaterialsToTransform:
                    case rowTypes.rowMaterialsAfterTransform:
                    case rowTypes.rowMaterialsRemains:
                        row.addClass("dx-row")
                            .addClass("dx-data-row")
                            .addClass("dx-row-lines")
                            .addClass("dx-column-lines");
                        let controlRow = $("<td/>").append(getControlRowLayer(rowIndex, data));
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
                        row.addClass("dx-row")
                            .addClass("dx-footer-row");

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

            switch (currentTransformationStage) {
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
                let controlRowLayer = $('<div/>');
                let validationUid = data.validationUid;
                let validationDiv = $(`<div class="row-validation-indicator"/>`)
                    .attr("validation-uid", validationUid)

                let deleteRowDiv = $(`<div class=""><a href="#" class="dx-link dx-icon-trash dx-link-icon" title="Удалить"></a></div>`)
                    .attr("validation-uid", validationUid)

                let duplicateRowDiv = $(`<div class=""><a href="#" class="dx-link dx-icon-copy dx-link-icon" title="Дублировать"></a></div>`)
                    .attr("validation-uid", validationUid)

                console.log(validationUid);

                controlRowLayer.append(validationDiv);
                controlRowLayer.append(deleteRowDiv);
                controlRowLayer.append(duplicateRowDiv);

                return controlRowLayer;
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
                                ["initial_comment_id", "=", material.initial_comment_id]];
                        }
                        break;
                    default:
                        filterConditions = [["standard_id", "=", material.standard_id],
                            "and",
                            ["initial_comment_id", "=", material.initial_comment_id]];
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

            /*function validateMaterialList(saveEditedData, showErrorWindowOnHighSeverity, validationUid, userAction) {
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
                    operationId: operationData.id,
                    timestamp: new Date(),
                    userAction: userAction,
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
                            setButtonIndicatorVisibleState("*", false)
                            setElementsDisabledState(false);
                        }

                        if (!needToShowErrorWindow){
                            if (saveEditedData) {
                                saveOperationData(userAction);
                            }
                        }
                    },
                    error: function (e) {
                        DevExpress.ui.notify("При проверке данных произошла неизвестная ошибка", "error", 5000)
                        setButtonIndicatorVisibleState("*", false)
                        setElementsDisabledState(false);
                    }
                });
            }*/

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
                                    uniqueBrands.push('or');
                                })

                                uniqueBrands.pop();

                                if (brandsFilterArray.length > 0) {
                                    filterArray.push(brandsFilterArray);
                                    filterArray.push("and");
                                }

                                filterArray.push(["accounting_type", "=", "2"])
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
                let materialsToTransfer = transformationData.store().createQuery()
                    .filter("rowType", "=", rowTypes.rowMaterialsToTransform)
                    .toArray()

                materialsToTransfer.forEach((material) => {
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
                            standard_weight: material.weight,
                            quantity: 0,
                            amount: 0,
                            comment: null,
                            initial_comment_id: null,
                            initial_comment: null,
                            total_quantity: material.quantity,
                            total_amount: material.amount,
                            brands: material.standard_brands,
                            rowType: rowTypes.rowMaterialsRemains
                        };
                        materialsRemains.push(data);
                        insertTransformationRow(data, transformationStages.fillingMaterialsRemainsAndTechnologicalLosses);
                        standardFound = false;
                    }
                })
            }

            function insertMaterialsTechnologicalLosses() {
                let materialsTechnologicalLosses = [];
                let standardFound = false;
                let materialsToTransfer = transformationData.store().createQuery()
                    .filter("rowType", "=", rowTypes.rowMaterialsToTransform)
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
                            standard_weight: material.weight,
                            quantity: 0,
                            amount: 0,
                            comment: null,
                            initial_comment_id: null,
                            initial_comment: null,
                            total_quantity: material.quantity,
                            total_amount: material.amount,
                            brands: material.standard_brands,
                            rowType: rowTypes.rowMaterialsRemains
                        };
                        materialsTechnologicalLosses.push(data);
                        insertTransformationRow(data, transformationStages.fillingMaterialsRemainsAndTechnologicalLosses);
                        standardFound = false;
                    }
                })
            }
        });
    </script>
@endsection
