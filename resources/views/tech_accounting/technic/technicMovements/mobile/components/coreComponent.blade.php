<script>
    $(() => {
        $('#entitiesListMobile').dxList({
            dataSource: entitiesDataSource,
            searchEnabled: true,
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
                    .html(`<b>Дата: </b>${new Date(data.order_start_date).toLocaleDateString()}`)
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
                editingRowId = e.itemData.id;
                choosedItemData = e.itemData;
                popupMobile.show()
            },

        });

    })
    
</script>
