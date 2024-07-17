@extends('layouts.app')

@section('title', 'Перемещение #'.json_decode($operationData)->id. ' [' .$operationRouteStage.']')

@section('url', "#")

@section('css_top')
    <style>
      .initial-content {
        float: left;
        text-align: left;
        line-height: 20px;
        margin: -4px;
        padding: 2px 4px;
        border-radius: 2px;
      }

      .initial-content.equal {
        background: #c6efce;
        color: #006100;
      }

      .initial-content.equal.deleted {
        background: lightgray;
        color: white;
      }

      .initial-content.equal.deleted {
        background: lightgray;
        color: white;
      }

      .initial-content.negative {
        background: #ffc7ce;
        color: #9c0006;
      }

      .initial-content.negative.deleted {
        background: lightgray;
        color: white;
      }

      .amount-cell-content {
        float: right;
        min-width: 50%;
      }

      .quantity-cell-content {
        float: right;
        min-width: 50%;
      }

      .dx-link.dx-icon-add.dx-datagrid {
        color: #006100;
      }

      .dx-link.dx-icon-revert.deleted {
        color: lightblue;
      }

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
    </style>
@endsection

@section('content')
    <div id="formContainer"></div>
    <div id="popupContainer">
        <div id="materialsStandardsAddingForm"></div>
    </div>
    <div id="validationPopoverContainer">
        <div
                id="validationTemplate"
                data-options="dxTemplate: { name: 'validationTemplate' }"
        >

        </div>
    </div>
    <div id="materialCommentPopoverContainer">
        <div
                id="materialCommentTemplate"
                data-options="dxTemplate: { name: 'materialCommentTemplate' }"
        >
        </div>
    </div>
    <div id="commentPopupContainer">
        <div id="commentEditForm"></div>
    </div>
    <div id="standardRemainsPopoverContainer">
        <div
                id="standardRemainsTemplate"
                data-options="dxTemplate: { name: 'standardRemainsTemplate' }"
        >
        </div>
    </div>
@endsection

