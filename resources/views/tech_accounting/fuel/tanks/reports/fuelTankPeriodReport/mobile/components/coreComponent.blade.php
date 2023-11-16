<script>
    $(() => {
        const popupMobile = $('#popupMobile').dxPopup({
            fullScreen: true,
            visible: false,
            dragEnabled: false,
            hideOnOutsideClick: false,
            showCloseButton: false,
            dragAndResizeArea: false,
            dragEnabled: false,
            dragOutsideBoundary: false,
            enableBodyScroll: false,

            onShowing(e) {
                if (e.component.option('newEntityMode')) {
                    $('#menuButtons').remove()
                }
            },

            onHiding() {
                $('.dx-toolbar-center .dx-item-content').html('')
                $('#popupContainer').html('')
                resetVars()
                resetStores()
            },

            toolbarItems: [{
                location: "before",
                widget: 'dxButton',
                options: {
                    icon: 'back',
                    stylingMode: 'text',
                    elementAttr: {
                        style: 'padding-top:4px'
                    }
                },
                onClick(e) {
                    popupMobile.hide()
                }

            },
                {
                    location: "after",
                    widget: 'dxButton',
                    validationGroup: "entityValidationGroup",
                    options: {
                        template: '<div class="text-color-blue">Сохранить</div>',
                        stylingMode: 'text',
                        elementAttr: {
                            'id': 'popupSaveButton'
                        }
                    },
                    onClick(e) {
                        if ($('#popupMobile').dxPopup('instance').option('newEntityMode')) {
                            if (DevExpress.validationEngine.validateGroup("entityValidationGroup").isValid) {
                                submitMobileDocumentForm()
                            }
                        } else {
                            submitMobileDocumentForm()
                        }


                    }

                },
            ]
        }).dxPopup('instance');


        $('#entitiesListMobile').dxList({
            dataSource: entitiesDataSourceListMobile,
            searchEnabled: true,
            // searchExpr: ['name', ],
            // grouped: true,
            // collapsibleGroups: true,
            // groupTemplate(data) {
            //     return $(`<div>Объект: ${data.key}</div>`);
            // },

            onGroupRendered(e) {
                e.component.collapseGroup(e.groupIndex)
            },

            onItemClick(e) {
                setPopupItemVariablesMobile(e.itemData)

                entityInfoByID.load()

                const popup = $('#popupMobile').dxPopup('instance')
                popup.option('contentTemplate', popupEditingModeContentTemplate)
                popup.option('newEntityMode', false)
                popup.show()
            },

            itemTemplate(data) {
                const listElement = $('<div>').addClass('listElementsElementWrapper')
                listElement.append('<div>').text(data.name)

                return listElement;
            },

        });
    })

    $('#newEntityButtonMobile')
        .dxButton({
            text: "Добавить",
            icon: "fas fa-plus",
            elementAttr: {
                width: '50%',
            },
            onClick: (e) => {
                const popup = $('#popupMobile').dxPopup('instance')
                popup.option('contentTemplate', popupNewEntityContentTemplate)
                popup.option('newEntityMode', true)
                popup.show()

            }
        })

    $('#filterTagBox').dxTagBox({
        dataSource: [],
        valueExpr: 'id',
        displayExpr: 'name',
        maxDisplayedTags: 1,
        searchEnabled: true,
        showSelectionControls: true,
        wrapItemText: true,
        showDropDownButton: true,

        onSelectionChanged(e) {
            for (let i = 0; i < this._selectedItems.length; i++) {
                // someArr.push(this._selectedItems[i].id)
            }
            entitiesDataSourceListMobile.reload();
        },
        placeholder: 'Выберите ответственного...',
    })

    const popupLoadPanel = $('#popupLoadPanel').dxLoadPanel({
        shadingColor: 'rgba(0,0,0,0.4)',
        position: {
            of: '#popupContainer'
        },
        visible: false,
        showIndicator: true,
        showPane: true,
        shading: true,
        hideOnOutsideClick: false,
        wrapperAttr: {},
    }).dxLoadPanel('instance')
</script>
