class skDataGrid extends DevExpress.ui.dxDataGrid {
    static popupOpeningText = "Загрузка карточки документа"
    static loadErrorMessageText = "При загрузке данных произошла ошибка"
    static addRowCommandCaption = "Добавить"
    static popupFormSaveButtonCaption = "Сохранить"
    static popupFormCancelButtonCaption = "Отмена"

    static defaultOptions;

    static editStates = {
        UNKNOWN: {
            name: 'unknown',
            suffix: ''
        },
        INSERT: {
            name: 'insert',
            suffix: ' — создание'
        },
        UPDATE: {
            name: 'update',
            suffix: ' — редактирование'
        },
        DUPLICATE: {
            name: 'duplicate',
            suffix: ' — дублирование'
        },
        DELETE: {
            name: 'delete',
            suffix: ' — удаление'
        }
    }

    static defaultComponentOptions = {
        showAppendButtonInCommandColumnHeader: true, //Создает кнопку «Добавить» в заголовке командного столбца
        editRowOnDoubleClick: true, //Переводит DataGrid в состояние редактирования при двойном клике
    };

    /** TODO: разобраться и перенести _defaultParentOptions в метод DevExpress.ui.skDataGrid.defaultOptions({})
     *
     *Настройки по умолчанию для компонента
     */
    static _defaultOptions = {
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
            mode: "skPopup",
            initialInsertionData: {name: 'test'}
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
        keyExpr: "id",
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
            mode: "multiple"
        },
        stateStoring: {
            enabled: true
        },
        syncLookupFilterValues: false,
        toolbar: {
            visible: true,
            items: [{}]
        },

    }

    _userOptions;

    constructor(element, options) {
        console.log('skDataGrid', 'construct');
        const userOptions = ({...options})

        super(element, options);

        console.log('constructor options', options);
        console.log('this options', this.option());
        this.beginUpdate();

        this._userOptions = userOptions;

        this._replaceObjectClassNameRecursively(this._$element[0], 'dx-datagrid-container', 'dx-datagrid');

        //Templates section
        this._addAppendButtonToCommandColumnHeader();

        //EventSection
        this._rowDoubleClick();
        this._editingStart();

        this.endUpdate();
        console.log('skDataGrid', this);
    }

    /**В поведении компонента наблюдаются проблемы со стилями из-за того, что для dxDataGrid при наследовании несколько
     * другим способом генерируются базовый класс от имени компонента, в отличие от других компонент DevExpress.
     * Пришлось делать замену классов, что бы сохранить стили*/
    _replaceObjectClassNameRecursively(element, oldClass, newClass) {
        if (element) {
            element.classList.replace(oldClass, newClass);

            let children = element.children;

            for (let i = 0; i < children.length; i++) {
                this._replaceObjectClassNameRecursively(children[i], oldClass, newClass);
            }
        }
    }

    /**Метод вызывается в родительском конструкторе для получения опций компонента по умолчанию.
     * Для изменения значений по умолчанию родительского элемента (в том числе событий) и сохранения значений
     * родительских методов мы расширяем его этим, сохраняя родительскую конфигурацию.*/

    //TODO: заменить jquery метод $.extend на нативный
    _getDefaultOptions() {
        let inheritedOptions = super._getDefaultOptions();
        let defaultOptions = {...skDataGrid._defaultOptions}
        let skDataGridOptions = {...skDataGrid.defaultComponentOptions};

        console.log('inheritedOptions', inheritedOptions);
        console.log('defaultOptions', defaultOptions);
        console.log('skDataGridOptions', skDataGridOptions);
        console.log('options result', $.extend(true, {}, inheritedOptions, defaultOptions, skDataGridOptions))

        return $.extend(true, {}, inheritedOptions, defaultOptions, skDataGridOptions)
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
        if (!this.option('editRowOnDoubleClick')) {
            return
        }

        this.option('onRowDblClick', function (e) {
            if (e.rowType === "data" && DevExpress.devices.current().deviceType === 'desktop') {
                e.component.editRow(e.rowIndex);
            }

            if (this._userOptions.onRowDblClick) {
                this._userOptions.onRowDblClick(e);
            }
        });
    }

    _editingStart() {
        if (this.option('editing.mode') === 'skPopup') {
            this.option('onEditingStart', function (e) {
                e.cancel = true;

                if (this._userOptions.onEditingStart) {
                    this._userOptions.onEditingStart(e);
                }

                this._initEditingForm(e.component, skDataGrid.editStates.UPDATE, e.key);
            })
        }
    }

    _getCommandColumnHeaderCellTemplate(container, options) {
        $('<div>')
            .appendTo(container)
            .dxButton({
                text: skDataGrid.addRowCommandCaption,
                icon: "fas fa-plus",
                onClick: (e) => {
                    if (options.component.option('editing.mode') === 'skPopup') {
                        options.component._initEditingForm(options.component, skDataGrid.editStates.INSERT);
                    } else {
                        options.component.addRow();
                    }
                }
            })
    }

    _initEditingForm(component, editState, key) {
        switch (editState.name) {
            case 'insert':
                let data = component.option('editing.defaultInsertionData')
                this._showEditingForm(component, editState, data)
                break;
            case 'update':
                component.beginCustomLoading(skDataGrid.popupOpeningText);
                component.getDataSource().store().byKey(key)
                    .done((data) => {
                        this._showEditingForm(component, editState, data)
                    })
                    .fail(() => {
                        DevExpress.ui.notify({
                            message: skDataGrid.loadErrorMessageText
                        }, 'error')
                    })
                    .always(() => {
                        component.endCustomLoading();
                    })
                break;
            case 'duplicate':
                break;
        }
    }

    _showEditingForm(component, editState, formData) {
        let formOptions = {}
        formOptions = {
            formData: formData,
            editingState: editState,
            dataGridInstance: component,
            ...component.option('editing.form')
        }

        formOptions.items = [...formOptions.items, this._getPopupFormControlButtons()];

        const form = $('<div>').dxForm(formOptions);

        const popupTitle = component.option('editing.popup.title');

        const popupOptions = {
            contentTemplate: () => {
                return form
            },
            ...component.option('editing.popup'),
            title: popupTitle + editState.suffix
        };

        let popupInstance = ($('<div>').appendTo('body')).dxPopup(popupOptions).dxPopup("instance");

        form.dxForm('instance').option('popupInstance', popupInstance);

        popupInstance.show();
    }

    _getPopupFormControlButtons() {
        return {
            itemType: "group",
            colSpan: 1,
            colCount: 2,
            cssClass: 'form-control-buttons-group',
            items: [
                {
                    itemType: "button",
                    buttonOptions: {
                        text: skDataGrid.popupFormSaveButtonCaption,
                        type: "normal",
                        width: 106,
                        onClick: (e) => {
                            const formInstance = e.element.closest('.dx-form').dxForm('instance');

                            formInstance.option('dataGridInstance').beginCustomLoading('Сохранение данных');

                            console.log('editingState', formInstance.option('editingState'));

                            switch (formInstance.option('editingState').name) {
                                case 'insert':
                                    DevExpress.ui.notify({
                                        message: "Insert is not implemented"
                                    }, 'warning')
                                    break;
                                case 'update':
                                    const store = formInstance.option('dataGridInstance').getDataSource().store();
                                    store.update(
                                        formInstance.option('formData').id,
                                        formInstance.option('formData')
                                    ).done(function (data, key) {
                                        if (!(store instanceof DevExpress.data.ArrayStore)) {
                                            store.push([{type: "update", data: data.data, key: key}]);
                                        }
                                        formInstance.option('dataGridInstance').endCustomLoading('Сохранение данных');
                                    })
                                    break;
                                case 'duplicate':
                                    DevExpress.ui.notify({
                                        message: "Duplicate is not implemented"
                                    }, 'warning')
                                    break;
                                case 'delete':
                                    DevExpress.ui.notify({
                                        message: "Delete is not implemented"
                                    }, 'warning')
                                    break;
                                default:
                                    console.error('Form in unknown edit state');
                            }

                            formInstance.option('editingState', skDataGrid.editStates.UNKNOWN);
                            formInstance.option('popupInstance').hide();
                        }
                    }
                },
                {
                    itemType: "button",
                    buttonOptions: {
                        text: skDataGrid.popupFormCancelButtonCaption,
                        type: "normal",
                        width: 106,
                        onClick: function (e) {
                            const formInstance = e.element.closest('.dx-form').dxForm('instance');
                            formInstance.option('popupInstance').hide();
                        }
                    }
                }
            ]
        }
    }
}

DevExpress.registerComponent("skDataGrid", skDataGrid);

// dxDataGrid.defaultOptions
// // // DevExpress
// DevExpress.ui.skDataGrid.defaultOptions({

//});
