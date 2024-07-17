@extends('layouts.app')

@section('title', 'Новая поставка')

@section('url', "#")

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
  </style>
@endsection

@section('content')
  <div id="formContainer"></div>
  <div id="popupContainer">
    <div id="materialsStandardsAddingForm"></div>
  </div>
  <div id="validationPopoverContainer">
    <div id="validationTemplate" data-options="dxTemplate: { name: 'validationTemplate' }">

    </div>
  </div>
  <div id="materialCommentPopoverContainer">
    <div id="materialCommentTemplate" data-options="dxTemplate: { name: 'materialCommentTemplate' }">
    </div>
  </div>
  <div id="commentPopupContainer">
    <div id="commentEditForm"></div>
  </div>
@endsection

@section('js_footer')
  <script>
    $(function () {
      let measureUnitData = {!!$measureUnits ?? ''!!};
      let projectObject = {{$projectObjectId}};
      let materialStandardsData = {!!$materialStandards!!};
      let materialTypesData = {!!$materialTypes!!};
      let materialErrorList = [];
      let supplyMaterialTempID = 0;
      let commentData = null;

      //<editor-fold desc="JS: DataSources">
      let contractorsStore = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function (loadOptions) {
          return $.getJSON("{{route('contractors.list')}}",
                  {data: JSON.stringify({dxLoadOptions: loadOptions})});
        },
      });

      let contractorsDataSource = new DevExpress.data.DataSource({
        store: contractorsStore
      });

      let materialsStandardsListStore = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "processed",
        load: function (loadOptions) {
          return $.getJSON("{{route('materials.standards.listex')}}",
                  {data: JSON.stringify({dxLoadOptions: loadOptions})});
        },
      });


      let materialsStandardsListDataSource = new DevExpress.data.DataSource({
        //group: "key",
        store: materialsStandardsListStore
      })

      let selectedMaterialStandardsListDataSource = new DevExpress.data.DataSource({
        store: new DevExpress.data.ArrayStore({
          key: "id",
          data: []
        })
      })

      let supplyMaterialData = [];

      let supplyMaterialStore = new DevExpress.data.ArrayStore({
        key: "id",
        data: supplyMaterialData
      })

      let supplyMaterialDataSource = new DevExpress.data.DataSource({
        reshapeOnPush: true,
        store: supplyMaterialStore
      })

      let projectObjectsListWhichParticipatesInMaterialAccountingDataSource = new DevExpress.data.DataSource({
        reshapeOnPush: true,
        store: new DevExpress.data.CustomStore({
          key: "id",
          loadMode: "raw",
          load: function (loadOptions) {
            return $.getJSON("{{route('project-objects.which-participates-in-material-accounting.list')}}",
                    {data: JSON.stringify(loadOptions)});
          }
        })
      });

      let usersWithMaterialListAccessStore = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function (loadOptions) {
          return $.getJSON("{{route('users-with-material-list-access.list')}}",
                  {data: JSON.stringify(loadOptions)});
        },
      });

      let materialOperationReasonStore = new DevExpress.data.CustomStore({
        key: "id",
        loadMode: "raw",
        load: function (loadOptions) {
          return $.getJSON("{{route('material-operation-reason')}}",
                  {data: JSON.stringify(loadOptions)});
        },
      });
      //</editor-fold>

      let materialCommentEditForm = $("#commentEditForm").dxForm({
        colCount: 1,
        items: [{
          editorType: "dxTextArea",
          name: "materialCommentTextArea",
          editorOptions: {
            width: 600,
            height: 200
          }
        },
          {
            itemType: "button",
            buttonOptions: {
              text: "ОК",
              type: "default",
              stylingMode: "text",
              useSubmitBehavior: false,
              onClick: (e) => {
                commentData.comment = materialCommentEditForm.getEditor("materialCommentTextArea").option("value");
                $("#commentPopupContainer").dxPopup("hide");
                getSupplyMaterialGrid().refresh();
              }
            }
          }]
      }).dxForm("instance");

      let materialsStandardsAddingForm = $("#materialsStandardsAddingForm").dxForm({
        colCount: 2,
        items: [{
          itemType: "group",
          colCount: 3,
          caption: "Эталоны",
          items: [{
            editorType: "dxDataGrid",
            name: "materialsStandardsList",
            editorOptions: {
              dataSource: materialsStandardsListDataSource,
              height: 400,
              width: 500,
              showColumnHeaders: false,
              showRowLines: false,
              grouping: {
                autoExpandAll: true,
              },
              scrolling: {
                mode: 'virtual'
              },
              groupPanel: {
                visible: false
              },
              selection: {
                allowSelectAll: true,
                deferred: false,
                mode: "multiple",
                selectAllMode: "allPages",
                showCheckBoxesMode: "always"
              },
              paging: {
                enabled: false
              },
              searchPanel: {
                visible: true,
                searchVisibleColumnsOnly: true,
                width: 240,
                placeholder: "Поиск..."
              },
              columns: [{
                dataField: "standard_name",
                dataType: "string",
                caption: "Наименование",
                calculateFilterExpression: function (filterValue, selectedFilterOperation, target) {
                  if (target === "search") {
                    let columnsNames = ["standard_name"]

                    let words = filterValue.split(" ");
                    let filter = [];

                    columnsNames.forEach(function (column, index) {
                      filter.push([]);
                      words.forEach(function (word) {
                        filter[filter.length - 1].push([column, "contains", word]);
                        filter[filter.length - 1].push("and");
                      });

                      filter[filter.length - 1].pop();
                      filter.push("or");
                    })
                    console.log(filter)
                    filter.pop();
                    return filter;
                  }
                  return this.defaultCalculateFilterExpression(filterValue, selectedFilterOperation);
                },
                cellTemplate: function (container, options) {
                  let text = options.data.standard_name;
                  let searchWords = options.component.option('searchPanel').text.split(" ");
                  let resElement = $('<span>')
                          .text(options.data.standard_name);
                  searchWords.forEach(function (word) {
                    if (word.length) {
                      let startPos = text.toLowerCase().indexOf(word.toLowerCase()),
                              span = "<span class='highlight'>",
                              spanLength = span.length,
                              itemText = "";
                      if (startPos >= 0) {
                        itemText = [
                          text.slice(0, startPos),
                          span,
                          text.slice(startPos, startPos + word.length),
                          "</span>",
                          text.slice(startPos + word.length)
                        ].join('');
                        resElement = $('<span>')
                                .html(itemText);
                      }
                    }
                  });
                  return resElement;
                }
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
                }],
              onSelectionChanged: function (e) {
                selectedMaterialStandardsListDataSource.store().clear();
                e.selectedRowsData.forEach(function (selectedRowItem) {
                  selectedMaterialStandardsListDataSource.store().insert({
                    id: selectedRowItem.id,
                    name: selectedRowItem.name,
                    accounting_type: selectedRowItem.accounting_type,
                    material_type: selectedRowItem.material_type,
                    measure_unit: selectedRowItem.measure_unit,
                    measure_unit_value: selectedRowItem.measure_unit_value,
                    weight: selectedRowItem.weight
                  })
                })

                selectedMaterialStandardsListDataSource.reload();
              }
            }
          }]
        },
          {
            itemType: "group",
            colCount: 3,
            caption: "Выбранные материалы",
            items: [{
              editorType: "dxList",
              name: "selectedMaterialsStandardsList",
              editorOptions: {
                dataSource: selectedMaterialStandardsListDataSource,
                allowItemDeleting: true,
                itemDeleteMode: "static",
                height: 400,
                width: 500,
                itemTemplate: function (data) {
                  return $("<div>").text(data.name)
                },
                onItemDeleted: function (e) {
                  let materialsStandardsList = materialsStandardsAddingForm.getEditor("materialsStandardsList");
                  let selectedMaterialsStandardsList = materialsStandardsAddingForm.getEditor("selectedMaterialsStandardsList");
                  let selectedRowsKeys = [];
                  selectedMaterialsStandardsList.option("items").forEach(function (selectedItem) {
                    selectedRowsKeys.push(selectedItem.id);
                  });

                  materialsStandardsList.option("selectedRowKeys", selectedRowsKeys);
                }
              }
            }]
          },
          {
            itemType: "button",
            colSpan: 2,
            horizontalAlignment: "right",
            buttonOptions: {
              text: "Добавить",
              type: "default",
              stylingMode: "text",
              useSubmitBehavior: false,

              onClick: function () {
                let selectedMaterialsData = materialsStandardsAddingForm.getEditor("selectedMaterialsStandardsList").option("items");

                selectedMaterialsData.forEach(function (materialStandard) {
                  let validationUid = getValidationUid();

                  supplyMaterialDataSource.store().insert({
                    id: ++supplyMaterialTempID,
                    standard_id: materialStandard.id,
                    standard_name: materialStandard.name,
                    accounting_type: materialStandard.accounting_type,
                    material_type: materialStandard.material_type,
                    measure_unit: materialStandard.measure_unit,
                    measure_unit_value: materialStandard.measure_unit_value,
                    standard_weight: materialStandard.weight,
                    quantity: null,
                    amount: null,
                    validationUid: validationUid,
                    validationState: "unvalidated",
                    validationResult: "none"
                  })

                  $.ajax({
                    type: "POST",
                    headers: {
                      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    contentType: "json",
                    dataType: "json",
                    url: "{{route('materials.standard.incriminate-selection-counter')}}",
                    data: JSON.stringify({standardId: materialStandard.id})
                  });

                  validateMaterialList(false, false, validationUid);
                })
                supplyMaterialDataSource.reload();
                $("#popupContainer").dxPopup("hide")
              }
            }
          }
        ]
      }).dxForm("instance");

      let popupContainer = $("#popupContainer").dxPopup({
        height: "auto",
        width: "auto",
        title: "Выберите материалы для добавления"
      });

      let materialCommentPopupContainer = $("#commentPopupContainer").dxPopup({
        height: "auto",
        width: "auto",
        title: "Введите комментарий"
      });

      //<editor-fold desc="JS: Columns definition">
      let supplyMaterialColumns = [
        {
          type: "buttons",
          width: 110,
          buttons: [
            {
              template: function (container, options) {
                let validationUid = options.data.validationUid;
                let validationDiv = $('<div class="row-validation-indicator"/>')
                        .attr("validation-uid", validationUid)

                updateRowsValidationState([options.data], options.data.validationState, options.data.validationResult, validationDiv);
                return validationDiv;
              }
            },
            {
              hint: "Удалить",
              icon: "trash",
              onClick: (e) => {
                console.log("detetion: ", e);
                e.component.deleteRow(e.row.rowIndex);
                e.component.refresh(true);
              }
            },
            {
              hint: "Дублировать",
              icon: "copy",
              onClick: function (e) {
                let clonedItem = $.extend({}, e.row.data, {id: ++supplyMaterialTempID,
                  validationUid: getValidationUid(),
                  validationState: "unvalidated",
                  validationResult: "none"
                });
                supplyMaterialData.splice(e.row.rowIndex, 0, clonedItem);
                e.component.refresh(true);
                e.component.repaint();
                // let rowIndex = e.component.getRowIndexByKey(key);
                // e.component.repaintRows(rowIndex);
                validateMaterialList(false, false, clonedItem.validationUid);
                e.event.preventDefault();
              }
            },
            {
              hint: "Комментарии",
              icon: "fas fa-message",

              template: (container, options) => {
                let accountingType;

                if (options.data.accounting_type) {
                  accountingType = options.data.accounting_type;
                }

                let commentIconClass = !options.data.comment ? "far fa-comment" : "fas fa-comment";

                let commentLink;

                switch (accountingType) {
                  case 1:
                  case 2:
                    commentLink = $("<a>")
                            .attr("href", "#")
                            .attr("title", "Комментарий")
                            .addClass("dx-link dx-icon " + commentIconClass + " dx-link-icon")
                            .click(() => {
                              commentData = options.data;
                              if (commentData.comment) {
                                materialCommentEditForm.getEditor("materialCommentTextArea").option("value", commentData.comment);
                              } else {
                                materialCommentEditForm.getEditor("materialCommentTextArea").option("value", "");
                              }
                              $("#commentPopupContainer").dxPopup("show");
                            })
                            .mouseenter(function () {
                              if (!options.data.comment) {
                                return;
                              }

                              let materialCommentPopover = $('#materialCommentTemplate');
                              materialCommentPopover.dxPopover({
                                position: "top",
                                width: 300,
                                contentTemplate: options.data.comment,
                                hideEvent: "mouseleave",
                              })
                                      .dxPopover("instance")
                                      .show($(this));
                            });
                    break;
                  default:
                    return;
                }
                return commentLink;
              }
            }]
        },
        {
          dataField: "standard_name",
          dataType: "string",
          allowEditing: false,
          width: "30%",
          caption: "Наименование",
          sortIndex: 0,
          sortOrder: "asc",
          cellTemplate: function (container, options) {
            let divStandardName = $(`<div class="standard-name"></div>`)
                    .appendTo(container);

            let divStandardText = $(`<div>${options.text}</div>`)
                    .appendTo(divStandardName);

            if (options.data.comment) {
              $(`<div class="material-comment">${options.data.comment}</div>`)
                      .appendTo(divStandardName);

              divStandardName.addClass("standard-name-cell-with-comment");
            }
          }
        },
        {
          dataField: "quantity",
          dataType: "number",
          caption: "Количество",
          editorOptions: {
            min: 0
          },
          cellTemplate: function (container, options) {
            let quantity = options.data.quantity;
            if (quantity !== null) {
              $(`<div>${quantity} ${options.data.measure_unit_value}</div>`)
                      .appendTo(container);
            } else {
              $(`<div class="measure-units-only">${options.data.measure_unit_value}</div>`)
                      .appendTo(container);
            }
          },
          //validationRules: [{type: "required"}]
        },
        {
          dataField: "amount",
          dataType: "number",
          caption: "Количество (шт)",
          editorOptions: {
            min: 0,
            format: "#"
          },
          cellTemplate: function (container, options) {
            let amount = options.data.amount;
            if (amount !== null) {
              $(`<div>${amount} шт</div>`)
                      .appendTo(container);
            } else {
              $(`<div class="measure-units-only">шт</div>`)
                      .appendTo(container);
            }
          },
          //validationRules: [{type: "required"}]
        },
        {
          dataField: "computed_weight",
          dataType: "number",
          allowEditing: false,
          caption: "Вес",
          calculateCellValue: function (rowData) {
            let weight = rowData.quantity * rowData.amount * rowData.standard_weight;

            if (isNaN(weight)) {
              weight = 0;
            } else {
              weight = weight.toFixed(3)
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
      let supplyMaterialGridConfiguration = {
        dataSource: supplyMaterialDataSource,
        focusedRowEnabled: false,
        hoverStateEnabled: true,
        columnAutoWidth: false,
        showBorders: true,
        showColumnLines: true,
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
          mode: "cell",
          allowUpdating: true,
          allowDeleting: true,
          selectTextOnEditStart: false,
          startEditAction: "click"
        },
        columns: supplyMaterialColumns,
        summary: {
          groupItems: [{
            column: "standard_id",
            summaryType: "count",
            displayFormat: "Количество: {0}",
          },
            {
              column: "amount",
              summaryType: "sum",
              customizeText: function (data) {
                return `Всего: ${data.value} шт`
              },
              showInGroupFooter: false,
              alignByColumn: true
            },
            {
              column: "computed_weight",
              summaryType: "sum",
              customizeText: function (data) {
                return `Всего: ${data.value.toFixed(3)} т.`
              },
              showInGroupFooter: false,
              alignByColumn: true
            }],
          totalItems: [{
            column: "computed_weight",
            summaryType: "sum",
            cssClass: "computed-weight-total-summary",
            customizeText: function (data) {
              return `Итого: ${data.value.toFixed(3)} т.`
            }
          }]
        },
        onRowUpdated: (e) => {
          validateMaterialList(false, false, e.data.validationUid);
        }
      };
      //</editor-fold>

      //<editor-fold desc="JS: Edit form configuration">
      let operationForm = $("#formContainer").dxForm({
        formData: [],
        colCount: 2,
        items: [{
          itemType: "group",
          colCount: 4,
          caption: "Поставка",
          items: [{
            name: "projectObjectSelectBox",
            colSpan: 4,
            dataField: "project_object_id",
            label: {
              text: "Объект"
            },
            editorType: "dxSelectBox",
            editorOptions: {
              dataSource: projectObjectsListWhichParticipatesInMaterialAccountingDataSource,
              displayExpr: "short_name",
              valueExpr: "id",
              searchEnabled: true,
              value: projectObject,
              onValueChanged: function (e) {
                projectObject = e.value;
              }
            },
            validationRules: [{
              type: "required",
              message: 'Поле "Объект" обязательно для заполнения'
            }]
          },
            {
              name: "operationDateDateBox",
              dataField: "operation_date",
              colSpan: 1,
              label: {
                text: "Дата поставки"
              },
              editorType: "dxDateBox",
              editorOptions: {
                value: Date.now(),
                max: Date.now(),
                min: getMinDate(),
                readOnly: false
              },
              validationRules: [{
                type: "required",
                message: 'Поле "Дата поставки" обязательно для заполнения'
              }]
            },
            {
              name: "materialOperationReasonSelectBox",
              colSpan: 2,
              dataField: "material_operation_reason_id",
              label: {
                text: "Причина движения"
              },
              editorType: "dxSelectBox",
              editorOptions: {
                dataSource: {
                  store: materialOperationReasonStore,
                  filter: [
                    'operation_route_id', '=', 1
                  ]
                },
                displayExpr: "name",
                valueExpr: "id",
                searchEnabled: true
              },
              validationRules: [{
                type: "required",
                message: 'Поле "Причина движения" обязательно для заполнения'
              }]

            },
            {
              name: "destinationResponsibleUserSelectBox",
              colSpan: 1,
              dataField: "destination_responsible_user_id",
              label: {
                text: "Ответственный"
              },
              editorType: "dxSelectBox",
              editorOptions: {
                dataSource: {
                  store: usersWithMaterialListAccessStore
                },
                displayExpr: "full_name",
                valueExpr: "id",
                searchEnabled: true,
                value: {{$currentUserId}}
              },
              validationRules: [{
                type: "required",
                message: 'Поле "Ответственный" обязательно для заполнения'
              }]

            }]
        }, {
          itemType: "group",
          caption: "Поставщик",
          items: [{
            name: "contractorSelectBox",
            dataField: "contractor_id",
            label: {
              text: "Поставщик"
            },
            editorType: "dxSelectBox",
            editorOptions: {
              dataSource: contractorsDataSource,
              displayExpr: "short_name",
              valueExpr: "id",
              searchEnabled: true
            },
            validationRules: [{
              type: "required",
              message: 'Поле "Поставщик" обязательно для заполнения'
            }]
          },
            {
              name: "consignmentNoteNumberTextBox",
              dataField: "consignment_note_number",
              label: {
                text: "Номер ТТН"
              },
              editorType: "dxTextBox",
              validationRules: [{
                type: "required",
                message: 'Поле "Номер ТТН" обязательно для заполнения'
              }]
            }]
        },
          {
            itemType: "group",
            caption: "Материалы",
            cssClass: "materials-grid",
            colSpan: 2,
            items: [{
              dataField: "",
              name: "supplyMaterialGrid",
              editorType: "dxDataGrid",
              editorOptions: supplyMaterialGridConfiguration
            }
            ]
          },
          {
            itemType: "group",
            caption: "Комментарий",
            colSpan: 2,
            items: [{
              name: "newCommentTextArea",
              dataField: "new_comment",
              label: {
                text: "Новый комментарий",
                visible: false
              },
              editorType: "dxTextArea",
            }
            ]
          },
          {
            itemType: "group",
            caption: "Файлы",
            colSpan: 2,
            colCount: 3,
            items: [{
              colSpan: 1,
              template:
                      '<div id="dropzone-external-1" class="dx-uploader-flex-box dx-theme-border-color dropzone-external">' +
                      '<img id="dropzone-image-1" class="dropzone-image" src="#" hidden alt="" />' +
                      '<div id="dropzone-text-1" class="dx-uploader-flex-box dropzone-text">' +
                      '<span class="dx-uploader-span">Фото ТТН</span>' +
                      '</div>' +
                      '<div id="upload-progress-1" class="upload-progress"></div>' +
                      '</div>' +
                      '<div class="file-uploader" purpose="consignment-note-photo" index="1"></div>'
            },
              {
                colSpan: 1,
                template: '<div id="dropzone-external-2" class="dx-uploader-flex-box dx-theme-border-color dropzone-external">' +
                        '<img id="dropzone-image-2" class="dropzone-image" src="#" hidden alt="" />' +
                        '<div id="dropzone-text-2" class="dx-uploader-flex-box dropzone-text">' +
                        '<span class="dx-uploader-span">Фото машины спереди</span>' +
                        '</div>' +
                        '<div id="upload-progress-2" class="upload-progress"></div>' +
                        '</div>' +
                        '<div class="file-uploader" purpose="frontal-vehicle-photo" index="2"></div>'
              },
              {
                colSpan: 1,
                template: '<div id="dropzone-external-3" class="dx-uploader-flex-box dx-theme-border-color dropzone-external">' +
                        '<img id="dropzone-image-3" class="dropzone-image" src="#" hidden alt="" />' +
                        '<div id="dropzone-text-3" class="dx-uploader-flex-box dropzone-text">' +
                        '<span class="dx-uploader-span">Фото машины сзади с материалами</span>' +
                        '</div>' +
                        '<div id="upload-progress-3" class="upload-progress"></div>' +
                        '</div>' +
                        '<div class="file-uploader" purpose="behind-vehicle-photo" index="3"></div>'
              }
            ]
          },
          {
            itemType: "button",
            name: "createSupplyOperation",
            colSpan: 2,
            horizontalAlignment: "right",
            buttonOptions: {
              text: "Создать поставку",
              type: "default",
              stylingMode: "contained",
              useSubmitBehavior: false,
              template: function(data, container) {
                $("<div class='button-loading-indicator'></div><span class='dx-button-text'>" + data.text + "</span>").appendTo(container);
                let loadingIndicator = container.find(".button-loading-indicator").dxLoadIndicator({
                  visible: false
                }).dxLoadIndicator("instance");
              },
              onClick: function (e) {
                operationForm.getEditor("supplyMaterialGrid").closeEditCell();

                let result = e.validationGroup.validate();
                if (!result.isValid) {
                  return;
                }

                setButtonIndicatorVisibleState("createSupplyOperation", true)
                setElementsDisabledState(true);

                let comment = operationForm.option("formData").new_comment;
                if (!comment) {
                  let confirmDialog = DevExpress.ui.dialog.confirm('Вы не заполнили поле "Комментарий".<br>Продолжить без заполнения?', 'Комметарий не заполнен');
                  confirmDialog.done(function (dialogResult) {
                    if (dialogResult) {
                      validateMaterialList(true, true);
                    } else {
                      setButtonIndicatorVisibleState("createSupplyOperation", false)
                      setElementsDisabledState(false);
                      return;
                    }
                  })
                } else {
                  validateMaterialList(true, true);
                }
              }
            }
          }]

      }).dxForm("instance")
      //</editor-fold>

      //<editor-fold desc="JS: Toolbar configuration">
      //</editor-fold>

      function saveOperationData() {
        let supplyOperationData = {};

        supplyOperationData.project_object_id = operationForm.option("formData").project_object_id;
        //TODO Дата формируется в UTC. Нужно либо учитывать это при перобразовании, либо хранить в UTC в БД
        supplyOperationData.operation_date = new Date(operationForm.option("formData").operation_date).toJSON().split("T")[0];
        supplyOperationData.destination_responsible_user_id = operationForm.option("formData").destination_responsible_user_id;
        supplyOperationData.contractor_id = operationForm.option("formData").contractor_id;
        supplyOperationData.consignment_note_number = operationForm.option("formData").consignment_note_number;
        supplyOperationData.material_operation_reason_id = operationForm.option("formData").material_operation_reason_id;
        supplyOperationData.new_comment = operationForm.option("formData").new_comment;

        let uploadedFiles = []
        $(".file-uploader").each(function () {
          if ($(this).attr("uploaded-file-id") !== undefined) {
            uploadedFiles.push($(this).attr("uploaded-file-id"));
          }
        });

        supplyOperationData.uploaded_files = uploadedFiles;
        supplyOperationData.materials = supplyMaterialData;

        postEditingData(supplyOperationData);
      }

      function validateMaterialList(saveEditedData, showErrorWindowOnHighSeverity, validationUid) {
        let validationData;
        if (validationUid && !(saveEditedData)) {
          validationData = supplyMaterialDataSource.store().createQuery()
                  .filter(['validationUid', '=', validationUid])
                  .toArray();
        } else {
          validationData = supplyMaterialDataSource.store().createQuery()
                  .toArray();
        }

        updateRowsValidationState(validationData, "inProcess", "none")

        let supplyOperationData = {
          materials: validationData,
          project_object_id: operationForm.option("formData").project_object_id,
          timestamp: new Date()
        };
        $.ajax({
          url: "{{route('materials.operations.supply.new.validate-material-list')}}",
          method: "POST",
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          contentType: "json",
          dataType: "json",
          data: JSON.stringify(supplyOperationData),
          success: function (e) {
            let needToShowErrorWindow = false;
            delete(materialErrorList["common"]);
            e.validationResult.forEach((validationElement) => {
              if (materialErrorList[validationElement.validationUid]) {
                let materialListTimestamp = new Date(materialErrorList[validationElement.validationUid].timestamp);
                let currentResponseTimestamp = new Date(e.timestamp);

                if (materialListTimestamp < currentResponseTimestamp) {
                  delete(materialErrorList[validationElement.validationUid]);
                } else {
                  return;
                }
              }

              let validatedData = supplyMaterialDataSource.store().createQuery()
                      .filter(['validationUid', '=', validationElement.validationUid])
                      .toArray();

              if (validationElement.isValid) {
                updateRowsValidationState(validatedData, "validated", "valid");
              } else {
                materialErrorList[validationElement.validationUid] = {};
                materialErrorList[validationElement.validationUid].errorList = validationElement.errorList;
                materialErrorList[validationElement.validationUid].timestamp = e.timestamp;
                updateRowsValidationState(validatedData, "validated", "invalid");
              }

              updateCommonValidationState();

              if (!validationElement.isValid) {
                validationElement.errorList.forEach((errorItem) => {
                  if (showErrorWindowOnHighSeverity) {
                    if (errorItem.severity > 500) {
                      needToShowErrorWindow = true;
                    }
                  }
                })
              }
            })

            if (needToShowErrorWindow) {
              showErrorWindow(materialErrorList);
              setButtonIndicatorVisibleState("createSupplyOperation", false)
              setElementsDisabledState(false);
            }

            if (!needToShowErrorWindow){
              if (saveEditedData) {
                saveOperationData();
              }
            }
          },
          error: function (e) {
            DevExpress.ui.notify("При проверке данных произошла неизвестная ошибка", "error", 5000)
            setButtonIndicatorVisibleState("createSupplyOperation", false)
            setElementsDisabledState(false);
          }
        });
      }

      function getValidationUid(){
        return "uid-" + new DevExpress.data.Guid().toString();
      }

      function updateCommonValidationState() {
        console.log("common material error list:", materialErrorList["common"]);
        if (materialErrorList["common"]) {
          materialErrorList["common"].errorList.forEach((item) => {
            if (item.type === "totalWeightIsTooLarge"){
              let summary = $(".computed-weight-total-summary");
              $('<i/>').addClass("dx-link fas fa-exclamation-triangle")
                      .attr("style", "color: #ffd358; margin-right: 4px;")
                      .attr('severity', item.severity)
                      .click((e) => {
                        e.preventDefault();
                      })
                      .mouseenter(function () {
                        if (!item.message) {
                          return;
                        }

                        let validationDescription = $('#validationTemplate');

                        validationDescription.dxPopover({
                          position: "top",
                          width: 300,
                          contentTemplate: "<ul>" + item.message + "</ul>",
                          hideEvent: "mouseleave",
                        })
                                .dxPopover("instance")
                                .show($(this));
                      })
                      .prependTo(summary);
            }
          })
        }
      }

      function updateRowsValidationState(data, validationState, validationResult, validationDiv){
        data.forEach((element) => {
          supplyMaterialDataSource.store()
                  .update(element.id, {validationState: validationState, validationResult: validationResult})
                  .done((dataObj, key) => {
                    let validationIndicatorDiv;
                    if (validationDiv) {
                      validationIndicatorDiv = validationDiv;
                    } else {
                      validationIndicatorDiv = $('[validation-uid=' + dataObj.validationUid + ']');
                    }
                    console.log(dataObj.validationUid, validationState, validationResult)
                    validationIndicatorDiv.empty();

                    switch (dataObj.validationState) {
                      case "inProcess":
                        let indicatorDiv = $('<div class="cell-validation-loading-indicator">');
                        indicatorDiv.dxLoadIndicator({
                          visible: true,
                          width: 16,
                          height: 16
                        }).appendTo(validationIndicatorDiv);
                        break;
                      case "validated":
                        if (validationResult === "valid"){
                          let checkIcon = $("<i/>")
                                  .addClass("dx-link dx-icon fas fa-check-circle dx-link-icon")
                                  .attr("style", "color: #8bc34a")
                                  .appendTo(validationIndicatorDiv);
                          return;
                        } else {
                          let exclamationTriangle = $("<a>")
                                  .attr("href", "#")
                                  .attr("style", "display: none")
                                  .addClass("dx-link dx-icon fas fa-exclamation-triangle dx-link-icon")
                                  .appendTo(validationIndicatorDiv);

                          let errorList = materialErrorList[element.validationUid].errorList;
                          let maxSeverity = 0;
                          let errorDescription = "";
                          let exclamationTriangleStyle = "";

                          errorList.forEach((errorItem) => {
                            if (errorItem.severity > maxSeverity) {
                              maxSeverity = errorItem.severity;
                            }

                            errorDescription = errorDescription + "<li>" + errorItem.message + "</li>"
                          })

                          switch (maxSeverity) {
                            case 500:
                              exclamationTriangleStyle = 'color: #ffd358';
                              break;
                            case 1000:
                              exclamationTriangleStyle = 'color: #f15a5a';
                              break;
                            default:
                              exclamationTriangleStyle = "display: none";
                          }

                          exclamationTriangle.attr('style', exclamationTriangleStyle);
                          exclamationTriangle.attr('severity', maxSeverity);
                          exclamationTriangle.click((e) => {
                            e.preventDefault();
                          });
                          exclamationTriangle.mouseenter(function () {
                            if (!errorDescription) {
                              return;
                            }

                            let validationDescription = $('#validationTemplate');

                            validationDescription.dxPopover({
                              position: "top",
                              width: 300,
                              contentTemplate: "<ul>" + errorDescription + "</ul>",
                              hideEvent: "mouseleave",
                            })
                                    .dxPopover("instance")
                                    .show($(this));
                          });
                        }
                        break;
                    }
                  });
        })
      }

      function postEditingData(supplyOperationData) {
        $.ajax({
          url: "{{route('materials.operations.supply.new')}}",
          method: "POST",
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          data: {
            data: JSON.stringify(supplyOperationData)
          },

          success: function (data, textStatus, jqXHR) {
            window.location.href = '{{route('materials.index')}}/?project_object=' + projectObject
          },
          error: function (jqXHR, textStatus, errorThrown) {
            DevExpress.ui.notify("При сохранении данных произошла ошибка", "error", 5000);
            setButtonIndicatorVisibleState("createSupplyOperation", false)
            setElementsDisabledState(false);
          }
        })
      }

      $(".file-uploader").each(function() {
        let uploaderIndex = $(this).attr('index');
        $(this).dxFileUploader({
          dialogTrigger: "#dropzone-external-" + uploaderIndex,
          dropZone: "#dropzone-external-" + uploaderIndex,
          multiple: false,
          allowedFileExtensions: [".jpg", ".jpeg", ".gif", ".png"],
          uploadMode: "instantly",
          uploadHeaders: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          uploadUrl: "{{route('materials.operations.upload-file')}}",
          uploadCustomData: {uploadPurpose: $(this).attr('purpose')},
          visible: false,
          onDropZoneEnter: function (e) {
            if (e.dropZoneElement.id === "dropzone-external-" + uploaderIndex)
              toggleDropZoneActive(e.dropZoneElement, true);
          },
          onDropZoneLeave: function (e) {
            if (e.dropZoneElement.id === "dropzone-external-" + uploaderIndex)
              toggleDropZoneActive(e.dropZoneElement, false);
          },
          onUploaded: function (e) {
            const file = e.file;
            const dropZoneText = document.getElementById("dropzone-text-" + uploaderIndex);
            const fileReader = new FileReader();
            fileReader.onload = function () {
              toggleDropZoneActive(document.getElementById("dropzone-external-" + uploaderIndex), false);
              const dropZoneImage = document.getElementById("dropzone-image-" + uploaderIndex);
              dropZoneImage.src = fileReader.result;
            }
            fileReader.readAsDataURL(file);
            dropZoneText.style.display = "none";
            uploadProgressBar.option({
              visible: false,
              value: 0
            });

            let fileId = JSON.parse(e.request.response).id;
            e.element.attr('uploaded-file-id', fileId);
          },
          onProgress: function (e) {
            uploadProgressBar.option("value", e.bytesLoaded / e.bytesTotal * 100)
          },
          onUploadStarted: function () {
            toggleImageVisible(false);
            uploadProgressBar.option("visible", true);
          }
        });

        let uploadProgressBar = $("#upload-progress-" + uploaderIndex).dxProgressBar({
          min: 0,
          max: 100,
          width: "30%",
          showStatus: false,
          visible: false
        }).dxProgressBar("instance");

        function toggleDropZoneActive(dropZone, isActive) {
          if (isActive) {
            dropZone.classList.add("dx-theme-accent-as-border-color");
            dropZone.classList.remove("dx-theme-border-color");
            dropZone.classList.add("dropzone-active");
          } else {
            dropZone.classList.remove("dx-theme-accent-as-border-color");
            dropZone.classList.add("dx-theme-border-color");
            dropZone.classList.remove("dropzone-active");
          }
        }

        function toggleImageVisible(visible) {
          const dropZoneImage = document.getElementById("dropzone-image-" + uploaderIndex);
          dropZoneImage.hidden = !visible;
        }

        document.getElementById("dropzone-image-" + uploaderIndex).onload = function () {
          toggleImageVisible(true);
        };
      });

      function setElementsDisabledState(state){
        operationForm.getEditor("createSupplyOperation").option("disabled", state);
        operationForm.getEditor("supplyMaterialGrid").option("disabled", state);
        operationForm.getEditor("projectObjectSelectBox").option("disabled", state);
        operationForm.getEditor("operationDateDateBox").option("disabled", state);
        operationForm.getEditor("materialOperationReasonSelectBox").option("disabled", state);
        operationForm.getEditor("destinationResponsibleUserSelectBox").option("disabled", state);
        operationForm.getEditor("contractorSelectBox").option("disabled", state);
        operationForm.getEditor("consignmentNoteNumberTextBox").option("disabled", state);
        operationForm.getEditor("newCommentTextArea").option("disabled", state);
      }

      function setButtonIndicatorVisibleState(buttonName, state){
        let loadingIndicator = operationForm.getEditor(buttonName).element()
                .find(".button-loading-indicator").dxLoadIndicator("instance");
        loadingIndicator.option('visible', state);
      }

      function showErrorWindow(errorList){
        let htmlMessage = "";
        for (key in errorList) {
          errorList[key].errorList.forEach((errorItem) => {
            switch (errorItem.severity) {
              case 500:
                exclamationTriangleStyle = 'color: #ffd358';
                break;
              case 1000:
                exclamationTriangleStyle = 'color: #f15a5a';
                break;
              default:
                exclamationTriangleStyle = "gray";
            }

            htmlMessage += '<p><i class="fas fa-exclamation-triangle" style="' + exclamationTriangleStyle + '"></i>  ';
            if (errorItem.itemName) {
              htmlMessage += errorItem.itemName + ': ' + errorItem.message;
            } else {
              htmlMessage += errorItem.message;
            }
            htmlMessage += '</p>'
          })
        }

        DevExpress.ui.dialog.alert(htmlMessage, "Обнаружены ошибки");
      }

      function createAddMaterialsButton(){
        let groupCaption = $('.materials-grid').find('.dx-form-group-with-caption');
        $('<div>').addClass('dx-form-group-caption-buttons').prependTo(groupCaption);
        groupCaption.find('span').addClass('dx-form-group-caption-span-with-buttons');
        let groupCaptionButtonsDiv = groupCaption.find('.dx-form-group-caption-buttons');

        $('<div>')
                .dxButton({
                  text: "Добавить",
                  icon: "fas fa-plus",
                  onClick: (e) => {
                    selectedMaterialStandardsListDataSource.store().clear();

                    let materialsStandardsList = materialsStandardsAddingForm.getEditor("materialsStandardsList");
                    materialsStandardsList.option("selectedRowKeys", []);

                    $("#popupContainer").dxPopup("show");
                  }
                })
                .addClass('dx-form-group-caption-button')
                .prependTo(groupCaptionButtonsDiv)
      }

      function getSupplyMaterialGrid () {
        return operationForm.getEditor("supplyMaterialGrid");
      }

      function getMinDate() {
        let minDate = new Date();

        return minDate.setDate(minDate.getDate() - 3);
      }

      createAddMaterialsButton();



    });
  </script>
@endsection