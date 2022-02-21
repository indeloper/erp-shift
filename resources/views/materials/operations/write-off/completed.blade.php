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
            let materialTypesData = {!!$materialTypes!!};

            //<editor-fold desc="JS: DataSources">

            let writeOffMaterialData = {!! $operationMaterials !!};

            let writeOffMaterialStore = new DevExpress.data.ArrayStore({
                key: "id",
                data: writeOffMaterialData
            })

            let writeOffMaterialDataSource = new DevExpress.data.DataSource({
                reshapeOnPush: true,
                store: writeOffMaterialStore
            })

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

            //<editor-fold desc="JS: Columns definition">
            let writeOffMaterialColumns = [
                {
                    dataField: "standard_name",
                    dataType: "string",
                    allowEditing: false,
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
                        colSpan: 3,
                        dataField: "source_project_object_name",
                        label: {
                            text: "Объект"
                        },
                        editorOptions: {
                            readOnly: true
                        }
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
                            readOnly: true
                        }
                    },
                        {
                            name: "sourceResponsibleUserSelectBox",
                            colSpan: 2,
                            dataField: "source_responsible_user_name",
                            label: {
                                text: "Ответственный"
                            },
                            editorOptions: {
                                readOnly: true
                            }

                        }]
                    },
                    {
                    itemType: "group",
                    caption: "Комментарии",
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
                                                photoUrl = `{{ asset('storage/img/user_images/') }}` + '/' + options.data.image;
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
                        caption: "Файлы",
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
                ]
            }).dxForm("instance")
            //</editor-fold>

            //<editor-fold desc="JS: Toolbar configuration">
            //</editor-fold>
        });
    </script>
@endsection
