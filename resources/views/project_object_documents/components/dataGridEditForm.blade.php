@include('project_object_documents.components.editFormGroups.infoTabbedGroup')
@include('project_object_documents.components.editFormGroups.historyCommentsTabbedGroup')
@include('project_object_documents.components.editFormGroups.filesTabbedGroup')

<script>
    const dataGridEditForm = {
        colCount: 6,
        elementAttr: {
            id: "documentEditingForm"
        },
        onContentReady() {
            
            $('#filesOnServerListWrapper').dxScrollView({
                scrollByContent: true,
                scrollByThumb: true,
                showScrollbar: 'onHover',
            })

            $('#filesOnServerListWrapperInfoTab').dxScrollView({
                scrollByContent: true,
                scrollByThumb: true,
                showScrollbar: 'onHover',
            })

            $('#commentsWrapperInfoTab').dxScrollView({
                scrollByContent: true,
                scrollByThumb: true,
                showScrollbar: 'onHover',
            })

            $('#commentsWrapperHistoryTab').dxScrollView({
                scrollByContent: true,
                scrollByThumb: true,
                showScrollbar: 'onHover',
            })

            let documentStatusButton = $('#documentStatusButton').dxButton("instance")
            if (documentStatusButton)
                documentStatusButton.option('disabled', checkDocumentStatusButtonIsDisabled())

            setDocumentStatusesByTypeStoreDataSourceFilter()

            // не работает во внешнем файле

            let images = document.querySelectorAll('.fileImg')
            if (images)
                for (let index = 0; index < images.length; index++) {
                    const element = images[index];
                    element.addEventListener('click', (e) => {
                        createDynamycLightGalleryData(e)
                    })
                }

            // let fakeCoverPdfElems = document.getElementById('filesNotImgGroupWrapper')?.querySelectorAll('.fakeCoverPDF');
            let fakeCoverPdfElems = document.querySelectorAll('.fakeCoverPDF');
            if (fakeCoverPdfElems) {
                for (let index = 0; index <= fakeCoverPdfElems.length; index++) {
                    const element = fakeCoverPdfElems[index];
                    lightGallery(element, {
                        selector: 'this',
                    });
                }
            }
            // Всплывающие элементы над файлами - чекбокс и кнопка удаления

            $('.fileOnServerDivWrapper').hover(
                function() {
                    if (!$(this).find('.dx-checkbox').length) {
                        let checkBox = $('<div>').dxCheckBox({
                            hint: "Скачать",
                            elementAttr: {
                                class: 'attacmentHoverCkeckbox'
                            }
                        })
                        $(this).append($(checkBox));
                    }

                    if (permissions.can_delete_project_object_document_files) {
                        let deleteButton = $('<div />').dxButton({
                            icon: "fas fa-trash",
                            hint: "Удалить",
                            elementAttr: {
                                class: 'attacmentHoverDeleteButton'
                            },
                            onClick(e) {
                                deleteAttachment(e);
                            },
                        })
                        $(this).append($(deleteButton));
                    }
                },
                function() {
                    let checkBox = $(this).find('.dx-checkbox').dxCheckBox('instance')
                    if (!checkBox.option('value'))
                        $(this).find('.attacmentHoverCkeckbox').last().remove();
                    $(this).find('.attacmentHoverDeleteButton').last().remove();
                }
            )
        },
        items: [{
                name: "dataGridEditFormMainGroup",
                visible: false,
                itemType: 'tabbed',
                colSpan: 6,
                tabPanelOptions: {
                    deferRendering: false,
                    height: "60vh"
                },
                tabs: [
                    infoTabbedGroup,
                    historyCommentsTabbedGroup,
                    filesTabbedGroup,
                ],
            },

            {
                colSpan: 6,
                name: "dataGridEditFormLoadPanel",
                itemType: 'simpleItem',
                template: (data, itemElement) => {
                    let dxPopupContentElems = document.querySelectorAll('.dx-popup-content')
                    itemElement.append('<div id="formLoadPanel" >')
                    itemElement.append(
                        $('#formLoadPanel').dxLoadPanel({
                            shadingColor: 'rgba(0,0,0,0.4)',
                            position: {
                                of: dxPopupContentElems[dxPopupContentElems.length - 1]
                            },
                            visible: !projectObjectDocumentInfoByID.isLoaded(),
                            showIndicator: true,
                            showPane: true,
                            shading: true,
                            hideOnOutsideClick: false,
                            wrapperAttr: {},
                        })
                    )
                },
                visible: false,

            }
        ],
    }
</script>