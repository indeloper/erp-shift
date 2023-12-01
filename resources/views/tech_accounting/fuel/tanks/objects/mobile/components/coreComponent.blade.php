<script>
    $(() => {
        $('#entitiesListMobile').dxList({
            dataSource: entitiesDataSource,
            searchEnabled: true,

            itemTemplate(data) {

                const listElement = $('<div>')

                const listElementRowWrapper =
                    $('<div>').addClass('list-element-wrapper').appendTo(listElement);

                    $('<div>')
                        .addClass('list-element-item-tank-number')
                        .text(` ${data.tank_number}`)
                        .appendTo(listElementRowWrapper);

                const textInfoWrapper = $('<div>')
                        .addClass('list-element-item-text-info-wrapper')
                        .appendTo(listElementRowWrapper)

                let colorClass = ''
                if(data.fuel_level > 0)
                    colorClass = 'text-color-green'
                if(data.fuel_level < 0)
                    colorClass = 'text-color-red'

                if(data.awaiting_confirmation) {
                    $('<p>')
                        .addClass('list-element-item-info-text text-color-red')
                        .css('font-weight', 'bold')
                        .text('Требуется подтверждение')
                        .appendTo(textInfoWrapper)

                    $('<p>')
                        .addClass('list-element-item-info-text text-color-red')
                        .css('margin-bottom', '10px')
                        .text(`Ответственный: ${data.responsible.full_name}`)
                        .appendTo(textInfoWrapper)
                }

                $('<p>')
                    .addClass('list-element-item-info-text ')
                    .html(`<b>Остаток топлива: </b><span class="${colorClass}">${new Intl.NumberFormat('ru-RU').format(data.fuel_level)} л</span>`)
                    .appendTo(textInfoWrapper)

                $('<p>')
                    .addClass('list-element-item-info-text ')
                    .html(`<b>Объект: </b>${data.object?.short_name}`)
                    .appendTo(textInfoWrapper)

                const listElementRowWrapper2 =
                    $('<div>').addClass('list-element-wrapper').appendTo(listElement);

                const buttonsWrapper = $('<div>').addClass('buttons-wrapper').appendTo(listElementRowWrapper2)

                $('<div>').dxButton({
                    template(data, container) {
                        container.append('<div class="tab-template-header-wrapper"><div class="fa fa-arrow-up tab-template-header-icon-elem text-color-green"></div><div>Приход</div></div>')
                    },
                    visible: userPermissions.create_fuel_tank_flows_for_reportable_tanks || userPermissions.create_fuel_tank_flows_for_any_tank,
                    onClick() {
                        editingRowId = data.id;
                        shownMobileFormType = 'increaseFuelForm';
                        showIncreaseFuelPopup();
                    }
                }).appendTo(buttonsWrapper)

                $('<div>').dxButton({
                    template(data, container) {
                        container.append('<div class="tab-template-header-wrapper"><div class="fas fa-exchange-alt tab-template-header-icon-elem text-color-blue"></div><div>Перемещение</div></div>')
                    },
                    visible: Boolean(Boolean("{{App::environment('local')}}") ? true : +data.responsible_id === +authUserId),
                    onClick() {
                        editingRowId = data.id;

                        if (data.awaiting_confirmation) {
                            shownMobileFormType = 'movingConfirmationTankForm';
                            showMovingConfirmationTankPopup(data);
                        } else {
                            shownMobileFormType = 'movingTankForm';
                            showMovingTankPopup();
                        }
                    }
                }).appendTo(buttonsWrapper)

                $('<div>').dxButton({
                    template(data, container) {
                        container.append('<div class="tab-template-header-wrapper"><div class="fa fa-arrow-down tab-template-header-icon-elem text-color-red"></div><div>Расход</div></div>')
                    },
                    visible: userPermissions.create_fuel_tank_flows_for_reportable_tanks || userPermissions.create_fuel_tank_flows_for_any_tank,
                    onClick() {
                        editingRowId = data.id;
                        shownMobileFormType = 'decreaseFuelForm';
                        showDecreaseFuelPopup();
                    }
                }).appendTo(buttonsWrapper)


                return listElement;
            },

        });

    })
</script>
