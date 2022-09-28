@extends('layouts.app')

@section('title', 'Заявки и приказы')

@section('url', route('labor-safety.orders-and-requests.index'))

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

        .dx-form-group.dx-group-no-border {
            border: 0;
            border-radius: 0;
        }

        .dx-form-group-caption-buttons {
            display: flex;
            flex-direction: row-reverse;
            align-items: flex-start;
        }

        .dx-placeholder {
            line-height: 6px;
        }

        .supply-planning-details {
            border: 1px solid #e0e0e0;
        }

        .dx-datagrid-borders > .dx-datagrid-header-panel {
            border-bottom: 0;
            border-top: 1px solid #e0e0e0;
            border-left: 1px solid #e0e0e0;
            border-right: 1px solid #e0e0e0;
        }

        .dx-datagrid-header-panel .dx-button {
            margin-top: 6px;
            margin-right: 8px;
        }

        .dx-tabpanel-tabs {
            display: none;
        }

        .order-types-grid {
            border-right: #e5e5e5 1px dashed;
        }

        .orders-header {
            color: #212121;
            font-size: larger;
            font-weight: bold;
            border-bottom: #e5e5e5 1px solid;
            padding-bottom: 10px;
            margin-bottom: -8px;
        }

        .order-types-panel {
            padding-top: 0 !important;
        }
    </style>
@endsection

@section('content')
    <div id="formContainer"></div>
    <div id="gridContainer"></div>
    <form id="downloadRequest" target="_blank" method="post" action="{{route('labor-safety.orders-and-requests.download')}}">
        @csrf
        <input id="requestId" type="hidden" name="requestId">
    </form>
@endsection

