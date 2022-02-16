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
            let suspendSourceObjectLookupValueChanged = false;
            let commentData = null;

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

            let operationHistoryStore = new DevExpress.data.CustomStore({
                key: "id",
                loadMode: "raw",
                load: function (loadOptions) {
                    return $.getJSON("{{route('materials.operations.comment-history.list')}}",
                        {operationId: operationData.id});
                },
            });

            let operationHistoryDataSource = new DevExpress.data.DataSource({
                reshapeOnPush: true,
                store: operationHistoryStore
            });

            let operationFileHistoryStore = new DevExpress.data.CustomStore({
                key: "operation_route_stage_id",
                loadMode: "raw",
                load: function (loadOptions) {
                    return $.getJSON("{{route('materials.operations.file-history.list')}}",
                        {operationId: operationData.id});
                },
            });

            let operationFileHistoryDataSource = new DevExpress.data.DataSource({
                reshapeOnPush: true,
                store: operationFileHistoryStore
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

            @if(in_array($routeStageId, [77, 79]) && ($allowEditing || $allowCancelling))
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
                                getWriteOffMaterialGrid().closeEditCell();

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
                        }
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
                        console.log(rowData);
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
                                return `Всего: ${Math.round(data.value * 1000) / 1000} т.`
                            },
                            showInGroupFooter: false,
                            alignByColumn: true
                        }],
                    totalItems: [{
                        column: "computed_weight",
                        summaryType: "sum",
                        cssClass: "computed-weight-total-summary",
                        customizeText: function (data) {
                            return `Итого: ${Math.round(data.value * 1000) / 1000} т.`
                        }
                    }]
                },

                onEditorPreparing: (e) => {
                    if (e.dataField === "quantity" && e.parentType === "dataRow") {
                        if (e.row.data.accounting_type === 2) {
                            e.cancel = true;
                            e.editorElement.append($(`<div>${e.row.data.quantity} ${e.row.data.measure_unit_value}</div>`))
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
                    },
                    {
                    itemType: "group",
                    caption: "Комментарий",
                    items: [
                        {
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
                    }
                    ]
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
                    {
                        itemType: "group",
                        caption: "Комментарии",
                        colSpan: 2,
                        items: [
                            {
                                name: "commentHistoryGrid",
                                editorType: "dxDataGrid",
                                editorOptions: {
                                    dataSource: operationHistoryDataSource,
                                    wordWrapEnabled: true,
                                    showColumnHeaders: false,
                                    columns: [
                                        {
                                            dataField: "user_id",
                                            dataType: "string",
                                            width: 240,
                                            cellTemplate: (container, options) => {
                                                let photoUrl;

                                                if (options.data.image) {
                                                    photoUrl = `{{ asset('storage/img/user_images/') }}` + options.data.image;
                                                } else {
                                                    photoUrl = `{{ mix('img/user-male-black-shape.png') }}`;
                                                }

                                                let authorName = options.data.last_name +
                                                    ' ' +
                                                    options.data.first_name.substring(0, 1) +
                                                    '. ' +
                                                    options.data.patronymic.substring(0, 1) +
                                                    '.';

                                                let commentDate = new Intl.DateTimeFormat('ru-RU', {
                                                    dateStyle: 'short',
                                                    timeStyle: 'short'
                                                }).format(new Date(options.data.created_at)).replaceAll(',', '');

                                                $(`<div class="comment-user-photo">` +
                                                    `<img src="` + photoUrl + `" class="photo">` +
                                                    `</div>`)
                                                    .appendTo(container);

                                                $(`<span class="comment-date">` +
                                                    commentDate +
                                                    `</span>` +
                                                    `<br><span class="comment-user-name">` +
                                                    authorName +
                                                    `</span>`)
                                                    .appendTo(container);
                                            }
                                        },
                                        {
                                            dataField: "comment",
                                            cellTemplate: (container, options) => {
                                                $(`<span class="comment">` +
                                                    options.data.comment +
                                                    `</span>`)
                                                    .appendTo(container);
                                            }
                                        },
                                        {
                                            dataField: "route_stage_name",
                                            width: 220
                                        }
                                    ]
                                }
                            }
                        ]
                    },
                    @if($allowEditing)
                    {
                        itemType: "group",
                        caption: "Файлы",
                        name: "fileUploaderGroup",
                        colSpan: 2,
                        colCount: 4,
                        items: getFileOptions()
                    },
                    @endif
                    {
                        itemType: "group",
                        caption: "Прикрепленные файлы",
                        colSpan: 2,
                        items: [
                            {
                                name: "fileHistoryGrid",
                                editorType: "dxDataGrid",
                                editorOptions: {
                                    dataSource: operationFileHistoryDataSource,
                                    wordWrapEnabled: true,
                                    showColumnHeaders: false,
                                    columns: [
                                        {
                                            dataField: "data[0].user_id",
                                            dataType: "string",
                                            width: 240,
                                            cellTemplate: (container, options) => {
                                                console.log('fileHistoryGrid options', options);
                                                let photoUrl = "";

                                                if (options.data.data[0].photo) {
                                                    photoUrl = `{{ asset('storage/img/user_images/') }}` + options.data.data[0].photo;
                                                } else {
                                                    photoUrl = `{{ mix('img/user-male-black-shape.png') }}`;
                                                }

                                                let authorName = options.data.data[0].last_name +
                                                    ' ' +
                                                    options.data.data[0].first_name.substr(0, 1) +
                                                    '. ' +
                                                    options.data.data[0].patronymic.substr(0, 1) +
                                                    '.';

                                                let commentDate = new Intl.DateTimeFormat('ru-RU', {
                                                    dateStyle: 'short',
                                                    timeStyle: 'short'
                                                }).format(new Date(options.data.data[0].created_at)).replaceAll(',', '');

                                                $(`<div class="comment-user-photo">` +
                                                    `<img src="` + photoUrl + `" class="photo">` +
                                                    `</div>`)
                                                    .appendTo(container);

                                                $(`<span class="comment-date">` +
                                                    commentDate +
                                                    `</span>` +
                                                    `<br><span class="comment-user-name">` +
                                                    authorName +
                                                    `</span>`)
                                                    .appendTo(container);
                                            }
                                        },
                                        {
                                            cellTemplate: (container, options) => {
                                                options.data.data.forEach((item) => {
                                                    let imageUrl = '{{ URL::to('/') }}' + '/' + item.file_path + item.file_name;

                                                    $(`<div><a href="${imageUrl}" target="_blank">${item.file_type_name}</a></div>`).appendTo(container);
                                                })
                                            }
                                        },
                                        {
                                            dataField: "data[0].route_stage_name",
                                            width: 220
                                        }
                                    ]
                                }
                            }
                        ]
                    },
                    @if(in_array($routeStageId, [77, 79]) && ($allowEditing || $allowCancelling))
                        applyOperationButtonGroup,
                    @endif
                ]
            }).dxForm("instance")
            //</editor-fold>

            //<editor-fold desc="JS: Toolbar configuration">
            //</editor-fold>

            function saveOperationData() {
                let writeOffOperationData = {};

                let uploadedFiles = []

                files.forEach((item) => {
                    if (item.uploadedFileId) {
                        uploadedFiles.push(item.uploadedFileId);
                    }
                })

                writeOffOperationData.operationId = operationData.id;
                writeOffOperationData.new_comment = operationForm.option("formData").new_comment;
                writeOffOperationData.uploaded_files = uploadedFiles;

                postEditingData(writeOffOperationData);
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
                @if(in_array($routeStageId, [77, 79]) && ($allowEditing || $allowCancelling))
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

                let uploadedFiles = []

                files.forEach((item) => {
                    if (item.uploadedFileId) {
                        uploadedFiles.push(item.uploadedFileId);
                    }
                })

                cancelledOperationData.operationId = operationData.id;
                cancelledOperationData.new_comment = operationForm.option("formData").new_comment;
                cancelledOperationData.uploaded_files = uploadedFiles;

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

            function getWriteOffMaterialGrid() {
                return operationForm.getEditor("writeOffMaterialGrid");
            }

            @if ($allowEditing)
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
                console.log(operationForm.itemOption("files.fileUploaderGroup", "items"));
            }
            createFileUploaderElement();
            @endif
        });
    </script>
@endsection
