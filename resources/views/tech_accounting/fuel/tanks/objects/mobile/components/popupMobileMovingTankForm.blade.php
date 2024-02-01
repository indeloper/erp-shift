<script>
    function showMovingTankPopup(formItem = {}) {
        popupMobile.option({
            visible: true,
            title: 'Перемещение емкости',
            contentTemplate: () => {
                return getMovingTankPopupContentTemplate(formItem)
            },
            onContentReady(e) {
                $('#popupSaveButton').dxButton({
                    template: '<div class="text-color-blue">Отправить</div>'
                })
            }
        })
    }

    const getMovingTankPopupContentTemplate = (formItem) => {
        return $('<div id="externalForm">').dxForm({
            validationGroup: "documentExternalValidationGroup",
            labelMode: 'outside',
            labelLocation: 'left',
            formData: formItem,
            items: [
                {
                    dataField: "id",
                    editorType: "dxSelectBox",
                    editorOptions: {
                        dataSource: entitiesDataSource,
                        valueExpr: "id",
                        displayExpr: "tank_number",
                        value: editingRowId,
                        readOnly: true
                    },
                    label: {
                        text: 'Топливная емкость'
                    },
                },
                {
                    dataField: "object_id",
                    editorType: "dxSelectBox",
                    editorOptions: {
                        dataSource: additionalResources.projectObjects,
                        valueExpr: "id",
                        displayExpr: "short_name",
                        itemTemplate(e) {
                            return `<div class="dx-list-item-line-break">${e.short_name}</div>`
                        }
                    },
                    label: {
                        text: 'Перемещение на объект'
                    },
                    validationRules: [{
                        type: 'required',
                        message: 'Укажите значение',
                    }],
                },
                {
                    dataField: "responsible_id",
                    editorType: "dxSelectBox",
                    editorOptions: {
                        dataSource: additionalResources.fuelTanksResponsibles,
                        valueExpr: "id",
                        displayExpr: "user_full_name"
                    },
                    label: {
                        text: 'Передача ответственному'
                    },
                    validationRules: [{
                        type: 'required',
                        message: 'Укажите значение',
                    }],
                },

                {
                    dataField: 'event_date',
                    editorType: "dxDateBox",
                    editorOptions: {
                        value: new Date(),
                        max: new Date(),
                        min: entitiesDataSource.items()?.find(el=>el.id===editingRowId)?.max_event_date
                    },
                    label: {
                        text: 'Дата отправки',
                    },
                    validationRules: [{
                        type: 'required',
                        message: 'Укажите значение',
                    }],
                },
                
                {
                    dataField: 'comment_movement_tmp',
                    editorType: "dxTextBox",
                    label: {
                        text: 'Комментарий'
                    },
                },
                
            ]
        })
    }
</script>
