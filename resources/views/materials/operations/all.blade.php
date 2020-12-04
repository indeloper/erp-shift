@extends('layouts.app')

@section('title', 'Операции')

@section('url', route('materials.operations.index'))

@section('css_top')
    <style>

    </style>
@endsection

@section('content')
    <div id="gridContainer"></div>
@endsection

@section('js_footer')
    <script>
        $(function () {
            $("div.content").children(".container-fluid.pd-0-360").removeClass();
        });

        $(function () {
            //<editor-fold desc="JS: DataSources">
            let projectObjectsStore = new DevExpress.data.CustomStore({
                key: "id",
                loadMode: "processed",
                load: function (loadOptions) {
                    console.log(loadOptions);
                    return $.getJSON("{{route('project-objects.list')}}",
                        {data: JSON.stringify(loadOptions)});
                },
            });

            let operationRoutesStore = new DevExpress.data.CustomStore({
                key: "id",
                loadMode: "processed",
                useDefaultSearch: true,
                load: function (loadOptions) {
                    console.log(loadOptions);
                    return $.getJSON("{{route('material.operation.routes.list')}}",
                        {data: JSON.stringify(loadOptions)});
                },
            });

            let operationsDataSource = new DevExpress.data.DataSource({
                reshapeOnPush: true,
                store: new DevExpress.data.CustomStore({
                    key: "id",
                    load: function (loadOptions) {
                        return $.getJSON("{{route('materials.operations.list')}}",
                            {data: JSON.stringify({filterOptions: loadOptions})});
                    },
                })
            });

            //</editor-fold>

            //<editor-fold desc="JS: Edit form configuration">

            //</editor-fold>

            //<editor-fold desc="JS: Columns definition">
            let materialTypesColumns = [
                {
                    dataField: "id",
                    caption: "Номер",
                    dataType: "number",
                    width: 70,
                    cellTemplate: function (container, options) {
                        container.html('<a href="{{route('materials.operations.transfer.view')}}/?operationId=' + options.displayValue + '">' + options.displayValue + '</a>');
                    }
                },
                {
                    dataField: "operation_route_id",
                    dataType: "number",
                    caption: "Тип",
                    lookup: {
                        dataSource: {
                            paginate: true,
                            pageSize: 25,
                            store: operationRoutesStore
                        },
                        displayExpr: "name",
                        valueExpr: "id"
                    }
                },
                {
                    dataField: "material_types_info",
                    dataType: "string",
                    caption: "Материалы",
                    cellTemplate: function (container, options) {
                        container.html(options.displayValue);
                    }
                },
                {
                    dataField: "source_project_object_id",
                    dataType: "number",
                    caption: "Объект отправления(?)",
                    lookup: {
                        dataSource: {
                            paginate: true,
                            pageSize: 25,
                            store: projectObjectsStore
                        },
                        displayExpr: "name",
                        valueExpr: "id"
                    }
                },
                {
                    dataField: "destination_project_object_id",
                    dataType: "number",
                    caption: "Объект назначения(?)",
                    lookup: {
                        dataSource: {
                            paginate: true,
                            pageSize: 25,
                            store: projectObjectsStore
                        },
                        displayExpr: "name",
                        valueExpr: "id"
                    }
                },
                {
                    dataField: "date_start",
                    dataType: "date",
                    caption: "Дата создания"
                },
                {
                    dataField: "date_end",
                    dataType: "date",
                    caption: "Дата завершения"
                },
                {
                    dataField: "operation_route_stage_name",
                    dataType: "string",
                    caption: "Статус"
                }
            ];
            //</editor-fold>

            //<editor-fold desc="JS: Grid configuration">
            $("#gridContainer").dxDataGrid({
                dataSource: operationsDataSource,
                remoteOperations: true,
                scrolling: {
                    mode: "virtual",
                    rowRenderingMode: "virtual",
                    useNative: false,
                    scrollByContent: true,
                    scrollByThumb: true,
                    showScrollbar: "onHover"
                },
                paging: {
                    pageSize: 100
                },
                height: function () {
                    return $("div.content").height()
                },
                showColumnLines: true,
                focusedRowEnabled: true,
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
                columns: materialTypesColumns
            });
            //</editor-fold>

            //<editor-fold desc="JS: Toolbar configuration">
            //</editor-fold>


        });

    </script>
@endsection
