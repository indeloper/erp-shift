<script>
    function hookHandlerDispatcher(hookTypeAndId) {
        if(hookTypeAndId.includes('confirmFuelTankRecieve'))
            confirmFuelTankRecieve(hookTypeAndId)
    }

    function confirmFuelTankRecieve(hookTypeAndId) {
        const fuelTankId = hookTypeAndId.split('-id-')[1];
        showMovingConfirmationFuelTankPopup(fuelTankId)
    }

    async function confirmMovingFuelTank(fuelTankId, movingConfirmationFuelTankFormPopup) {
        return $.ajax({
            url: "{{route('building::tech_acc::fuel::tanks::'.'confirmMovingFuelTank')}}",
            method: "POST",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                fuelTankId: JSON.stringify(fuelTankId),
            },
            success: function (data, textStatus, jqXHR) {
                DevExpress.ui.notify("Данные успешно обновлены", "success", 1000)
                movingConfirmationFuelTankFormPopup.hide()
            },
        })
    }

    const getConfirmPopupContentTemplate = () => {

            return $('<div id="movingConfirmationFuelTankFormPopup">').dxForm({
                labelMode: 'outside',
                labelLocation: 'left',
                items: [
                    {
                        dataField: "id",
                        visible: false

                    },
                    {
                        dataField: "tank_number",
                        editorOptions: {
                            readOnly: true
                        },
                        label: {
                            text: "Топливная емкость"
                        },
                        
                    },
                    {
                        dataField: "object_name",
                        editorOptions: {
                            readOnly: true
                        },
                        label: {
                            text: "Объект"
                        },
                    },
                    {
                        dataField: "responsible_name",
                        editorOptions: {
                            readOnly: true
                        },
                        label: {
                            text: "Ответственный"
                        },
                    },
                    {
                        dataField: 'fuel_level',
                        editorOptions: {
                            readOnly: true
                        },
                        label: {
                            text: 'Остаток топлива',
                        },
                    },
                ]
            })
    }

    const showMovingConfirmationFuelTankPopup = (fuelTankId) => {
        const movingConfirmationFuelTankFormPopup = $("#externalPopup").dxPopup({
            visible: true,
            title: 'Подтверждение перемещения емкости',
            height: 'auto',
            width: '400px',
            onContentReady() {
                const url = "{{route('building::tech_acc::fuel::tanks::'.'getFuelTankConfirmationFormData')}}"
                $.getJSON( url, {
                    fuelTankId: JSON.stringify(fuelTankId),
                }).done(function( data ) {
                    if(data.status === 'not need confirmation') {
                        alert ('Подтверждение не требуется');
                        movingConfirmationFuelTankFormPopup.hide()
                        return;
                    }
                    
                    $('#movingConfirmationFuelTankFormPopup').dxForm('instance').option('formData', data)
                })                
            },
            toolbarItems: [
                {
                    widget: 'dxButton',
                    toolbar: 'bottom',
                    location: 'after',
                    options: {
                        text: 'Подтвердить',
                    },
                    onClick(e) {
                        confirmMovingFuelTank(fuelTankId, movingConfirmationFuelTankFormPopup)
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
                        movingConfirmationFuelTankFormPopup.hide()
                    }
                }
            ],
            contentTemplate: () => {
                // fuelTankFormData = {id: 12}
                return getConfirmPopupContentTemplate()

            }
        }).dxPopup('instance')
    }
</script>