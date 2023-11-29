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
                container.attr('id', 'tank_id-' + options.data.id + '-company_id')
                const spanText = $('<span>').text(options.displayValue).appendTo(container)

                if(options.data.awaiting_confirmation) {
                    spanText
                        .addClass('text-color-red')
                        .addClass('text-bold')
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
                container.attr('id', 'tank_id-' + options.data.id + '-tank_number')
                const spanText = $('<span>').text(options.displayValue).appendTo(container)

                if(options.data.awaiting_confirmation) {

                    spanText
                        .addClass('text-color-red')
                        .addClass('text-bold')
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

                container.attr('id', 'tank_id-' + options.data.id + '-object_id')
                const spanText = $('<span>').text(options.displayValue).appendTo(container)

                if(options.data.awaiting_confirmation) {
                    spanText
                        .addClass('text-color-red')
                        .addClass('text-bold')
                        .css({
                            textDecoration: 'underline',
                            textDecorationStyle: 'dashed',
                            cursor: 'pointer'
                        })

                    const previousResponsible = fuelTanksResponsiblesStore.__rawData.find(el=>el.id === options.data.previous_responsible_id).full_name
                    const previousObject = projectObjectsStore.__rawData.find(el=>el.id === options.data.previous_object_id).short_name

                    const popoverDiv = $('<div>')
                        .attr('id', 'tank_id-' + options.data.id + '-object_id' + '_popover')
                        .append(`<div><b>Передал:</b> ${previousResponsible}</div>`)
                        .append(`<div><b>Предыдущий объект:</b> ${previousObject}</div>`)

                        if(options.data.comment_movement_tmp) {
                            popoverDiv
                                .append(`<div><b>Комментарий:</b> ${options.data.comment_movement_tmp}</div>`)
                        }

                        popoverDiv
                            .append(`<div><b>Остаток топлива:</b> ${options.data.fuel_level}</div>`)
                            .append('<hr style="margin-left: -20px; margin-right: -20px;">')
                            .appendTo(container)

                        confirmationButtonWrapper =
                            $('<div>')
                            .css({
                                width: '100%',
                                display: 'flex',
                                justifyContent: 'end'
                            }).appendTo(popoverDiv)

                        $('<div>').dxButton({
                            text: 'Принять',
                            type: 'default',
                            visible: Boolean(+options.data.responsible_id === +authUserId),
                            elementAttr: {
                                class: 'confirmationButton'
                            },
                            onClick() {
                                confirmMovingFuelTank(options.data.id, popover)
                            }
                        }).appendTo(confirmationButtonWrapper)

                    const popover = $('#' + 'tank_id-' + options.data.id + '-object_id' + '_popover').dxPopover({
                        target: '#' + 'tank_id-' + options.data.id + '-object_id',
                        showEvent: 'click',
                        position: 'bottom',
                        width: 300,
                        showTitle: true,
                        title: 'Требуется подтверждение нового ответственного'
                    }).dxPopover('instance')
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
                container.attr('id', 'tank_id-' + options.data.id + '-responsible_id')

                const spanText = $('<span>').text(options.displayValue).appendTo(container)

                if(options.data.awaiting_confirmation) {
                    spanText
                        .addClass('text-color-red')
                        .addClass('text-bold')
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
                let cssTextColor = ''
                
                if (options.value > 0) {
                    cssTextColor = 'text-color-green'
                }
                if (options.value < 0) {
                    cssTextColor = 'text-color-red'
                }
                $('<span>')
                    .addClass(cssTextColor)
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
                    visible: function (e) {
                        return Boolean(+e.row.data.responsible_id === +authUserId);
                    },
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
                        visible: userPermissions.add_fuel_tanks,
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
