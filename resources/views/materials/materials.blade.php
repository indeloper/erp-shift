@extends('layouts.app')

@section('title', 'Материалы')

@section('url', route('materials.index'))

@section('css_top')

@endsection

@section('content')
    <div id="gridContainer" style="height: 100%"></div>
@endsection

@section('js_footer')
    <script>
        $(function(){
            //<editor-fold desc="JS: DataSources">
            let measureUnitsData = {!!$measureUnits!!};
            let accountingTypesData = {!!$accountingTypes!!};
            let materialTypesData = {!!$materialTypes!!};
            let materialStandardsData = {!!$materialStandards!!};
            let projectObject = {{$projectObjectId}};

            let projectObjectsData = new DevExpress.data.DataSource({
                    reshapeOnPush: true,
                    store: new DevExpress.data.ArrayStore({
                        key: "id",
                        data: {!! $projectObjects !!}
                    })
                });

            let materialStandardsDataSource = new DevExpress.data.DataSource({
                reshapeOnPush: true,
                store: new DevExpress.data.CustomStore({
                    key: "id",
                    load: function() {
                        return $.getJSON("{{route('materials.list')}}",
                            {
                                project_object: projectObject
                            });
                    },
                    /*byKey: function (key, extraOptions){

                    }*/
                })
            });

            //</editor-fold>

            //<editor-fold desc="JS: Edit form configuration">

            //</editor-fold>

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
                    dataField: "measure_unit",
                    dataType: "number",
                    caption: "Ед. изм.",
                    lookup: {
                        dataSource: measureUnitsData,
                        displayExpr: "value",
                        valueExpr: "id"
                    }
                },
                {
                    dataField: "computed_quantity",
                    dataType: "number",
                    caption: "Количество",
                    cellTemplate: function (container, options) {
                        let data = options.data;
                        let computedQuantity;

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
                        $("<div>" + options.data.computed_weight.toFixed(3) + " т.</div>")
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
                }
            ];
            //</editor-fold>

            //<editor-fold desc="JS: Grid configuration">
            $("#gridContainer").dxDataGrid({
                dataSource: materialStandardsDataSource,
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
                        //displayFormat: "Всего: {0} т.",
                        customizeText: function (data) {
                            return "Всего: " + data.value.toFixed(3) + " т."
                        },
                        showInGroupFooter: false,
                        alignByColumn: true
                    }],
                    totalItems: [{
                        column: "computed_weight",
                        summaryType: "sum",
                        customizeText: function (data) {
                            return "Итого: " + data.value.toFixed(3) + " т."
                        }
                    }]
                },

                onToolbarPreparing: function(e) {
                    e.toolbarOptions.items.unshift(
                        {
                            location: "before",
                            widget: "dxSelectBox",
                            options: {
                                width: 200,
                                searchEnabled: true,
                                dataSource: projectObjectsData,
                                displayExpr: "name",
                                valueExpr: "id",
                                value: {{$projectObjectId}},
                                onValueChanged: function(e){
                                    projectObject = e.value;
                                    $("#gridContainer").dxDataGrid("instance").refresh();
                                    window.history.pushState("", "", "?project_object=" + projectObject)
                                }
                            }
                        },
                        {
                            location: "after",
                            widget: "dxDropDownButton",
                            options: {
                                text: "Операции",
                                //icon: "save",
                                dropDownOptions: {
                                    width: 230
                                },
                                onItemClick: function(e) {
                                    if (e.itemData === "Поставка") {
                                        document.location.href = "{{route('materials.operations.supply.new')}}" + "/?project_object=" + projectObject;
                                    }
                                },
                                items: ["Поставка", "Перемещение", "Производство", "Списание"]
                            }
                        }
                    );
                },
                onRowDblClick: function(e){
                    console.log(e);
                }
            });
            //</editor-fold>

            //<editor-fold desc="JS: Toolbar configuration">
            //</editor-fold>


        });

    </script>
@endsection
