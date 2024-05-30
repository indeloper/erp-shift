class skDataGrid extends DevExpress.ui.dxDataGrid {
  static popupOpeningText = 'Загрузка карточки документа';
  static popupSavingDataText = 'Сохранение данных';
  static loadErrorMessageText = 'При загрузке данных произошла ошибка';
  static addRowCommandCaption = 'Добавить';
  static popupFormSaveButtonCaption = 'Сохранить';
  static popupFormCancelButtonCaption = 'Отмена';

  static defaultOptions;

  static editStates = {
    UNKNOWN: {
      name: 'unknown',
      suffix: '',
    },
    INSERT: {
      name: 'insert',
      suffix: ' — создание',
    },
    UPDATE: {
      name: 'update',
      suffix: ' — редактирование',
    },
    DUPLICATE: {
      name: 'duplicate',
      suffix: ' — дублирование',
    },
    DELETE: {
      name: 'delete',
      suffix: ' — удаление',
    },
  };

  static defaultComponentOptions = {
    showAppendButtonInCommandColumnHeader: true, //Создает кнопку «Добавить» в заголовке командного столбца
    startEditingOnDoubleClick: true, //Переводит DataGrid в состояние редактирования при двойном клике
  };

  /** TODO: разобраться и перенести _defaultParentOptions в метод DevExpress.ui.skDataGrid.defaultOptions({})
   *
   *Настройки по умолчанию для компонента
   */
  static _skDefaultOptions = {
    activeStateEnabled: true,
    allowColumnReordering: true,
    allowColumnResizing: true,
    columnAutoWidth: false,
    columnMinWidth: 50,
    editing: {
      allowUpdating: true,
      allowAdding: true,
      allowDeleting: true,
      selectTextOnEditStart: true,
      useIcons: true,
      /**
       * Настройки для skDataGrid
       */
      mode: 'skPopup',
      initialInsertionData: {},
    },
    filterRow: {
      visible: true,
    },
    focusedRowEnabled: true,
    grouping: {
      autoExpandAll: true,
      allowCollapsing: true,
      expandMode: 'rowClick',
    },
    highlightChanges: true,
    hoverStateEnabled: true,
    keyExpr: 'id',
    paging: {
      enabled: true,
      pageSize: 100,
    },
    remoteOperations: true,
    repaintChangesOnly: true,
    scrolling: {
      mode: 'infinite',
      rowRenderingMode: 'virtual',
    },
    showBorders: true,
    showColumnLines: true,
    showRowLines: true,
    sorting: {
      mode: 'multiple',
    },
    stateStoring: {
      enabled: true,
    },
    syncLookupFilterValues: false,
    toolbar: {
      visible: false,
      items: [{}],
    },

  };

  _userOptions;

  constructor(element, options) {
    console.log('skDataGrid', 'construct');
    const userOptions = ({ ...options });

    super(element, options);

    console.log('constructor options', options);
    console.log('this options', this.option());
    this.beginUpdate();

    this._userOptions = userOptions;

    //Templates section
    this._addAppendButtonToCommandColumnHeader();

    //EventSection
    this._rowDoubleClick();
    this._editingStart();
    this._initFilterRowEditors();
    this._initCalculateColumnExpressions();

    this.endUpdate();
    console.log('skDataGrid', this);
  }

  _initCalculateColumnExpressions() {
    this.option().columns.forEach((column) => {
      if (column.tableName && !column.calulateFilterExpression) {
        column.calculateFilterExpression = this._calculateColumnFilterExpressionWithTableName;
      }
    });
  }

  /**Метод вызывается в родительском конструкторе для получения опций компонента по умолчанию.
   * Для изменения значений по умолчанию родительского элемента (в том числе событий) и сохранения значений
   * родительских методов мы расширяем его этим, сохраняя родительскую конфигурацию.*/

  //TODO: заменить jquery метод $.extend на нативный
  _getDefaultOptions() {
    const inheritedOptions = super._getDefaultOptions();
    const defaultOptions = { ...skDataGrid._skDefaultOptions };
    const skDataGridOptions = { ...skDataGrid.defaultComponentOptions };

    console.log('inheritedOptions', inheritedOptions);
    console.log('defaultOptions', defaultOptions);
    console.log('skDataGridOptions', skDataGridOptions);
    console.log('options result', $.extend(true, {}, inheritedOptions, defaultOptions, skDataGridOptions));

    return $.extend(true, {}, inheritedOptions, defaultOptions, skDataGridOptions);
  }

  /** Метод служит для создания кнопки "Добавить" в заголовок колонки с кнопками управления на основании
   * свойства showAppendButtonInCommandColumnHeader. При нажатии кнопка создает новую запись, используя метод
   * dxDataGrid.addRow()*/
  _addAppendButtonToCommandColumnHeader() {
    if (this.option('showAppendButtonInCommandColumnHeader')) {
      const commandColumnOptions = this.columnOption('type:buttons');

      const allowAdding = this.option('editing.allowAdding');

      if (commandColumnOptions && !commandColumnOptions.headerCellTemplate && allowAdding) {
        this.columnOption('type:buttons', 'headerCellTemplate', this._getCommandColumnHeaderCellTemplate);

      }
    }
  }

  _rowDoubleClick() {
    if (!this.option('startEditingOnDoubleClick') || !this.option('editing.allowUpdating')) {
      return;
    }

    this.option('onRowDblClick', function (e) {
      if (e.rowType === 'data' && DevExpress.devices.current().deviceType === 'desktop') {
        e.component.editRow(e.rowIndex);
      }

      if (this._userOptions.onRowDblClick) {
        this._userOptions.onRowDblClick(e);
      }
    });
  }

  _initFilterRowEditors() {
    this.option('onEditorPreparing', function (e) {
      if (e.parentType === `filterRow` && e.lookup && e.useTagBoxRowFilter) {
        this._createFilterRowTagBoxFilterControlForLookupColumns(e);
      }

      if (this._userOptions.onEditorPreparing) {
        this._userOptions.onEditorPreparing(e);
      }
    });
  }

  _calculateColumnFilterExpressionWithTableName(filterValue, selectedFilterOperation) {
    let fullDataFieldName = `${this.tableName}.${this.dataField}`;
    if (selectedFilterOperation === 'between' && $.isArray(filterValue)) {
      return [
        [fullDataFieldName, '>=', filterValue[0]],
        'and', [fullDataFieldName, '<=', filterValue[1]],
      ];
    }
    return [fullDataFieldName, selectedFilterOperation, filterValue];
  }

  _createFilterRowTagBoxFilterControlForLookupColumns(onEditorPreparingEventArguments) {
    onEditorPreparingEventArguments.editorName = `dxTagBox`;
    onEditorPreparingEventArguments.editorOptions.showSelectionControls = true;
    onEditorPreparingEventArguments.editorOptions.dataSource = onEditorPreparingEventArguments.lookup.dataSource;
    onEditorPreparingEventArguments.editorOptions.displayExpr = onEditorPreparingEventArguments.lookup.displayExpr;
    onEditorPreparingEventArguments.editorOptions.valueExpr = onEditorPreparingEventArguments.lookup.valueExpr;
    onEditorPreparingEventArguments.editorOptions.applyValueMode = `useButtons`;
    onEditorPreparingEventArguments.editorOptions.value = onEditorPreparingEventArguments.value || [];
    onEditorPreparingEventArguments.editorOptions.dataFieldName = onEditorPreparingEventArguments.dataField;
    onEditorPreparingEventArguments.editorOptions.onValueChanged = () => {
      function calculateFilterExpression() {
        let filterExpression = [];
        onEditorPreparingEventArguments.element.find(`.dx-datagrid-filter-row`).find(`.dx-tagbox`).each((index, item) => {
          let tagBoxFilterExpression = [];
          let tagBox = $(item).dxTagBox(`instance`);
          tagBox.option(`value`).forEach(function (value) {
            let dataFieldName = tagBox.option().dataFieldName;
            if (onEditorPreparingEventArguments.tableName) {
              dataFieldName = onEditorPreparingEventArguments.tableName + '.' + tagBox.option().dataFieldName;
            }
            tagBoxFilterExpression.push([dataFieldName, `=`, Number(value)]);
            tagBoxFilterExpression.push(`or`);
          }, onEditorPreparingEventArguments);
          tagBoxFilterExpression.pop();
          if (tagBoxFilterExpression.length) {
            filterExpression.push(tagBoxFilterExpression);
            filterExpression.push(`and`);
          }
        });
        filterExpression.pop();
        return filterExpression;
      }

      let calculatedFilterExpression = calculateFilterExpression();

      if (calculatedFilterExpression.length) {
        if (calculatedFilterExpression.length === 1) {
          onEditorPreparingEventArguments.component.filter(calculatedFilterExpression[0]);
        }

        if (calculatedFilterExpression.length > 1) {
          onEditorPreparingEventArguments.component.filter(calculatedFilterExpression);
        }
      } else {
        onEditorPreparingEventArguments.component.clearFilter(`dataSource`);
      }
    };
  }

  _editingStart() {
    if (this.option('editing.mode') === 'skPopup') {
      this.option('onEditingStart', function (e) {
        e.cancel = true;

        if (this._userOptions.onEditingStart) {
          this._userOptions.onEditingStart(e);
        }

        this._initEditingForm(e.component, skDataGrid.editStates.UPDATE, e.key);
      });
    }
  }

  _getCommandColumnHeaderCellTemplate(container, options) {
    $('<div>')
      .appendTo(container)
      .dxButton({
        text: skDataGrid.addRowCommandCaption,
        icon: 'fas fa-plus',
        onClick: (e) => {
          /* TODO: Devexpress не определяет, что editing.mode = skPopup и сбрасывает его на "row",
          **  из-за этого приходится писать этот велосипед. Соответственно встроенная функция dxDataGrid.addRow()
          **  работает некорректно. Нужно разобраться, как научить компонент принимать этот аргумент*/
          if (options.component.option('editing.mode') === 'skPopup') {
            options.component._initEditingForm(options.component, skDataGrid.editStates.INSERT);
          } else {
            options.component.addRow();
          }
        },
      });
  }

  _initEditingForm(component, editState, key) {
    switch (editState.name) {
      case 'insert':
        let data = component.option('editing.defaultInsertionData');
        this._showEditingForm(component, editState, data);
        break;
      case 'update':
        component.beginCustomLoading(skDataGrid.popupOpeningText);
        component.getDataSource().store().byKey(key)
          .done((data) => {
            this._showEditingForm(component, editState, data);
          })
          .fail(() => {
            DevExpress.ui.notify({
              message: skDataGrid.loadErrorMessageText,
            }, 'error');
          })
          .always(() => {
            component.endCustomLoading();
          });
        break;
      case 'duplicate':
        break;
    }
  }

  _showEditingForm(component, editState, formData) {
    let formOptions = {};
    formOptions = {
      formData: formData,
      editingState: editState,
      dataGridInstance: component,
      ...component.option('editing.form'),
    };

    formOptions.items = [...formOptions.items, this._getPopupFormControlButtons()];

    const form = $('<div>').dxForm(formOptions);

    const popupTitle = component.option('editing.popup.title');

    const popupOptions = {
      contentTemplate: () => {
        return form;
      },
      ...component.option('editing.popup'),
      title: popupTitle + editState.suffix,
    };

    let popupInstance = ($('<div>').appendTo('body')).dxPopup(popupOptions).dxPopup('instance');

    form.dxForm('instance').option('popupInstance', popupInstance);

    popupInstance.show();
  }

  _getPopupFormControlButtons() {
    return {
      itemType: 'group',
      colSpan: 1,
      colCount: 2,
      cssClass: 'form-control-buttons-group',
      items: [
        {
          itemType: 'button',
          buttonOptions: {
            text: skDataGrid.popupFormSaveButtonCaption,
            type: 'normal',
            width: 106,
            onClick: (e) => {
              const formInstance = e.element.closest('.dx-form').dxForm('instance');

              if (!formInstance.validate().isValid) {
                return;
              }

              formInstance.option('dataGridInstance').beginCustomLoading(skDataGrid.popupSavingDataText);

              console.log('editingState', formInstance.option('editingState'));

              const store = formInstance.option('dataGridInstance').getDataSource().store();

              switch (formInstance.option('editingState').name) {
                case 'insert':
                  store.insert(
                    formInstance.option('formData'),
                  ).done((data, key) => {
                    if (!(store instanceof DevExpress.data.ArrayStore)) {
                      store.push([{ type: 'insert', data: data.data }]);
                    }
                    formInstance.option('dataGridInstance').endCustomLoading();
                  }).always(() => {
                    formInstance.option('dataGridInstance').endCustomLoading();
                  });
                  break;
                case 'update':
                  store.update(
                    formInstance.option('formData').id,
                    formInstance.option('formData'),
                  ).done((data, key) => {
                    if (!(store instanceof DevExpress.data.ArrayStore)) {
                      store.push([{ type: 'update', data: data.data, key: key }]);
                    }
                  }).always(() => {
                    formInstance.option('dataGridInstance').endCustomLoading();
                  });

                  break;
                case 'duplicate':
                  DevExpress.ui.notify({
                    message: 'Duplicate is not implemented',
                  }, 'warning');
                  break;
                case 'delete':
                  DevExpress.ui.notify({
                    message: 'Delete is not implemented',
                  }, 'warning');
                  break;
                default:
                  console.error('Form in unknown edit state');
              }

              formInstance.option('editingState', skDataGrid.editStates.UNKNOWN);
              formInstance.option('popupInstance').hide();
            },
          },
        },
        {
          itemType: 'button',
          buttonOptions: {
            text: skDataGrid.popupFormCancelButtonCaption,
            type: 'normal',
            width: 106,
            onClick: function (e) {
              const formInstance = e.element.closest('.dx-form').dxForm('instance');
              formInstance.option('popupInstance').hide();
            },
          },
        },
      ],
    };
  }
}

DevExpress.registerComponent('skDataGrid', skDataGrid);
