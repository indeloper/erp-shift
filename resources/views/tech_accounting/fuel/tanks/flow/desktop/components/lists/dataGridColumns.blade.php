<script>
    const dataGridColumns = [

        {
            visible: false,
            caption: "Объект",
            dataField: "object_id",
            lookup: {
                dataSource: additionalResources.projectObjects,
                valueExpr: "id",
                displayExpr: "name"
            },
            // groupIndex: 1,
        },

        {
            visible: false,
            caption: "Поставщик",
            dataField: "contractor_id",
            lookup: {
                dataSource: additionalResources.fuelContractors,
                valueExpr: "id",
                displayExpr: "short_name"
            },
        },
        {
            visible: false,
            caption: "Потребитель",
            dataField: "our_technic_id",
            lookup: {
                dataSource: additionalResources.fuelConsumers,
                valueExpr: "id",
                displayExpr: "name"
            },
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
                        const url = "{{route('building::tech_acc::fuel::reports::fuelTankPeriodReport::'.'getPdf')}}?" + 'year=' + year + '&month=' + mothNum + '&loadOptions=' + JSON.stringify(currentLoadOptions)
                        window.open(url, '_blank');
                    },
                }).appendTo(headerContent);

            }
        },

        {
            caption: "Тип операции",
            dataField: "fuel_tank_flow_type_id",
            width: '140px',
            lookup: {
                dataSource: additionalResources.fuelFlowTypes,
                valueExpr: "id",
                displayExpr: "name"
            },
            cellTemplate(container, options) {

                let displayValue = options.text
                let cssTextColor = ''

                const marker = $('<div>')

                if (options.value === additionalResources.fuelFlowTypes.find(el => el.slug === 'outcome').id) {
                    cssTextColor = 'text-color-red'
                    marker.addClass('fa fa-arrow-down')
                } else if (options.value === additionalResources.fuelFlowTypes.find(el => el.slug === 'income').id) {
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
            width: '90px',
        },

        {
            caption: "Топливная емкость",
            dataField: "fuel_tank_id",
            lookup: {
                dataSource: additionalResources.fuelTanks,
                valueExpr: "id",
                displayExpr: "tank_number"
            },
            alignment: "left",
            cellTemplate(container, options) {
                const objectName = additionalResources.projectObjects.find(el => el.id === options.row.data.object_id)?.short_name
                $('<span>')
                    .attr('title', options.text + ' (' + objectName + ')')
                    .html(`<div class="flex"><span style="font-weight:bold">${options.text}</span><div class="ml_5 text-overflow-ellipsis">(${objectName})</div></div>`)
                    .appendTo(container)
            }
        },
        {
            caption: "Компания",
            dataField: "company_id",
            lookup: {
                dataSource: additionalResources.companies,
                valueExpr: "id",
                displayExpr: "name",
            },
            width: '150px',
            tableName: "fuel_tank_flows",
        },
        {
            caption: "Ответственный",
            dataField: "responsible_id",
            lookup: {
                dataSource: additionalResources.fuelResponsibles,
                valueExpr: "id",
                displayExpr: "user_full_name"
            },
            cellTemplate(container, options) {
                if (options.row.data.fuel_tank_flow_type_id === additionalResources.fuelFlowTypes.find(el => el.slug === 'adjustment').id) {
                    $('<div>').text(additionalResources.users.find(el=>el.id===options.row.data.author_id).user_full_name).appendTo(container)
                } else {
                    $('<div>').text(additionalResources.users.find(el=>el.id===options.row.data.responsible_id).user_full_name).appendTo(container)
                }
            },
            width: '150px',
            tableName: "fuel_tank_flows",
        },
        {
            caption: "Объем (л)",
            dataField: "volume",
            editorType: 'dxNumberBox',
            editorOptions: {
                min: 0.001
            },
            dataType: "number",
            alignment: 'right',
            cellTemplate(container, options) {

                let displayValue = ''
                let cssTextColor = ''

                if (options.row.data.fuel_tank_flow_type_id === additionalResources.fuelFlowTypes.find(el => el.slug === 'outcome').id) {
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
            },
            width: '100px',
        },

        {
            type: "buttons",
            buttons: [
                {
                    icon: 'fas fa-list-alt dx-link-icon',

                    visible(e) {
                        const dateDiff = getDatesDaysDiff(e.row.data.created_at, Date())
                        if (dateDiff >= 1) {
                            return true
                        }

                        if (Boolean("{{App::environment('local')}}")) {
                            return false;
                        }

                        if (!Boolean(+e.row.data.author_id === +authUserId)) {
                            return true;
                        }

                        return false
                    },

                    onClick(e) {
                        editingRowId = e.row.data.id;

                        let choosedItem = getChoosedItem(e.row.data.id)
                        let fuelFlowType = additionalResources.fuelFlowTypes.find(el => el.id === choosedItem.fuel_tank_flow_type_id).slug

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
                    },
                },
                // 'edit',
                {
                    name: 'delete',
                    visible(e) {
                        const dateDiff = getDatesDaysDiff(e.row.data.created_at, Date())
                        if (dateDiff >= 1) {
                            return false
                        }
                        if (Boolean("{{App::environment('local')}}")) {
                            return true;
                        }
                        if (!Boolean(+e.row.data.author_id === +authUserId)) {
                            return false;
                        }
                        return true;
                    }
                }

            ],

            headerCellTemplate: (container, options) => {

                $('<div>')
                    .appendTo(container)
                    .dxDropDownButton({
                        text: 'Создать',
                        dataSource: getAddFlowButtonDatasource(),
                        valueExpr: 'id',
                        dropDownOptions: {
                            width: 200
                        },
                        itemTemplate(item) {
                            let iconTemplate;
                            switch(item.name) {
                                case 'Приход':
                                    iconTemplate = 'fa fa-arrow-up text-color-green mr5'
                                    break
                                case 'Расход':
                                    iconTemplate = 'fa fa-arrow-down text-color-red mr5'
                                    break
                                case 'Корректировка':
                                    iconTemplate = 'fas fa-exchange-alt text-color-blue mr5'
                                    break
                                default:
                                    iconTemplate = ''
                            }

                            return `<span class="${iconTemplate}"></span> ` + item.name
                        },

                        visible: userPermissions.create_fuel_tank_flows_for_reportable_tanks || userPermissions.create_fuel_tank_flows_for_any_tank || userPermissions.adjust_fuel_tank_remains,

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
