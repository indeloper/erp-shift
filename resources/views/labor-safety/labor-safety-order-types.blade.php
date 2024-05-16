@extends('layouts.app')

@section('title', 'Шаблоны приказов')

@section('url', route('labor-safety.order-types.index'))

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
@endsection

@section('js_footer')
    <script>
        let dataSourceLoadOptions = {};

        let editForm = {
            colCount: 1,
            items: [
                {
                    dataField: "template",
                    label: {
                        text: "Шаблон",
                        visible: false
                    },
                    itemType: "simpleItem",
                    editorType: "dxHtmlEditor",
                    editorOptions: {
                        height: "75vh",
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
                    }
                },
            ]
        };

        $(function () {
            //<editor-fold desc="JS: DataSources">
            let materialsSupplyPlanningSource = new DevExpress.data.DataSource({
                store: new DevExpress.data.CustomStore({
                    key: "id",
                    load: function (loadOptions) {
                        return $.getJSON("{{route('labor-safety.order-types.list')}}",
                            {
                                loadOptions: JSON.stringify(loadOptions),
                            });
                    },
                    update: function (key, values) {
                        return $.ajax({
                            url: "{{route('labor-safety.order-types.update')}}",
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

            let orderTypesEditForm = $("#formContainer").dxForm({
                items: [
                    {
                        itemType: "group",
                        caption: "Список шаблонов приказов",
                        items: [{
                            name: "orderTypesGrid",
                            editorType: "dxDataGrid",
                            editorOptions: {
                                dataSource: materialsSupplyPlanningSource,
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
                                        title: "Шаблон приказа",
                                        showTitle: true,
                                        width: 700,
                                        height: "auto",
                                        position: {
                                            my: "center",
                                            at: "center",
                                            of: window
                                        }
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

                                        dataField: "short_name",
                                        caption: "Краткое наименование",
                                        width: 200
                                    },
                                    {
                                        dataField: "name",
                                        caption: "Полное наименование"
                                    },
                                    {
                                        dataField: "template",
                                        visible: false
                                    },
                                    {
                                        type: 'buttons',
                                        width: 110,
                                        buttons: [
                                            {
                                                hint: 'Редактировать шаблон',
                                                icon: 'fa fa-file-text',
                                                visible: (e) => {
                                                    return !e.row.isEditing;
                                                },
                                                onClick: (e) => {
                                                    e.component.editRow(e.row.rowIndex);
                                                },
                                            }
                                        ],
                                    }
                                ],
                                onRowDblClick: function (e) {
                                    e.component.editRow(e.RowIndex);
                                },
                                onSaving(e) {
                                    console.log("saving", e)                                }
                            }
                        }]
                    }
                ]
            }).dxForm('instance')
        });
    </script>
@endsection
