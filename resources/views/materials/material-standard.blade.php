@extends('layouts.app')

@section('title', 'Эталоны')

@section('url', route('materials.types.index'))

@section('css_top')
<style>
    .custom-item {
        position: relative;
        min-height: 30px;
    }

    .custom-item .material-type-name {
        width: 100%;
    }

    .material-type-description{
        font-size: small;
        width: 100%;
        color: #8e8e8e;
        font-style: italic;
    }
</style>
@endsection

@section('content')
    <div id="gridContainer" style="height: 100%"></div>
@endsection

@section('js_footer')
    <script>
        $(function () {
            $("div.content").children(".container-fluid.pd-0-360").removeClass();
        });

        $(function () {
            //<editor-fold desc="JS: DataSources">
            let measureUnitsData = {!!$measureUnits!!};
        let materialTypesData = {!!$materialTypes!!};

        let materialStandardsDataSource = new DevExpress.data.DataSource({
            reshapeOnPush: true,
            store: new DevExpress.data.CustomStore({
                key: "id",
                load: function(loadOptions) {
                    return $.getJSON("{{route('materials.standards.list')}}");
                },
                /*byKey: function (key, extraOptions){

                },*/
                insert: function (values) {
                    return $.ajax({
                        url: "{{route('materials.standards.store')}}",
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
                        url: "{{route('materials.standards.update')}}",
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
                        url: "{{route('materials.standards.delete')}}",
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

        //<editor-fold desc="JS: Edit form configuration">
        let editForm = {
            colCount: 2,
            items: [
                {
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
                    dataField: "material_type",
                    label: {
                        text: "Тип материала"
                    },
                    editorType: "dxSelectBox",
                    editorOptions: {
                        items: materialTypesData,
                        displayExpr: "name",
                        valueExpr: "id",
                        searchEnabled: false,
                        itemTemplate: function (data) {
                            return "<div class='custom-item'>" +
                                "<div class='material-type-name'>" +
                                data.name +
                                "</div>" +
                                "<div class='material-type-description'>" +
                                data.measure_unit_value +
                                "</div>" +
                                "</div>"
                        }
                    },
                    validationRules: [{
                        type: "required",
                        message: 'Поле "Тип материала" обязательно для заполнения'
                    }]
                },
                {
                    colSpan: 1,
                    dataField: "weight",
                    label: {
                        text: "Вес (т)"
                    },
                    editorType: "dxNumberBox",
                    editorOptions: {
                        min: 0,
                        showSpinButtons: false
                    },
                    validationRules: [{
                        type: "required",
                        message: 'Поле "Вес" обязательно для заполнения'
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
                caption: "Наименование эталона",
                calculateFilterExpression: function (filterValue, selectedFilterOperation, target) {
                    if (["contains", "notcontains"].indexOf(selectedFilterOperation) !== -1) {
                        let columnsNames = ["name"]

                        let words = filterValue.split(" ");
                        let filter = [];

                        columnsNames.forEach(function (column, index) {
                            filter.push([]);
                            words.forEach(function (word) {
                                filter[filter.length - 1].push([column, selectedFilterOperation, word]);
                                filter[filter.length - 1].push("and");
                            });

                            filter[filter.length - 1].pop();
                            filter.push("or");
                        })
                        filter.pop();
                        return filter;
                    }
                    return this.defaultCalculateFilterExpression(filterValue, selectedFilterOperation);
                }
            },
            {
                dataField: "weight",
                caption: "Вес, т",
                dataType: "number",
                width: 120
            },
            {
                dataField: "description",
                dataType: "string",
                caption: "Описание"
            },
            {
                dataField: "material_type",
                dataType: "number",
                caption: "Тип материала",
                groupIndex: 0,
                lookup: {
                    dataSource: materialTypesData,
                    displayExpr: "name",
                    valueExpr: "id"
                }
            },
        ];
            //</editor-fold>

            //<editor-fold desc="JS: Grid configuration">
            $("#gridContainer").dxDataGrid({
                dataSource: materialStandardsDataSource,
                focusedRowEnabled: true,
                hoverStateEnabled: true,
                columnAutoWidth: false,
                showBorders: true,
                showColumnLines: true,
                height: "100%",
                filterRow: {
                    visible: true,
                    applyFilter: "auto"
                },
                grouping: {
                    autoExpandAll: true,
                },
                groupPanel: {
                    visible: true
                },
            editing: {
                mode: "popup",
                allowUpdating: true,
                allowAdding: true,
                allowDeleting: true,
                popup: {
                    title: "Эталон",
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
                    confirmDeleteMessage: "Вы уверены, что хотите удалить этот эталон?\nВсе связанные с эталоном данные так же будут удалены"
                }
            },
            columns: materialTypesColumns,
            onRowDblClick: function(e){
                console.log(e);
            }
        });
        //</editor-fold>

        //<editor-fold desc="JS: Toolbar configuration">
        //</editor-fold>


    });

    </script>
@endsection
