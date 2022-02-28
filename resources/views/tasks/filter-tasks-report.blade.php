@extends('layouts.app')

@section('title', 'Отчет по задачам и КП')

@section('url', route('tasks::index'))
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

    #formContainer {
        width: 50%;
        margin-left: 25%;
    }
    .dx-box-item-content {
        justify-content: center;
    }
</style>
@endsection

@section('content')
    <div id="formContainer"></div>
    <form id="filterTasksReport" target="_blank" method="post" action="{{route('tasks.download-tasks-report')}}">
        @csrf
        <input id="filterOptions" type="hidden" name="filterOptions">
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

        let filterTasksReportForm = $("#formContainer").dxForm({
            items: [
                {
                    itemType: "group",
                    caption: "Фильтрация",
                    name: "filterGroup",
                    colCount: 4,

                    items: [
                        {
                            verticalAlignment: "center",
                            dataField: 'addressCheckBox',
                            dataType: 'boolean',
                            caption: "Адрес",
                            value: true,
                            label: {
                                visible: false
                            },
                            editorType: "dxCheckBox",
                            editorOptions: {
                                value: false,
                                text: "Адрес"
                            }
                        },
                        {
                            colSpan: 3,
                            dataField: 'addressComboBox',
                            dataType: 'integer',
                            caption: "Адрес",
                            value: true,
                            label: {
                                visible: false
                            },
                            editorType: "dxSelectBox",
                            editorOptions: {
                                displayExpr: "project_object_name",
                                valueExpr: "id",
                                dataSource: {
                                    paginate: true,
                                    pageSize: 25,
                                    store: new DevExpress.data.CustomStore({
                                        key: "id",
                                        loadMode: "processed",
                                        load: function (loadOptions) {
                                            return $.getJSON("{{route('tasks.current-user-tasks-project-objects.list')}}",
                                                {data: JSON.stringify(loadOptions)});
                                        }
                                    })
                                },
                                searchEnabled: true
                            }
                        },
                        {
                            dataField: 'contractorCheckBox',
                            dataType: 'boolean',
                            caption: "Заказчик",
                            value: true,
                            label: {
                                visible: false
                            },
                            editorType: "dxCheckBox",
                            editorOptions: {
                                value: false,
                                text: "Заказчик"
                            }
                        },
                        {
                            colSpan: 3,
                            dataField: 'contractorComboBox',
                            dataType: 'integer',
                            caption: "Заказчик",
                            value: true,
                            label: {
                                visible: false
                            },
                            editorType: "dxSelectBox",
                            editorOptions: {
                                displayExpr: "short_name",
                                valueExpr: "id",
                                dataSource: {
                                    paginate: true,
                                    pageSize: 25,
                                    store: new DevExpress.data.CustomStore({
                                        key: "id",
                                        loadMode: "processed",
                                        load: function (loadOptions) {
                                            return $.getJSON("{{route('tasks.current-user-tasks-contractors.list')}}",
                                                {data: JSON.stringify(loadOptions)});
                                        }
                                    })
                                },
                                searchEnabled: true
                            }
                        },
                        {
                            dataField: 'splitMaterialsCheckBox',
                            dataType: 'boolean',
                            caption: "Адрес",
                            value: true,
                            label: {
                                visible: false
                            },
                            editorType: "dxCheckBox",
                            editorOptions: {
                                value: false,
                                text: "Номенклатура"
                            }
                        },
                        {
                            colSpan: 3,
                            dataField: 'splitMaterialsTagBox',
                            dataType: 'integer',
                            caption: "Адрес",
                            value: true,
                            label: {
                                visible: false
                            },
                            editorType: "dxTagBox",
                            editorOptions: {
                                displayExpr: "material_name",
                                valueExpr: "man_mat_id",
                                dataSource: {
                                    paginate: true,
                                    pageSize: 25,
                                    store: new DevExpress.data.CustomStore({
                                        key: "id",
                                        loadMode: "raw",
                                        load: function (loadOptions) {
                                            return $.getJSON("{{route('tasks.current-user-tasks-split-material.list')}}",
                                                {data: JSON.stringify(loadOptions)});
                                        }
                                    })
                                },
                                searchEnabled: true,
                                showSelectionControls: true
                            }
                        },
                        {
                            itemType: "empty"
                        },
                        {
                            itemType: "button",
                            colSpan: 3,
                            buttonOptions: {
                                text: "Печать",
                                icon: "fa fa-print",
                                onClick: () => {
                                    let filterExpression = generateFilterExpression(filterTasksReportForm.option('formData'));
                                    $('#filterOptions').val(JSON.stringify({filter: filterExpression}));
                                    $('#filterTasksReport').get(0).submit();

                                    console.log('filterExpression:', generateFilterExpression(filterTasksReportForm.option('formData')));
                                }
                            }
                        }
                    ]
                }
            ]
        }).dxForm("instance");

        function generateFilterExpression(data) {
            let filterArray = [];
            if (data.addressCheckBox){
                if (data.addressComboBox){
                    filterArray.push(['project_objects.id', '=', data.addressComboBox]);
                }
            }

            if (data.contractorCheckBox){
                if (data.contractorComboBox){
                    if (filterArray.length !== 0) {
                        filterArray.push('and');
                    }

                    filterArray.push(['contractors.id', '=', data.contractorComboBox]);
                }
            }

            if (data.splitMaterialsCheckBox){
                if (data.splitMaterialsTagBox){
                    if (filterArray.length !== 0) {
                        filterArray.push('and');
                    }

                    let splitMaterialsFilterArray = [];

                    data.splitMaterialsTagBox.forEach((item, i) => {
                        splitMaterialsFilterArray.push(['man_mat_id', '=', item])

                        if (data.splitMaterialsTagBox.length !== i + 1) {
                            splitMaterialsFilterArray.push('or');
                        }
                    })
                    filterArray.push(splitMaterialsFilterArray);
                }
            }

            return filterArray;
        }
    });
</script>

@endsection
