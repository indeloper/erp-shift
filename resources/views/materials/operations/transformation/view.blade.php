@extends('layouts.app')

@section('title', 'Преобразование #'.json_decode($operationData)->id. ' [' .$operationRouteStage.']')

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

        .transform-wizard-caption {
            font-weight: bold;
            font-size: larger;
            color: darkslategray;
        }


        #materialsToTransformElements, #materialsAfterTransformElements, #materialsRemainsTransformElements {
            display: flex;
            flex-direction: column;
        }

        #materialsAfterTransform, #materialsRemains {
            margin-top: 8px;
        }
    </style>
@endsection

@section('content')
    <div id="formContainer"></div>
    <div id="popupContainer">
    </div>
    <div id="validationPopoverContainer">
        <div id="validationTemplate" data-options="dxTemplate: { name: 'validationTemplate' }">

        </div>
    </div>
@endsection

@section('js_footer')
    <script>
        $(function () {
            let materialErrorList = [];

            let operationData = {!! $operationData !!};
            console.log('operationData', operationData);
            let projectObjectId = operationData.source_project_object_id;

            let operationMaterials = {!! $operationMaterials !!};

            //<editor-fold desc="JS: DataSources">

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
            //</editor-fold>

            //<editor-fold desc="JS: Edit form configuration">
            @if(in_array($routeStageId, [71]) && ($allowEditing || $allowCancelling))
            let applyOperationButtonGroup = {
                itemType: "simpleItem",
                colSpan: 2,
                template: function (data, itemElement) {
                    @if($allowCancelling)
                    $('<div id="cancelOperationButton">')
                        .css('float', 'left')
                        .dxButton({
                            text: "Отменить операцию",
                            type: "danger",
                            stylingMode: "contained",
                            useSubmitBehavior: false,
                            template: function(data, container) {
                                $("<div class='button-loading-indicator'></div><span class='dx-button-text'>" + data.text + "</span>").appendTo(container);
                                let loadingIndicator = container.find(".button-loading-indicator").dxLoadIndicator({
                                    visible: false
                                }).dxLoadIndicator("instance");
                            },
                            onClick: function (e) {
                                let confirmDialog = DevExpress.ui.dialog.confirm('Вы действительно хотите отменить операцию?', 'Отмена операции');
                                confirmDialog.done(function (dialogResult) {
                                    if (dialogResult) {
                                        setButtonIndicatorVisibleState("cancelOperationButton", true);
                                        setElementsDisabledState(true);
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
                    $('<div id="applyOperationButton">')
                        .css('float', 'right')
                        .dxButton({
                            text: "Подтвердить изменения",
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

                                setButtonIndicatorVisibleState("applyOperationButton", true);
                                setElementsDisabledState(true);

                                let comment = operationForm.option("formData").new_comment;
                                if (!comment) {
                                    let confirmDialog = DevExpress.ui.dialog.confirm('Вы не заполнили поле "Комментарий".<br>Продолжить без заполнения?', 'Комметарий не заполнен');
                                    confirmDialog.done(function (dialogResult) {
                                        if (dialogResult) {
                                            saveOperationData();
                                        } else {
                                            setButtonIndicatorVisibleState("applyOperationButton", false)
                                            setElementsDisabledState(false);
                                        }
                                    })
                                } else {
                                    saveOperationData();
                                }
                            }
                        })
                        .appendTo(itemElement)
                    @endIf
                }
            };
            @endIf

            let operationForm = $("#formContainer").dxForm({
                formData: operationData,
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
                            dataSource: projectObjectsDataSource,
                            displayExpr: "short_name",
                            valueExpr: "id",
                            searchEnabled: true,
                            value: projectObjectId,
                            disabled: true
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
                            disabled: true,
                            validationRules: [{
                                type: "required",
                                message: 'Поле "Дата преобразования" обязательно для заполнения'
                            }]
                        },
                        {
                            name: "sourceResponsibleUserSelectBox",
                            colSpan: 1,
                            dataField: "source_responsible_user_id",
                            label: {
                                text: "Ответственный"
                            },
                            editorType: "dxSelectBox",
                            editorOptions: {
                                dataSource: usersData,
                                displayExpr: "full_name",
                                valueExpr: "id",
                                searchEnabled: true,
                                disabled: true,
                            },
                            validationRules: [{
                                type: "required",
                                message: 'Поле "Ответственный" обязательно для заполнения'
                            }
                            ]

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
                                disabled: true,
                                onValueChanged: () => {
                                    repaintMaterialRemains();
                                }
                            },
                            validationRules: [{
                                type: "required",
                                message: 'Поле "Тип преобразования" обязательно для заполнения'
                            }]
                        }

                    ]
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
                            @if(!$allowEditing || !$allowCancelling)
                            disabled: true
                            @endif
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
                                itemElement.append( $("<div id='materialsToTransform'>"));
                                itemElement.append( $("<div id='materialsAfterTransform'>"));
                                itemElement.append( $("<div id='materialsRemains'>"));

                                repaintTransformLayers();
                            }
                        }
                        ]
                    },
                    @if(in_array($routeStageId, [71]) && ($allowEditing || $allowCancelling))
                        applyOperationButtonGroup,
                    @endif
                    ]

            }).dxForm("instance")
            //</editor-fold>

            //<editor-fold desc="JS: Toolbar configuration">
            //</editor-fold>

            function saveOperationData() {
                let transformationOperationData = {};

                transformationOperationData.operationId = operationData.id;
                transformationOperationData.new_comment = operationForm.option("formData").new_comment;

                postEditingData(transformationOperationData);
            }

            function postEditingData(transformationOperationData) {
                $.ajax({
                    url: "{{route('materials.operations.transformation.move')}}",
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        data: JSON.stringify(transformationOperationData)
                    },

                    success: function (data, textStatus, jqXHR) {
                        window.location.href = '{{route('materials.index')}}/?project_object=' + projectObjectId
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        DevExpress.ui.notify("При сохранении данных произошла ошибка", "error", 5000);
                        setButtonIndicatorVisibleState("*", false)
                        setElementsDisabledState(false);
                    }
                })
            }

            function setElementsDisabledState(state){
                @if(in_array($routeStageId, [71]) && ($allowEditing || $allowCancelling))
                @if($allowCancelling)
                $('#cancelOperationButton').dxButton("instance").option("disabled", state);
                @endIf
                @if ($allowEditing)
                $('#applyOperationButton').dxButton("instance").option("disabled", state);
                @endIf
                @endIf
                operationForm.getEditor("newCommentTextArea").option("disabled", state);
            }

            function setButtonIndicatorVisibleState(buttonId, state){
                if (buttonId === "*"){
                    $(".button-loading-indicator").each((index, element) =>{
                        console.log(element);
                        try {
                            let loadingIndicator = $(element).dxLoadIndicator("instance");
                            if (loadingIndicator) {
                                loadingIndicator.option('visible', state);
                            }
                        }
                        catch(err) {
                            console.log(err)
                        }
                    })
                } else {
                    let loadingIndicator = $("#" + buttonId).find(".button-loading-indicator").dxLoadIndicator("instance");
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
                const transformationHeaderText = 'Добавленные материалы:';

                let layer = $('#materialsToTransform');

                layer.empty();

                layer.append($('<span class="transform-wizard-caption">' + transformationHeaderText + '</span>'));

                layer.append($('<div id="materialsToTransformElements"></div>'));

                let elements = layer.find('#materialsToTransformElements');

                operationMaterials.forEach(function (material) {
                    if (material.transform_operation_stage_id === 1) {
                        elements.append($('<div class="transform-element"></div>'));

                        let element = layer.find('.transform-element').last();

                        let standardNameElement = $('<div class="standard-name-cell-with-comment transformation-standard-name-cell"/>');
                        element.append(standardNameElement);

                        let materialName;
                        switch (material.accounting_type) {
                            case 2:
                                materialName = material.standard_name +
                                    ' (' +
                                    material.quantity +
                                    ' ' +
                                    material.measure_unit_value +
                                    '/'
                                    + material.amount +
                                    ' шт)'
                                break;
                            default:
                                materialName = material.standard_name +
                                    ' (' +
                                    material.quantity +
                                    ' ' +
                                    material.measure_unit_value +
                                    ')'
                                break;
                        }
                        standardNameElement.append($(`<div class="standard-name">${materialName}</div>`));

                        if (material.initial_comment) {
                            standardNameElement.append($(`<div class="material-comment">${material.initial_comment}</div>`));
                        }
                }
                });
            }

            function repaintMaterialsAfterTransformLayer(){
                const transformationHeaderText = 'Пребразованные материалы:';

                let layer = $('#materialsAfterTransform');

                layer.empty();

                layer.append($("<hr>"));

                layer.append($('<span class="transform-wizard-caption">' + transformationHeaderText + '</span>'));

                layer.append($('<div id="materialsAfterTransformElements"></div>'));

                let elements = layer.find('#materialsAfterTransformElements');

                operationMaterials.forEach(function (material) {
                    if (material.transform_operation_stage_id === 2) {
                        elements.append($('<div class="transform-element"></div>'));

                        let element = layer.find('.transform-element').last();
                        let standardNameElement = $('<div class="standard-name-cell-with-comment transformation-standard-name-cell"/>');
                        element.append(standardNameElement);

                        let materialName;
                        switch (material.accounting_type) {
                            case 2:
                                materialName = material.standard_name +
                                    ' (' +
                                    material.quantity +
                                    ' ' +
                                    material.measure_unit_value +
                                    '/'
                                    + material.amount +
                                    ' шт)'
                                break;
                            default:
                                materialName = material.standard_name +
                                    ' (' +
                                    material.quantity +
                                    ' ' +
                                    material.measure_unit_value +
                                    ')'
                                break;
                        }
                        standardNameElement.append($(`<div class="standard-name">${materialName}</div>`));

                        if (material.comment) {
                            standardNameElement.append($(`<div class="material-comment">${material.comment}</div>`));
                        }
                    }
                });
            }

            function repaintMaterialRemains() {
                const transformationHeaderStageText = 'Остатки исходных материалов:';

                let layer = $('#materialsRemains');

                layer.empty();

                layer.append($("<hr>"));

                layer.append($('<span class="transform-wizard-caption">' + transformationHeaderStageText + '</span>'));

                layer.append($('<div id="materialsRemainsTransformElements"></div>'));

                let elements = layer.find('#materialsRemainsTransformElements');

                operationMaterials.forEach(function (material) {
                    if (material.transform_operation_stage_id === 3) {
                        elements.append($('<div class="transform-element"></div>'));

                        let element = layer.find('.transform-element').last();
                        let standardNameElement = $('<div class="standard-name-cell-with-comment transformation-standard-name-cell"/>');
                        element.append(standardNameElement);

                        let materialName;
                        switch (material.accounting_type) {
                            case 2:
                                materialName = material.standard_name +
                                    ' (' +
                                    material.quantity +
                                    ' ' +
                                    material.measure_unit_value +
                                    '/'
                                    + material.amount +
                                    ' шт)'
                                break;
                            default:
                                materialName = material.standard_name +
                                    ' (' +
                                    material.quantity +
                                    ' ' +
                                    material.measure_unit_value +
                                    ')'
                                break;
                        }
                        standardNameElement.append($(`<div class="standard-name">${materialName}</div>`));

                        if (material.comment) {
                            standardNameElement.append($(`<div class="material-comment">${material.comment}</div>`));
                        }
                    }
                });
            }

            @if ($allowCancelling)
            function cancelOperation() {
                let cancelledOperationData = {};
                cancelledOperationData.operationId = operationData.id;
                cancelledOperationData.new_comment = operationForm.option("formData").new_comment;

                $.ajax({
                    url: "{{route('materials.operations.transformation.cancel')}}",
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        data: JSON.stringify(cancelledOperationData),
                        options: null
                    },

                    success: function (data, textStatus, jqXHR) {
                        window.location.href = '{{route('materials.index')}}/?project_object=' + projectObjectId
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        DevExpress.ui.notify("При отмене операции произошла ошибка", "error", 5000)
                    }
                })
            }
            @endif
        });
    </script>
@endsection
