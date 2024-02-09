<script>
    const dataGridEditForm01Group1Elems = [
        {
            dataField: "department_id",
            colSpan: 2,
            label: {
                text: "Подразделение"
            },
            validationRules: [{
                type: 'required',
                message: 'Укажите значение',
            }],
        },
        {
            dataField: "name",
            colSpan: 2,
            label: {
                text: "Наименование"
            },
            validationRules: [{
                type: 'required',
                message: 'Укажите значение',
            }],
        },
        {
            itemType: "group",
            colSpan: 2,
            caption: "Тарифы",
            items: [
                {
                    colSpan: 2,
                    itemType: "simple",
                    dataField: "postTariffs",
                    label: {
                        visible: false
                    },
                    editorType: "skDataGrid",
                    /*
                    TODO: Добавить стиль, убирающий лишний padding. Но он влияет и на основной datagrid
                        div.dx-datagrid-header-panel {
                            padding: 0
                        }
                    */
                    editorOptions: {
                        keyExpr: "id",
                        height: 300,
                        focusedRowEnabled: true,
                    },
                    template: setDataGridColumnTemplate
                }
            ]
        },
        getFormControlButtons()
    ];

    function getFormControlButtons() {
        return {
            itemType: "group",
            colSpan: 2,
            colCount: 2,
            cssClass: 'form-control-buttons-group',
            items: [
            {
                itemType: "button",
                buttonOptions: {
                    text: "Сохранить",
                    type: "normal",
                    width: 106,
                    onClick: (e) => {
                        const formInstance = e.element.closest('.dx-form').dxForm('instance');

                        formInstance.option('dataGridInstance').beginCustomLoading('Сохранение данных');

                        switch (formInstance.option('editingState')) {
                            case formEditStates.INSERT:
                                break;
                            case formEditStates.UPDATE:
                                const store = formInstance.option('dataGridInstance').getDataSource().store();
                                store.update(
                                    formInstance.option('formData').id,
                                    formInstance.option('formData')
                                ).done(function (data, key) {
                                    store.push([{ type: "update", data: data.data, key: key }]);
                                    formInstance.option('dataGridInstance').endCustomLoading('Сохранение данных');
                                });

                                break;
                            default:
                                console.error('Form in unknown edit state');
                        }

                        formInstance.option('editingState', formEditStates.UNKNOWN);
                        formInstance.option('popupInstance').hide();
                    }
                }
            },
            {
                itemType: "button",
                buttonOptions: {
                    text: "Отмена",
                    type: "normal",
                    width: 106,
                    onClick: function (e) {
                        const formInstance = e.element.closest('.dx-form').dxForm('instance');
                        formInstance.option('popupInstance').hide();
                    }
                }
            },
        ]
        }
    }

    function setDataGridColumnTemplate(itemOptions, itemElement) {
        const dataGridOptions = {
            ...itemOptions.editorOptions,
            dataSource: itemOptions.component.option('formData')[itemOptions.dataField],
            onSaved: (e) => {
                itemOptions.component.updateData(itemOptions.dataField, e.component.getDataSource().items());
            }
        };

        ($('<div>').skDataGrid(dataGridOptions)).appendTo(itemElement);
    }
</script>
