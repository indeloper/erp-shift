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
    $(function(){
        //<editor-fold desc="JS: DataSources">
        let measureUnitsData = {!!$measureUnits!!};
        let accountingTypesData = {!!$accountingTypes!!};

        let materialTypesDataSource = new DevExpress.data.DataSource({
            reshapeOnPush: true,
            store: new DevExpress.data.CustomStore({
                key: "id",
                load: function(loadOptions) {
                    return $.getJSON("{{route('materials.types.list')}}");
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
                        success: function (data, textStatus, jqXHR){
                            DevExpress.ui.notify("Данные успешно добавлены", "success", 1000)
                        },

                        /*error: function(jqXHR, textStatus, errorThrown) {
                            DevExpress.ui.notify("При сохранении данных произошла ошибка", "error", 5000)
                        }*/
                    })
                },
                update: function(key, values) {
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
                        success: function (data, textStatus, jqXHR){
                            DevExpress.ui.notify("Данные успешно изменены", "success", 1000)
                        },

                        /*error: function(jqXHR, textStatus, errorThrown) {
                            DevExpress.ui.notify("При сохранении данных произошла ошибка", "error", 5000)
                        }*/
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
                        success: function (data, textStatus, jqXHR){
                            DevExpress.ui.notify("Данные успешно удалены", "success", 1000)
                        },

                        error: function(jqXHR, textStatus, errorThrown) {
                            DevExpress.ui.notify("При удалении данных произошла ошибка", "error", 5000)
                        }
                    })
                },

            })
        });

        //</editor-fold>

        //<editor-fold desc="JS: Edit form configurtion">
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
                    colSpan: 1,
                    dataField: "measure_unit",
                    label: {
                        text: "Единица измерения"
                    },
                    editorType: "dxSelectBox",
                    editorOptions: {
                        items: measureUnitsData,
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
                    colSpan: 1,
                    dataField: "accounting_type",
                    label: {
                        text: "Учет"
                    },
                    editorType: "dxSelectBox",
                    editorOptions: {
                        items: accountingTypesData,
                        displayExpr: "value",
                        valueExpr: "id",
                        searchEnabled: false
                    },
                    validationRules: [{
                        type: "required",
                        message: 'Поле "Учет" обязательно для заполнения'
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
                caption: "Единица измерения",
                dataType: "number",
                width: 70,
                lookup: {
                    dataSource: measureUnitsData,
                    displayExpr: "value",
                    valueExpr: "id"
                }
            },
            {
                dataField: "accounting_type",
                caption: "Учет",
                dataType: "number",
                width: 120,
                lookup: {
                    dataSource: accountingTypesData,
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

        //<editor-fold desc="JS: Grid configurtion">
        $("#gridContainer").dxDataGrid({
            dataSource: materialTypesDataSource,
            focusedRowEnabled: true,
            hoverStateEnabled: true,
            columnAutoWidth: false,
            showBorders: true,
            filterRow: {
                visible: true,
                applyFilter: "auto"
            },
            editing: {
                mode: "popup",
                allowUpdating: true,
                allowAdding: true,
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
            onRowDblClick: function(e){
                console.log(e);
            }
        });
        //</editor-fold>

        //<editor-fold desc="JS: Toolbar configurtion">
        //</editor-fold>


    });

    </script>
@endsection
