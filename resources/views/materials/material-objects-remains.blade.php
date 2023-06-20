@extends('layouts.app')

@section('title', 'Остатки на объектах')

@section('url', route('materials.objects.remains'))

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

    <form id="printMaterialRemains" target="_blank" method="post" action="{{route('materials.objects.remains.print')}}">
        @csrf
        <input id="detailing_level" type="hidden" name="detailing_level">
        <input id="filterOptions" type="hidden" name="filterOptions">
        <input id="filterList" type="hidden" name="filterList">
    </form>
@endsection

@section('js_footer')
    <script>
        let detailing_level = {{$detailing_level}};
        let detailing_level_codes = {
            "Максимальная детализация" : 1,
            "Средняя детализация" : 2,
            "Минимальная детализация" : 3,
        }
        let filterText = '';
        let dataSourceLoadOptions = {};

        function getKeyByValue(object, value) {
            return Object.keys(object).find(key => object[key] === value);
        }

        function getDetailingLevel() {
            return detailing_level_codes[detailing_level] 
                ? detailing_level_codes[detailing_level] 
                : new URL(window.location.href).searchParams.get('detailing_level')
        }

        $(function () {
            $("div.content").children(".container-fluid.pd-0-360").removeClass();
        });

        $(function () {
            let projectObjectsStore = new DevExpress.data.CustomStore({
                key: "id",
                loadMode: "processed",
                load: function (loadOptions) {
                    return $.getJSON("{{route('project-objects.list')}}",
                        {
                            data: JSON.stringify(loadOptions),
                            detailing_level: 3
                        }
                    );
                }
            });

            let materialsRemainsDataSource = new DevExpress.data.DataSource({
                store: new DevExpress.data.CustomStore({
                    key: "id",
                    load: function (loadOptions) {
                        /*loadOptions.filter = getLoadOptionsFilterArray();*/
                        dataSourceLoadOptions = loadOptions;
                        return $.getJSON("{{route('materials.objects.remains.list')}}",
                            {
                                data: JSON.stringify(loadOptions),
                                detailing_level: getDetailingLevel() 
                            });
                    },
                })
            });

            let materialGridForm = $("#formContainer").dxForm({
                items: [
                    {
                        itemType: "group",
                        caption: "Остатки на объектах",
                        cssClass: "material-snapshot-grid",
                        items: [{
                            name: "materialsRemainsGrid",
                            editorType: "dxDataGrid",
                            editorOptions: {
                                dataSource: materialsRemainsDataSource,
                                remoteOperations: true,
                                focusedRowEnabled: false,
                                hoverStateEnabled: true,
                                columnAutoWidth: false,
                                showBorders: true,
                                showColumnLines: true,
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
                                sorting: {
                                    mode: 'multiple',
                                },
                                columns: [
                                    {
                                        caption: "Объект",
                                        dataField: "object_name",
                                        sortOrder: 'asc',
                                    },
                                    {
                                        caption: "Наименование",
                                        dataField: "standard_name",
                                        sortOrder: 'asc',
                                        cellTemplate: function (container, options) {
                                            $(`<div class="material-name">${options.data.standard_name}</div>`)
                                                .appendTo(container);

                                            if (options.data.comment) {
                                                $(`<div class="material-comment">${options.data.comment}</div>`)
                                                    .appendTo(container);

                                                container.addClass("standard-name-cell-with-comment");
                                            }
                                        }
                                    },
                                    {
                                        caption: "Количество",
                                        dataField: "quantity",
                                        sortOrder: 'asc',
                                        calculateCellValue: function (rowData) { 
                                            return new Intl.NumberFormat('ru-RU').format( Math.round(rowData.quantity * 100) / 100) + " " + rowData.unit_measure_value;
                                        },
                                        width: 150
                                    },
                                    {
                                        caption: "Количество (шт.)",
                                        dataField: "amount",
                                        calculateCellValue: function(rowData) {
                                            return new Intl.NumberFormat('ru-RU').format( Math.round(rowData.amount * 100) / 100) + ' шт';
                                        },
                                        width: 150
                                    },
                                    {
                                        caption: "Вес",
                                        dataField: "summary_weight",
                                        calculateCellValue: function(rowData) {
                                            return new Intl.NumberFormat('ru-RU').format( Math.round(rowData.summary_weight * 1000) / 1000) + ' т';
                                        },
                                        width: 150
                                    }

                                ]
                            }
                        }]
                    }
                ]
            }).dxForm("instance");

            function createGridReportButtons(){
                let groupCaption = $('.material-snapshot-grid').find('.dx-form-group-with-caption');
                $('<div>').addClass('dx-form-group-caption-buttons').prependTo(groupCaption);
                groupCaption.find('span').addClass('dx-form-group-caption-span-with-buttons');
                let groupCaptionButtonsDiv = groupCaption.find('.dx-form-group-caption-buttons');

                
                $('<div>')
                    .dxButtonGroup({
                        keyExpr: "hint",
                        selectedItemKeys: [ getKeyByValue(detailing_level_codes, detailing_level) ],
                        onSelectionChanged: function (e) {
                            if(e.addedItems[0]) {
                                detailing_level = e.addedItems[0].hint;
                                materialsRemainsDataSource.reload();
                                window.history.pushState("", "", getUrlParameters(detailing_level));
                            }
                        },
                        items: [
                            {
                                icon: 'fas fa-th',
                                hint: 'Максимальная детализация',
                            },
                            {
                                icon: 'fas fa-th-large',
                                hint: 'Средняя детализация',
                            },
                            {
                                icon: 'fas fa-square',
                                hint: 'Минимальная детализация',   
                            }
                        ]
                })
                .addClass('dx-form-group-caption-button')
                .prependTo(groupCaptionButtonsDiv)


                $('<div>')
                    .dxButton({
                        text: "Скачать",
                        icon: "fa fa-download",
                        onClick: (e) => {
                            delete dataSourceLoadOptions.skip;
                            delete dataSourceLoadOptions.take;

                            $('#detailing_level').val(JSON.stringify(getDetailingLevel()));
                            $('#filterList').val(JSON.stringify(filterText));
                            $('#filterOptions').val(JSON.stringify(dataSourceLoadOptions));
                            $('#printMaterialRemains').get(0).submit();
                        }
                    })
                    .addClass('dx-form-group-caption-button')
                    .prependTo(groupCaptionButtonsDiv)                  
                    
            }

            createGridReportButtons();

            function getUrlParameters(){
                return '?detailing_level=' + detailing_level_codes[detailing_level];
            }
        });
    </script>
@endsection
