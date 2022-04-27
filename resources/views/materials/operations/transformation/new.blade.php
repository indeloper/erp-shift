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

        .transform-element {
            margin-top: 8px;
            display: inline-flex;
            align-content: center;
            flex-direction: row;
            align-items: center;
            font-size: larger;
        }

        .transform-element>.transformation-number-box, .transform-element>.transformation-comment-box {
            display: inline-block;
            margin: 8px;
            max-width: 6%;
            min-width: 60px;
        }


        .transform-element>.transformation-comment-box {
            max-width: 20%;
            min-width: 200px;
        }


        .transform-wizard-button {
            margin: 8px 8px 8px 0;
        }

        .transform-wizard-caption {
            font-weight: bold;
            font-size: larger;
            color: darkslategray;
        }

        .transformation-validator {
            display: inline-flex;
            align-content: center;
            align-items: center;
        }

        .fa-exclamation-triangle, .fa-trash-alt, .fa-copy, .fa-check-circle{
            font-size: larger;
            margin-right: 8px;
        }

        .fa-check-circle {
            color: #006100
        }

        #materialsToTransformElements, #materialsAfterTransformElements, #materialsRemainsTransformElements {
            display: flex;
            flex-direction: column;
        }

        .transformation-standard-name-cell {
            min-width: 20%;
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
            let measureUnitData = {!!$measureUnits1 ?? '[]'!!};
            let projectObject = {{$projectObjectId}};
            let materialTypesData = {!!$materialTypes!!};
            let materialErrorList = [];

            let projectObjectId = {{$projectObjectId}};

            let materialsToTransform = [];
            let materialsAfterTransform = [];
            let materialsRemains = [];

            let currentTransformationStage = "fillingMaterialsToTransform";
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
                store: availableMaterialsStore,
                filter: [ "accounting_type", "=", "2" ]
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
                store: materialStandardsListStore,
                filter: [ "accounting_type", "=", "2" ]
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
                                             updateComponentsDataSources(currentValue);

                                             currentTransformationStage = "fillingMaterialsToTransform";
                                             materialsToTransform = [];
                                             materialsAfterTransform = [];
                                             materialsRemains = [];

                                             repaintMaterialsToTransformLayer();
                                             repaintMaterialsAfterTransformLayer();
                                             repaintMaterialRemains();
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
                        colSpan: 1,
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

                    },
                    {
                        name: "transformationTypeSelectBox",
                        colSpan: 1,
                        dataField: "transformation_type_id",
                        label: {
                            text: "Тип преобразования"
                        },
                        editorType: "dxSelectBox",
                        editorOptions: {
                            dataSource: materialTransformationTypesDataSource,
                            displayExpr: "value",
                            valueExpr: "id",
                            searchEnabled: true,
                            value: null,
                            onValueChanged: () => {
                                repaintMaterialRemains();
                            }
                        },
                        validationRules: [{
                            type: "required",
                            message: 'Поле "Тип преобразования" обязательно для заполнения'
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
                        items: [{
                            dataField: "",
                            name: "materialsToTransformLayer",
                            template: function (data, itemElement) {
                                itemElement.append( $("<div id='materials-to-transform'>"));
                                itemElement.append( $("<div id='materials-after-transform'>"));
                                itemElement.append( $("<div id='materials-remains'>"));

                                repaintTransformLayers();
                            }
                        }
                        ]
                    }]

            }).dxForm("instance")
            //</editor-fold>

            //<editor-fold desc="JS: Toolbar configuration">
            //</editor-fold>

            function saveOperationData() {
                let transformationOperationData = {};

                transformationOperationData.project_object_id = operationForm.option("formData").project_object_id;
                //TODO Дата формируется в UTC. Нужно либо учитывать это при перобразовании, либо хранить в UTC в БД
                transformationOperationData.operation_date = new Date(operationForm.option("formData").operation_date).toJSON().split("T")[0];
                transformationOperationData.responsible_user_id = operationForm.option("formData").responsible_user_id;
                transformationOperationData.new_comment = operationForm.option("formData").new_comment;
                transformationOperationData.transformation_type_id = operationForm.option("formData").transformation_type_id;

                transformationOperationData.materialsToTransform = materialsToTransform;
                transformationOperationData.materialsAfterTransform = materialsAfterTransform;
                transformationOperationData.materialsRemains = materialsRemains;

                postEditingData(transformationOperationData);
            }

            function validateMaterialList(saveEditedData, showErrorWindowOnHighSeverity) {
                setButtonIndicatorVisibleState(true);
                setElementsDisabledState(true);

                let transformationOperationData = {
                    materialsToTransform: materialsToTransform,
                    materialsAfterTransform: materialsAfterTransform,
                    materialsRemains: materialsRemains,
                    projectObjectId: operationForm.option("formData").project_object_id,
                    transformationStage: currentTransformationStage
                };
                $.ajax({
                    url: "{{route('materials.operations.transformation.new.validate-material-list')}}",
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType: "json",
                    data: {
                        transformationOperationData
                    },
                    success: function (e) {
                        $('.fa-exclamation-triangle').attr('style', 'display:none');
                        $('.validator-description-text').attr('style', 'display:none');
                        if (saveEditedData) {
                            saveOperationData();
                        } else {
                            setButtonIndicatorVisibleState(false);
                            setElementsDisabledState(false);
                        }
                    },
                    error: function (e) {
                        if (e.responseJSON.result === 'error') {
                            let needToShowErrorWindow = false;

                            $('.fa-exclamation-triangle').attr('style', 'display:none');
                            e.responseJSON.errors.forEach((errorElement) => {
                                updateValidationExclamationTriangles($('.transform-element[validationId=' + errorElement.validationId.toString().replaceAll('.', '\\.') + ']'), errorElement);
                                errorElement.errorList.forEach((errorItem) => {
                                    if (showErrorWindowOnHighSeverity) {
                                        if (errorItem.severity > 500) {
                                            needToShowErrorWindow = true;
                                        }
                                    }
                                })
                            })

                            if (needToShowErrorWindow) {
                                showErrorWindow(e.responseJSON.errors);
                            }
                            materialErrorList = e.responseJSON.errors;
                        } else {
                            DevExpress.ui.notify("При проверке данных произошла неизвестная ошибка", "error", 5000)
                        }
                        setButtonIndicatorVisibleState(false);
                        setElementsDisabledState(true);
                    }
                });
            }

            function updateValidationExclamationTriangles(element, errorElement) {
                let maxSeverity = 0;
                let errorDescription = "";
                let exclamationTriangleStyle = ""

                let validatorLayer = element.find('.transformation-validator');
                let validationIcon = element.find('.validator-icon');
                validationIcon.empty();
                validationIcon.append('<i class="fas fa-exclamation-triangle">');

                let validationDescription = element.find('.validator-description');
                validationDescription.empty();

                let descriptionArray = [];

                errorElement.errorList.forEach((errorItem) => {
                    if (errorItem.severity > maxSeverity) {
                        maxSeverity = errorItem.severity;
                    }
                    descriptionArray.push(errorItem.message);
                })

                validationDescription.append($('<span class = "validator-description-text">' + descriptionArray.join(" | ") + '</span>'));

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

                validatorLayer.attr('style', exclamationTriangleStyle);
                validatorLayer.attr('severity', maxSeverity);
            }

            function postEditingData(supplyOperationData) {
                $.ajax({
                    url: "{{route('materials.operations.transformation.new')}}",
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        data: JSON.stringify(supplyOperationData)
                    },

                    success: function (data, textStatus, jqXHR) {
                        window.location.href = '{{route('materials.index')}}/?project_object=' + projectObjectId
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        DevExpress.ui.notify("При сохранении данных произошла ошибка", "error", 5000);
                        setButtonIndicatorVisibleState(false)
                        setElementsDisabledState(false);
                    }
                })
            }

            function setElementsDisabledState(state){
                let currentButtonClass = "";

                switch (currentTransformationStage) {
                    case "fillingMaterialsToTransform":
                        currentButtonClass = "button-materials-to-transfer";
                        break;
                    case "fillingMaterialsAfterTransform":
                        currentButtonClass = "button-materials-after-transfer";
                        break;
                    case "fillingMaterialsRemains":
                        currentButtonClass = "createTransformationOperationButton";
                        break;
                }
                let button = $('.' + currentButtonClass).dxButton("instance");
                if (button) {
                    $('.' + currentButtonClass).dxButton("instance").option("disabled", state);
                }
            }

            function setButtonIndicatorVisibleState(state){
                let currentButtonClass = "";

                switch (currentTransformationStage) {
                    case "fillingMaterialsToTransform":
                        currentButtonClass = "button-materials-to-transfer";
                        break;
                    case "fillingMaterialsAfterTransform":
                        currentButtonClass = "button-materials-after-transfer";
                        break;
                    case "fillingMaterialsRemains":
                        currentButtonClass = "createTransformationOperationButton";
                        break;
                }

                let loadingIndicator = $('.' + currentButtonClass)
                    .find(".button-loading-indicator").dxLoadIndicator("instance");
                if (loadingIndicator) {
                    loadingIndicator.option('visible', state);
                }
            }

            function showErrorWindow(errorList){
                let htmlMessage = "";
                errorList.forEach((errorElement) => {
                    errorElement.errorList.forEach((errorItem) => {
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
                        if ( errorItem.itemName) {
                            htmlMessage += errorItem.itemName + ': ' + errorItem.message;
                        } else {
                            htmlMessage += errorItem.message;
                        }
                        htmlMessage += '</p>'
                    })
                });

                DevExpress.ui.dialog.alert(htmlMessage, "При сохранении операции обнаружены ошибки");
            }

            function repaintTransformLayers() {
                repaintMaterialsToTransformLayer();
                repaintMaterialsAfterTransformLayer();
                repaintMaterialRemains();
            }

            function repaintMaterialsToTransformLayer() {
                const transformationHeaderStageText = 'Добавьте материалы для преобразования';
                const transformationHeaderText = 'Добавленные материалы:';

                let layer = $('#materials-to-transform');

                layer.empty();

                if (currentTransformationStage === "fillingMaterialsToTransform") {
                    layer.append($('<span class="transform-wizard-caption">' + transformationHeaderStageText + '</span>'));
                } else {
                    layer.append($('<span class="transform-wizard-caption">' + transformationHeaderText + '</span>'));
                }
                layer.append($('<div id="materialsToTransformElements"></div>'));

                let elements = layer.find('#materialsToTransformElements');

                materialsToTransform.forEach(function (material) {

                    let isQuantityControlDisabled = material.accounting_type === 2;
                    let isAmountControlDisabled = currentTransformationStage !== "fillingMaterialsToTransform"
                    let validationId = "0";
                    let standardId = "";
                    let quantity = "";

                    if (material.quantity) {
                        quantity = material.quantity;
                    }

                    if (material.standard_id) {
                        standardId = material.standard_id;
                    }

                    switch (material.accounting_type) {
                        case 2:
                            validationId = standardId + "-" + quantity
                            break;
                        default:
                            validationId = standardId;
                    }

                    elements.append($('<div class="transform-element" validationId="' + validationId + '"></div>'));

                    let element = layer.find('.transform-element').last();

                    if (currentTransformationStage === "fillingMaterialsToTransform"){
                        element.append($('<a href="#" class="dx-link far fa-trash-alt" uid="' + material.id + '"></a>'));
                        element.find('.fa-trash-alt').click(function (e) {
                            e.preventDefault();

                            materialsToTransform.forEach((material, index) => {
                                if (material.id === $(this).attr("uid")) {
                                    console.log(materialsToTransform);
                                    materialsToTransform.splice(index, 1);
                                    repaintMaterialsToTransformLayer();
                                    validateMaterialList(false, false);
                                }

                            });

                            return false;
                        });
                    }
                    let standardNameElement = $('<div class="standard-name-cell-with-comment transformation-standard-name-cell"/>');
                    element.append(standardNameElement);

                    standardNameElement.append($(`<div class="standard-name">${material.standard_name}</div>`));

                    if (material.initial_comment) {
                        standardNameElement.append($(`<div class="material-comment">${material.initial_comment}</div>`));
                    }

                    element.append($('<div class="transformation-number-box transformation-quantity" uid="' + material.id + '" material-id = "' + material.material_id + '"></div>')
                        .dxNumberBox({
                            min: 0,
                            value: material.quantity,
                            format: "#0.## " + material.measure_unit_value,
                            placeholder: material.measure_unit_value,
                            disabled: isQuantityControlDisabled,
                            onValueChanged: (e) => {
                                e.component.option("format", "#0.## " + material.measure_unit_value);
                                material.quantity = e.value;
                                validateMaterialList(false, false);
                            },
                            onFocusIn: (e) => {
                                e.component.option("format", "");
                            },
                            onFocusOut: (e) => {
                                e.component.option("format", "#0.## " + material.measure_unit_value);
                            }
                        }))

                    if (material.accounting_type === 2) {
                        element.append($('<div class="transformation-number-box transformation-amount" uid="' + material.id + '" material-id = "' + material.material_id + '"></div>')
                            .dxNumberBox({
                                min: 0,
                                format: "#0 шт.",
                                value: material.amount,
                                disabled: isAmountControlDisabled,
                                onValueChanged: (e) => {
                                    e.component.option("format", "#0 шт");
                                    material.amount = e.value;
                                    validateMaterialList(false, false);
                                },
                                onFocusIn: (e) => {
                                    e.component.option("format", "");
                                },
                                onFocusOut: (e) => {
                                    e.component.option("format", "#0 шт");
                                }
                            }))
                    }

                    element.append($('<div class="transformation-validator" uid="' + material.id + '" material-id = "' + material.material_id + '">' +
                        '<div class="validator-icon"></div>' +
                        '<div class="validator-description"></div>' +
                        '</div>'))
                });

                if (currentTransformationStage === "fillingMaterialsToTransform") {
                    layer.append($('<div class="transform-wizard-button">').dxButton({
                        text: () => {
                            if (materialsToTransform.length > 0) {
                                return "Добавить еще"
                            } else {
                                return "Добавить"
                            }
                        },
                        type: "default",
                        stylingMode: "outlined",
                        onClick: (e) => {
                            let materialsList = materialsStandardsAddingForm.getEditor("materialsStandardsList");
                            materialsList.option("dataSource", availableMaterialsDataSource);
                            availableMaterialsDataSource.reload();
                            materialsList.option("selectedRowKeys", []);

                            $("#popupContainer").dxPopup("show")
                        }
                    }));


                    if (materialsToTransform.length > 0) {
                        layer.append($('<div class="transform-wizard-button button-materials-to-transfer">').dxButton({
                            text: "Далее",
                            type: "default",
                            stylingMode: "contained",
                            template: function(data, container) {
                                $("<div class='button-loading-indicator'></div><span class='dx-button-text'>" + data.text + "</span>").appendTo(container);
                                let loadingIndicator = container.find(".button-loading-indicator").dxLoadIndicator({
                                    visible: false
                                }).dxLoadIndicator("instance");
                            },
                            onClick: (e) => {
                                currentTransformationStage = "fillingMaterialsAfterTransform";
                                //materialsRemains = JSON.parse(JSON.stringify(materialsToTransform));
                                materialsToTransform.forEach((material) => {
                                    let pushElement = true;

                                    materialsRemains.forEach((materialRemain) => {
                                        if (material.standard_id === materialRemain.standard_id) {
                                            pushElement = false;
                                        }
                                    })

                                    if (pushElement) {
                                        materialsRemains.push(JSON.parse(JSON.stringify(material)))
                                    }
                                })

                                materialsRemains.forEach((material) => {
                                    material.amount = null;
                                    material.quantity = null;
                                })

                                repaintTransformLayers();
                            }
                        }));
                    }
                }
            }

            function repaintMaterialsAfterTransformLayer(){
                let layer = $('#materials-after-transform');
                layer.empty();

                if (currentTransformationStage === "fillingMaterialsToTransform"){
                    return
                }

                const transformationHeaderStageText = 'Добавьте преобразованные материалы';
                const transformationHeaderText = 'Пребразованные материалы:';

                let isQuantityControlDisabled = currentTransformationStage !== "fillingMaterialsAfterTransform";
                let isAmountControlDisabled = currentTransformationStage !== "fillingMaterialsAfterTransform";

                layer.append($('<hr>'));

                if (currentTransformationStage === "fillingMaterialsAfterTransform") {
                    layer.append($('<span class="transform-wizard-caption">' + transformationHeaderStageText + '</span>'));
                } else {
                    layer.append($('<span class="transform-wizard-caption">' + transformationHeaderText + '</span>'));
                }
                layer.append($('<div id="materialsAfterTransformElements"></div>'));

                let elements = layer.find('#materialsAfterTransformElements');

                materialsAfterTransform.forEach(function (material) {
                    let validationId = 0;
                    let standardId = "";
                    let quantity = "";

                    if (material.quantity) {
                        quantity = material.quantity;
                    }

                    if (material.standard_id) {
                        standardId = material.standard_id;
                    }

                    switch (material.accounting_type) {
                        case 2:
                            validationId = standardId + "-" + quantity
                            break;
                        default:
                            validationId = standardId;
                    }

                    elements.append($('<div class="transform-element" validationId="' + validationId + '"></div>'));
                    let element = layer.find('.transform-element').last();

                    if (currentTransformationStage === "fillingMaterialsAfterTransform"){
                        element.append($('<a href="#" class="dx-link far fa-copy" uid="' + material.id + '"></a>'));
                        element.find('.fa-copy').click(function (e) {
                            e.preventDefault();
                            materialsAfterTransform.forEach((material, index) => {
                                if (material.id === $(this).attr("uid")) {
                                    let clonedItem = $.extend({}, material, {id: "uid-" + new DevExpress.data.Guid().toString()});
                                    materialsAfterTransform.push(clonedItem);
                                    repaintMaterialsAfterTransformLayer();
                                    validateMaterialList(false, false);
                                }
                            })
                        });

                        element.append($('<a href="#" class="dx-link far fa-trash-alt" uid="' + material.id + '"></a>'));
                        element.find('.fa-trash-alt').click(function (e) {
                            e.preventDefault();

                            materialsAfterTransform.forEach((material, index) => {
                                console.log(materialsAfterTransform);
                                if (material.id === $(this).attr("uid")) {
                                    console.log(materialsAfterTransform);
                                    materialsAfterTransform.splice(index, 1);
                                    repaintMaterialsAfterTransformLayer();
                                    validateMaterialList(false, false);
                                }

                            });

                            return false;
                        });
                    }

                    let standardNameElement = $('<div class="standard-name-cell-with-comment transformation-standard-name-cell"/>');
                    element.append(standardNameElement);

                    standardNameElement.append($(`<div class="standard-name">${material.standard_name}</div>`));

                    element.append($('<div class="transformation-number-box transformation-quantity" uid="' + material.id + '" material-id = "' + material.material_id + '"></div>')
                        .dxNumberBox({
                            min: 0,
                            value: material.quantity,
                            format: "#0.## " + material.measure_unit_value,
                            disabled: isQuantityControlDisabled,
                            placeholder: material.measure_unit_value,
                            onValueChanged: (e) => {
                                e.component.option("format", "#0.## " + material.measure_unit_value);
                                material.quantity = e.value;
                                repaintMaterialsAfterTransformLayer();
                                validateMaterialList(false, false);
                            },
                            onFocusIn: (e) => {
                                e.component.option("format", "");
                            },
                            onFocusOut: (e) => {
                                e.component.option("format", "#0.## " + material.measure_unit_value);
                            }
                        }))

                    if (material.accounting_type === 2) {
                        element.append($('<div class="transformation-number-box transformation-amount" uid="' + material.id + '" material-id = "' + material.material_id + '"></div>')
                            .dxNumberBox({
                                min: 0,
                                format: "#0 шт",
                                value: material.amount,
                                disabled: isAmountControlDisabled,
                                placeholder: "шт",
                                onValueChanged: (e) => {
                                    e.component.option("format", "#0 шт");
                                    material.amount = e.value;
                                    repaintMaterialsAfterTransformLayer();
                                    validateMaterialList(false, false);
                                },
                                onFocusIn: (e) => {
                                    e.component.option("format", "");
                                },
                                onFocusOut: (e) => {
                                    e.component.option("format", "#0 шт");
                                }
                            }))

                        element.append($('<div class="transformation-comment-box transformation-comment" uid="' + material.id + '" material-id = "' + material.material_id + '"></div>')
                            .dxTextEditor({
                                value: material.comment,
                                disabled: isAmountControlDisabled,
                                placeholder: "Введите комментарий...",
                                onValueChanged: (e) => {
                                    material.comment = e.value;
                                }
                            }))
                    }

                    element.append($('<div class="transformation-validator" uid="' + material.id + '" material-id = "' + material.material_id + '">' +
                        '<div class="validator-icon"></div>' +
                        '<div class="validator-description"></div>' +
                        '</div>'))
                });

                if (currentTransformationStage === "fillingMaterialsAfterTransform") {
                    if (materialsAfterTransform.length <= 0) {
                        layer.append($('<div class="transform-wizard-button">').dxButton({
                            text: "Добавить",
                            type: "default",
                            stylingMode: "outlined",
                            onClick: (e) => {
                                let materialsList = materialsStandardsAddingForm.getEditor("materialsStandardsList");
                                materialsList.option("dataSource", materialStandardsListDataSource);
                                materialStandardsListDataSource.reload();
                                materialsList.option("selectedRowKeys", []);

                                $("#popupContainer").dxPopup("show")
                            }
                        }));
                    }


                    if (materialsAfterTransform.length > 0) {
                        layer.append($('<div class="transform-wizard-button button-materials-after-transfer">').dxButton({
                            text: "Далее",
                            type: "default",
                            stylingMode: "outlined",
                            template: function(data, container) {
                                $("<div class='button-loading-indicator'></div><span class='dx-button-text'>" + data.text + "</span>").appendTo(container);
                                let loadingIndicator = container.find(".button-loading-indicator").dxLoadIndicator({
                                    visible: false
                                }).dxLoadIndicator("instance");
                            },
                            onClick: (e) => {
                                setButtonIndicatorVisibleState(true)
                                setElementsDisabledState(true);

                                $.ajax({
                                    url: "{{route('materials.transformation.is-user-responsible-for-material-accounting')}}",
                                    method: "POST",
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    },
                                    data: {
                                        data: JSON.stringify({project_object_id: projectObjectId})
                                    },
                                    success: function (data, textStatus, jqXHR) {
                                        isUserIsResponsibleForMaterialAccounting = data.isUserResponsibleForMaterialAccounting;

                                        currentTransformationStage = "fillingMaterialsRemains";
                                        repaintTransformLayers();
                                        setButtonIndicatorVisibleState(false)
                                        setElementsDisabledState(false);

                                        console.log("isUserIsResponsibleForMaterialAccounting", isUserIsResponsibleForMaterialAccounting);
                                    },
                                    error: function (jqXHR, textStatus, errorThrown) {
                                        isUserIsResponsibleForMaterialAccounting = false;

                                        currentTransformationStage = "fillingMaterialsRemains";
                                        repaintTransformLayers();
                                        setButtonIndicatorVisibleState(false)
                                        setElementsDisabledState(false);
                                    }
                                })
                            }
                        }));
                    }
                }
            }

            function repaintMaterialRemains() {
                let layer = $('#materials-remains');
                layer.empty();

                if (currentTransformationStage !== "fillingMaterialsRemains") {
                    return
                }

                const transformationHeaderStageText = 'Распределите остатки исходных материалов';

                layer.append($('<hr>'));

                layer.append($('<span class="transform-wizard-caption">' + transformationHeaderStageText + '</span>'));

                layer.append($('<div id="materialsRemainsTransformElements"></div>'));

                let elements = layer.find('#materialsRemainsTransformElements');

                materialsRemains.forEach(function (material) {
                    elements.append($('<div class="transform-element" standard-id= "' + material.standard_id + '"></div>'));
                    let element = layer.find('.transform-element').last();
                    let remainsSummary = calculateRemains(material.standard_id);

                    if (currentTransformationStage === "fillingMaterialsRemains"){
                        element.append($('<a href="#" class="dx-link far fa-copy" uid="' + material.id + '"></a>'));
                        element.find('.fa-copy').click(function (e) {
                            e.preventDefault();
                            materialsRemains.forEach((material, index) => {
                                if (material.id === $(this).attr("uid")) {
                                    let clonedItem = $.extend({}, material, {id: "uid-" + new DevExpress.data.Guid().toString()});
                                    materialsRemains.push(clonedItem);
                                    repaintMaterialRemains();
                                    //validateMaterialList(false, false);
                                }
                            })
                        });

                        if ($('.transform-element[standard-id="' +  material.standard_id + '"]').length > 1) {
                            element.append($('<a href="#" class="dx-link far fa-trash-alt material-remains-trash" standard-id= "' + material.standard_id + '" uid="' + material.id + '"></a>'));
                            element.find('.fa-trash-alt').click(function (e) {
                                e.preventDefault();

                                materialsRemains.forEach((material, index) => {
                                    if (material.id === $(this).attr("uid")) {
                                        materialsRemains.splice(index, 1);
                                        repaintMaterialRemains();
                                        //validateMaterialList(false, false);
                                    }

                                });

                                return false;
                            });
                        }
                    }

                    let standardNameElement = $('<div class="standard-name-cell-with-comment transformation-standard-name-cell"/>');
                    element.append(standardNameElement);

                    standardNameElement.append($(`<div class="standard-name">${material.standard_name}</div>`));

                    element.append($('<div class="transformation-number-box transformation-quantity" uid="' + material.id + '" material-id = "' + material.material_id + '"></div>')
                        .dxNumberBox({
                            min: 0,
                            value: material.quantity,
                            format: "#0.## " + material.measure_unit_value,
                            placeholder: material.measure_unit_value,
                            onValueChanged: (e) => {
                                e.component.option("format", "#0.## " + material.measure_unit_value);
                                material.quantity = e.value;
                                let remains = calculateRemains(material.standard_id);
                                $('.calculation-summary[standard-id="' + material.standard_id + '"]').html(getCalculationSummaryText(remains.delta, remains.total, remains.transform_total));
                                repaintMaterialRemains();
                            },
                            onFocusIn: (e) => {
                                e.component.option("format", "");
                            },
                            onFocusOut: (e) => {
                                e.component.option("format", "#0.## " + material.measure_unit_value);
                            }
                        }))

                    if (material.accounting_type === 2) {
                        element.append($('<div class="transformation-number-box transformation-amount transformation-remains-amount" uid="' + material.id + '" material-id = "' + material.material_id + '"></div>')
                            .dxNumberBox({
                                min: 0,
                                format: "#0 шт",
                                value: material.amount,
                                onValueChanged: (e) => {
                                    material.amount = e.value;
                                    let remains = calculateRemains(material.standard_id);
                                    $('.calculation-summary[standard-id="' + material.standard_id + '"]').html(getCalculationSummaryText(remains.delta, remains.total, remains.transform_total));
                                    repaintMaterialRemains();
                                },
                                onFocusIn: (e) => {
                                    e.component.option("format", "");
                                },
                                onFocusOut: (e) => {
                                    e.component.option("format", "#0 шт");
                                }
                            }))
                    }

                    element.append($('<div class="transformation-comment-box transformation-comment" uid="' + material.id + '" material-id = "' + material.material_id + '"></div>')
                        .dxTextEditor({
                            value: material.comment,
                            placeholder: "Введите комментарий...",
                            onValueChanged: (e) => {
                                material.comment = e.value;
                            }
                        }))

                    element.append($('<span class="calculation-summary" standard-id="' + material.standard_id + '">' + getCalculationSummaryText(remainsSummary.delta, remainsSummary.total, remainsSummary.transform_total) + '</span>'));
                });
                if (currentTransformationStage === "fillingMaterialsRemains") {
                    let isCreateTransformationButtonDisabled = true;
                    if (materialsRemains.length === 0) {
                        isCreateTransformationButtonDisabled = true;
                    } else {
                        if ($(".allocation-pending").length !== 0) {
                            if (getTransformationType() === "corningManufacturing") {
                                isCreateTransformationButtonDisabled = false;
                            }
                        } else {
                            isCreateTransformationButtonDisabled = false;
                        }
                    }

                    let buttonText = isUserIsResponsibleForMaterialAccounting ? "Подтвердить преобразование" : "Отправить на согласование";

                    if (materialsRemains.length > 0) {
                        layer.append($('<div class="createTransformationOperationButton transform-wizard-button" >').dxButton({
                            text: buttonText,
                            type: "default",
                            stylingMode: "contained",
                            useSubmitBehavior: false,
                            disabled: isCreateTransformationButtonDisabled,
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

                                setButtonIndicatorVisibleState(true)
                                setElementsDisabledState(true);

                                let comment = operationForm.option("formData").new_comment;
                                if (!comment) {
                                    let confirmDialog = DevExpress.ui.dialog.confirm('Вы не заполнили поле "Комментарий".<br>Продолжить без заполнения?', 'Комметарий не заполнен');
                                    confirmDialog.done(function (dialogResult) {
                                        if (dialogResult) {
                                            validateMaterialList(true, true);
                                        } else {
                                            setButtonIndicatorVisibleState(false);
                                            setElementsDisabledState(false);
                                            return;
                                        }
                                    })
                                } else {
                                    validateMaterialList(true, true);
                                }
                            }
                        }));
                    }
                }
            }

            function calculateRemains(standardId) {
                let transformationType = getTransformationType();
                let result = {
                    standard_id: standardId,
                    amount: 0,
                    quantity: 0,
                    total: 0,
                    transform_amount: 0,
                    transform_quantity: 0,
                    transform_total: 0,
                    remain_amount: 0,
                    remain_quantity: 0,
                    remain_total: 0,
                    source_sum: 0,
                    remain_sum: 0,
                    delta: 0
                };

                materialsToTransform.forEach((material) => {
                    if (standardId === material.standard_id) {
                        result.amount = Math.round((result.amount + material.amount) * 100) / 100;
                        result.quantity = Math.round((result.quantity + material.quantity) * 100) / 100;
                        result.total = Math.round((result.total + material.amount * material.quantity) * 100) / 100
                    }
                })

                materialsAfterTransform.forEach((material) => {
                    result.transform_amount = Math.round((result.transform_amount + material.amount) * 100) / 100;
                    result.transform_quantity = Math.round((result.transform_quantity + material.quantity) * 100) / 100;
                    result.transform_total = Math.round((result.transform_total + material.amount * material.quantity) * 100) / 100
                })

                materialsRemains.forEach((material) => {
                    if (standardId === material.standard_id) {
                        result.remain_amount = Math.round((result.remain_amount + material.amount) * 100) / 100;
                        result.remain_quantity = Math.round((result.remain_quantity + material.quantity) * 100) / 100;
                        result.remain_total = Math.round((result.remain_total + material.amount * material.quantity) * 100) / 100
                    }
                })

                switch (transformationType) {
                    case "cutting":
                        result.delta = Math.round((result.total - result.transform_total - result.remain_total) * 100) / 100;
                        break;
                    case "lengthDocking":
                        result.delta = Math.round((result.total - result.transform_total - result.remain_total) * 100) / 100;
                        break;
                    case "wedgeShapedProduction":
                        result.delta = Math.round((result.total - result.transform_total - result.remain_total) * 100) / 100;
                        break;
                    case "corningManufacturing":
                        let uniqueStandards = [];

                        materialsToTransform.forEach((material) => {
                            if (!uniqueStandards.includes(material.standard_id)) {
                                uniqueStandards.push(material.standard_id);
                            }
                        })

                        if (uniqueStandards.length === 1) {
                            result.delta = Math.round((result.total - result.transform_total * 2 - result.remain_total) * 100) / 100;
                        } else {
                            result.delta = Math.round((result.total - result.transform_total - result.remain_total) * 100) / 100;
                        }
                        break;
                }
                return result;
            }

            function getCalculationSummaryText(delta, sourceSum, transformSum) {
                if (getTransformationType() === "none"){
                    return '<i class="allocation-pending fas fa-exclamation-triangle" style="color: #f15a5a"></i>' + "Невозможно рассчитать остатки - выберите тип преобразования";
                }

                if (delta === 0){
                    return '<i class = "fas fa-check-circle"></i>' + "Остатки распределены";
                } else {
                    if (delta < 0){
                        return '<i class = "allocation-pending"></i>Количество остатков превышено на ' + Math.abs(delta) + ' м.п. (Исходное количество: ' + sourceSum + ' м.п.; Преобразованное количество: ' + transformSum + ' м.п.)';
                    } else {
                        return '<i class = "allocation-pending"></i>Осталось распределить ' + Math.abs(delta) + ' м.п. (Исходное количество: ' + sourceSum + ' м.п.; Преобразованное количество: ' + transformSum + ' м.п.)';
                    }
                }
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
