<script>
    const infoTabbedGroup =
        {
            tabTemplate(data, index, element) {
                return '<div style="display: flex; align-items:center"><div class="fa fa-info-circle info-circle-icon-color" style="padding-top: 1px;"></div><div style="margin-left:6px">Инфо</div></div>'
            },

            items: [
                {
                    itemType: 'group',
                    colCount: 6,
                    items: [
                        {
                            dataField: "project_object_id",
                            editorType: "dxSelectBox",
                            editorOptions: {
                                onSelectionChanged(e) {
                                    let responsiblesPtoEngineerDiv = document.getElementById('responsiblesPtoEngineerDiv')
                                    let responsiblesProjectManagerEngineerDiv = document.getElementById('responsiblesProjectManagerEngineerDiv')
                                    let responsiblesForemanDiv = document.getElementById('responsiblesForemanDiv')

                                    if (responsiblesPtoEngineerDiv) {
                                        if (e.selectedItem.tongue_pto_engineer_full_names)
                                            responsiblesPtoEngineerDiv.innerHTML = e.selectedItem.tongue_pto_engineer_full_names
                                        else
                                            responsiblesPtoEngineerDiv.innerHTML = '<span class="popup-field-nodata">Нет данных</span>'
                                        if (e.selectedItem.tongue_project_manager_full_names)
                                            responsiblesProjectManagerEngineerDiv.innerHTML = e.selectedItem.tongue_project_manager_full_names
                                        else
                                            responsiblesProjectManagerEngineerDiv.innerHTML = '<span class="popup-field-nodata">Нет данных</span>'
                                        if (e.selectedItem.tongue_foreman_full_names)
                                            responsiblesForemanDiv.innerHTML = e.selectedItem.tongue_foreman_full_names
                                        else
                                            responsiblesForemanDiv.innerHTML = '<span class="popup-field-nodata">Нет данных</span>'
                                    }
                                }
                            },
                            validationRules: [{
                                type: 'required',
                                message: 'Укажите значение',
                            }],
                            colSpan: 6
                        },
                        {
                            dataField: "document_name",
                            editorType: "dxTextBox",
                            validationRules: [{
                                type: 'required',
                                message: 'Укажите значение',
                            }],
                            colSpan: 6
                        },
                        {
                            dataField: "document_date",
                            editorType: "dxDateBox",
                            colSpan: 2
                        },
                        {
                            dataField: "document_type_id",
                            editorType: "dxSelectBox",
                            editorOptions: {
                                onSelectionChanged(e) {
                                    editingRowTypeId = e.selectedItem.id
                                    documentStatusesByTypeStoreDataSource.reload()

                                    let documentStatusMainFormSelector = document.getElementById('documentStatusMainFormSelector')
                                    if (documentStatusMainFormSelector) {
                                        let defaultStatusId = e.selectedItem.project_object_document_status_type_relations.filter(el => el.default_selection === 1)[0]?.document_status_id
                                        let statusObj = documentStatusesStore?.__rawData?.filter(el => el.id === defaultStatusId)[0]

                                        mainFormStatusMarker.style.backgroundColor = statusObj.project_object_documents_status_type.style
                                        documentStatusMainFormSelector.value = statusObj.name

                                        coreDataGridInstance = getCoreDataGridInstance()
                                        coreDataGridInstance.cellValue(coreDataGridInstance.getRowIndexByKey(editingRowId), "document_status_id", statusObj.id)

                                        editingRowStatusId = statusObj.id
                                        editingRowNewStatusId = editingRowStatusId

                                        $('#documentStatusButton').dxButton("instance").option('disabled', false)
                                    }
                                }
                            },
                            validationRules: [{
                                type: 'required',
                                message: 'Укажите значение',
                            }],
                            colSpan: 2,
                        },
                        {
                            dataField: "document_status_id",
                            editorType: "dxSelectBox",
                            editorOptions: {
                                readOnly: true,
                                buttons: [{
                                    name: 'status-options-icon',
                                    location: 'after',
                                    options: {
                                        elementAttr: {id: "documentStatusButton"},
                                        icon: 'more',
                                        type: 'default',
                                        disabled: false,
                                        onClick(e) {
                                            resetStatusOptionsVars()
                                            showOptionsPopup()
                                        },
                                    },
                                }],
                                fieldTemplate(data, container) {
                                    const result = $(`
                                    <div style="display:flex; align-items:center">
                                        <div id="mainFormStatusMarker" class="round-color-marker" style="background-color: ${data?.project_object_documents_status_type?.style}; margin-right:0; margin-left:10px" />
                                        <div class='status-name'></div>
                                    </div>
                                `);
                                    result
                                        .find('.status-name')
                                        .dxTextBox({
                                            value: data?.name,
                                            readOnly: true,
                                            inputAttr: {id: "documentStatusMainFormSelector"},
                                        });

                                    container.append(result);
                                },
                            },
                            colSpan: 2
                        },
                    ]
                },
                {
                    itemType: 'group',
                    colCount: 3,
                    items: [
                        {
                            itemType: 'simple',
                            template: (data, itemElement) => {
                                let itemElementContent = '<b>Ответственные ПТО:</b><div id="responsiblesPtoEngineerDiv"></div>';
                                itemElement.append(itemElementContent)
                                let coreDataGridInstance = getCoreDataGridInstance();
                                let currentObjectId = coreDataGridInstance.cellValue(coreDataGridInstance.getRowIndexByKey(editingRowId), "project_object_id");
                                let responsibles = projectObjectsStore?.__rawData?.filter(el => el.id === currentObjectId)[0]?.tongue_pto_engineer_full_names
                                if (responsibles)
                                    responsiblesPtoEngineerDiv.innerHTML = responsibles
                                else
                                    responsiblesPtoEngineerDiv.innerHTML = '<span class="popup-field-nodata">Нет данных</span>'
                            }
                        },
                        {
                            itemType: 'simple',
                            template: (data, itemElement) => {
                                let itemElementContent = '<b>Ответственные прорабы:</b><div id="responsiblesForemanDiv"></div>';
                                itemElement.append(itemElementContent)
                                let coreDataGridInstance = getCoreDataGridInstance();
                                let currentObjectId = coreDataGridInstance.cellValue(coreDataGridInstance.getRowIndexByKey(editingRowId), "project_object_id");
                                let responsibles = projectObjectsStore?.__rawData?.filter(el => el.id === currentObjectId)[0]?.tongue_foreman_full_names
                                if (responsibles)
                                    responsiblesForemanDiv.innerHTML = responsibles
                                else
                                    responsiblesForemanDiv.innerHTML = '<span class="popup-field-nodata">Нет данных</span>'
                            }
                        },
                        {
                            itemType: 'simple',
                            template: (data, itemElement) => {
                                let itemElementContent = '<b>Ответственные РП:</b><div id="responsiblesProjectManagerEngineerDiv">tongue_project_manager_full_names</div>';
                                itemElement.append(itemElementContent)
                                let coreDataGridInstance = getCoreDataGridInstance();
                                let currentObjectId = coreDataGridInstance.cellValue(coreDataGridInstance.getRowIndexByKey(editingRowId), "project_object_id");
                                let responsibles = projectObjectsStore?.__rawData?.filter(el => el.id === currentObjectId)[0]?.tongue_project_manager_full_names
                                if (responsibles)
                                    responsiblesProjectManagerEngineerDiv.innerHTML = responsibles
                                else
                                    responsiblesProjectManagerEngineerDiv.innerHTML = '<span class="popup-field-nodata">Нет данных</span>'
                            }
                        },
                    ],
                },
                {
                    itemType: 'group',
                    colCount: 2,
                    items: [
                        {
                            itemType: 'group',
                            caption: 'Последние комментарии',
                            items: [
                                {
                                    itemType: 'simple',
                                    template: (data, itemElement) => {
                                        itemElement.attr('id', 'commentsWrapperInfoTab').css('height', '20vh')
                                        itemElement.append('<div id="newAddedCommentsInfoTab" style="color:#829be3;  background: #fbfbfb"></div>')
                                        const commentsInfoTab = $('<div id="commentsInfoTab">').appendTo(itemElement)
                                        if (projectObjectDocumentInfoByID.items()[0])
                                            handleCommentsDataArr(projectObjectDocumentInfoByID.items()[0]?.comments.original.slice(0, 3), commentsInfoTab)
                                        else
                                            $(itemElement).append('<span class="popup-field-nodata">Нет данных</span>')
                                    }
                                }
                            ]
                        },
                        {
                            itemType: 'group',
                            caption: 'Последние файлы',
                            items: [
                                {
                                    itemType: 'simple',
                                    template: (data, itemElement) => {
                                        const filesOnServerListWrapper =
                                            $('<div>')
                                                .attr('id', 'filesOnServerListWrapperInfoTab')
                                                .css({
                                                    'width': '100%',
                                                    'height': '20vh',
                                                    'position': 'relative'
                                                });

                                        if (projectObjectDocumentInfoByID.items()[0]) {
                                            const filesDataArr = projectObjectDocumentInfoByID.items()[0]?.attachments.original

                                            if (filesDataArr.length === 0) {
                                                $(itemElement).append('<span class="popup-field-nodata">Нет данных</span>')
                                                return
                                            }

                                            itemElement.append(filesOnServerListWrapper);

                                            const group = Object.keys(filesDataArr)[0];
                                            $(filesOnServerListWrapper).append(`<div class="files-group-header">${group}</div>`)
                                            let filesGroupWrapper = $('<div>').attr('id', 'filesGroupWrapper');
                                            let filesNotImgGroupWrapper = $('<div>').attr('id', 'filesNotImgGroupWrapper');
                                            const filesArr = filesDataArr[group];

                                            handleFilesDataArr(filesArr, filesGroupWrapper, filesNotImgGroupWrapper)

                                            $(filesOnServerListWrapper).append(filesGroupWrapper)
                                            $(filesOnServerListWrapper).append(filesNotImgGroupWrapper)
                                            addLightGallery('filesGroupWrapper')
                                        } else {
                                            $(itemElement).append('<span class="popup-field-nodata">Нет данных</span>')
                                        }
                                    }
                                }
                            ]
                        }
                    ]
                },
            ],
        }
</script>
