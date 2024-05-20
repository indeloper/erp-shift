@extends('layouts.app')

@section('title', 'Операции')

@section('url', route('materials.operations.index'))

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

        .dx-form-group .dx-group-no-border {
            border: 0;
            border-radius: 0;
        }

        .dx-item-content {
            justify-content: flex-end;
        }
    </style>
@endsection

@section('content')
    <div id="formContainer"></div>
    <div id="gridContainer"></div>
    <form id="printAllOperations" target="_blank" multisumit="true" method="post" action="{{route('materials.operations.print')}}">
        @csrf
        <input id="filterOptions" type="hidden" name="filterOptions">
        <input id="filterList" type="hidden" name="filterList">
    </form>
@endsection

@section('js_footer')
    <script>
        $(function () {
            let projectObjectsStore = new DevExpress.data.CustomStore({
                key: "id",
                loadMode: "processed",
                load: function (loadOptions) {
                    return $.getJSON("{{route('project-objects.list')}}",
                        {data: JSON.stringify(loadOptions)});
                }
            });

            let operationRoutesStore = new DevExpress.data.CustomStore({
                key: "id",
                loadMode: "processed",
                useDefaultSearch: true,
                load: function (loadOptions) {
                    return $.getJSON("{{route('material.operation.routes.list')}}",
                        {data: JSON.stringify(loadOptions)});
                },
            });

            let operationsDataSource = new DevExpress.data.DataSource({
                reshapeOnPush: true,
                store: new DevExpress.data.CustomStore({
                    key: "id",
                    loadMode: "processed",
                    load: function (loadOptions) {
                        return $.getJSON("{{route('materials.operations.list')}}",
                            {data: JSON.stringify(loadOptions)});
                    },
                })
            });

            let materialTypesColumns = [
                {
                    dataField: "id",
                    caption: "Операция",
                    dataType: "number",
                    width: 140,
                    sortOrder: "desc",
                    sortIndex: 1,
                    cellTemplate: function (container, options) {
                        let operationId = options.data.id;
                        let operationUrl = options.data.url;

                        $(`<div><a class="dx-link dx-link-icon" href="${operationUrl}">Операция №${operationId}</a></div>`)
                            .appendTo(container);
                    },
                    tableName: "q3w_material_operations",
                },
                {
                    dataField: "operation_route_id",
                    dataType: "number",
                    caption: "Тип",
                    useTagBoxRowFilter: true,
                    lookup: {
                        dataSource: {
                            paginate: true,
                            pageSize: 25,
                            store: operationRoutesStore
                        },
                        displayExpr: "name",
                        valueExpr: "id",
                    },
                    tableName: "q3w_material_operations",
                },
                {
                    dataField: "material_types_info",
                    dataType: "string",
                    caption: "Материалы",
                    allowFiltering: false,
                    cellTemplate: function (container, options) {
                        container.html(options.displayValue);
                    }
                },
                {
                    dataField: "source_project_object_id",
                    dataType: "number",
                    caption: "Объект отправления",
                    useTagBoxRowFilter: true,
                    lookup: {
                        dataSource: {
                            paginate: true,
                            pageSize: 25,
                            store: projectObjectsStore
                        },
                        displayExpr: "short_name",
                        valueExpr: "id"
                    }
                },
                {
                    dataField: "destination_project_object_id",
                    dataType: "number",
                    caption: "Объект назначения",
                    useTagBoxRowFilter: true,
                    lookup: {
                        dataSource: {
                            paginate: true,
                            pageSize: 25,
                            store: projectObjectsStore
                        },
                        displayExpr: "short_name",
                        valueExpr: "id"
                    }
                },
                {
                    dataField: "operation_date",
                    dataType: "date",
                    caption: "Дата операции",
                    width: 100
                },
                {
                    dataField: "name",
                    dataType: "string",
                    caption: "Статус",
                    sortOrder: "asc",
                    sortIndex: 0,
                    cellTemplate: function (container, options) {
                        let data = options.data.name;

                        if (options.data.expected_users_names) {
                            data = data + "<br>" + options.data.expected_users_names;
                        }
                        container.html(data);
                    },
                    calculateSortValue: "route_stage_type_sort_order",
                },
            ];

            let operationsGridOptions = {
                editing: {
                    allowAdding: false,
                    allowUpdating: false,
                    allowDeleting: false
                },
                remoteOperations: true,
                dataSource: operationsDataSource,
                height: "calc(100vh - 259px)",
                columns: materialTypesColumns,
                onRowPrepared: (e) => {
                    if (e.rowType === "data") {
                        if (e.data.have_conflict) {
                            e.rowElement.addClass("row-conflict-operation")
                        }
                    }
                }
            };

            let operationGridForm = $("#formContainer").dxForm({
                formData: {
                    currentSelectedFilterGroup: null,
                    currentSelectedFilterGroupData: null
                },
                items: [
                    {
                        itemType: "group",
                        caption: "Список операций",
                        items: [
                            {
                                editorType: "skDataGrid",
                                name: "operationsGrid",
                                editorOptions: operationsGridOptions
                            }
                        ]
                    }
                ]
            }).dxForm("instance");

            function createGridReportButtons(){
                let groupCaption = $('.all-operations-grid').find('.dx-form-group-with-caption');
                $('<div>').addClass('dx-form-group-caption-buttons').prependTo(groupCaption);
                groupCaption.find('span').addClass('dx-form-group-caption-span-with-buttons');
                let groupCaptionButtonsDiv = groupCaption.find('.dx-form-group-caption-buttons');

                $('<div>')
                    .dxButton({
                        text: "Печать",
                        icon: "fa fa-print",
                        onClick: (e) => {
                            delete dataSourceLoadOptions.skip;
                            delete dataSourceLoadOptions.take;

                            $('#filterList').val(JSON.stringify(filterList));
                            $('#filterOptions').val(JSON.stringify(dataSourceLoadOptions));
                            $('#printAllOperations').submit();
                        }
                    })
                    .addClass('dx-form-group-caption-button')
                    .prependTo(groupCaptionButtonsDiv)
            }

            createGridReportButtons();
        });
    </script>
@endsection
