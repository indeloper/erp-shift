@extends('layouts.app')

@section('title', 'Преобразование ('.$operationRouteStage.')')

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

            let projectObjectId = operationData.source_project_object_id;

            let operationMaterials = {!! $operationMaterials !!};

            //<editor-fold desc="JS: DataSources">
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
            //</editor-fold>

            //<editor-fold desc="JS: Edit form configuration">

            let operationForm = $("#formContainer").dxForm({
                formData: operationData,
                colCount: 2,
                items: [{
                    itemType: "group",
                    colCount: 3,
                    caption: "Преобразование",
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
                                text: "Дата преобразования"
                            },
                            editorType: "dxDateBox",
                            editorOptions: {
                                readOnly: true,
                            }
                        },
                        {
                            colSpan: 1,
                            dataField: "source_responsible_user_name",
                            label: {
                                text: "Ответственный"
                            },
                            editorOptions: {
                                readOnly: true,
                            },
                        },
                        {
                            colSpan: 1,
                            dataField: "transformation_type_value",
                            label: {
                                text: "Тип преобразования"
                            },
                            editorOptions: {
                                readOnly: true,
                            }
                        }

                    ]
                },
                    {
                        itemType: "group",
                        caption: "Комментарий",
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
                            name: "materialsToTransformLayer",
                            template: function (data, itemElement) {
                                itemElement.append( $("<div id='materialsToTransform'>"));
                                itemElement.append( $("<div id='materialsAfterTransform'>"));
                                itemElement.append( $("<div id='materialsRemains'>"));

                                repaintTransformLayers();
                            }
                        }
                        ]
                    }
                ]

            }).dxForm("instance")
            //</editor-fold>

            //<editor-fold desc="JS: Toolbar configuration">
            //</editor-fold>

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
        });
    </script>
@endsection