@section('js_footer')
    <script>
      $(function () {
        //<editor-fold desc="JS: DataSources">
        let operationData = {!! $operationData !!};

        let materialTypesStore = new DevExpress.data.CustomStore({
          key: 'id',
          loadMode: 'raw',
          load: function (loadOptions) {
            return $.getJSON("{{route('material.types.lookup-list')}}",
              { data: JSON.stringify({ dxLoadOptions: loadOptions }) });
          },
        });

        let materialTypesDataSource = new DevExpress.data.DataSource({
          key: 'id',
          store: materialTypesStore,
        });

        let transferMaterialData = {!! $operationMaterials !!};
        let transferMaterialStore = new DevExpress.data.ArrayStore({
          key: 'id',
          data: transferMaterialData,
        });
        let transferMaterialDataSource = new DevExpress.data.DataSource({
          reshapeOnPush: true,
          store: transferMaterialStore,
        });

        let operationHistoryStore = new DevExpress.data.CustomStore({
          key: 'id',
          loadMode: 'raw',
          load: function (loadOptions) {
            return $.getJSON("{{route('materials.operations.comment-history.list')}}",
              { operationId: operationData.id });
          },
        });

        let operationHistoryDataSource = new DevExpress.data.DataSource({
          reshapeOnPush: true,
          store: operationHistoryStore,
        });

        let operationFileHistoryStore = new DevExpress.data.CustomStore({
          key: 'operation_route_stage_id',
          loadMode: 'raw',
          load: function (loadOptions) {
            return $.getJSON("{{route('materials.operations.file-history.list')}}",
              { operationId: operationData.id });
          },
        });

        let operationFileHistoryDataSource = new DevExpress.data.DataSource({
          reshapeOnPush: true,
          store: operationFileHistoryStore,
        });

        //</editor-fold>

        //<editor-fold desc="JS: Columns definition">
        let transferMaterialColumns = [
          {
            dataField: 'standard_name',
            dataType: 'string',
            allowEditing: false,
            width: '30%',
            caption: 'Наименование',
            sortIndex: 0,
            sortOrder: 'asc',
            cellTemplate: function (container, options) {
              $(`<div class="standard-name">${options.text}</div>`)
                .appendTo(container);

              if (options.data.comment) {
                $(`<div class="material-comment">${options.data.comment}</div>`)
                  .appendTo(container);

                container.addClass('standard-name-cell-with-comment');
              }
            },
          },
          {
            dataField: 'quantity',
            dataType: 'number',
            caption: 'Количество',
            editorOptions: {
              min: 0,
            },
            showSpinButtons: false,
            cellTemplate: function (container, options) {
              let quantity = options.data.quantity;

              $(`<div>${quantity} ${options.data.measure_unit_value}</div>`)
                .appendTo(container);
            },
          },
          {
            dataField: 'amount',
            dataType: 'number',
            caption: 'Количество (шт)',
            editorOptions: {
              min: 0,
              format: '#',
            },
            cellTemplate: function (container, options) {
              let amount = options.data.amount;

              $(`<div>${amount} шт.</div>`)
                .appendTo(container);
            },
          },
          {
            dataField: 'computed_weight',
            dataType: 'number',
            allowEditing: false,
            caption: 'Вес',
            calculateCellValue: function (rowData) {
              let weight = rowData.quantity * rowData.amount * rowData.standard_weight;

              if (isNaN(weight)) {
                weight = 0;
              } else {
                weight = weight.toFixed(3);
              }

              rowData.computed_weight = weight;
              return weight;

            },
            cellTemplate: function (container, options) {
              let computed_weight = options.data.computed_weight;
              if (computed_weight !== null) {
                $(`<div>${computed_weight} т.</div>`)
                  .appendTo(container);
              }
            },
          },
          {
            dataField: 'material_type',
            dataType: 'number',
            caption: 'Тип материала',
            groupIndex: 0,
            lookup: {
              dataSource: { store: materialTypesStore },
              displayExpr: 'name',
              valueExpr: 'id',
            },
          },
        ];
        //</editor-fold>

        //<editor-fold desc="JS: Grid configuration">
        let transferMaterialGridConfiguration = {
          dataSource: transferMaterialDataSource,
          focusedRowEnabled: false,
          hoverStateEnabled: true,
          columnAutoWidth: false,
          showBorders: true,
          showColumnLines: true,
          grouping: {
            autoExpandAll: true,
          },
          groupPanel: {
            visible: false,
          },
          paging: {
            enabled: false,
          },
          editing: {
            mode: 'cell',
            allowUpdating: false,
            allowDeleting: false,
            selectTextOnEditStart: false,
            startEditAction: 'click',
          },
          columns: transferMaterialColumns,
          summary: {
            groupItems: [
              {
                column: 'standard_id',
                summaryType: 'count',
                displayFormat: 'Количество: {0}',
              },
              {
                name: 'totalAmountGroupSummary',
                showInColumn: 'amount',
                summaryType: 'custom',
                showInGroupFooter: false,
                alignByColumn: true,
              },
              {
                name: 'totalWeightGroupSummary',
                showInColumn: 'computed_weight',
                summaryType: 'custom',
                showInGroupFooter: false,
                alignByColumn: true,
              },
            ],
            totalItems: [
              {
                name: 'totalWeightSummary',
                showInColumn: 'computed_weight',
                summaryType: 'custom',
              },
            ],
            calculateCustomSummary: function (options) {
              if (options.name === 'totalWeightSummary' || options.name === 'totalWeightGroupSummary') {
                if (options.summaryProcess === 'start') {
                  options.totalValue = 0;
                }

                if (options.summaryProcess === 'calculate') {
                  if (options.value.edit_states.indexOf('deletedByRecipient') === -1) {
                    options.totalValue = options.totalValue + (options.value.amount * options.value.quantity * options.value.standard_weight);
                  }
                }

                if (options.summaryProcess === 'finalize') {
                  if (options.name === 'totalWeightSummary') {
                    options.totalValue = 'Итого: ' + options.totalValue.toFixed(3) + ' т.';
                  } else {
                    options.totalValue = 'Всего: ' + options.totalValue.toFixed(3) + ' т.';
                  }

                }
              }

              if (options.name === 'totalAmountGroupSummary') {
                if (options.summaryProcess === 'start') {
                  options.totalValue = 0;
                }

                if (options.summaryProcess === 'calculate') {
                  if (options.value.edit_states.indexOf('deletedByRecipient') === -1) {
                    options.totalValue = options.totalValue + options.value.amount;
                  }
                }

                if (options.summaryProcess === 'finalize') {
                  options.totalValue = 'Всего: ' + options.totalValue + ' шт';
                }
              }
            },
          },
        };
        //</editor-fold>

        //<editor-fold desc="JS: Edit form configuration">
        let operationForm = $('#formContainer').dxForm({
          formData: operationData,
          colCount: 2,
          items: [
            {
              itemType: 'group',
              colCount: 4,
              caption: 'Отправление',
              items: [
                {
                  colSpan: 4,
                  dataField: 'source_project_object_name',
                  label: {
                    text: 'Объект отправления',
                  },
                  readOnly: true,
                  editorOptions: {
                    readOnly: true,
                  },
                },
                {
                  dataField: 'operation_date',
                  readOnly: true,
                  colSpan: 1,
                  label: {
                    text: 'Дата отправления',
                  },
                  editorType: 'dxDateBox',
                  editorOptions: {
                    readOnly: true,
                  },
                },
                {
                    name: "materialOperationReasonSelectBox",
                    colSpan: 2,
                    dataField: "material_operation_reason_name",
                    label: {
                        text: "Причина движения"
                    },
                    editorOptions: {
                        readOnly:true
                    }
                },
                {
                  colSpan: 1,
                  dataField: 'source_responsible_user_name',
                  label: {
                    text: 'Ответственный',
                  },
                  editorOptions: {
                    readOnly: true,
                  },
                },
              ],
            }, {
              itemType: 'group',
              colCount: 3,
              caption: 'Получение',
              items: [
                {
                  colSpan: 3,
                  dataField: 'destination_project_object_name',
                  label: {
                    text: 'Объект получения',
                  },
                  editorOptions: {
                    readOnly: true,
                  },
                },
                {
                  colSpan: 2,
                  dataField: 'destination_responsible_user_name',
                  label: {
                    text: 'Ответственный',
                  },
                  editorOptions: {
                    readOnly: true,
                  },
                },
                {
                  colSpan: 1,
                  dataField: 'consignment_note_number',
                  label: {
                    text: 'Номер ТН',
                  },
                  editorType: 'dxTextBox',
                  editorOptions: {
                    readOnly: true,
                  },
                },
              ],
            },
            {
              itemType: 'group',
              caption: 'Материалы',
              cssClass: 'materials-grid',
              colSpan: 2,
              items: [
                {
                  dataField: '',
                  editorType: 'dxDataGrid',
                  name: 'transferMaterialGrid',
                  editorOptions: transferMaterialGridConfiguration,
                },
              ],

            },
            {
              itemType: 'group',
              caption: 'Комментарии',
              colSpan: 2,
              items: [
                {
                  name: 'commentHistoryGrid',
                  editorType: 'dxDataGrid',
                  editorOptions: {
                    dataSource: operationHistoryDataSource,
                    wordWrapEnabled: true,
                    showColumnHeaders: false,
                    columns: [
                      {
                        dataField: 'user_id',
                        dataType: 'string',
                        width: 240,
                        cellTemplate: (container, options) => {
                          let photoUrl;

                          if (options.data.image) {
                            photoUrl = `{{ asset('storage/img/user_images/') }}` + '/' + options.data.image;
                          } else {
                            photoUrl = `{{ mix('img/user-male-black-shape.png') }}`;
                          }

                          let authorName = options.data.last_name +
                            ' ' +
                            options.data.first_name.substring(0, 1) +
                            '. ' +
                            options.data.patronymic.substring(0, 1) +
                            '.';

                          let commentDate = new Intl.DateTimeFormat('ru-RU', {
                            dateStyle: 'short',
                            timeStyle: 'short',
                          }).format(new Date(options.data.created_at)).replaceAll(',', '');

                          $(`<div class="comment-user-photo">` +
                            `<img src="` + photoUrl + `" class="photo">` +
                            `</div>`)
                            .appendTo(container);

                          $(`<span class="comment-date">` +
                            commentDate +
                            `</span>` +
                            `<br><span class="comment-user-name">` +
                            authorName +
                            `</span>`)
                            .appendTo(container);
                        },
                      },
                      {
                        dataField: 'comment',
                        cellTemplate: (container, options) => {
                          $(`<span class="comment">` +
                            options.data.comment +
                            `</span>`)
                            .appendTo(container);
                        },
                      },
                      {
                        dataField: 'route_stage_name',
                        width: 220,
                      },
                    ],
                  },
                },
              ],
            },
            {
              itemType: 'group',
              caption: 'Файлы',
              colSpan: 2,
              items: [
                {
                  name: 'fileHistoryGrid',
                  editorType: 'dxDataGrid',
                  editorOptions: {
                    dataSource: operationFileHistoryDataSource,
                    wordWrapEnabled: true,
                    showColumnHeaders: false,
                    columns: [
                      {
                        dataField: 'data[0].user_id',
                        dataType: 'string',
                        width: 240,
                        cellTemplate: (container, options) => {
                          console.log('fileHistoryGrid options', options);
                          let photoUrl = '';

                          if (options.data.data[0].image) {
                            photoUrl = `{{ asset('storage/img/user_images') }}/` + options.data.data[0].image;
                          } else {
                            photoUrl = `{{ mix('img/user-male-black-shape.png') }}`;
                          }

                          let authorName = options.data.data[0].last_name +
                            ' ' +
                            options.data.data[0].first_name.substring(0, 1) +
                            '. ' +
                            options.data.data[0].patronymic.substring(0, 1) +
                            '.';

                          let commentDate = new Intl.DateTimeFormat('ru-RU', {
                            dateStyle: 'short',
                            timeStyle: 'short',
                          }).format(new Date(options.data.data[0].created_at)).replaceAll(',', '');

                          $(`<div class="comment-user-photo">` +
                            `<img src="` + photoUrl + `" class="photo">` +
                            `</div>`)
                            .appendTo(container);

                          $(`<span class="comment-date">` +
                            commentDate +
                            `</span>` +
                            `<br><span class="comment-user-name">` +
                            authorName +
                            `</span>`)
                            .appendTo(container);
                        },
                      },
                      {
                        cellTemplate: (container, options) => {
                          options.data.data.forEach((item) => {
                            let imageUrl = '{{ URL::to('/') }}' + '/' + item.file_path + item.file_name;

                            $(`<div><a href="${imageUrl}" target="_blank">${item.file_type_name}</a></div>`).appendTo(container);
                          });
                        },
                      },
                      {
                        dataField: 'data[0].route_stage_name',
                        width: 220,
                      },
                    ],
                  },
                },
              ],
            },
          ],

        }).dxForm('instance');
        //</editor-fold>

        //<editor-fold desc="JS: Toolbar configuration">
        //</editor-fold>
      });
    </script>
@endsection
