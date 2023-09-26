<script>
    $(() => {
        const popupForm = $('#popupFormMobile').dxPopup({
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
                if(e.component.option('newDocumentMode')){
                    $('#menuButtons').remove()
                }

                if ($('#menuButtons').length) {
                    $('#menuButtons').dxButtonGroup('instance').option('selectedItemKeys', ['Инфо'])
                    renderInfoTemplate()
                }
                setDocumentStatusesByTypeStoreDataSourceFilter()
            },

            onHiding() {
                $('.dx-toolbar-center .dx-item-content').html('')
                $('#popupContainer').html('')
                resetVars()
                resetStores()
                resetStatusOptionsVars()
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
                        popupForm.hide()
                    }

                },
                {
                    location: "after",
                    widget: 'dxButton',
                    validationGroup: "documentValidationGroup",
                    options: {
                        template: '<div style="color:#4993df">Сохранить</div>',
                        stylingMode: 'text',
                        elementAttr: {
                            'id': 'popupSaveButton'
                        }
                    },
                    onClick(e) {
                        if ($('#popupFormMobile').dxPopup('instance').option('newDocumentMode')) {
                            if (DevExpress.validationEngine.validateGroup("documentValidationGroup").isValid) {
                                submitMobileDocumentForm()
                            }
                        } else {
                            submitMobileDocumentForm()
                        }


                    }

                },
                {
                    toolbar: 'bottom',
                    location: "before",
                    widget: 'dxButtonGroup',
                    options: {
                        height: '60px', 
                        elementAttr: { class: 'mt-18px', id: 'menuButtons'},                    
                        items: [{
                                            text: 'Инфо',
                                            template: '<div style="display: flex; align-items:center; font-size: 18px"><div class="fa fa-info-circle info-circle-icon-color" style="padding-top: 1px; color: #725fdb; "></div><div style="margin-left:6px">Инфо</div></div>',
                                            elementAttr: {
                                                width: '33.5vw',
                                                // height: '60px',
                                                id: 'menuInfoButton'
                                            }
                                        },
                                        {
                                            text: 'История',
                                            template: '<div style="display: flex; align-items:center; font-size: 18px"><span class="fa fa-comment comment-icon-color" style="padding-top: 1px; color: #1b91d7;"></span><span style="margin-left:6px">История</span></div>',
                                            elementAttr: {
                                                width: '33.5vw',
                                                // height: '60px'
                                            }
                                        },
                                        {
                                            text: 'Файлы',
                                            template: '<div style="display: flex; align-items:center; font-size: 18px"><span class="fa fa-file" style="padding-top: 1px;"></span><span style="margin-left:6px">Файлы</span></div>',
                                            elementAttr: {
                                                width: '33.5vw',
                                                // height: '60px'
                                            }
                                        },
                                    ],
                                    stylingMode: 'outlined',
                                    keyExpr: 'text',
                                    onItemClick(e) {
                                        if (e.itemData.text === 'Инфо')
                                            renderInfoTemplate()
                                        if (e.itemData.text === 'История')
                                            renderHistoryTemplate()
                                        if (e.itemData.text === 'Файлы')
                                            renderFilesTemplate()
                                    },
                    }
                },
            ]
        }).dxPopup('instance');


        $('#documentsListMobile').dxList({
            dataSource: dataSourceListMobile,
            searchEnabled: true,
            onGroupRendered(e) {
                e.component.collapseGroup(e.groupIndex)
            },
            searchExpr: ['document_name', 'project_object_short_name'],
            onItemClick(e) {
                setPopupItemVariablesMobile(e.itemData)
                projectObjectDocumentInfoByID.load()

                const popup = $('#popupFormMobile').dxPopup('instance')
                popup.option('contentTemplate', popupContentTemplate)
                popup.option('newDocumentMode', false)
                popup.show()
            },
            // height: '90%',
            grouped: true,
            collapsibleGroups: true,
            groupTemplate(data) {
                return $(`<div>Объект: ${data.key}</div>`);
            },
            itemTemplate(data) {
                const listElement = $('<div>').addClass('documentsListElemWrapper')
                listElement.append('<div>').text(data.document_name)

                const statusInfoWrapper = $('<div>').css({
                        'display': 'flex',
                        'alignItems': 'center'
                    })
                    .appendTo(listElement)

                const statusMarker = $('<div>')
                    .css({
                        'background': data.status.project_object_documents_status_type.style,
                        'width': '10px',
                        'height': '10px',
                        'borderRadius': '50%',
                        'marginRight': '5px',
                    })
                    .appendTo(statusInfoWrapper)

                const statusName = $('<div>')
                    .css({
                        // 'color': data.status.project_object_documents_status_type.style
                    })
                    .text(data.status.name)
                    .appendTo(statusInfoWrapper)

                return listElement;
            },

        });
    })

    $('#newDocumentButtonMobile')
        .dxButton({
            text: "Добавить",
            icon: "fas fa-plus",
            elementAttr: {
                width: '50%',
            },
            onClick: (e) => {
                const popup = $('#popupFormMobile').dxPopup('instance')
                popup.option('contentTemplate', popupNewDocumentFormContentTemplate)
                popup.option('newDocumentMode', true)
                popup.show()

                // fileUploader
                const filesContainer = $('#popupNewDocumentFilesContainer')
                filesContainer.html('')
                const containerScrollableWrapper = $('<div id="containerScrollableWrapper">').appendTo(filesContainer)
                renderFilesUploader(containerScrollableWrapper)
            }
        })

    $('#responsiblesFilterMobile').dxTagBox({
        dataSource: responsibles_all,
        valueExpr: 'id',
        displayExpr: 'user_full_name',
        maxDisplayedTags: 1,
        searchEnabled: true,
        showSelectionControls: true,
        wrapItemText: true,
        showDropDownButton: true,
        onInitialized(e) {
            // getPermissions().then((permissions) => {
            //     if (permissions.project_object_documents_default_filtering_by_responsible_user) {
            //         e.component.option('value', [+"{{Auth::user()->id}}"])
            //     }
            // })
        },
        onSelectionChanged(e) {
            oldFilterVal = customFilter['projectResponsiblesFilter']
            customFilter['projectResponsiblesFilter'] = [];
            for (let i = 0; i < this._selectedItems.length; i++) {
                customFilter['projectResponsiblesFilter'].push(this._selectedItems[i].id)
            }

            dataSourceListMobile.reload();
            // window.history.pushState("", "", window.location.origin + window.location.pathname + "?projectResponsiblesFilter=" + JSON.stringify(customFilter['projectResponsiblesFilter']));
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