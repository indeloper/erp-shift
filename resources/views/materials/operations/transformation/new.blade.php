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
                fillingMaterialsToTransform: Symbol("fillingMaterialsToTransform")
            });

            let materialTypesData = {!!$materialTypes!!};
            let materialErrorList = [];

            let projectObjectId = {{$projectObjectId}};

            let materialsToTransform = [];
            let materialsAfterTransform = [];
            let materialsRemains = [];

            let currentTransformationStage = transformationStages.transformationTypesSelection;
            let isUserIsResponsibleForMaterialAccounting = false;


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

            let supplyMaterialData = [];

            let supplyMaterialStore = new DevExpress.data.ArrayStore({
                key: "id",
                data: supplyMaterialData
            })

            let supplyMaterialDataSource = new DevExpress.data.DataSource({
                reshapeOnPush: true,
                store: supplyMaterialStore
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
                            scrolling: {
                                mode: 'virtual'
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
                                    let comment;

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
                                height: () => {
                                    return 400/*$(document).height() - ($(document).height()/100*20)*/;
                                },
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

                                            break;
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
                                        case "fillingMaterialsToTransform":
                                            materialsToTransform.push({
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
                                                total_amount: material.amount
                                            });
                                            break;
                                        case "fillingMaterialsAfterTransform":
                                            materialsAfterTransform.push({
                                                id: "uid-" + new DevExpress.data.Guid().toString(),
                                                standard_id: material.standard_id,
                                                standard_name: material.standard_name,
                                                accounting_type: material.accounting_type,
                                                material_type: material.material_type,
                                                measure_unit: material.measure_unit,
                                                measure_unit_value: material.measure_unit_value,
                                                standard_weight: material.weight,
                                                quantity: null,
                                                amount: null,
                                                comment: material.comment,
                                                initial_comment_id: material.comment_id,
                                                initial_comment: material.comment,
                                                total_quantity: material.quantity,
                                                total_amount: material.amount
                                            });
                                            break;
                                        case "fillingMaterialsRemains":
                                            break;
                                    }
                                })


                                switch(currentTransformationStage) {
                                    case "fillingMaterialsToTransform":
                                        repaintMaterialsToTransformLayer();
                                        break;
                                    case "fillingMaterialsAfterTransform":
                                        repaintMaterialsAfterTransformLayer();
                                        break;
                                    case "fillingMaterialsRemains":
                                        break;
                                }
                                $("#popupContainer").dxPopup("hide");
                                validateMaterialList(false, false);
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

                                    }
                                ]
                            }
                        ]
                    }],
                onContentReady: e => {
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
                    transformTypeLayer.append(
                        $(`<div class="transformation-type-item"/>`).append(
                            $(`<div class="transformation-type-text">${element.value}</div>`)
                        )
                    )
                })
            }

            function getTransformationType() {
                switch(operationForm.getEditor("transformationTypeSelectBox").option("value")) {
                    case 1:
                        return "cutting"
                    case 2:
                        return "lengthDocking";
                    case 3:
                        return "corningManufacturing";
                    case 4:
                        return "wedgeShapedProduction";
                    case 5:
                        return "pairProduction";
                    case 6:
                        return "corningCutting";
                    default:
                        return "none"
                }
            }

            function getMinDate() {
                let minDate = new Date();

                return minDate.setDate(minDate.getDate() - 3);
            }
        });
    </script>
@endsection
