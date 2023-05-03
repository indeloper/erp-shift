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

        .employee-role {
            font-size: smaller;
            font-style: oblique;
            color: #9b9797;
        }

        .employee-name {
            text-overflow: ellipsis;
            overflow: hidden;
        }

        .employee-post {
            font-size: smaller;
            font-style: oblique;
            color: #9b9797;
            text-overflow: ellipsis;
            overflow: hidden;
        }


        .tag-cell-editor {
            padding-left: 11px !important;
            padding-right: 11px !important;
        }

        .tag-cell-value {
            display: flex !important;
            flex-wrap: wrap !important;
        }

        .dx-tag-content-without-delete {
            padding: 4px 11px 4px 8px !important;
        }
    </style>
@endsection

@section('content')
    <div id="formContainer"></div>
    <div id="gridContainer"></div>
    <form id="downloadRequest" target="_blank" method="post"
          action="{{route('labor-safety.orders-and-requests.download')}}">
        @csrf
        <input id="requestId" type="hidden" name="requestId">
    </form>
@endsection

@section('js_footer')
    <script>
        let employeesData = new Map();
        let currentEditingRowIndex;
        let currentEditingRowKey;
        let requestWorkersGrid;
        let isOrderNumberVisible = false;
        let editAction = "";

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
            }
        });

        let employeesDataSource = new DevExpress.data.DataSource({
            store: employeesStore
        })

        let employeesStoreForSelectors = new DevExpress.data.CustomStore({
            key: "id",
            loadMode: "raw",
            load: function (loadOptions) {
                return $.getJSON("{{route('employees.list')}}",
                    {data: JSON.stringify(loadOptions)});
            }
        });

        let employeesDataSourceForSelectors = new DevExpress.data.DataSource({
            store: employeesStoreForSelectors
        })

        employeesStore.load();
        employeesStoreForSelectors.load();

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

        let workersTypesStore = new DevExpress.data.CustomStore({
            key: "id",
            loadMode: "raw",
            load: function (loadOptions) {
                return $.getJSON("{{route('labor-safety.request-workers.worker-types')}}",
                    {data: JSON.stringify(loadOptions)});
            },
        })

        let workersTypesDataSource = new DevExpress.data.DataSource({
            store: workersTypesStore
        })
        workersTypesDataSource.load();

        let orderTypesData = [];

        let orderTypesStore = new DevExpress.data.CustomStore({
            key: "id",
            loadMode: "raw",
            load: function (loadOptions) {
                return $.getJSON("{{route('labor-safety.order-types.short-name-list')}}",
                    {data: JSON.stringify(loadOptions)});
            },
            onLoaded: (data) => {
                orderTypesData = data;
            }
        })

        let orderTypesDataSource = new DevExpress.data.DataSource({
            store: orderTypesStore
        });
        orderTypesDataSource.load();


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
                            success: function (data, textStatus, jqXHR) {
                                DevExpress.ui.notify("Данные успешно добавлены", "success", 1000)
                            },
                        })
                    },
                    update: function (key, values) {
                        values.workers = requestWorkersGrid.getDataSource().store().createQuery().toArray();
                        values.editAction = editAction;
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

            function getWorkersListEditForm() {
                return {
                    colCount: 1,
                    items: [
                        {
                            dataField: "worker_employee_id",
                        },
                        {
                            dataField: "employee_role_id",
                        }
                    ]
                }
            }

            function getRequestEditForm(requestStatusId) {
                return {
                    colCount: 4,
                    items: [
                        {
                            dataField: "order_number",
                            label: {
                                text: "Номер приказа"
                            },
                            visible: typeof (currentEditingRowKey) !== "undefined",
                            editorType: "dxTextBox",
                            editorOptions: {
                                visible: typeof (currentEditingRowKey) !== "undefined",
                                readOnly: isRowReadOnly(requestStatusId)
                            }
                        },
                        {
                            dataField: "order_date",
                            label: {
                                text: "Дата приказа"
                            },
                            editorType: "dxDateBox",
                            editorOptions: {
                                readOnly: isRowReadOnly(requestStatusId),
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
                                readOnly: isRowReadOnly(requestStatusId),
                            }
                        },
                        {
                            colSpan: 2,
                            dataField: "project_object_id",
                            label: {
                                text: "Адрес объекта"
                            },
                            itemType: "simpleItem",
                            editorType: "dxSelectBox",
                            editorOptions: {
                                readOnly: isRowReadOnly(requestStatusId),
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
                            dataField: "project_manager_employee_id",
                            visible: typeof (currentEditingRowKey) === 'undefined',
                        },
                        {
                            dataField: "sub_project_manager_employee_id",
                            visible: typeof (currentEditingRowKey) === 'undefined',
                        },
                        {
                            dataField: "responsible_employee_id",
                            visible: typeof (currentEditingRowKey) === 'undefined'
                        },
                        {
                            dataField: "sub_responsible_employee_id",
                            visible: typeof (currentEditingRowKey) === 'undefined',
                        },
                        {
                            colSpan: 4,
                            itemType: "empty",
                            visible: typeof (currentEditingRowKey) === 'undefined',
                        },
                        getWorkersSectionConfig(requestStatusId)
                    ]
                };
            }

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
                                editing: {
                                    mode: 'popup',
                                    allowUpdating: true,
                                    allowAdding: false,
                                    allowDeleting: false,
                                    selectTextOnEditStart: true,
                                    popup: getRequestEditingPopup(),
                                    form: getRequestEditForm(),
                                },
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
                                    enabled: true,
                                    pageSize: 50
                                },
                                columns: [
                                    {
                                        dataField: "id",
                                        caption: "Идентификатор",
                                        width: 70,
                                        sortIndex: 0,
                                        sortOrder: "desc"
                                    },
                                    {
                                        dataField: "order_number",
                                        caption: "Номер приказа",
                                        dataType: "string",
                                        width: 90
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
                                        visible: false,
                                        formItem: {
                                            label: {
                                                text: "Ответственный",
                                            },
                                            editorType: "dxSelectBox",
                                            editorOptions: {
                                                dataSource: employeesDataSourceForSelectors,
                                                displayExpr: "employee_1c_name",
                                                valueExpr: "id",
                                                searchEnabled: true,
                                                itemTemplate: getEmployeesSelectBoxTemplate
                                            },
                                            validationRules: [{type: "required"}]
                                        }
                                    },
                                    {
                                        dataField: "sub_responsible_employee_id",
                                        visible: false,
                                        formItem: {
                                            label: {
                                                text: "Замещающий ответственного",
                                            },
                                            editorType: "dxSelectBox",
                                            editorOptions: {
                                                dataSource: employeesDataSourceForSelectors,
                                                displayExpr: "employee_1c_name",
                                                valueExpr: "id",
                                                searchEnabled: true,
                                                itemTemplate: getEmployeesSelectBoxTemplate
                                            }
                                        }
                                    },
                                    {
                                        dataField: "project_manager_employee_id",
                                        visible: false,
                                        formItem: {
                                            label: {
                                                text: "Руководитель проекта",
                                            },
                                            editorType: "dxSelectBox",
                                            editorOptions: {
                                                dataSource: employeesDataSourceForSelectors,
                                                displayExpr: "employee_1c_name",
                                                valueExpr: "id",
                                                searchEnabled: true,
                                                itemTemplate: getEmployeesSelectBoxTemplate
                                            },
                                            validationRules: [{type: "required"}]
                                        }
                                    },
                                    {
                                        dataField: "sub_project_manager_employee_id",
                                        visible: false,
                                        formItem: {
                                            label: {
                                                text: "Зам. Руководителя проекта",
                                            },
                                            editorType: "dxSelectBox",
                                            editorOptions: {
                                                dataSource: employeesDataSourceForSelectors,
                                                displayExpr: "employee_1c_name",
                                                valueExpr: "id",
                                                searchEnabled: true,
                                                itemTemplate: getEmployeesSelectBoxTemplate
                                            }
                                        }
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
                                            {
                                                name: 'edit',
                                                visible: (e) => {
                                                    return !isRowReadOnly(e.row.data.request_status_id)
                                                },
                                                onClick: (e) => {
                                                    employeesDataSourceForSelectors.filter(["company_id", "=", e.row.data.company_id]);
                                                    employeesDataSourceForSelectors.load()

                                                    getRequestsGrid().editRow(e.row.rowIndex);
                                                }
                                            },
                                            {
                                                name: 'view',
                                                icon: 'fas fa-list-alt',
                                                visible: (e) => {
                                                    return isRowReadOnly(e.row.data.request_status_id)
                                                },
                                                onClick: (e) => {
                                                    getRequestsGrid().editRow(e.row.rowIndex);
                                                }
                                            },
                                            {
                                                visible: (e) => {
                                                    return e.row.data.is_orders_generated && isUserCanGenerateOrders();
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
                                onEditorPreparing: function(e) {
                                    if (e.dataField === "company_id" && e.parentType === "dataRow") {
                                        e.editorOptions.onValueChanged = (args) => {
                                            employeesDataSourceForSelectors.filter(["company_id", "=", args.value]);
                                            employeesDataSourceForSelectors.load()

                                            e.setValue(args.value)
                                        }
                                    }
                                },
                                onRowDblClick: function (e) {
                                    employeesDataSourceForSelectors.filter(["company_id", "=", e.data.company_id]);
                                    employeesDataSourceForSelectors.load()

                                    e.component.editRow(e.rowIndex);
                                },
                                onEditingStart: (e) => {
                                    currentEditingRowKey = e.key;
                                    currentEditingRowIndex = e.component.getRowIndexByKey(e.key);
                                    e.component.option("editing.form", getRequestEditForm(e.data.request_status_id));
                                    e.component.option("editing.popup", getRequestEditingPopup(e.data.request_status_id));

                                    isOrderNumberVisible = true;

                                    $.getJSON("{{route('labor-safety.request-workers.list')}}",
                                        {requestId: currentEditingRowKey})
                                        .done((data) => {
                                            requestWorkersStore.createQuery().toArray().forEach((item) => {
                                                requestWorkersGrid.getDataSource().store().push([{
                                                    type: "remove",
                                                    key: item.id
                                                }]);
                                            });

                                            data.forEach((item) => {
                                                requestWorkersGrid.getDataSource().store().push([{
                                                    type: "insert",
                                                    data: item
                                                }]);

                                            })
                                        })
                                        .always(() => {
                                            requestWorkersGrid.getDataSource().reload();
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
                        onClick: () => {
                            employeesDataSourceForSelectors.filter(null);
                            employeesDataSourceForSelectors.load();

                            currentEditingRowKey = undefined;
                            currentEditingRowIndex = undefined;

                            isOrderNumberVisible = false;

                            requestWorkersStore.createQuery().toArray().forEach((item) => {
                                requestWorkersGrid.getDataSource().store().push([{
                                    type: "remove",
                                    key: item.id
                                }]);
                            });

                            getRequestsGrid().option("editing.popup", getRequestEditingPopup());
                            getRequestsGrid().option("editing.form", getRequestEditForm());
                            getRequestsGrid().addRow();
                        }
                    })
                    .addClass('dx-form-group-caption-button')
                    .prependTo(groupCaptionButtonsDiv)
            }

            createGridGroupHeaderButtons();
            @endcan

            function getWorkersSectionConfig(requestStatusId) {
                let canGenerateDocuments = false;
                @can('labor_safety_generate_documents_access')
                    canGenerateDocuments = true;
                @endcan

                let config = getWorkersSectionConfigForWorkersEditing(requestStatusId);

                if ((typeof (currentEditingRowKey) !== "undefined") && canGenerateDocuments) {
                    config.editorOptions.columns = getWorkersColumnsForDocumentGeneration(requestStatusId);
                    config.editorOptions.editing.allowUpdating = !isRowReadOnly(requestStatusId);
                    config.editorOptions.editing.allowDeleting = !isRowReadOnly(requestStatusId);
                    config.editorOptions.editing.mode = 'cell';
                } else {
                    config.editorOptions.columns = getWorkersColumnsForEditing(requestStatusId);
                    config.editorOptions.editing.allowUpdating = true;
                    config.editorOptions.editing.allowDeleting = true;
                    config.editorOptions.editing.mode = 'popup';
                }

                return config;
            }

            function getWorkersSectionConfigForWorkersEditing(requestStatusId) {
                return {
                    colSpan: 4,
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
                            requestWorkersGrid.getDataSource().reload();
                        },
                        onDisposing: (e) => {
                            requestWorkersGrid = undefined;
                        },
                        onSaved: (e) => {
                            let canGenerateDocuments = false;
                            @can('labor_safety_generate_documents_access')
                                canGenerateDocuments = true;
                            @endcan

                            if ((typeof (currentEditingRowKey) !== "undefined") && canGenerateDocuments) {
                                e.component.option("editing.mode", "cell");
                            } else {
                                e.component.option("editing.mode", "popup");
                            }
                        },
                        onSaving: (e) => {
                            if (e.changes.length !== 0) {
                                if (e.changes[0].type === "remove") {
                                    return;
                                }

                                employeesDataSource.store().byKey(e.changes[0].data.worker_employee_id).then(
                                    (dataItem => {
                                        e.changes[0].data.employee_1c_name = dataItem.employee_1c_name;
                                        e.changes[0].data.company_name = dataItem.company_name;
                                        e.changes[0].data.post_name = dataItem.post_name;
                                    })
                                )

                                workersTypesDataSource.store().byKey(e.changes[0].data.employee_role_id).then(
                                    (dataItem => {
                                        e.changes[0].data.employee_role = dataItem.name;
                                        if (!e.changes[0].data.orders){
                                            e.changes[0].data.orders = [];
                                        }

                                        switch (e.changes[0].data.employee_role_id) {
                                            case 4:
                                                e.changes[0].data.orders = [16];
                                                break;
                                            case 5:
                                                e.changes[0].data.orders = [17];
                                                break;
                                            case 6:
                                                e.changes[0].data.orders = [20];
                                                break;
                                            case 7:
                                                e.changes[0].data.orders = [22];
                                                break;
                                            case 8:
                                                e.changes[0].data.orders = [24];
                                                break;
                                            case 11:
                                                e.changes[0].data.orders = [14];
                                                break;
                                            case 12:
                                                e.changes[0].data.orders = [14];
                                                break;
                                        }
                                    })
                                )
                            }
                        },
                        editing: {
                            allowAdding: false,
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
                        onEditorPreparing: (e) => {
                            if (e.dataField === "quantity" && e.parentType === "dataRow") {
                                if (e.row.data.accounting_type === 2 && e.row.data.edit_states.indexOf("addedByRecipient") === -1) {
                                    e.cancel = true;
                                    e.editorElement.append($(`<div>${e.row.data.quantity} ${e.row.data.measure_unit_value}</div>`))
                                }
                            }
                        },
                        onInitNewRow: (e) => {
                            e.data.employee_role_id = 3
                        }
                    }
                }
            }

            function getWorkersColumnsForDocumentGeneration(requestStatusId) {
                return [
                    {
                        dataField: "worker_employee_id",
                        dataType: "string",
                        caption: "Сотрудник",
                        width: "40%",
                        cellTemplate: (container, options) => {
                            $(`<div class="employee-name">${options.data.employee_1c_name}</div>`)
                                .appendTo(container);

                            $(`<div class="employee-role">${options.data.employee_role}</div>`)
                                .appendTo(container);

                            $(`<div class="employee-post">${options.data.post_name} (${options.data.company_name})</div>`)
                                .appendTo(container);

                        },
                        formItem: {
                            label: {
                                text: "Сотрудник",
                            },
                            editorType: "dxSelectBox",
                            editorOptions: {
                                dataSource: employeesDataSourceForSelectors,
                                displayExpr: 'employee_extended_name',
                                valueExpr: 'id',
                                searchEnabled: true
                            }
                        },
                        validationRules: [{type: "required"}]
                    },
                    {
                        dataField: "orders",
                        caption: "Документы",
                        width: "60%",
                        editCellTemplate: (cellElement, cellInfo) => {
                            return $('<div class="tag-cell-editor">').dxTagBox({
                                dataSource: orderTypesDataSource,
                                value: cellInfo.value,
                                valueExpr: 'id',
                                displayExpr: 'short_name',
                                showSelectionControls: true,
                                showMultiTagOnly: false,
                                applyValueMode: 'instantly',
                                searchEnabled: true,
                                onValueChanged(e) {
                                    cellInfo.setValue(e.value);
                                },
                                onSelectionChanged() {
                                    cellInfo.component.updateDimensions();
                                },
                            })
                        },
                        lookup: {
                            dataSource: {
                                store: orderTypesStore,
                                paginate: true,
                                pageSize: 25,
                            },
                            displayExpr: 'short_name',
                            valueExpr: 'id'
                        },
                        cellTemplate(container, options) {
                            let textValues = '';

                            let valueArray = options.value;

                            if (valueArray) {
                                valueArray.sort((a, b) => {
                                    if (a > b) return 1;
                                    if (a === b) return 0;
                                    if (a < b) return -1;
                                })


                                valueArray.forEach((item) => {
                                    textValues += `<div class="dx-tag">
                                                <div class="dx-tag-content-without-delete dx-tag-content">
                                                    <span>
                                                        ${options.column.lookup.calculateCellValue(item)}
                                                    </span>
                                                </div>
                                               </div>`
                                });
                            }

                            container.append($(`<div class="tag-cell-value">${textValues}</div>`))
                        }
                    },
                    {
                        dataField: "employee_role_id",
                        visible: false,
                        formItem: {
                            label: {
                                text: "Роль",
                            },
                            editorType: "dxSelectBox",
                            editorOptions: {
                                dataSource: new DevExpress.data.DataSource({
                                    store: workersTypesStore,
                                    paginate: true,
                                    pageSize: 25,
                                    filter: [
                                        ["id", "<>", 1],
                                        "and",
                                        ["id", "<>", 2],
                                        "and",
                                        ["id", "<>", 9],
                                        "and",
                                        ["id", "<>", 10],
                                    ]
                                }),
                                displayExpr: 'name',
                                valueExpr: 'id',
                                searchEnabled: true
                            }
                        }
                    },
                    {
                        type: 'buttons',
                        width: 150,
                        visible: true,
                        buttons: [
                            {
                                name: 'delete',
                                visible: (e) => {
                                    return !isRowReadOnly(requestStatusId) && ![1,2,9,10].includes(e.row.data.employee_role_id)
                                }
                            }
                        ],
                        headerCellTemplate: (container, options) => {
                            console.log('headerCellTemplate', options);
                            //if (options.data.requestStatusId && !isRowReadOnly(options.data.requestStatusId)) {
                            $('<div>')
                                .appendTo(container)
                                .dxButton({
                                    text: "Добавить",
                                    icon: "fas fa-plus",
                                    onClick: (e) => {
                                        options.component.option('editing.mode', 'popup');
                                        options.component.option('editing.form', getWorkersListEditForm());
                                        options.component.option('editing.popup', getWorkersEditingPopup());
                                        options.component.addRow();
                                    }
                                })
                            //}
                        }
                    }
                ]
            }

            function getWorkersColumnsForEditing(requestStatusId) {
                return [
                    {
                        dataField: "worker_employee_id",
                        dataType: "string",
                        caption: "Сотрудник",
                        width: "40%",
                        cellTemplate: (container, options) => {
                            $(`<div class="employee-name">${options.data.employee_1c_name}</div>`)
                                .appendTo(container);

                            $(`<div class="employee-role">${options.data.employee_role}</div>`)
                                .appendTo(container);

                            $(`<div class="employee-post">${options.data.post_name} (${options.data.company_name})</div>`)
                                .appendTo(container);

                        },
                        formItem: {
                            label: {
                                text: "Сотрудник",
                            },
                            editorType: "dxSelectBox",
                            editorOptions: {
                                dataSource: employeesDataSourceForSelectors,
                                displayExpr: 'employee_extended_name',
                                valueExpr: 'id',
                                searchEnabled: true
                            }
                        },
                        validationRules: [{type: "required"}]
                    },
                    {
                        dataField: "employee_role_id",
                        visible: false,
                        formItem: {
                            label: {
                                text: "Роль",
                            },
                            editorType: "dxSelectBox",
                            editorOptions: {
                                dataSource: new DevExpress.data.DataSource({
                                    store: workersTypesStore,
                                    paginate: true,
                                    pageSize: 25,
                                    filter: [
                                        ["id", "<>", 1],
                                        "and",
                                        ["id", "<>", 2],
                                        "and",
                                        ["id", "<>", 9],
                                        "and",
                                        ["id", "<>", 10],
                                    ]
                                }),
                                displayExpr: 'name',
                                valueExpr: 'id',
                                searchEnabled: true
                            }
                        }
                    },
                    {
                        type: 'buttons',
                        width: 150,
                        visible: !isRowReadOnly(requestStatusId),
                        buttons: [
                            {
                                name: 'edit',
                                visible: (e) => {
                                    return !isRowReadOnly(requestStatusId) && ![1,2,9,10].includes(e.row.data.employee_role_id)
                                }
                            },
                            {
                                name: 'delete',
                                visible: (e) => {
                                    return !isRowReadOnly(requestStatusId) && ![1,2,9,10].includes(e.row.data.employee_role_id)
                                }
                            }
                        ],
                        headerCellTemplate: (container, options) => {
                            if (!isRowReadOnly(requestStatusId)) {
                                $('<div>')
                                    .appendTo(container)
                                    .dxButton({
                                        text: "Добавить",
                                        icon: "fas fa-plus",
                                        onClick: (e) => {
                                            options.component.option("editing.popup", getWorkersEditingPopup());
                                            options.component.option("editing.form", getWorkersListEditForm());
                                            options.component.addRow();
                                        }
                                    })
                            }
                        }
                    }
                ]
            }

            function getRequestsGrid() {
                return requestsForm.getEditor("requestsGrid");
            }

            function getRequestEditingPopup(requestStatus) {
                return {
                    title: "Заявка",
                    showTitle: true,
                    width: "60%",
                    height: "auto",
                    showCloseButton: true,
                    position: {
                        my: "center",
                        at: "center",
                        of: window
                    },
                    toolbarItems: getEditFormToolbarOptions(requestStatus)
                }
            }

            function getWorkersEditingPopup() {
                return {
                    title: "Сотрудник",
                    showTitle: true,
                    width: "40%",
                    height: "auto",
                    showCloseButton: true,
                    position: {
                        my: "center",
                        at: "center",
                        of: window
                    },
                    //toolbarItems: getEditFormToolbarOptions()
                }
            }

            function isUserCanGenerateOrders() {
                let result = false;

                @can('labor_safety_generate_documents_access')
                    result = true;
                @endcan

                return result;
            }

            function isRowReadOnly(requestStatus) {
                return requestStatus === 3 || requestStatus === 4 || (requestStatus === 2 && !isUserCanGenerateOrders());
            }

            function getEditFormToolbarOptions(requestStatus) {
                const isInEditing = typeof (currentEditingRowIndex) !== "undefined";

                return [
                    {
                        toolbar: 'bottom',
                        location: 'before',
                        widget: "dxButton",
                        visible: isInEditing && !isRowReadOnly(requestStatus) && isUserCanGenerateOrders(),
                        options: {
                            text: "Отменить заявку",
                            type: 'danger',
                            stylingMode: 'contained',
                            onClick: (e) => {
                                editAction = "cancelRequest";
                                if (!getRequestsGrid().hasEditData() && currentEditingRowKey) {
                                    getRequestsGrid().cellValue(
                                        currentEditingRowIndex,
                                        "perform_orders",
                                        isUserCanGenerateOrders()
                                    )
                                }
                                getRequestsGrid().saveEditData();
                            }
                        }
                    },
                    {
                        toolbar: 'bottom',
                        location: 'before',
                        widget: "dxButton",
                        visible: isInEditing && !isRowReadOnly(requestStatus) && isUserCanGenerateOrders(),
                        options: {
                            text: "Завершить",
                            type: 'default',
                            stylingMode: 'contained',
                            onClick: function (e) {
                                editAction = "completeRequest";
                                if (!getRequestsGrid().hasEditData() && currentEditingRowKey) {
                                    getRequestsGrid().cellValue(
                                        currentEditingRowIndex,
                                        "perform_orders",
                                        isUserCanGenerateOrders()
                                    )
                                }
                                getRequestsGrid().saveEditData();
                            }
                        }
                    },
                    {
                        toolbar: 'bottom',
                        location: 'after',
                        widget: "dxButton",
                        visible: !isRowReadOnly(requestStatus),
                        options: {
                            text: "Сохранить",
                            type: 'normal',
                            stylingMode: 'contained',
                            onClick: function () {
                                editAction = "saveRequest";

                                getRequestsGrid().cellValue(
                                    currentEditingRowIndex,
                                    "perform_orders",
                                    isUserCanGenerateOrders() && currentEditingRowKey !== undefined
                                )

                                getRequestsGrid().saveEditData();
                            }
                        }
                    },
                    {
                        toolbar: 'bottom',
                        location: 'after',
                        widget: "dxButton",
                        visible: !isRowReadOnly(requestStatus),
                        options: {
                            text: "Отменить редактирование",
                            type: 'normal',
                            stylingMode: 'contained',
                            onClick: function (e) {
                                getRequestsGrid().cancelEditData();
                            }
                        }
                    }
                ]
            }

            function getEmployeesSelectBoxTemplate(data) {
                return `<div class="employee-name">${data.employee_1c_name}</div>` +
                `<div class="employee-post">${data.post_name} (${data.company_name})</div>`;
            }
        });
    </script>
@endsection
