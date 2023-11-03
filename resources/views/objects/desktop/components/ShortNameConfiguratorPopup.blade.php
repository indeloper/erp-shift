<script>
    function showShortNameConfiguratorPopup() {
        const shortNameConfigurationPopup = $('#shortNameConfigurationPopup').dxPopup({
            title: 'Конфигуратор сокращенного наименования',
            width: '50vw',
            height: 'auto',
            visible: true,
            hideOnOutsideClick: true,
            showCloseButton: true,
            contentTemplate: () => {
                let dataGridInstance = $('#dataGridContainer').dxDataGrid('instance');
                let bitrixId = dataGridInstance.cellValue(dataGridInstance.option('focusedRowIndex'), 'bitrix_id');
                return shortNameConfiguratorPopupContentTemplate(bitrixId)
            },
            toolbarItems: [
                {
                    widget: 'dxButton',
                    toolbar: 'bottom',
                    location: 'after',
                    options: {
                        text: 'OK',
                    },
                    onClick(e) {
                        let shortNameFormInstance = $('#shortNameConfiguratorForm').dxForm('instance');
                        let dataGridInstance = $('#dataGridContainer').dxDataGrid('instance');
                        let shortName = shortNameFormInstance.option("formData")['generatedShortName'];
                        if (!dataGridInstance.option('focusedRowIndex')){
                            dataGridInstance.cellValue(0, 'short_name', shortName);
                        } else {
                            dataGridInstance.cellValue(dataGridInstance.option('focusedRowIndex'), 'short_name', shortName);
                        }

                        shortNameConfigurationPopup.hide()
                    }
                },
                {
                    widget: 'dxButton',
                    toolbar: 'bottom',
                    location: 'after',
                    options: {
                        text: 'Отмена',
                    },
                    onClick() {
                        shortNameConfigurationPopup.hide()
                    }
                }
            ]
        }).dxPopup('instance')
    }

    function shortNameConfiguratorPopupContentTemplate(bitrixId) {
        return $('<div id="shortNameConfiguratorForm">').dxForm({
            formData: {
                bitrixId: bitrixId,
                objectName: "",
                objectCaption: "",
                postalCode: "",
                city: "",
                street: "",
                section: "",
                building: "",
                housing: "",
                letter: "",
                construction: "",
                stead: "",
                queue: "",
                lot: "",
                stage: "",
                housingArea: "",
                cadastralNumber: "",
                generatedShortName: ""
            },
            colCount: 4,
            alignItemLabelsInAllGroups: false,
            items: [
                {
                    itemType: "group",
                    colCount: 2,
                    colSpan: 4,
                    items: [{
                        dataField: 'objectName',
                        label: {
                            text: 'Наименование объекта'
                        },
                        editorType: 'dxTextBox',
                        colSpan: 1
                    },
                    {
                        dataField: 'objectCaption',
                        label: {
                            text: 'Название объекта'
                        },
                        editorType: 'dxTextBox',
                        colSpan: 1
                    }],
                },
                {
                    itemType: "group",
                    colCount: 8,
                    colSpan: 4,
                    items: [{
                            dataField: 'postalCode',
                            label: {
                                text: 'Индекс'
                            },
                            editorType: 'dxTextBox',
                            colSpan: 2
                        },
                        {
                            dataField: 'city',
                            label: {
                                text: 'Город'
                            },
                            editorType: 'dxTextBox',
                            colSpan: 3
                        },
                        {
                            dataField: 'cadastralNumber',
                            label: {
                                text: 'Кадастровый номер'
                            },
                            editorType: 'dxTextBox',
                            colSpan: 3
                        }]
                },
                {
                    itemType: "group",
                    colCount: 5,
                    colSpan: 4,
                    items: [{
                            dataField: 'street',
                            label: {
                                text: 'Улица'
                            },
                            editorType: 'dxTextBox',
                            colSpan: 3
                        },
                        {
                            dataField: 'section',
                            label: {
                                text: 'Участок'
                            },
                            editorType: 'dxTextBox',
                            colSpan: 1
                        },
                        {
                            dataField: 'building',
                            label: {
                                text: 'Дом'
                            },
                            editorType: 'dxTextBox',
                            colSpan: 1
                        }]
                },
                {
                    itemType: "group",
                    colCount: 4,
                    colSpan: 4,
                    items: [{
                        dataField: 'housing',
                        label: {
                            text: 'Корпус'
                        },
                        editorType: 'dxTextBox',
                        colSpan: 1
                    },
                        {
                            dataField: 'letter',
                            label: {
                                text: 'Литера'
                            },
                            editorType: 'dxTextBox',
                            colSpan: 1
                        },
                        {
                            dataField: 'construction',
                            label: {
                                text: 'Строение'
                            },
                            editorType: 'dxTextBox',
                            colSpan: 1
                        },
                        {
                            dataField: 'stead',
                            label: {
                                text: 'Земельный участок'
                            },
                            editorType: 'dxTextBox',
                            colSpan: 1
                        }]
                },
                {
                    itemType: "group",
                    colCount: 4,
                    colSpan: 4,
                    items: [{
                        dataField: 'queue',
                        label: {
                            text: 'Очередь'
                        },
                        editorType: 'dxTextBox',
                        colSpan: 1
                    },
                        {
                            dataField: 'lot',
                            label: {
                                text: 'Лот'
                            },
                            editorType: 'dxTextBox',
                            colSpan: 1
                        },
                        {
                            dataField: 'stage',
                            label: {
                                text: 'Этап'
                            },
                            editorType: 'dxTextBox',
                            colSpan: 1
                        },
                        {
                            dataField: 'housingArea',
                            label: {
                                text: 'Массив'
                            },
                            editorType: 'dxTextBox',
                            colSpan: 1
                        }]
                },
                {
                    dataField: 'generatedShortName',
                    itemType: "simple",
                    label: {
                        text: 'Сформированное наименование',
                        visible: false
                    },
                    colSpan: 4,
                    template: function (data, itemElement) {
                        console.log(data);
                        itemElement.append($(`<div id='generated-short-name'><b>${data.component.option('formData')[data.dataField]}</b></div>`))
                    }
                }
            ],
            onFieldDataChanged: function (e) {
                if (e.dataField === "generatedShortName" || e.dataField === "bitrixId"){
                    return
                }

                let filteredData = {}
                let data = e.component.option("formData");
                for (const key in data) {
                    if (data[key] !== "") {
                        switch (key) {
                            case 'objectName':
                            case 'objectCaption':
                                filteredData[key] = data[key];
                                break;
                            case 'postalCode':
                                filteredData[key] = data[key];
                                break;
                            case 'city':
                                filteredData[key] = "г." + data[key];
                                break;
                            case 'street':
                                filteredData[key] = data[key];
                                break;
                            case 'section':
                                filteredData[key] = "уч." + data[key];
                                break;
                            case 'building':
                                filteredData[key] = "д." + data[key];
                                break;
                            case 'housing':
                                filteredData[key] = "корп." + data[key];
                                break;
                            case 'letter':
                                filteredData[key] = "лит." + data[key];
                                break;
                            case 'construction':
                                filteredData[key] = "стр." + data[key];
                                break;
                            case 'stead':
                                filteredData[key] = "зем.уч." + data[key];
                                break;
                            case 'queue':
                                filteredData[key] = "оч." + data[key];
                                break;
                            case 'lot':
                                filteredData[key] = "лот." + data[key];
                                break;
                            case 'stage':
                                filteredData[key] = "эт." + data[key];
                                break;
                            case 'housingArea':
                                filteredData[key] = "массив " + data[key];
                                break;
                            case 'cadastralNumber':
                                filteredData[key] = "к.н " + data[key];
                                break;

                        }
                    }
                }

                let generatedShortName = Object.values(filteredData).join(", ");

                let formattedBitrixId = "";
                if (bitrixId) {
                    formattedBitrixId = `[ID${bitrixId}] - `;
                }
                generatedShortName = `${formattedBitrixId}${generatedShortName}`;
                e.component.updateData('generatedShortName', generatedShortName)
                $('#generated-short-name').html(`Сокращенное наименование: <b>${generatedShortName}</b>`)

            }
        })
    }
</script>
