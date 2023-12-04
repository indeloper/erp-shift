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
            dataField: "event_date_custom",
            dataType: "date",
            groupIndex: 0,
            sortOrder: 'desc',

            calculateGroupValue: function (data) {
                return data.event_date.getFullYear() + "-" + (data.event_date.getMonth() + 1);
            },
            groupCellTemplate: function (element, options) {

                const year = options.data.summary.year
                const mothNum = options.data.summary.month
                const month = months[mothNum]

                const headerContent = $('<div>')
                    .addClass('group-cell-template-content-justify-space-between')
                    .appendTo(element)

                const groupHeader = $('<div>')
                    .text(month + ' ' + year)
                    .appendTo(headerContent);

                const reportButton = $('<div>').dxButton({
                    text: 'Отчет',
                    onClick: function () {
                        // console.log(currentLoadOptions);
                        // const url = "{{route('building::tech_acc::fuel::reports::fuelFlowPersonalPeriodReport::'.'resource.index')}}?" + 'year=' + year + '&month=' + mothNum
                        const url = "{{route('building::tech_acc::fuel::reports::fuelFlowPeriodReport::'.'resource.index')}}?" + 'year=' + year + '&month=' + mothNum + '&loadOptions=' + JSON.stringify(currentLoadOptions)

                        window.open(url, '_blank');
                    },
                }).appendTo(headerContent);

            }
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
                let cssTextColor = ''

                const marker = $('<div>')

                if (options.value === fuelFlowTypesStore.__rawData.find(el => el.slug === 'outcome').id) {
                    cssTextColor = 'text-color-red'
                    marker.addClass('fa fa-arrow-down')
                } else if (options.value === fuelFlowTypesStore.__rawData.find(el => el.slug === 'income').id) {
                    cssTextColor = 'text-color-green'
                    marker.addClass('fa fa-arrow-up')
                } else {
                    cssTextColor = 'text-color-blue'
                    marker.addClass('fas fa-exchange-alt')
                }

                const wrapper =
                    $('<div>')
                        .addClass('cell-template-content-wrapper-justify-start')
                        .appendTo(container)

                marker
                    .addClass('icon-marker-location-left')
                    .addClass(cssTextColor)
                    .appendTo(wrapper)

                $('<div>')
                    .addClass(cssTextColor)
                    .text(displayValue)
                    .appendTo(wrapper)
            }
        },

        {
            caption: 'Дата операции',
            dataField: 'event_date',
            dataType: "date",
            width: 150,
            sortOrder: 'desc',
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
                const objectName = projectObjectsStore.__rawData?.find(el => el.id === options.row.data.object_id)?.short_name
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
            alignment: 'right',
            cellTemplate(container, options) {

                let displayValue = ''
                let cssTextColor = ''

                if (options.row.data.fuel_tank_flow_type_id === fuelFlowTypesStore.__rawData.find(el => el.slug === 'outcome').id) {
                    displayValue = options.value * -1
                } else {
                    displayValue = options.value
                }

                if (displayValue > 0) {
                    cssTextColor = 'text-color-green'
                } else {
                    cssTextColor = 'text-color-red'
                }

                $('<span>')
                    .addClass(cssTextColor)
                    .text(new Intl.NumberFormat('ru-RU').format(displayValue * 1000 / 1000))
                    .appendTo(container)
            }
        },

        {
            type: "buttons",
            buttons: [
                {
                    icon: 'fas fa-list-alt dx-link-icon',
                    onClick(e) {
                        editingRowId = e.row.data.id;

                        let choosedItem = getChoosedItem(e.row.data.id)
                        let fuelFlowType = fuelFlowTypesStore.__rawData.find(el => el.id === choosedItem.fuel_tank_flow_type_id).slug

                        if (fuelFlowType === 'outcome') {
                            if (choosedItem.our_technic_id) {
                                choosedItem.fuelConsumerType = 'our_technik_radio_elem'
                            } else {
                                choosedItem.fuelConsumerType = 'third_party_technik_radio_elem'
                            }

                            showDecreaseFuelPopup(choosedItem)
                        }

                        if (fuelFlowType === 'income')
                            showIncreaseFuelPopup(choosedItem)

                        if (fuelFlowType === 'adjustment')
                            showAdjustmentFuelPopup(choosedItem)
                    }
                },
                // 'edit',
                // 'delete'
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

                        visible: userPermissions.create_fuel_tank_flows_for_reportable_tanks || userPermissions.create_fuel_tank_flows_for_any_tank,

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
