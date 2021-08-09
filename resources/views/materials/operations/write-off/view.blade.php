@extends('layouts.app')

@section('title', 'Cписание ('.$operationRouteStage.')')

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
    <div id="validationPopoverContainer">
        <div id="validationTemplate" data-options="dxTemplate: { name: 'validationTemplate' }">

        </div>
    </div>
@endsection

@section('js_footer')
    <script>
        $(function () {
            let operationData = {!! $operationData !!};
            let projectObject = operationData.source_project_object_id;
            let materialStandardsData = {!!$materialStandards!!};
            let materialTypesData = {!!$materialTypes!!};
            let materialErrorList = [];

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

            let writeOffMaterialData = {!! $operationMaterials !!};

            let writeOffMaterialStore = new DevExpress.data.ArrayStore({
                key: "id",
                data: writeOffMaterialData
            })

            let writeOffMaterialDataSource = new DevExpress.data.DataSource({
                reshapeOnPush: true,
                store: writeOffMaterialStore
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

            @if(in_array($routeStageId, [77]) && ($allowEditing || $allowCancelling))
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
                            text: "Подтвердить списание",
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

            //<editor-fold desc="JS: Columns definition">
            let writeOffMaterialColumns = [
                {
                    dataField: "standard_id",
                    dataType: "string",
                    allowEditing: false,
                    caption: "Наименование",
                    sortIndex: 0,
                    sortOrder: "asc",
                    lookup: {
                        dataSource: materialStandardsData,
                        displayExpr: "name",
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
                        if (quantity !== null) {
                            $(`<div>${quantity} ${options.data.measure_unit_value}</div>`)
                                .appendTo(container);
                        } else {
                            $(`<div class="measure-units-only">${options.data.measure_unit_value}</div>`)
                                .appendTo(container);
                        }
                    },
                    //validationRules: [{type: "required"}]
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
                    },
                    //validationRules: [{type: "required"}]
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
                columnAutoWidth : false,
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
                    allowUpdating: false,
                    allowDeleting: false,
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
                        customizeText: function (data) {
                            return `Итого: ${data.value.toFixed(3)} т.`
                        }
                    }]
                },

                onEditorPreparing: (e) => {
                    if (e.dataField === "quantity" && e.parentType === "dataRow") {
                        if (e.row.data.accounting_type === 2) {
                            e.cancel = true;
                            e.editorElement.append($("<div>" + e.row.data.quantity + " " + e.row.data.measure_unit_value + "</div>"))
                        }
                        console.log(e);
                    }
                }
            };
            //</editor-fold>

            //<editor-fold desc="JS: Edit form configuration">
            let operationForm = $("#formContainer").dxForm({
                formData: operationData,
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
                            dataSource: projectObjectsDataSource,
                            disabled: true,
                            displayExpr: "short_name",
                            valueExpr: "id",
                            searchEnabled: true,
                            value: projectObject
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
                                disabled: true
                            },
                            validationRules: [{
                                type: "required",
                                message: 'Поле "Дата списания" обязательно для заполнения'
                            }]
                        },
                        {
                            name: "sourceResponsibleUserSelectBox",
                            colSpan: 2,
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
                            name: "writeOffMaterialGrid",
                            editorType: "dxDataGrid",
                            editorOptions: writeOffMaterialGridConfiguration
                        }
                        ]
                    },
                    @if(in_array($routeStageId, [77]) && ($allowEditing || $allowCancelling))
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

            function postEditingData(writeOffOperationData) {
                $.ajax({
                    url: "{{route('materials.operations.write-off.move')}}",
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
                        setButtonIndicatorVisibleState("*", false)
                        setElementsDisabledState(false);
                    }
                })
            }

            function setElementsDisabledState(state){
                @if(in_array($routeStageId, [77]) && ($allowEditing || $allowCancelling))
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

            @if ($allowCancelling)
            function cancelOperation() {
                let cancelledOperationData = {};
                cancelledOperationData.operationId = operationData.id;
                cancelledOperationData.new_comment = operationForm.option("formData").new_comment;

                $.ajax({
                    url: "{{route('materials.operations.write-off.cancel')}}",
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        data: JSON.stringify(cancelledOperationData),
                        options: null
                    },

                    success: function (data, textStatus, jqXHR) {
                        window.location.href = '{{route('materials.index')}}/?project_object=' + projectObject
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
