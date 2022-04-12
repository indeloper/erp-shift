@extends('layouts.app')

@section('title', 'Остатки материалов')

@section('url', route('materials.remains'))

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
    </style>
@endsection

@section('content')
    <div id="formContainer"></div>
    <div id="gridContainer"></div>

    <form id="printMaterialRemains" target="_blank" method="post" action="{{route('materials.remains.print')}}">
        @csrf
        <input id="projectObjectId" type="hidden" name="projectObjectId">
        <input id="requestedDate" type="hidden" name="requestedDate">
        <input id="filterOptions" type="hidden" name="filterOptions">
        <input id="filterList" type="hidden" name="filterList">
    </form>
@endsection

@section('js_footer')
    <script>
        let projectObject = {{$projectObjectId}};
        let requestedDate = new Date('{{$requestedDate}}');
        let filterText = '';

        let dataSourceLoadOptions = {};

        $(function () {
            $("div.content").children(".container-fluid.pd-0-360").removeClass();
        });

        $(function () {
            //<editor-fold desc="JS: DataSources">
            let projectObjectsStore = new DevExpress.data.CustomStore({
                key: "id",
                loadMode: "processed",
                load: function (loadOptions) {
                    return $.getJSON("{{route('project-objects.list')}}",
                        {data: JSON.stringify(loadOptions)});
                }
            });

            let materialsRemainsDataSource = new DevExpress.data.DataSource({
                store: new DevExpress.data.CustomStore({
                    key: "id",
                    load: function (loadOptions) {
                        /*loadOptions.filter = getLoadOptionsFilterArray();*/
                        dataSourceLoadOptions = loadOptions;

                        return $.getJSON("{{route('materials.remains.list')}}",
                            {
                                data: JSON.stringify(loadOptions),
                                projectObjectId: projectObject,
                                requestedDate: new Date(requestedDate).toISOString().split("T")[0]
                            });
                    },
                })
            });

            let materialGridForm = $("#formContainer").dxForm({
                items: [
                    {
                        itemType: "group",
                        caption: "Табель учета материалов",
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
                                columns: [
                                    {
                                        caption: "Наименование",
                                        dataField: "standard_name",
                                        width: 450
                                    },
                                    {
                                        caption: "Завоз",
                                        alignment: 'center',
                                        columns: [
                                            {
                                                caption: "шт.",
                                                dataField: "coming_to_material_amount",
                                                cellTemplate: getCellTemplate
                                            },
                                            {
                                                caption: "п.м./м²",
                                                dataField: "coming_to_material_quantity",
                                                cellTemplate: getCellTemplate
                                            },
                                            {
                                                caption: "тн.",
                                                dataField: "coming_to_material_weight",
                                                cellTemplate: getCellTemplate
                                            }
                                        ]
                                    },
                                    {
                                        caption: "Вывоз",
                                        alignment: 'center',
                                        columns: [
                                            {
                                                caption: "шт.",
                                                dataField: "outgoing_material_amount",
                                                cellTemplate: getCellTemplate
                                            },
                                            {
                                                caption: "п.м./м²",
                                                dataField: "outgoing_material_quantity",
                                                cellTemplate: getCellTemplate
                                            },
                                            {
                                                caption: "тн.",
                                                dataField: "outgoing_material_material_weight",
                                                cellTemplate: getCellTemplate
                                            }
                                        ]
                                    },
                                    {
                                        caption: "Остаток",
                                        alignment: 'center',
                                        columns: [
                                            {
                                                caption: "шт.",
                                                dataField: "amount_remains",
                                                cellTemplate: getCellTemplate
                                            },
                                            {
                                                caption: "п.м./м²",
                                                dataField: "quantity_remains",
                                                cellTemplate: getCellTemplate
                                            },
                                            {
                                                caption: "тн.",
                                                dataField: "weight_remains",
                                                cellTemplate: getCellTemplate
                                            }
                                        ]
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
                    .text("Объект:")
                    .addClass('main-filter-label')
                    .prependTo(groupCaptionButtonsDiv)

                $('<div>')
                    .dxSelectBox({
                        width: 280,
                        dataSource: new DevExpress.data.DataSource({
                            store: new DevExpress.data.CustomStore({
                                key: "id",
                                loadMode: "raw",
                                load: function (loadOptions) {
                                    return $.getJSON("{{route('project-objects.which-participates-in-material-accounting.list')}}",
                                        {data: JSON.stringify(loadOptions)});
                                },
                            })
                        }),
                        displayExpr: "short_name",
                        valueExpr: "id",
                        searchEnabled: true,
                        searchExpr: "short_name",
                        value: projectObject,
                        onValueChanged: (e) => {
                            projectObject = e.value;
                            materialsRemainsDataSource.reload();
                            window.history.pushState("", "", getUrlParameters(projectObject, requestedDate));
                        }
                    })
                    .addClass('dx-form-group-caption-button')
                    .prependTo(groupCaptionButtonsDiv)

                $('<div>')
                    .text("Дата:")
                    .addClass('main-filter-label')
                    .prependTo(groupCaptionButtonsDiv)

                $('<div>')
                    .dxDateBox({
                        width: 280,
                        value: requestedDate,
                        onValueChanged: (e) => {
                            requestedDate = e.value;
                            materialsRemainsDataSource.reload();
                            window.history.pushState("", "", getUrlParameters(projectObject, requestedDate));
                        }
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

                            $('#projectObjectId').val(JSON.stringify(projectObject));
                            $('#requestedDate').val(JSON.stringify(new Date(requestedDate).toISOString().split("T")[0]));
                            $('#filterList').val(JSON.stringify(filterText));
                            $('#filterOptions').val(JSON.stringify(dataSourceLoadOptions));
                            $('#printMaterialRemains').get(0).submit();
                        }
                    })
                    .addClass('dx-form-group-caption-button')
                    .prependTo(groupCaptionButtonsDiv)
            }

            createGridReportButtons();

            function getUrlParameters(projectObject, requestedDate){
                return '?projectObjectId=' + projectObject + '&requestedDate=' + new Date(requestedDate).toISOString().split("T")[0];
            }

            function getCellTemplate(container, options) {
                let cssClass;

                switch(options.column.ownerBand) {
                    case 1:
                        cssClass = "coming";
                        break;
                    case 5:
                        cssClass = "outgoing";
                        break;
                    case 9:
                        cssClass = "remains";
                        break;
                }

                container.addClass(cssClass);
                container.append($(`<div>${Math.round(options.displayValue * 1000) / 1000}</div>`))
            }
        });
    </script>
@endsection
