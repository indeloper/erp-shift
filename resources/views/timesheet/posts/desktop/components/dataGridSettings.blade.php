<script>
    const postDataGridEditForm = {
        colCount: 1,
        items: [
            {
                itemType: 'group',
                colCount: 2,
                items: [{
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
                                    editing: {
                                        popup: {
                                            title: "Тариф"
                                        },
                                        form: postTariffEditForm
                                    }
                                },
                                template: setDataGridColumnTemplate
                            }
                        ]
                    }]
            },
        ],
    };

    const dataGridSettings = {
        height: "calc(100vh - 200px)",
        editing: {
            mode: "skPopup",
            popup: {
                title: "Должность",
                width: '40vw',
            },
            form: postDataGridEditForm
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
