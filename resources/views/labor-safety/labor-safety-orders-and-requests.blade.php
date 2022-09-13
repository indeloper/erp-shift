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
        let ordersData = new Map();
        let currentEditingRowIndex;
        let currentEditingRowKey;

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
                return $.getJSON("{{route('users.list')}}",
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

        $(function () {
            $("div.content").children(".container-fluid.pd-0-360").removeClass();
        });

        function getEditFormAttributesTemplate() {
            console.log(currentSelectedOrder);
            let formItems;
            let orderAttributes = {};
            if (ordersData.has(currentSelectedOrder.id)) {
                orderAttributes = ordersData.get(currentSelectedOrder.id);
            }

            switch(currentSelectedOrder.order_type_category_id) {
                case 1:
                    formItems = [
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
                                            return $.getJSON("{{route('users.list')}}",
                                                {data: JSON.stringify(loadOptions)});
                                        },
                                    })
                                }),
                                displayExpr: "full_name",
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
                                            return $.getJSON("{{route('users.list')}}",
                                                {data: JSON.stringify(loadOptions)});
                                        },
                                    })
                                }),
                                displayExpr: "full_name",
                                valueExpr: "id",
                                searchEnabled: true
                            }
                        },

                    ]
                    break;
                case 2:
                    break;
                case 3:
                    break;
                case 4:
                    break;
                case 5:
                    break;
                case 6:
                    break;
                case 7:
                    break;
                case 8:
                    break;
                case 9:
                    break;
                case 10:
                    break;
            }



            let attributesFormDiv = $(`<div>`);
            let attributesForm = attributesFormDiv.dxForm({
                colCount: 2,
                formData: orderAttributes,
                items: formItems
            }).dxForm("instance");

            ordersData.set(currentSelectedOrder.id, attributesForm.option("formData"));

            console.log("ordersData", ordersData)

            return(attributesFormDiv)
        }

        $(function () {
            //<editor-fold desc="JS: DataSources">
            let orderTypesDataSource = new DevExpress.data.DataSource({
                store: new DevExpress.data.CustomStore({
                    key: "id",
                    load: function (loadOptions) {
                        return $.getJSON("{{route('labor-safety.order-types.list')}}",
                            {
                                loadOptions: JSON.stringify(loadOptions),
                            });
                    },
                })
            })

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
                        values.ordersData = Array.from(ordersData.entries());
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

                            /*error: function(jqXHR, textStatus, errorThrown) {
                                DevExpress.ui.notify("При сохранении данных произошла ошибка", "error", 5000)
                            }*/
                        })
                    },
                    update: function (key, values) {
                        values.ordersData = Array.from(ordersData.entries());
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

            let editForm = {
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
                        label: {
                            text: "Приказ",
                            visible: false
                        },
                        itemType: "simpleItem",
                        name: "orderTypesGrid",
                        editorType: "dxDataGrid",
                        editorOptions: {
                            height: "60vh",
                            focusedRowEnabled: true,
                            dataSource: orderTypesDataSource,
                            showColumnHeaders: false,
                            selection: {
                                allowSelectAll: false,
                                deferred: false,
                                mode: "multiple",
                                selectAllMode: "allPages",
                                showCheckBoxesMode: "always"
                            },
                            paging: {
                                enabled: false
                            },
                            columns: [
                                {
                                    dataField: "short_name",
                                    cellTemplate: (container, options) => {
                                        let orderTypeName = `[${options.data.short_name}] ${options.data.name}`;
                                        $(`<div>${orderTypeName}</div>`)
                                            .appendTo(container);
                                    }
                                }
                            ],
                            onSelectionChanged: (e) => {
                                if (e.currentSelectedRowKeys.length > 0) {
                                    e.currentSelectedRowKeys.forEach((key) => {
                                        e.component.byKey(key).done((data) => {
                                            if (!ordersData.has(data.id)) {
                                                ordersData.set(data.id, {});
                                            } else {
                                                ordersData.get(data.id).include_in_formation = true;
                                            }
                                        })
                                    })
                                }

                                if (e.currentDeselectedRowKeys.length > 0) {
                                    e.currentDeselectedRowKeys.forEach((key) => {
                                        e.component.byKey(key).done((data) => {
                                            if (!ordersData.has(data.id)) {
                                                ordersData.set(data.id, {});
                                            } else {
                                                ordersData.get(data.id).include_in_formation = true;
                                            }
                                        })
                                    })
                                }

                                $(".dx-tabpanel").dxTabPanel("instance").repaint();
                            },
                            onFocusedRowChanged: (e) => {
                                console.log("onFocusedRowChanged", e);
                                currentSelectedOrder = e.row.data;
                                $(".dx-tabpanel").dxTabPanel("instance").repaint();
                            },
                            onSaving: (e) => {
                                console.log("saving", e);
                            }
                        }
                    },
                    {
                        colSpan: 2,
                        label: {
                            visible: false
                        },
                        itemType: "simpleItem",
                        editorType: "dxTabPanel",
                        name: "orderTabPanel",
                        editorOptions: {
                            height: "60vh",
                            items: [
                                {
                                    title: "Атрибуты",
                                    template: (itemData, itemIndex, element) => {
                                        const attributesTemplate = getEditFormAttributesTemplate();
                                        console.log('templateRepainted');
                                        attributesTemplate.appendTo(element);
                                    }
                                },
                                /*{
                                    title: "Приказ",
                                    template: (itemData, itemIndex, element) => {
                                        const htmlEditorDiv = $(`<div style="padding-top:15px; padding-left:15px; padding-right:15px; height: 100%">`)
                                        htmlEditorDiv.dxHtmlEditor({
                                            toolbar: {
                                                items: [
                                                    'undo', 'redo', 'separator',
                                                    {
                                                        name: 'size',
                                                        acceptedValues: ['8pt', '10pt', '12pt', '14pt', '18pt', '24pt', '36pt'],
                                                    },
                                                    {
                                                        name: 'font',
                                                        acceptedValues: ['Arial', 'Calibri', 'Courier New', 'Georgia', 'Impact', 'Lucida Console', 'Tahoma', 'Times New Roman', 'Verdana'],
                                                    },
                                                    'separator', 'bold', 'italic', 'strike', 'underline', 'separator',
                                                    'alignLeft', 'alignCenter', 'alignRight', 'alignJustify', 'separator',
                                                    'orderedList', 'bulletList', 'separator',
                                                    {
                                                        name: 'header',
                                                        acceptedValues: [false, 1, 2, 3, 4, 5],
                                                    }, 'separator',
                                                    'color', 'background', 'separator',
                                                    'link', 'image', 'separator',
                                                    'clear', 'codeBlock', 'blockquote', 'separator',
                                                    'insertTable', 'deleteTable',
                                                    'insertRowAbove', 'insertRowBelow', 'deleteRow',
                                                    'insertColumnLeft', 'insertColumnRight', 'deleteColumn',
                                                ],
                                            }
                                        })
                                        htmlEditorDiv.appendTo(element);
                                    }
                                }*/
                            ],
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
                                                        //requestsForm.getEditor("requestsGrid").saveEditData();
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
                                                        if (!requestsForm.getEditor("requestsGrid").hasEditData() && currentEditingRowKey) {
                                                            requestsForm.getEditor("requestsGrid").cellValue(
                                                                currentEditingRowIndex,
                                                                "perform_orders",
                                                                true
                                                            )
                                                        }
                                                        requestsForm.getEditor("requestsGrid").saveEditData();
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
                                                    onClick: function(e){
                                                        console.log("currentEditingRowIndex", currentEditingRowIndex)
                                                        if (!requestsForm.getEditor("requestsGrid").hasEditData() && currentEditingRowKey) {
                                                            requestsForm.getEditor("requestsGrid").cellValue(
                                                                currentEditingRowIndex,
                                                                "perform_orders",
                                                                false
                                                            )
                                                        }
                                                        requestsForm.getEditor("requestsGrid").saveEditData();
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
                                                        requestsForm.getEditor("requestsGrid").cancelEditData();
                                                    }
                                                }
                                            }
                                        ]
                                    },
                                    form: editForm,
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
                                        type: 'buttons',
                                        width: 110,
                                        buttons: [
                                            'edit',
                                            {
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
                                    console.log("onEditingStart", e);
                                    ordersData = new Map();
                                    e.data.orders_data.forEach((dataItem) => {
                                        ordersData.set(dataItem.order_type_id, dataItem);
                                    })
                                    currentEditingRowKey = e.key;
                                    currentEditingRowIndex = e.component.getRowIndexByKey(e.key);
                                }
                            }
                        }]
                    }
                ]
            }).dxForm('instance')

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
                            requestsForm.getEditor("requestsGrid").addRow();
                        }
                    })
                    .addClass('dx-form-group-caption-button')
                    .prependTo(groupCaptionButtonsDiv)
            }

            createGridGroupHeaderButtons();

        });
    </script>
@endsection