@section('js_footer')
    <script>
        let dataSourceLoadOptions = {};
        let currentSelectedOrder = {};
        let employeesData = new Map();
        let currentEditingRowIndex;
        let currentEditingRowKey;
        let selectedOrderTypes = [];
        let requestWorkersGrid;

        let usersStore = new DevExpress.data.CustomStore({
            key: "id",
            loadMode: "raw",
            load: function (loadOptions) {
                return $.getJSON("{{route('users.list')}}",
                    {data: JSON.stringify(loadOptions)});
            },
        });

        let employeesStore = new DevExpress.data.CustomStore({
            key: "id",
            loadMode: "raw",
            load: function (loadOptions) {
                return $.getJSON("{{route('employees.list')}}",
                    {data: JSON.stringify(loadOptions)});
            },
        });

        let statusesStore = new DevExpress.data.CustomStore({
            key: "id",
            loadMode: "raw",
            load: function (loadOptions) {
                return $.getJSON("{{route('labor-safety.statuses.list')}}",
                    {data: JSON.stringify(loadOptions)});
            },
        });

        let companiesStore = new DevExpress.data.CustomStore({
            key: "id",
            loadMode: "raw",
            load: function (loadOptions) {
                return $.getJSON("{{route('companies.list')}}",
                    {
                        loadOptions: JSON.stringify(loadOptions),
                    });
            },
        });

        let projectObjectsStore = new DevExpress.data.CustomStore({
            key: "id",
            loadMode: "raw",
            load: function (loadOptions) {
                return $.getJSON("{{route('project-objects.list')}}",
                    {data: JSON.stringify(loadOptions)});
            },
        })

        let projectObjectsDataSource = new DevExpress.data.DataSource({
            store: projectObjectsStore
        });

        let requestWorkersStoreData = [];

        let requestWorkersStore = new DevExpress.data.ArrayStore({
            key: "id",
            data: requestWorkersStoreData,
        });

        let requestWorkersDataSource = new DevExpress.data.DataSource({
            store: requestWorkersStore
        })

        $(function () {
            $("div.content").children(".container-fluid.pd-0-360").removeClass();
        });

        $(function () {
            //<editor-fold desc="JS: DataSources">

            let requestsDataSource = new DevExpress.data.DataSource({
                store: new DevExpress.data.CustomStore({
                    key: "id",
                    load: function (loadOptions) {
                        return $.getJSON("{{route('labor-safety.orders-and-requests.list')}}",
                            {
                                loadOptions: JSON.stringify(loadOptions),
                            });
                    },
                    insert: function (values) {
                        values.workers = requestWorkersGrid.getDataSource().store().createQuery().toArray();
                        return $.ajax({
                            url: "{{route('labor-safety.orders-and-requests.store')}}",
                            method: "POST",
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: {
                                data: JSON.stringify(values),
                                options: null
                            },
                            success: function (data, textStatus, jqXHR){
                                DevExpress.ui.notify("Данные успешно добавлены", "success", 1000)
                            },
                        })
                    },
                    update: function (key, values) {
                        values.workers = requestWorkersGrid.getDataSource().store().createQuery().toArray();
                        return $.ajax({
                            url: "{{route('labor-safety.orders-and-requests.update')}}",
                            method: "PUT",
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: {
                                key: key,
                                modifiedData: JSON.stringify(values)
                            },
                            success: function (data, textStatus, jqXHR) {
                                DevExpress.ui.notify("Данные успешно изменены", "success", 1000)
                            }
                        });
                    }
                })
            });

            let requestEditForm = {
                colCount: 3,
                items: [
                    {
                        dataField: "order_date",
                        label: {
                            text: "Дата приказа"
                        },
                        editorType: "dxDateBox",
                        editorOptions: {
                            dateSerializationFormat: "yyyy-MM-ddTHH:mm:ss"
                        }
                    },
                    {
                        dataField: "company_id",
                        label: {
                            text: "Организация"
                        },
                        itemType: "simpleItem",
                        editorType: "dxSelectBox",
                        editorOptions: {
                            dataSource: {
                                store: companiesStore
                            },
                            displayExpr: "name",
                            valueExpr: "id",
                            searchEnabled: true,
                        }
                    },
                    {
                        dataField: "project_object_id",
                        label: {
                            text: "Адрес объекта"
                        },
                        itemType: "simpleItem",
                        editorType: "dxSelectBox",
                        editorOptions: {
                            dataSource: {
                                store: projectObjectsStore,
                                paginate: true,
                                pageSize: 25,
                            },
                            displayExpr: 'short_name',
                            valueExpr: 'id',
                            searchEnabled: true
                        }
                    },
                    {
                        dataField: "responsible_employee_id",
                        label: {
                            text: "Ответственный сотрудник",
                        },
                        editorType: "dxSelectBox",
                        editorOptions: {
                            dataSource: new DevExpress.data.DataSource({
                                store: new DevExpress.data.CustomStore({
                                    key: "id",
                                    loadMode: "raw",
                                    load: function (loadOptions) {
                                        return $.getJSON("{{route('employees.list')}}",
                                            {data: JSON.stringify(loadOptions)});
                                    },
                                })
                            }),
                            displayExpr: "employee_1c_name",
                            valueExpr: "id",
                            searchEnabled: true
                        }
                    },
                    {
                        dataField: "sub_responsible_employee_id",
                        label: {
                            text: "Замещающий ответственного",
                        },
                        editorType: "dxSelectBox",
                        editorOptions: {
                            dataSource: new DevExpress.data.DataSource({
                                store: new DevExpress.data.CustomStore({
                                    key: "id",
                                    loadMode: "raw",
                                    load: function (loadOptions) {
                                        return $.getJSON("{{route('employees.list')}}",
                                            {data: JSON.stringify(loadOptions)});
                                    },
                                })
                            }),
                            displayExpr: "employee_1c_name",
                            valueExpr: "id",
                            searchEnabled: true
                        }
                    },
                    {
                        itemType: "empty"
                    },
                    {
                        colSpan: 3,
                        itemType: "simpleItem",
                        dataField: "workers",
                        name: 'workers',
                        cssClass: 'request-workers-grid',
                        label: {
                            text: "Персонал",
                            visible: false
                        },
                        editorType: "dxDataGrid",
                        editorOptions: {
                            onInitialized: (e) => {
                                requestWorkersGrid = e.component;
                                requestWorkersGrid.getDataSource().store().createQuery().toArray().forEach((item) => {
                                    requestWorkersGrid.getDataSource().store().push([{type: "remove", key: item.id}]);
                                });
                            },
                            onDisposing: (e) => {
                                requestWorkersGrid = undefined;
                            },
                            editing: {
                                mode: 'popup',
                                allowUpdating: true,
                                allowAdding: false,
                                allowDeleting: true,
                                selectTextOnEditStart: true,
                                newRowPosition: "last",
                                popup: {
                                    title: "Сотрудник",
                                    showTitle: false,
                                    width: "800",
                                    height: "auto",
                                    position: {
                                        my: "center",
                                        at: "center",
                                        of: window
                                    }
                                },
                                form: {
                                    colCount: 1
                                }
                            },
                            height: "40vh",
                            dataSource: requestWorkersDataSource,
                            hoverStateEnabled: true,
                            columnAutoWidth: true,
                            showBorders: true,
                            showColumnLines: true,
                            filterRow: {
                                visible: false,
                                applyFilter: "auto"
                            },
                            toolbar: {
                                visible: false
                            },
                            grouping: {
                                autoExpandAll: true,
                            },
                            groupPanel: {
                                visible: false
                            },
                            paging: {
                                enabled: false
                            },
                            columns: [
                                {
                                    dataField: "worker_employee_id",
                                    caption: "Сотрудники",
                                    lookup: {
                                        dataSource: {
                                            store: employeesStore,
                                            paginate: true,
                                            pageSize: 25,
                                        },
                                        displayExpr: 'employee_extended_name',
                                        valueExpr: 'id'
                                    },
                                    validationRules: [{type: "required"}]
                                },
                                {
                                    type: 'buttons',
                                    width: 150,
                                    buttons: [
                                        'edit',
                                        'delete',
                                    ],
                                    headerCellTemplate: (container, options) => {
                                        $('<div>')
                                            .appendTo(container)
                                            .dxButton({
                                                text: "Добавить",
                                                icon: "fas fa-plus",
                                                onClick: (e) => {
                                                    options.component.addRow();
                                                }
                                            })
                                    }
                                }
                            ]
                        }
                    }
                ]
            };

            let requestsForm = $("#formContainer").dxForm({
                items: [
                    {
                        itemType: "group",
                        caption: "Список заявок и приказов",
                        cssClass: "requests-grid",
                        items: [{
                            name: "requestsGrid",
                            editorType: "dxDataGrid",
                            editorOptions: {
                                dataSource: requestsDataSource,
                                focusedRowEnabled: false,
                                hoverStateEnabled: true,
                                columnAutoWidth: false,
                                showBorders: true,
                                showColumnLines: true,
                                filterRow: {
                                    visible: true,
                                    applyFilter: "auto"
                                },
                                toolbar: {
                                    visible: false
                                },
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
                                    mode: 'popup',
                                    allowUpdating: true,
                                    allowAdding: false,
                                    allowDeleting: false,
                                    selectTextOnEditStart: true,
                                    popup: {
                                        title: "Заявка",
                                        showTitle: true,
                                        width: "60%",
                                        //height: "75vh",
                                        height: "auto",
                                        position: {
                                            my: "center",
                                            at: "center",
                                            of: window
                                        },
                                        toolbarItems:[
                                            {
                                                toolbar:'bottom',
                                                location: 'before',
                                                widget: "dxButton",
                                                //visible:
                                                options: {
                                                    text: "Отменить заявку",
                                                    type: 'danger',
                                                    stylingMode: 'contained',
                                                    onClick: function(e){
                                                        //getRequestsGrid().saveEditData();
                                                    }
                                                }
                                            },
                                            {
                                                toolbar:'bottom',
                                                location: 'before',
                                                widget: "dxButton",
                                                options: {
                                                    text: "Сформировать документы",
                                                    type: 'default',
                                                    stylingMode: 'contained',
                                                    onClick: function(e){
                                                        if (!getRequestsGrid().hasEditData() && currentEditingRowKey) {
                                                            getRequestsGrid().cellValue(
                                                                currentEditingRowIndex,
                                                                "perform_orders",
                                                                true
                                                            )
                                                        }
                                                        getRequestsGrid().saveEditData();
                                                    }
                                                }
                                            },
                                            {
                                                toolbar:'bottom',
                                                location: 'after',
                                                widget: "dxButton",
                                                options: {
                                                    text: "Сохранить",
                                                    type: 'normal',
                                                    stylingMode: 'contained',
                                                    onClick: function(e) {
                                                        if (!getRequestsGrid().hasEditData() && currentEditingRowKey) {
                                                            getRequestsGrid().cellValue(
                                                                currentEditingRowIndex,
                                                                "perform_orders",
                                                                false
                                                            )
                                                        }

                                                        getRequestsGrid().saveEditData();
                                                    }
                                                }
                                            },
                                            {
                                                toolbar:'bottom',
                                                location: 'after',
                                                widget: "dxButton",
                                                options: {
                                                    text: "Отменить редактирование",
                                                    type: 'normal',
                                                    stylingMode: 'contained',
                                                    onClick: function(e){
                                                        console.log("e", e);
                                                        console.log("this", this);
                                                        getRequestsGrid().cancelEditData();
                                                    }
                                                }
                                            }
                                        ]
                                    },
                                    form: requestEditForm,
                                },
                                columns: [
                                    {
                                        dataField: "id",
                                        caption: "Идентификатор",
                                        width: 70
                                    },
                                    {
                                        dataField: "order_date",
                                        caption: "Дата приказа",
                                        dataType: "date",
                                        width: 120,
                                        validationRules: [{type: "required"}]
                                    },
                                    {
                                        dataField: "project_object_id",
                                        caption: "Объект",
                                        lookup: {
                                            dataSource: {
                                                store: projectObjectsStore,
                                                paginate: true,
                                                pageSize: 25,
                                            },
                                            displayExpr: 'short_name',
                                            valueExpr: 'id'
                                        },
                                        validationRules: [{type: "required"}]
                                    },
                                    {
                                        dataField: "company_id",
                                        caption: "Организация",
                                        width: 200,
                                        lookup: {
                                            dataSource: {
                                                store: companiesStore,
                                                paginate: true,
                                                pageSize: 25,
                                            },
                                            displayExpr: 'name',
                                            valueExpr: 'id'
                                        },
                                        validationRules: [{type: "required"}]
                                    },
                                    {
                                        dataField: "author_user_id",
                                        caption: "Автор",
                                        lookup: {
                                            dataSource: {
                                                store: usersStore,
                                                paginate: true,
                                                pageSize: 25,
                                            },
                                            displayExpr: 'full_name',
                                            valueExpr: 'id'
                                        },
                                    },
                                    {
                                        dataField: "implementer_user_id",
                                        caption: "Ответственный",
                                        lookup: {
                                            dataSource: {
                                                store: usersStore,
                                                paginate: true,
                                                pageSize: 25,
                                            },
                                            displayExpr: 'full_name',
                                            valueExpr: 'id'
                                        },
                                    },
                                    {
                                        dataField: "responsible_employee_id",
                                        visible: false
                                    },
                                    {
                                        dataField: "sub_responsible_employee_id",
                                        visible: false
                                    },
                                    {
                                        dataField: "request_status_id",
                                        caption: "Статус",
                                        lookup: {
                                            dataSource: {
                                                store: statusesStore,
                                                paginate: true,
                                                pageSize: 25,
                                            },
                                            displayExpr: 'name',
                                            valueExpr: 'id'
                                        },
                                    },
                                    {
                                        dataField: "perform_orders",
                                        dataType: "boolean",
                                        visible: false
                                    },
                                    {
                                        dataField: "workers",
                                        dataType: "boolean",
                                        visible: false
                                    },
                                    {
                                        type: 'buttons',
                                        width: 110,
                                        buttons: [
                                            'edit',
                                            {
                                                visible: (e) => {
                                                    return e.row.data.generated_html;
                                                },
                                                hint: 'Скачать',
                                                icon: 'download',
                                                onClick: (e) => {
                                                    $('#requestId').val(JSON.stringify(e.row.key));
                                                    $('#downloadRequest').get(0).submit();
                                                }
                                            }
                                        ]
                                    }
                                ],
                                onRowDblClick: function (e) {
                                    e.component.editRow(e.rowIndex);
                                },
                                onEditingStart: (e) => {
                                    currentEditingRowKey = e.key;
                                    currentEditingRowIndex = e.component.getRowIndexByKey(e.key);

                                    $.getJSON("{{route('labor-safety.request-workers.list')}}",
                                        {requestId: currentEditingRowKey})
                                        .done((data) => {
                                            data.forEach((item) => {
                                                console.log(item);
                                                requestWorkersGrid.getDataSource().store().push([{type: "insert", data: item}]);

                                            })

                                            console.log('data loaded');
                                        })
                                        .always(() => {
                                            console.log('end update');
                                            requestWorkersGrid.getDataSource().reload();
                                            requestWorkersGrid.endCustomLoading();
                                        });
                                }
                            }
                        }]
                    }
                ]
            }).dxForm('instance')

            @can('labor_safety_order_creation')
            function createGridGroupHeaderButtons() {
                let groupCaption = $('.requests-grid').find('.dx-form-group-with-caption');
                $('<div>').addClass('dx-form-group-caption-buttons').prependTo(groupCaption);
                groupCaption.find('span').addClass('dx-form-group-caption-span-with-buttons');
                let groupCaptionButtonsDiv = groupCaption.find('.dx-form-group-caption-buttons');

                $('<div>')
                    .dxButton({
                        text: "Добавить",
                        icon: "fas fa-plus",
                        onClick: (e) => {
                            getRequestsGrid().addRow();
                            currentEditingRowKey = undefined;
                            currentEditingRowIndex = undefined;
                            //getRequestWorkersDataSource().reload();
                        }
                    })
                    .addClass('dx-form-group-caption-button')
                    .prependTo(groupCaptionButtonsDiv)
            }
            createGridGroupHeaderButtons();
            @endcan

            function getRequestsGrid() {
                return requestsForm.getEditor("requestsGrid");
            }
        });
    </script>
@endsection
