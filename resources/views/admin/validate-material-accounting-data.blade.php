@extends('layouts.app')

@section('title', 'Проверка данных материального учета')

@section('url', route('admin.validate-material-accounting_data'))

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

        .warning {
            color: #ffd402f7;
        }

        .error {
            color: #c90505b5;
        }

        .remains {
            background: #bdbdf7;
            color: #20205a;
        }
    </style>
@endsection

@section('content')
    <div id="formContainer"></div>

    <form id="printMaterialRemains" target="_blank" method="post" action="{{route('materials.remains.print')}}">
        @csrf
        <input id="projectObjectId" type="hidden" name="projectObjectId">
        <input id="filterOptions" type="hidden" name="filterOptions">
        <input id="filterList" type="hidden" name="filterList">
    </form>
@endsection

@section('js_footer')
    <script>
        //$(".warning").hide();
        let projectObject = {{$projectObjectId}};
        let filterText = '';

        let dataSourceLoadOptions = {};

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

            let materialGridForm = $("#formContainer").dxForm({
                items: [
                    {
                        itemType: "group",
                        caption: "Проверка данных материального учета",
                        cssClass: "material-snapshot-grid",
                        items: [{
                            name: "validationResult",
                            itemType: "simple",
                            template: () => {
                                return $(`<div class="validation-group"></div>`);
                            }

                        }]
                    }
                ]
            }).dxForm("instance");

            function createGridReportButtons() {
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
                        }
                    })
                    .addClass('dx-form-group-caption-button')
                    .prependTo(groupCaptionButtonsDiv)

                $('<div>')
                    .dxButton({
                        text: "Проверить",
                        icon: "fa fa-refresh",
                        onClick: (e) => {
                            $.get("{{route('admin.get-material-accounting-data-validation-result')}}", {projectObjectId: projectObject}, (data => {
                                $(`.validation-group`).empty();

                                let operationId = 0;

                                JSON.parse(data).forEach((item) => {

                                    if (operationId === 0) {
                                        operationId = item.operation_id
                                    }

                                    if (operationId !== item.operation_id) {
                                        $(`<hr>`).appendTo(`.validation-group`);
                                        operationId = item.operation_id;
                                    }

                                    const uncriticalDelta = 0.002;
                                    let errorStyle = "";
                                    let weightDelta = Math.abs(Math.round((item.accumulativeMaterialWeight - item.snapshotWeight) * 1000)/1000);

                                    console.log(weightDelta, ' - ', weightDelta >= uncriticalDelta);

                                    if (weightDelta > uncriticalDelta){
                                        errorStyle = `class="error"`;
                                    } else {
                                        errorStyle = `class="warning"`;
                                    }

                                    $(`<div ${errorStyle}>`).html(`<Δ ${weightDelta}> т ➜ <a href="${item.operationUrl}" target="_blank">Операция ${item.operation_id}</a>: [${item.standardID}] ${item.standardName} ${item.quantity} - накопительный вес <b>${item.accumulativeMaterialWeight} т</b>  :  вес в снапшотe <b>${item.snapshotWeight} т</b>`)
                                        .appendTo(`.validation-group`)
                                })
                                //$(`.validation-group`).html(data);
                                alert("Validation completed");
                            }))
                        }
                    })
                    .addClass('dx-form-group-caption-button')
                    .prependTo(groupCaptionButtonsDiv)
            }

            createGridReportButtons();

            function getUrlParameters(projectObject) {
                return '?projectObjectId=' + projectObject;
            }
        });
    </script>
@endsection
