<script>
    function showShortNameConfiguratorPopup() {
        const shortNameConfigurationPopup = $('#shortNameConfigurationPopup').dxPopup({
            title: 'Конфигуратор сокращенного наименования',
            width: '50vw',
            height: 'auto',
            visible: true,
            hideOnOutsideClick: true,
            showCloseButton: true,
            contentTemplate: shortNameConfiguratorPopupContentTemplate,
            toolbarItems: [
                {
                    widget: 'dxButton',
                    toolbar: 'bottom',
                    location: 'after',
                    options: {
                        text: 'OK',
                    },
                    onClick(e) {
                        //handleChoosingBitrixProject()
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

    const shortNameConfiguratorPopupContentTemplate = () => {
        return $('<div id="shortNameConfiguratorForm">').dxForm({
            formData: {
                postalCode: "",
                city: "",
                street: "",
                section: "",
                building: "",
                housing: "",
                letter: "",
                cadastralNumber: "",
                generatedShortName: ""
            },
            colCount: 4,
            items: [
                {
                    dataField: 'postalCode',
                    label: {
                        text: 'Индекс'
                    },
                    editorType: 'dxTextBox',
                    colSpan: 1
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
                    dataField: 'street',
                    label: {
                        text: 'Улица'
                    },
                    editorType: 'dxTextBox',
                    colSpan: 4
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
                },
                {
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
                    dataField: 'cadastralNumber',
                    label: {
                        text: 'Кадастровый номер'
                    },
                    editorType: 'dxTextBox',
                    colSpan: 2
                }
            ],
            onFieldDataChanged: function (e) {
                if (e.dataField === "generatedShortName"){
                    return
                }

                let filteredData = {}
                let data = e.component.option("formData");
                for (const key in data) {
                    if (data[key] !== "") {
                        switch (key) {
                            case 'postalCode':
                                filteredData[key] = data[key];
                                break;
                            case 'city':
                                filteredData[key] = "гор. " + data[key];
                                break;
                            case 'street':
                                filteredData[key] = data[key];
                                break;
                            case 'section':
                                filteredData[key] = "уч. " + data[key];
                                break;
                            case 'building':
                                filteredData[key] = "д. " + data[key];
                                break;
                            case 'housing':
                                filteredData[key] = "корп. " + data[key];
                                break;
                            case 'letter':
                                filteredData[key] = "лит. " + data[key];
                                break;
                            case 'cadastralNumber':
                                filteredData[key] = "кад. номер " + data[key];
                                break;
                        }
                    }
                }

                console.log("result", Object.values(filteredData).join(", "))

            }
        })
    }
</script>
