<script>
    // Форма. Элементы группы Объект
    const dataGridEditForm01Group1Elems = [


        {
            dataField: "name",
            colSpan: 2,
            validationRules: [{
                type: 'required',
                message: 'Укажите значение',
            }],
        },
        {
            dataField: "short_name",
            colSpan: 2,
            editorType: 'dxTextBox',
            editorOptions: {
                elementAttr: {
                    id: 'objectShotrNameFormField'
                },
            },
            validationRules: [{
                type: 'required',
                message: 'Укажите значение',
            }],
        },
        {
            dataField: "address",
            colSpan: 2,
            validationRules: [{
                type: 'required',
                message: 'Укажите значение',
            }],
        },
        {
            dataField: "cadastral_number",
            /*editorType: "dxTextBox",
            editorOptions: {
                mask: 'a:a:b:d',
                maskRules: {
                    a:/\d/,
                    b:/\d{6,7}/,
                    d: /[\d]+/}
            }*/

            // {X: /\d{2}:\d{2}:\d{6,7}:\d*/}
            // validationRules: [{}],
        },

        {
            dataField: "bitrix_id",
            editorType: "dxSelectBox",
            editorOptions: {
                readOnly: true,
                showClearButton: true,
                elementAttr: {
                    id: "bitrixIdFormField"
                },

                buttons: [
                    {
                        name: 'clear-bitrix-projects-editor-button',
                        location: 'after',
                        options: {
                            elementAttr: {
                                id: "clearBitrixProjectsOpenPopupButton",
                            },
                            stylingMode: 'text',
                            icon: 'clear',
                            type: 'default',
                            disabled: false,
                            onClick(e) {
                                resetChoosedBitrixProject()
                            },
                        },
                    },

                    {
                        name: 'bitrix-projects-editor-button',
                        location: 'after',
                        options: {
                            elementAttr: {
                                id: "bitrixProjectsOpenPopupButton"
                            },
                            icon: 'more',
                            type: 'default',
                            disabled: false,
                            onClick(e) {
                                showBitrixProjectsPopup()
                            },
                        },
                    },

                ],
                fieldTemplate(data, container) {
                    const result = $(`
                            <div style="display:flex; align-items:center">
                                <div id="bitrix-project-name"></div>
                            </div>
                        `);
                    result
                        .find('#bitrix-project-name')
                        .dxTextBox({
                            value: () => {
                                const bitrixId = $('#bitrixIdFormField').dxSelectBox('instance').option('value')
                                if(bitrixId) {
                                    return getBitrixProjectFormDisplayValue(bitrixId)
                                }
                                return 'Выбрать...'
                            },
                            readOnly: true,
                            inputAttr: {
                                // id: "bitrixProjectNameFormField"
                            },
                        });

                    container.append(result);
                },
            },
        },
    ];
</script>
