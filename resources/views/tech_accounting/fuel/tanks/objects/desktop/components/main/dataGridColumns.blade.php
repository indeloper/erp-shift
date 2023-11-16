<script>
    const dataGridColumns = [

        {
            caption: "Идентификатор",
            dataField: "id",
            width: 75,
            visible: false,
        },

        {
            caption: "Начало эксплуатации",
            dataField: "explotation_start",
            dataType: "date",
            visible: false,
        },
        {
            caption: "Компания",
            dataField: "company_id",
            lookup: {
                dataSource: companiesStore,
                valueExpr: "id",
                displayExpr: "name"
            },

            cellTemplate(container, options) {
                const spanText = $('<span>').text(options.displayValue).appendTo(container)
                spanText.attr('id', 'tank_id-' + options.data.id + '-company_id')

                if(options.data.awaiting_confirmation) {
                    spanText
                        .css({
                            color: '#dd5e5e',
                            fontWeight: 'bold',
                        })

                    const previousResponsible = fuelTanksResponsiblesStore.__rawData.find(el=>el.id === options.data.previous_responsible_id).full_name
                    const previousObject = projectObjectsStore.__rawData.find(el=>el.id === options.data.previous_object_id).short_name
                    
                    const popoverDiv = $('<div>')
                        .attr('id', 'tank_id-' + options.data.id + '-company_id' + '_popover')
                        .append(`<div><b>Передал:</b> ${previousResponsible}</div>`)
                        .append(`<div><b>Предыдущий объект:</b> ${previousObject}</div>`)
                        .appendTo(container)

                    $('#' + 'tank_id-' + options.data.id + '-company_id' + '_popover').dxPopover({
                        target: '#' + 'tank_id-' + options.data.id + '-company_id',
                        showEvent: 'mouseenter',
                        hideEvent: 'mouseleave',
                        position: 'bottom',
                        width: 300,
                        showTitle: true,
                        title: 'Требуется подтверждение нового ответственного',
                    })
                }
            }

        },

        {
            caption: "Номер емкости",
            dataField: "tank_number",
            width: 150,
            sortIndex: 0,
            sortOrder: "asc",
            cellTemplate(container, options) {
                const spanText = $('<span>').text(options.displayValue).appendTo(container)
                spanText.attr('id', 'tank_id-' + options.data.id + '-tank_number')

                if(options.data.awaiting_confirmation) {
                    spanText
                        .css({
                            color: '#dd5e5e',
                            fontWeight: 'bold',
                        })

                    const previousResponsible = fuelTanksResponsiblesStore.__rawData.find(el=>el.id === options.data.previous_responsible_id).full_name
                    const previousObject = projectObjectsStore.__rawData.find(el=>el.id === options.data.previous_object_id).short_name
                    
                    const popoverDiv = $('<div>')
                        .attr('id', 'tank_id-' + options.data.id + '-tank_number' + '_popover')
                        .append(`<div><b>Передал:</b> ${previousResponsible}</div>`)
                        .append(`<div><b>Предыдущий объект:</b> ${previousObject}</div>`)
                        .appendTo(container)

                    $('#' + 'tank_id-' + options.data.id + '-tank_number' + '_popover').dxPopover({
                        target: '#' + 'tank_id-' + options.data.id + '-tank_number',
                        showEvent: 'mouseenter',
                        hideEvent: 'mouseleave',
                        position: 'bottom',
                        width: 300,
                        showTitle: true,
                        title: 'Требуется подтверждение нового ответственного',
                    })
                }
            }
        },
        {
            caption: "Объект",
            dataField: "object_id",
            lookup: {
                dataSource: projectObjectsStore,
                valueExpr: "id",
                displayExpr: "short_name"
            },

            cellTemplate(container, options) {
                const spanText = $('<span>').text(options.displayValue).appendTo(container)
                spanText.attr('id', 'tank_id-' + options.data.id + '-object_id')

                if(options.data.awaiting_confirmation) {
                    spanText
                        .css({
                            color: '#dd5e5e',
                            fontWeight: 'bold',
                        })

                    const previousResponsible = fuelTanksResponsiblesStore.__rawData.find(el=>el.id === options.data.previous_responsible_id).full_name
                    const previousObject = projectObjectsStore.__rawData.find(el=>el.id === options.data.previous_object_id).short_name
                    
                    const popoverDiv = $('<div>')
                        .attr('id', 'tank_id-' + options.data.id + '-object_id' + '_popover')
                        .append(`<div><b>Передал:</b> ${previousResponsible}</div>`)
                        .append(`<div><b>Предыдущий объект:</b> ${previousObject}</div>`)
                        .appendTo(container)

                    $('#' + 'tank_id-' + options.data.id + '-object_id' + '_popover').dxPopover({
                        target: '#' + 'tank_id-' + options.data.id + '-object_id',
                        showEvent: 'mouseenter',
                        hideEvent: 'mouseleave',
                        position: 'bottom',
                        width: 300,
                        showTitle: true,
                        title: 'Требуется подтверждение нового ответственного',
                    })
                }
            }
        },
        {
            caption: "Ответственный",
            dataField: "responsible_id",
            lookup: {
                dataSource: fuelTanksResponsiblesStore,
                valueExpr: "id",
                displayExpr: "full_name"
            },
            
            cellTemplate(container, options) {
                const spanText = $('<span>').text(options.displayValue).appendTo(container)
                spanText.attr('id', 'tank_id-' + options.data.id + '-responsible_id')

                if(options.data.awaiting_confirmation) {
                    spanText
                        .css({
                            color: '#dd5e5e',
                            fontWeight: 'bold',
                        })

                    const previousResponsible = fuelTanksResponsiblesStore.__rawData.find(el=>el.id === options.data.previous_responsible_id).full_name
                    const previousObject = projectObjectsStore.__rawData.find(el=>el.id === options.data.previous_object_id).short_name
                    
                    const popoverDiv = $('<div>')
                        .attr('id', 'tank_id-' + options.data.id + '-responsible_id' + '_popover')
                        .append(`<div><b>Передал:</b> ${previousResponsible}</div>`)
                        .append(`<div><b>Предыдущий объект:</b> ${previousObject}</div>`)
                        .appendTo(container)

                    $('#' + 'tank_id-' + options.data.id + '-responsible_id' + '_popover').dxPopover({
                        target: '#' + 'tank_id-' + options.data.id + '-responsible_id',
                        showEvent: 'mouseenter',
                        hideEvent: 'mouseleave',
                        position: 'bottom',
                        width: 300,
                        showTitle: true,
                        title: 'Требуется подтверждение нового ответственного',
                    })
                }
            }
        },
        {
            caption: "Текущий остаток (л)",
            dataField: "fuel_level",
            editorType: 'dxNumberBox',
            editorOptions: {
                min: 0.001
            },
            cellTemplate(container, options) {
                fontColor = 'black'
                if (options.text > 0)
                    fontColor = '#1f931f';
                if (options.text < 0)
                    fontColor = '#dd5e5e'

                $('<span>')
                    .css('color', fontColor)
                    .text(new Intl.NumberFormat('ru-RU').format(options.text * 1000 / 1000))
                    .appendTo(container)

            }

        },

        {
            type: "buttons",
            buttons: [
                {
                    hint: 'Переместить',
                    icon: 'fas fa-exchange-alt',
                    onClick(e) {
                        if (!e.row.data.awaiting_confirmation) {
                            showMovingFuelTankPopup(e.row.data)
                        } else {
                            showMovingConfirmationFuelTankPopup(e.row.data)
                        }
                    },
                },
                'edit',
                {
                    name: 'delete',
                    visible: userPermissions.delete_fuel_tanks
                }

                
            ],

            headerCellTemplate: (container, options) => {
                $('<div>')
                    .appendTo(container)
                    .dxButton({
                        text: "Добавить",
                        icon: "fas fa-plus",
                        disabled: !userPermissions.add_fuel_tanks,
                        onClick: (e) => {
                            options.component.addRow();
                            $('#mainDataGrid').dxDataGrid('instance').option("focusedRowKey", undefined);
                            $('#mainDataGrid').dxDataGrid('instance').option("focusedRowIndex", undefined);
                        }
                    })
            }
        }


    ];
</script>
