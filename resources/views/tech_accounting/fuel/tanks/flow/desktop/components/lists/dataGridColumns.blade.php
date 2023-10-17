<script>
    const dataGridColumns = [
        

        {
            visible: false,
            caption: "Объект",
            dataField: "object_id",
            lookup: {
                dataSource: projectObjectsStore,
                valueExpr: "id",
                displayExpr: "name"
            },
            // groupIndex: 1,
        },

        {
            caption: "Поставщик",
            dataField: "contractor_id",
            lookup: {
                dataSource: fuelContractorsStore,
                valueExpr: "id",
                displayExpr: "short_name"
            },
            visible: false
        },
        {
            caption: "Потребитель",
            dataField: "our_technic_id",
            lookup: {
                dataSource: fuelConsumersStore,
                valueExpr: "id",
                displayExpr: "name"
            },
            visible: false
        },
        {
            caption: "Тип операции",
            dataField: "fuel_tank_flow_type_id",
            lookup: {
                dataSource: fuelFlowTypesStore,
                valueExpr: "id",
                displayExpr: "name"
            },
            cellTemplate(container, options) {
   
                let displayValue = options.text
                fontColor = ''

                const marker = $('<div>')
                    
                if (options.value === fuelFlowTypesStore.__rawData.find(el => el.slug === 'outcome').id) {
                    fontColor = '#dd5e5e' 
                    marker.addClass('fa fa-arrow-down')
                } 
                else if(options.value === fuelFlowTypesStore.__rawData.find(el => el.slug === 'income').id) {
                    fontColor = '#1f931f' 
                    marker.addClass('fa fa-arrow-up')
                }
                else {
                    fontColor = '#3a6fcb'
                    marker.addClass('fas fa-exchange-alt')
                }

                const wrapper = 
                    $('<div>')
                        .css({
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'start'
                        }).appendTo(container)

                marker.css({
                    marginRight: '6px',
                    color: fontColor
                }).appendTo(wrapper)

                $('<div>')
                    .css('color', fontColor)
                    .text(displayValue)
                    .appendTo(wrapper)
            }
        },
        {
            caption: "Дата",
            dataField: "created_at",
            dataType: "date",
            width: 150,
        },

        {
            caption: "Топливная емкость",
            dataField: "fuel_tank_id",
            lookup: {
                dataSource: fuelTanksStore,
                valueExpr: "id",
                displayExpr: "tank_number"
            },
            cellTemplate(container, options) {
                const objectName = projectObjectsStore.__rawData?.find(el=>el.id===options.row.data.object_id)?.short_name
                $('<span>')
                    .attr('title', options.text + ' (' + objectName + ')')
                    .text(options.text + ' (' + objectName + ')')
                    .appendTo(container)
            }
        },
        {
            caption: "Компания",
            dataField: "company_id",
            lookup: {
                dataSource: companiesStore,
                valueExpr: "id",
                displayExpr: "name"
            },
            // groupIndex: 0,
        },
        {
            caption: "Ответственный",
            dataField: "responsible_id",
            lookup: {
                dataSource: fuelResponsiblesStore,
                valueExpr: "id",
                displayExpr: "user_full_name"
            },
        },
        {
            caption: "Объем (л)",
            dataField: "volume",
            editorType: 'dxNumberBox',
            editorOptions: {
                min: 0.001
            },
            dataType: "number",
            // customizeText: (data) => {
            //     return new Intl.NumberFormat('ru-RU').format(data.value * 1000 / 1000);
            // },
            cellTemplate(container, options) {

                let displayValue = fontColor = ''

                if (options.row.data.fuel_tank_flow_type_id === fuelFlowTypesStore.__rawData.find(el => el.slug === 'outcome').id) {
                    displayValue = options.value * -1
                } else {
                    displayValue = options.value
                }

                if (displayValue > 0) {
                    fontColor = '#1f931f'
                } else {
                    fontColor = '#dd5e5e'
                }

                $('<span>')
                    .css('color', fontColor)
                    .text(new Intl.NumberFormat('ru-RU').format(displayValue * 1000 / 1000))
                    .appendTo(container)
            }
        },

        {
            type: "buttons",
            buttons: [
                'edit',
                'delete'
            ],

            headerCellTemplate: (container, options) => {

                $('<div>')
                    .appendTo(container)
                    .dxDropDownButton({
                        // icon: 'overflow',
                        text: 'Создать',
                        dropDownOptions: {
                            width: 200
                        },

                        dataSource: fuelFlowTypesStore,
                        valueExpr: 'id',
                        displayExpr: 'name',

                        onItemClick(e) {

                            if (e.itemData.slug === 'income')
                                showIncreaseFuelPopup();

                            if (e.itemData.slug === 'outcome')
                                showDecreaseFuelPopup();

                            if (e.itemData.slug === 'adjustment')
                                showAdjustmentFuelPopup();
                        }
                    })
            }
        }


    ];
</script>