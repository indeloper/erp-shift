<script>
    $(() => {
        // const popupMobile = $('#popupMobile').dxPopup({
        //     fullScreen: true,
        //     visible: false,
        //     dragEnabled: false,
        //     hideOnOutsideClick: false,
        //     showCloseButton: false,
        //     dragAndResizeArea: false,
        //     dragEnabled: false,
        //     dragOutsideBoundary: false,
        //     enableBodyScroll: false,

        //     onHiding() {
        //         $('.dx-toolbar-center .dx-item-content').html('')
        //         $('#popupContainer').html('')
        //         resetVars()
        //         resetStores()
        //     },

        //     toolbarItems: [{
        //         location: "before",
        //         widget: 'dxButton',
        //         options: {
        //             icon: 'back',
        //             stylingMode: 'text',
        //             elementAttr: {
        //                 style: 'padding-top:4px'
        //             }
        //         },
        //         onClick(e) {
        //             popupMobile.hide()
        //         }

        //     },
        //         {
        //             location: "after",
        //             widget: 'dxButton',
        //             validationGroup: "entityValidationGroup",
        //             options: {
        //                 template: '<div class="text-color-blue">Сохранить</div>',
        //                 stylingMode: 'text',
        //                 elementAttr: {
        //                     'id': 'popupSaveButton'
        //                 }
        //             },
        //             onClick(e) {
        //                 if ($('#popupMobile').dxPopup('instance').option('newEntityMode')) {
        //                     if (DevExpress.validationEngine.validateGroup("entityValidationGroup").isValid) {
        //                         submitMobileDocumentForm()
        //                     }
        //                 } else {
        //                     submitMobileDocumentForm()
        //                 }


        //             }

        //         },
        //     ]
        // }).dxPopup('instance');

        $('#entitiesListMobile').dxList({
            dataSource: fuelTanksStore,
            searchEnabled: true,
            
            itemTemplate(data) {
                console.log(data);
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
                    .html(`<b>Объект: </b>${data.object.short_name}`)
                    .appendTo(textInfoWrapper)

                const listElementRowWrapper2 = 
                    $('<div>').addClass('list-element-wrapper').appendTo(listElement);

                const buttonsWrapper = $('<div>').addClass('buttons-wrapper').appendTo(listElementRowWrapper2)

                $('<div>').dxButton({
                        text: 'Приход',
                        onClick(e) {
                            editingRowId = data.id;
                            shownMobileFormType = 'increaseFuelForm';
                            showIncreaseFuelPopup();
                        }
                }).appendTo(buttonsWrapper)

                $('<div>').dxButton({
                        text: 'Перемещение'
                }).appendTo(buttonsWrapper)

                $('<div>').dxButton({
                        text: 'Расход',
                        onClick(e) {
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
