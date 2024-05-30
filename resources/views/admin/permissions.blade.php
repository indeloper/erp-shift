@extends('layouts.app')

@section('title', 'Управление доступами')

@section('url', route('admin.permissions'))

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

        .standard-name-cell-with-comment {
            margin: 0 -1px -1px -1px;
        }

        .operation-delimiter {
            border-top: 2px solid #a5a4a4 !important;
        }

        .operation-label {
            border: 1px solid #2a6285;
            padding: 2px 6px 2px 6px;
            background: #42a3df;
            color: aliceblue;
            border-radius: 4px;
            font-weight: bold;
        }

        .operation-label:hover {
            color: aliceblue;
            text-decoration: underline;
        }

        .operation-container {
            height: 30px;
            position: absolute;
        }

        .dx-form-group-caption-buttons {
            display: flex;
            flex-direction: row-reverse;
            align-items: flex-start;
        }

        .dx-placeholder {
            line-height: 6px;
        }

        .dx-selectbox, .dx-datebox {
            height: 29px;
            margin-left: 4px;
        }

        .main-filter-label {
            line-height: 33px;
            font-weight: bold;
            padding-left: 14px;
        }

        .dx-datagrid-filter-panel {
            display: none !important;
        }

        .coming {
            color: #335633;
            background: #dbf7b1;
        }

        .outgoing {
            color: #762828;
            background: #fbb1b1;;
        }

        .remains {
            background: #bdbdf7;
            color: #20205a;
        }
        .material-name {
            text-overflow: ellipsis;
            overflow: hidden;
            width: 100%;
            white-space: nowrap;
        }


    </style>
@endsection

@section('content')
    <div id="formContainer"></div>
    <div id="gridContainer"></div>
@endsection

@section('js_footer')
    <script>

        $(function () {
            let categoriesListStore = new DevExpress.data.CustomStore({
                key: "id",
                loadMode: "raw",
                load: function (loadOptions) {
                    return $.getJSON("{{route('admin.permission.categories')}}");
                }
            })


            let dataSourceList = new DevExpress.data.DataSource({
                store: new DevExpress.data.CustomStore({
                    key: "id",
                    loadMode: "processed",
                    load: function (loadOptions) {
                        return $.getJSON("{{route('admin.permission.index')}}",
                            {
                                data: JSON.stringify(loadOptions),
                            });
                    },
                    insert: function (values) {

                        return $.ajax({
                            url: "{{route('admin.permission.store')}}",
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

                      return $.ajax({
                        url: getUrlWithId("{{route('admin.permission.update', ['permission'=>'setId'])}}", key),
                        method: "PUT",
                        headers: {
                          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                          data: JSON.stringify(values),
                          options: null
                        },
                        success: function (data, textStatus, jqXHR) {
                          DevExpress.ui.notify("Данные успешно обновлены", "success", 1000)
                        },
                      })

                    },

                    remove: function (key) {

                      return $.ajax({
                        url: getUrlWithId("{{route('admin.permission.destroy', ['permission'=>'setId'])}}", key),
                        method: "DELETE",
                        headers: {
                          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (data, textStatus, jqXHR) {
                          DevExpress.ui.notify("Данные успешно удалены", "success", 1000)
                        },
                      })

                    },


                })
            });

            function getEditingForm() {
                return {
                colCount: 2,
                items: [
                            {
                                dataField: "name",
                                colSpan: 2,
                            },
                            {
                                dataField: "codename",
                                colSpan: 1
                            },
                            {
                                dataField: "category",
                                colSpan: 1
                            },
                        ],
                }

            }


            let gridForm = $("#formContainer").dxForm({
                items: [
                    {
                        itemType: "group",
                        caption: "Список доступов",
                        cssClass: "material-snapshot-grid",
                        items: [{
                            name: "permissionsGrid",
                            editorType: "dxDataGrid",
                            editorOptions: {
                                elementAttr: {
                                    id: "mainDataGrid"
                                },
                                dataSource: dataSourceList,
                                remoteOperations: true,
                                focusedRowEnabled: false,
                                hoverStateEnabled: true,
                                columnAutoWidth: false,
                                showBorders: true,
                                showColumnLines: true,
                                focusedRowEnabled: true,
                                onRowDblClick: function(e) {
                                    if (e.rowType === "data" && DevExpress.devices.current().deviceType === 'desktop') {
                                        e.component.editRow(e.rowIndex);
                                    }
                                },
                                filterRow: {
                                    visible: true,
                                    applyFilter: "auto"
                                },
                                filterPanel: {
                                    visible: true,
                                    customizeText: (e) => {
                                        filterText = e.text;
                                    }
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
                                scrolling: {
                                    mode: 'infinite',
                                },
                                editing: {
                                    allowUpdating: true,
                                    mode: "popup",
                                    /*form: {
                                        labelLocation: "top"
                                    },*/
                                    popup: {
                                        showTitle: true,
                                        title: "Доступ к БД",
                                        hideOnOutsideClick: true,
                                        showCloseButton:true,
                                        maxWidth: 500,
                                        height: 'auto',
                                    },
                                    allowAdding: true,
                                    allowDeleting: true,
                                    selectTextOnEditStart: true,
                                    startEditAction: 'click',
                                    form: getEditingForm()
                                },
                                toolbar: {
                                    visible:false,
                                    items: [
                                        {
                                            name: 'addRowButton',
                                            showText: 'always',
                                        }
                                    ]
                                },

                                columns: [

                                    {
                                        caption: "Наименование",
                                        dataField: "name",
                                        validationRules: [{
                                            type: 'required',
                                            max: 255,
                                            message: 'Наименование - обязательное поле',
                                         }],
                                    },
                                    {
                                        caption: "Кодовое наименование",
                                        dataField: "codename",
                                        validationRules: [{
                                            type: 'required',
                                            max: 255,
                                            message: 'Кодовое наименование - обязательное поле',
                                         }],
                                    },
                                    {
                                        caption: "Категория",
                                        dataField: "category",
                                        dataType: "integer",
                                        validationRules: [{
                                            type: 'required',
                                            message: 'Категория - обязательное поле',
                                         }],
                                        lookup: {
                                            dataSource: {
                                                id: "id",
                                                store: categoriesListStore
                                            },
                                            valueExpr: "id",
                                            displayExpr: "value"
                                         },
                                    },
                                    {
                                        type: "buttons",
                                        buttons: [
                                            'edit',
                                            'delete'
                                        ],

                                        headerCellTemplate: (container, options) => {
                                            $('<div>')
                                                .appendTo(container)
                                                .dxButton({
                                                    text: "Добавить",
                                                    icon: "fas fa-plus",
                                                    onClick: (e) => {
                                                        options.component.addRow();
                                                        $('#mainDataGrid').dxDataGrid('instance').option("focusedRowKey", undefined);
                                                        $('#mainDataGrid').dxDataGrid('instance').option("focusedRowIndex", undefined);
                                                    }
                                                })
                                        }
                                    }
                                ]
                            }
                        }]
                    }
                ]
            }).dxForm("instance");



        });

        function getUrlWithId(url, id){
            return url.replace("/setId","/" + id)
        }

    </script>
@endsection
