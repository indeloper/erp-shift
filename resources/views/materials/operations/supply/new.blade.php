@extends('layouts.app')

@section('title', 'Новая поставка')

@section('url', "#")

@section('css_top')

@endsection

@section('content')
    <div id="formContainer"></div>
    <div id="popupContainer">
        <div id="materialsStandardsAddingForm"></div>
    </div>
@endsection

@section('js_footer')
    <script>
        $(function(){
            let projectObject = {{$projectObjectId}};
            let materialStandardsData = {!!$materialStandards!!};
            let materialTypesData = {!!$materialTypes!!};


            //<editor-fold desc="JS: DataSources">
            let selectedDataSource = new DevExpress.data.DataSource ({
                reshapeOnPush: true,
                store: new DevExpress.data.ArrayStore({
                    key: "id",
                    data: materialStandardsData
                })
            })

            let supplyMaterialDataSource = new DevExpress.data.DataSource({
                reshapeOnPush: true,
                store: new DevExpress.data.ArrayStore({
                    data: []
                })
            })

            let projectObjectsData = new DevExpress.data.DataSource({
                reshapeOnPush: true,
                store: new DevExpress.data.ArrayStore({
                    key: "id",
                    data: {!! $projectObjects !!}
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
                items:[{
                    editorType: "dxList",
                    id: "checkList",
                    editorOptions: {
                        height: 400,
                        dataSource: new DevExpress.data.DataSource ({
                           store: new DevExpress.data.ArrayStore({
                               key: "id",
                               data: materialStandardsData
                           })
                        }),
                        showSelectionControls: true,
                        selectionMode: "multiply",
                        searchEnabled: true,
                        searchExpr: "name",
                        itemTemplate: function(data) {
                            return $("<div>").text(data.name)
                        },
                        onSelectionChanged: function(data) {
                            selectedDataSource.store().push([{id: 1, name: "asdasd"}]);
                            selectedDataSource.reload();
                        }
                        /*grouped: true,
                        collapsibleGruops: true,

                        ,
                        groupTemplate: function(data)*/
                    }
                },
                {
                    editorType: "dxList",
                    id: "selectionList",
                    editorOptions: {
                        height: 400,
                        itemTemplate: function(data) {
                            return $("<div>").text(data.name)
                        }
                    }
                }
                ]
            });

            let popupContainer = $("#popupContainer").dxPopup({height: "auto",
                width: "auto",
                width: "600px"
            });

            //<editor-fold desc="JS: Columns definition">
            let materialColumns = [
                {
                    dataField: "standard_id",
                    dataType: "string",
                    caption: "Наименование",
                    lookup: {
                        dataSource: materialStandardsData,
                        displayExpr: "name",
                        valueExpr: "id"
                    },
                    cellTemplate: function (container, options) {
                        let data = options.data;
                        let materialName = data.standard_name;
                        if (data.accounting_type === 1){
                            materialName += " (" + data.quantity + " " + data.measure_unit_value + ")";
                        }
                        $("<div>" + materialName + "</div>")
                            .appendTo(container);
                    }
                },
                {
                    dataField: "computed_quantity",
                    dataType: "number",
                    caption: "Количество",
                    cellTemplate: function (container, options) {
                        let data = options.data;
                        let computedQuantity = null;

                        if (data.accounting_type === 1){
                            computedQuantity = data.computed_quantity + " шт.";
                        } else {
                            computedQuantity = data.computed_quantity + " " + data.measure_unit_value;
                        }

                        $("<div>" + computedQuantity + "</div>")
                            .appendTo(container);
                    }
                },
                {
                    dataField: "computed_weight",
                    dataType: "number",
                    caption: "Вес",
                    cellTemplate: function (container, options) {
                        $("<div>" + options.data.computed_weight + " т.</div>")
                            .appendTo(container);
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
                },
            ];
            //</editor-fold>

            //<editor-fold desc="JS: Grid configuration">
            let supplyMaterialGridConfiguration = {
                dataSource: supplyMaterialDataSource,
                focusedRowEnabled: false,
                hoverStateEnabled: true,
                columnAutoWidth: false,
                showBorders: true,
                filterRow: {
                    visible: true,
                    applyFilter: "auto"
                },
                grouping: {
                    autoExpandAll: true,
                },
                groupPanel: {
                    visible: false
                },
                selection: {
                    allowSelectAll:true,
                    deferred:false,
                    mode:"multiple",
                    selectAllMode:"allPages",
                    showCheckBoxesMode:"always"
                },
                columns: materialColumns,
                summary: {
                    groupItems: [{
                        column: "standard_id",
                        summaryType: "count",
                        displayFormat: "Количество: {0}",
                    },
                        {
                            column: "computed_quantity",
                            summaryType: "sum",
                            displayFormat: "Всего: {0}",
                            showInGroupFooter: false,
                            alignByColumn: true
                        },
                        {
                            column: "computed_weight",
                            summaryType: "sum",
                            displayFormat: "Всего: {0} т.",
                            showInGroupFooter: false,
                            alignByColumn: true
                        }],
                    totalItems: [{
                        column: "computed_weight",
                        summaryType: "sum",
                        displayFormat: "Итого: {0} т.",
                    }]
                },

                onToolbarPreparing: function(e) {
                    let dataGrid = e.component;

                    e.toolbarOptions.items.unshift(
                        {
                            location: "before",
                            widget: "dxButton",
                            options: {
                                icon: "add",
                                text: "Добавить",
                                onClick: function (e){
                                    $("#popupContainer").dxPopup("show")
                                }
                            }
                        }
                    );
                },
                onRowDblClick: function(e){
                    console.log(e);
                }
            };
            //</editor-fold>

            //<editor-fold desc="JS: Edit form configuration">
            $("#formContainer").dxForm({
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
                            dataSource: projectObjectsData,
                            displayExpr: "name",
                            valueExpr: "id",
                            searchEnabled: true,
                            value: projectObject
                        }
                    },
                    {
                        dataField: "",
                        colSpan: 1,
                        label: {
                            text: "Дата начала"
                        },
                        editorType: "dxDateBox",
                        editorOptions: {

                        }
                    },
                    {
                        dataField: "",
                        colSpan: 1,
                        label: {
                            text: "Дата окончания"
                        },
                        editorType: "dxDateBox",
                        editorOptions: {

                        }
                    },
                    {
                        colSpan: 2,
                        dataField: "",
                        label: {
                            text: "Ответственный"
                        },
                        editorType: "dxSelectBox",
                        editorOptions: {
                            dataSource: usersData,
                            displayExpr: "full_name",
                            valueExpr: "id",
                            searchEnabled: true
                        }
                    }]
                },{
                    itemType: "group",
                    caption: "Контрагент",
                    items: [{
                        dataField: "",
                        label: {
                            text: "Поставщик"
                        },
                        editorType: "dxSelectBox",
                        editorOptions: {

                        }
                    },
                    {
                        dataField: "",
                        label: {
                            text: "Договор"
                        },
                        editorType: "dxSelectBox",
                        editorOptions: {

                        }
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

                }]
            })
            //</editor-fold>



            //<editor-fold desc="JS: Toolbar configuration">
            //</editor-fold>


        });

    </script>
@endsection
