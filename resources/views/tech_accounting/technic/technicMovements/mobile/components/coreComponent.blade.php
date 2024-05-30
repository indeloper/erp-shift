<script>
    $(() => {
        $('#entitiesListMobile').dxList({
            dataSource: entitiesDataSource,
            // searchEnabled: true,
            // activeStateEnabled: false,
            
            itemTemplate(data) {
                
                const listElement = $('<div>')

                const listElementRowWrapper =
                    $('<div>').addClass('list-element-wrapper').appendTo(listElement);

                const textInfoWrapper = $('<div>')
                        .addClass('list-element-item-text-info-wrapper')
                        .appendTo(listElementRowWrapper)
                        
                $('<p>')
                    .addClass('list-element-item-info-text ')
                    .html(`<b>Заявка #</b>${data.id}`)
                    .appendTo(textInfoWrapper)
                
                if(data.responsible_id) {
                    const responsible = additionalResources.technicResponsiblesAllTypes.find(el=>el.id===data.responsible_id)?.user_full_name
                    $('<p>')
                    .addClass('list-element-item-info-text ')
                    .html(`<b>Ответственный: </b>${responsible}`)
                    .appendTo(textInfoWrapper)
                }
                
                if(data.movement_start_datetime) {
                    $('<p>')
                    .addClass('list-element-item-info-text ')
                    .html(`<b>Транспортировка: </b>${new Date(data.movement_start_datetime).toLocaleString([], {year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit'})}`)
                    .appendTo(textInfoWrapper)
                }

                const period = data.order_end_date ? 
                    new Date(data.order_start_date).toLocaleDateString() + ' - ' + new Date(data.order_end_date).toLocaleDateString()
                    : 'c ' + new Date(data.order_start_date).toLocaleDateString()
                $('<p>')
                    .addClass('list-element-item-info-text ')
                    .html(`<b>Период: </b>${period}`)
                    .appendTo(textInfoWrapper)

                $('<p>')
                    .addClass('list-element-item-info-text ')
                    .html(`<b>Объект: </b>${data.object?.short_name}`)
                    .appendTo(textInfoWrapper)
                
                const technicCategoryName = getTechnicCategoryName(data.technic_category_id)
                $('<p>')
                    .addClass('list-element-item-info-text ')
                    .html(`<b>Техника: </b>${technicCategoryName}`)
                    .appendTo(textInfoWrapper)
                
                const status = additionalResources.technicMovementStatuses.find(el=>el.id===data.technic_movement_status_id).name
                $('<p>')
                    .addClass('list-element-item-info-text ')
                    .html(`<b>Статус: </b>${status.toLowerCase()}`)
                    .appendTo(textInfoWrapper)

                return listElement;
            },

            onItemClick(e) {
                if(!Boolean(
                        userPermissions.technics_movement_create_update 
                        || userPermissions.technics_processing_movement_standart_sized_equipment 
                        || userPermissions.technics_processing_movement_oversized_equipment
                    )) {
                    return
                }
                editingRowId = e.itemData.id;
                choosedItemData = e.itemData;
                popupMobile.show()
            },
        });

        $('#newEntityButtonMobile')
            .dxButton({
                visible: Boolean(userPermissions.technics_movement_create_update),
                text: "Добавить",
                icon: "fas fa-plus",
                elementAttr: {
                    width: '50%',
                },
                onClick: (e) => {
                    popupMobile.show()
                }
            })
    })
    
</script>
