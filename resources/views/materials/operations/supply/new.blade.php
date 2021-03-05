@extends('layouts.app')

@section('title', 'Новая поставка')

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
@endsection

@section('js_footer')
    <script>
        $(function () {
            let measureUnitData = {!!$measureUnits ?? ''!!};
            let projectObject = {{$projectObjectId}};
            let materialStandardsData = {!!$materialStandards!!};
            let materialTypesData = {!!$materialTypes!!};

            let supplyMaterialTempID = 0;

            //<editor-fold desc="JS: DataSources">
            let contractorsStore = new DevExpress.data.CustomStore({
                key: "id",
                loadMode: "raw",
                load: function (loadOptions) {
                    return $.getJSON("{{route('contractors.list')}}",
                        {data: JSON.stringify({dxLoadOptions: loadOptions})});
                },
            });

            let contractorsDataSource = new DevExpress.data.DataSource({
                store: contractorsStore
            });

            let materialsStandardsListStore = new DevExpress.data.CustomStore({
                key: "id",
                loadMode: "processed",
                load: function (loadOptions) {
                    return $.getJSON("{{route('materials.standards.listex')}}",
                        {data: JSON.stringify({dxLoadOptions: loadOptions})});
                },
            });


            let materialsStandardsListDataSource = new DevExpress.data.DataSource({
                //group: "key",
                store: materialsStandardsListStore
            })

            /*let materialsStandardsListDataSource = new DevExpress.data.DataSource({
                group: "material_type_name",
                store: new DevExpress.data.ArrayStore({
                    key: "id",
                    data: materialStandardsData
                })
            })*/

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
                            dataSource: materialsStandardsListDataSource,
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
                                dataField: "name",
                                dataType: "string",
                                caption: "Наименование",
                                sortIndex: 0,
                                sortOrder: "asc",
                                calculateFilterExpression: function (filterValue, selectedFilterOperation, target) {
                                    if (target === "search") {
                                        let words = filterValue.split(" ");
                                        let filter = [];
                                        words.forEach(function (word) {
                                            filter.push(["name", "contains", word]);
                                            filter.push("and");
                                        });
                                        filter.pop();
                                        return filter;
                                    }
                                    return this.defaultCalculateFilterExpression(filterValue, selectedFilterOperation);
                                },
                                cellTemplate: function (container, options) {
                                    let text = options.data.name;
                                    let searchWords = options.component.option('searchPanel').text.split(" ");
                                    let resElement = $('<span>')
                                        .text(options.data.name);
                                    searchWords.forEach(function (word) {
                                        if (word.length) {
                                            let startPos = text.toLowerCase().indexOf(word.toLowerCase()),
                                                span = "<span class='highlight'>",
                                                spanLength = span.length,
                                                itemText = "";
                                            if (startPos >= 0) {
                                                itemText = [
                                                    text.slice(0, startPos),
                                                    span,
                                                    text.slice(startPos, startPos + word.length),
                                                    "</span>",
                                                    text.slice(startPos + word.length)
                                                ].join('');
                                                resElement = $('<span>')
                                                    .html(itemText);
                                            }
                                        }
                                    });
                                    return resElement;
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
                                    selectedMaterialStandardsListDataSource.store().insert({
                                        id: selectedRowItem.id,
                                        name: selectedRowItem.name,
                                        accounting_type: selectedRowItem.accounting_type,
                                        material_type: selectedRowItem.material_type,
                                        measure_unit: selectedRowItem.measure_unit,
                                        measure_unit_value: selectedRowItem.measure_unit_value,
                                        weight: selectedRowItem.weight
                                    })
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
                        name: "selectedMaterialsStandardsList",
                            editorOptions: {
                                dataSource: selectedMaterialStandardsListDataSource,
                                allowItemDeleting: true,
                                itemDeleteMode: "static",
                                height: 400,
                                width: 500,
                                itemTemplate: function (data) {
                                    return $("<div>").text(data.name)
                                },

                                onItemDeleted: function (e) {
                                    let materialsStandardsList = materialsStandardsAddingForm.getEditor("materialsStandardsList");
                                    let selectedMaterialsStandardsList = materialsStandardsAddingForm.getEditor("selectedMaterialsStandardsList");
                                    let selectedRowsKeys = [];
                                    selectedMaterialsStandardsList.option("items").forEach(function (selectedItem) {
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
                            let selectedMaterialsData = materialsStandardsAddingForm.getEditor("selectedMaterialsStandardsList").option("items");

                            selectedMaterialsData.forEach(function (materialStandard) {
                                supplyMaterialDataSource.store().insert({
                                    id: ++supplyMaterialTempID,
                                    standard_id: materialStandard.id,
                                    standard_name: materialStandard.name,
                                    accounting_type: materialStandard.accounting_type,
                                    material_type: materialStandard.material_type,
                                    measure_unit: materialStandard.measure_unit,
                                    measure_unit_value: materialStandard.measure_unit_value,
                                    standard_weight: materialStandard.weight,
                                    quantity: null,
                                    amount: null
                                })
                            })
                            supplyMaterialDataSource.reload();
                            $("#popupContainer").dxPopup("hide")
                        }
                    }
                }
                ]
            }).dxForm("instance");

            let popupContainer = $("#popupContainer").dxPopup({
                height: "auto",
                width: "auto"
            });

            //<editor-fold desc="JS: Columns definition">
            let supplyMaterialColumns = [
                {
                    type: "buttons",
                    width: 110,
                    buttons: ["delete", {
                        hint: "Дублировать",
                        icon: "copy",
                        onClick: function (e) {
                            let clonedItem = $.extend({}, e.row.data, {id: ++supplyMaterialTempID});
                            supplyMaterialData.splice(e.row.rowIndex, 0, clonedItem);
                            e.component.refresh(true);
                            e.event.preventDefault();
                        }
                    }]
                },
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
                    dataField: "measure_unit",
                    dataType: "number",
                    alignment: "right",
                    allowEditing: false,
                    caption: "Единица измерения",
                    lookup: {
                        dataSource: measureUnitData,
                        displayExpr: "value",
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
            let supplyMaterialGridConfiguration = {
                dataSource: supplyMaterialDataSource,
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
                    allowUpdating: true,
                    allowDeleting: true,
                    selectTextOnEditStart: false,
                    startEditAction: "click"
                },
                columns: supplyMaterialColumns,
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

                onToolbarPreparing: function (e) {
                    let dataGrid = e.component;
                    e.toolbarOptions.items.unshift(
                        {
                            location: "before",
                            widget: "dxButton",
                            options: {
                                icon: "add",
                                text: "Добавить",
                                onClick: function (e){
                                    selectedMaterialStandardsListDataSource.store().clear();

                                    let materialsStandardsList = materialsStandardsAddingForm.getEditor("materialsStandardsList");
                                    materialsStandardsList.option("selectedRowKeys", []);

                                    $("#popupContainer").dxPopup("show")
                                }
                            }
                        }
                    );
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
                    caption: "Поставка",
                    items: [{
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
                            value: projectObject,
                            onValueChanged: function (e) {
                                projectObject = e.value;
                            }
                        },
                        validationRules: [{
                            type: "required",
                            message: 'Поле "Объект" обязательно для заполнения'
                        }]
                    },
                        {
                            dataField: "date_start",
                            colSpan: 1,
                            label: {
                                text: "Дата поставки"
                            },
                            editorType: "dxDateBox",
                            editorOptions: {
                                value: Date.now()
                            },
                            validationRules: [{
                                type: "required",
                                message: 'Поле "Дата поставки" обязательно для заполнения'
                            }]
                        },
                        {
                            colSpan: 2,
                            dataField: "destination_responsible_user_id",
                            label: {
                                text: "Ответственный"
                            },
                            editorType: "dxSelectBox",
                            editorOptions: {
                                dataSource: usersData,
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
                    caption: "Поставщик",
                    items: [{
                        dataField: "contractor_id",
                        label: {
                            text: "Поставщик"
                        },
                        editorType: "dxSelectBox",
                        editorOptions: {
                            dataSource: contractorsDataSource,
                            displayExpr: "short_name",
                            valueExpr: "id",
                            searchEnabled: true
                        },
                        validationRules: [{
                            type: "required",
                            message: 'Поле "Поставщик" обязательно для заполнения'
                        }]
                    },
                        {
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
                            }]
                        }]
                    },
                    {
                        itemType: "group",
                        caption: "Материалы",
                        colSpan: 2,
                        items: [{
                            dataField: "",
                            editorType: "dxDataGrid",
                            editorOptions: supplyMaterialGridConfiguration
                        }
                        ]
                    },
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
                            /*validationRules: [{
                                type: "required",
                                message: 'Поле "Комментарий" обязательно для заполнения'
                            }]*/
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
                        colSpan: 2,
                        horizontalAlignment: "right",
                        buttonOptions: {
                            text: "Создать поставку",
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
                                            saveOperationData();
                                        } else {
                                            return;
                                        }
                                    })
                                } else {
                                    saveOperationData();
                                }
                            }
                        }
                    }]

            }).dxForm("instance")
            //</editor-fold>

            //<editor-fold desc="JS: Toolbar configuration">
            //</editor-fold>

            function saveOperationData() {
                let supplyOperationData = {};

                supplyOperationData.project_object_id = operationForm.option("formData").project_object_id;
                //TODO Дата формируется в UTC. Нужно либо учитывать это при перобразовании, либо хранить в UTC в БД
                supplyOperationData.date_start = new Date(operationForm.option("formData").date_start).toJSON().split("T")[0];
                supplyOperationData.destination_responsible_user_id = operationForm.option("formData").destination_responsible_user_id;
                supplyOperationData.contractor_id = operationForm.option("formData").contractor_id;
                supplyOperationData.consignment_note_number = operationForm.option("formData").consignment_note_number;
                supplyOperationData.new_comment = operationForm.option("formData").new_comment;

                let uploadedFiles = []
                $(".file-uploader").each(function () {
                    if ($(this).attr("uploaded-file-id") !== undefined) {
                        uploadedFiles.push($(this).attr("uploaded-file-id"));
                    }
                });

                supplyOperationData.uploaded_files = uploadedFiles;
                supplyOperationData.materials = supplyMaterialData;

                console.log(supplyOperationData);
                validateMaterialList(supplyOperationData, false);
            }

            function validateMaterialList(supplyOperationData, forcePostData) {
                $.ajax({
                    url: "{{route('materials.operations.supply.new.validate-material-list')}}",
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        data: JSON.stringify(supplyOperationData)
                    },
                    success: function (data, textStatus, jqXHR) {
                        postEditingData(supplyOperationData)
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        if (forcePostData) {
                            postEditingData(supplyOperationData)
                        }
                        DevExpress.ui.notify("При сохранении данных произошла ошибка<br>Список ошибок", "error", 5000)
                    }
                })
            }

            function postEditingData(supplyOperationData) {
                $.ajax({
                    url: "{{route('materials.operations.supply.new')}}",
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        data: JSON.stringify(supplyOperationData)
                    },

                    success: function (data, textStatus, jqXHR) {
                        window.location.href = '{{route('materials.index')}}/?project_object=' + projectObject
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        DevExpress.ui.notify("При сохранении данных произошла ошибка", "error", 5000)
                    }
                })
            }

            $(".file-uploader").each(function() {
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
