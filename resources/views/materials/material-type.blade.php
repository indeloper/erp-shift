@extends('layouts.app')

@section('title', 'Типы материалов')

@section('url', route('materials.types.index'))

@section('css_top')

@endsection

@section('content')
    <div id="gridContainer" style="height: 100%"></div>
@endsection

@section('js_footer')
    <script>

        $(function () {
            //<editor-fold desc="JS: DataSources">
            let measureUnitsStore = new DevExpress.data.CustomStore({
                key: "id",
                loadMode: "raw",
                load: function (loadOptions) {
                    return $.getJSON("{{route('material.measure-units.list')}}",
                        {data: JSON.stringify(loadOptions)});
                },
            });

            let materialTypesDataSource = new DevExpress.data.DataSource({
                reshapeOnPush: true,
                store: new DevExpress.data.CustomStore({
                    key: "id",
                    load: function (loadOptions) {
                        return $.getJSON("{{route('materials.types.list')}}",
                            {data: JSON.stringify({dxLoadOptions: loadOptions})});
                    },
                    byKey: function (key) {
                        return $.getJSON("{{route('materials.types.by-key')}}",
                            {data: {key: key}}
                        );
                    },
                    insert: function (values) {
                        return $.ajax({
                            url: "{{route('materials.types.store')}}",
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
                            url: "{{route('materials.types.update')}}",
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
                    },
                    remove: function (key) {
                        return $.ajax({
                            url: "{{route('materials.types.delete')}}",
                            method: "DELETE",
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: {
                                key: key
                            },
                            success: function (data, textStatus, jqXHR) {
                                DevExpress.ui.notify("Данные успешно удалены", "success", 1000)
                            },

                            error: function (jqXHR, textStatus, errorThrown) {
                                DevExpress.ui.notify("При удалении данных произошла ошибка", "error", 5000)
                            }
                        })
                    },

                })
            });

            //</editor-fold>

            //<editor-fold desc="JS: Edit form configuration">
            let editForm = {
                colCount: 2,
                items: [{
                    colSpan: 2,
                    dataField: "name",
                    label: {
                        text: "Наименование"
                    },
                    validationRules: [{
                        type: "required",
                        message: 'Поле "Наименование" обязательно для заполнения'
                    }]
                },
                    {
                        colSpan: 2,
                        dataField: "measure_unit",
                        label: {
                            text: "Единица измерения"
                        },
                        editorType: "dxSelectBox",
                        editorOptions: {
                            items: measureUnitsStore,
                            displayExpr: "value",
                            valueExpr: "id",
                            searchEnabled: false
                        },
                        validationRules: [{
                            type: "required",
                            message: 'Поле "Единица измерения" обязательно для заполнения'
                        }]
                    },
                    {
                        colSpan: 2,
                        dataField: "description",
                        editorType: "dxTextArea",
                        label: {
                            text: "Описание"
                        },
                        editorOptions: {
                            height: 100
                        }
                    },
                    {
                        colSpan: 2,
                        dataField: "measure_instructions",
                        editorType: "dxTextArea",
                        label: {
                            text: "Инструкции по измерению"
                        },
                        editorOptions: {
                            height: 100
                        }
                    }
                ]
            };
            //</editor-fold>

            //<editor-fold desc="JS: Columns definition">
            let materialTypesColumns = [
                {
                    dataField: "id",
                    caption: "Идентификатор",
                    dataType: "number",
                    width: 70
                },
                {
                    dataField: "name",
                    dataType: "string",
                    caption: "Наименование типа материала"
                },
                {
                    dataField: "measure_unit",
                    dataType: "number",
                    caption: "Единица измерения",
                    lookup: {
                        dataSource: {
                            paginate: true,
                            pageSize: 25,
                            store: measureUnitsStore
                        },
                        displayExpr: "value",
                        valueExpr: "id"
                    }
                },
                {
                    dataField: "description",
                    dataType: "string",
                    caption: "Описание"
                },
                {
                    dataField: "measure_instructions",
                    dataType: "string",
                    caption: "Инструкции по измерению"
                },
            ];
            //</editor-fold>

            //<editor-fold desc="JS: Grid configuration">
            $("#gridContainer").dxDataGrid({
                dataSource: materialTypesDataSource,
                focusedRowEnabled: true,
                hoverStateEnabled: true,
                columnAutoWidth: false,
                showBorders: true,
                showColumnLines: true,
                remoteOperations: true,
                scrolling: {
                    mode: "virtual",
                    rowRenderingMode: "virtual",
                    useNative: false,
                    scrollByContent: true,
                    scrollByThumb: true,
                    showScrollbar: "onHover"
                },
                height: function () {
                    return $("div.content").height()
                },
                filterRow: {
                    visible: true,
                    applyFilter: "auto"
                },
                editing: {
                    mode: "popup",
                    allowUpdating: true,
                    allowDeleting: true,
                    popup: {
                        title: "Тип Материала",
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
                    texts: {
                        confirmDeleteMessage: "Вы уверены, что хотите удалить этот тип материала?\nВсе связанные с этим типом данные так же будут удалены"
                    }
                },
                columns: materialTypesColumns,
                onRowDblClick: function (e) {
                    console.log(e);
                    e.component.editRow(e.RowIndex);
                },
                onToolbarPreparing: function (e) {
                    let dataGrid = e.component;

                    e.toolbarOptions.items.unshift(
                        {
                            location: "before",
                            widget: "dxButton",
                            options: {
                                icon: "add",
                                text: "Добавить",
                                onClick: function (e) {
                                    dataGrid.addRow();
                                }
                            }
                        }
                    );
                },
            });
            //</editor-fold>

            //<editor-fold desc="JS: Toolbar configuration">
            //</editor-fold>


        });

    </script>
@endsection
