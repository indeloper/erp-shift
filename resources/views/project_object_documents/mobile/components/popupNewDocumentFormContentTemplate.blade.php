<script>
    const popupNewDocumentFormContentTemplate = () => {
        const popupContentWrapper = $('<div>')
            .css({
                'width': '100%',
                'height': '100%',
                'positon': 'relative'
            })

        $('<div id="documentMobileObjectId" class="mobile-new-document-form-element">').dxSelectBox({
            label: 'Объект',
            labelMode: 'floating',
            dataSource: projectObjectsStore,
            valueExpr: 'id',
            displayExpr: 'short_name',
        }).dxValidator({
            validationGroup: "documentValidationGroup",
            validationRules: [{
                type: "required",
                message: 'Укажите значение',
            }]
        }).appendTo(popupContentWrapper)

        $('<div id="documentMobileName" class="mobile-new-document-form-element">').dxTextBox({
            label: 'Документ',
            labelMode: 'floating',
        }).dxValidator({
            validationGroup: "documentValidationGroup",
            validationRules: [{
                type: "required",
                message: 'Укажите значение',
            }]
        }).appendTo(popupContentWrapper)

        $('<div id="documentMobileDate" class="mobile-new-document-form-element">').dxDateBox({
            label: 'Дата',
            labelMode: 'floating',
        }).appendTo(popupContentWrapper)


        $('<div id="documentMobileTypeId" class="mobile-new-document-form-element">').dxSelectBox({
            label: 'Тип',
            labelMode: 'floating',
            dataSource: documentTypesStore,
            valueExpr: 'id',
            displayExpr: 'name',

            onSelectionChanged(e) {
                editingRowTypeId = e.selectedItem.id
                let defaultStatusId = e.selectedItem.project_object_document_status_type_relations.filter(el => el.default_selection === 1)[0]?.document_status_id
                let statusObj = documentStatusesStore?.__rawData?.filter(el => el.id === defaultStatusId)[0]

                editingRowStatusId = statusObj.id
                editingRowNewStatusId = editingRowStatusId

                $('#documentMobileStatusId').dxSelectBox('instance').option('disabled', true)
                $('#popupSaveButton').dxButton('instance').option('visible', false)

                documentStatusesByTypeStoreDataSource.reload().done(() => {
                    $('#documentMobileStatusId').dxSelectBox('instance').option('disabled', false)
                    $('#popupSaveButton').dxButton('instance').option('visible', true)
                    $('#documentMobileStatusId').dxSelectBox('instance').option('value', statusObj.id)
                })
            }
        }).dxValidator({
            validationGroup: "documentValidationGroup",
            validationRules: [{
                type: "required",
                message: 'Укажите значение',
            }]
        }).appendTo(popupContentWrapper)

        $('<div id="documentMobileStatusId" class="mobile-new-document-form-element">').dxSelectBox({
            label: 'Статус',
            labelMode: 'floating',
            dataSource: documentStatusesByTypeStoreDataSource,
            valueExpr: 'id',
            displayExpr: 'name',
            disabled: true,
            itemTemplate(data) {
                return $(`
                                <div style="display:flex; align-items:center">
                                    <div class="round-color-marker" style="background-color: ${data?.project_object_documents_status_type?.style}; margin-right:15px; margin-left:5px" />
                                    <div class="status-name">${data?.name}</div>
                                </div>
                            `);
            },

            fieldTemplate(data, container) {
                const result = $(`
                                <div style="display:flex; align-items:center">
                                    <div id="mainFormStatusMarker" class="round-color-marker" style="background-color: ${data?.project_object_documents_status_type?.style}; margin-right:0; margin-left:10px" />
                                    <div class="status-name"></div>
                                </div>
                            `);
                result
                    .find('.status-name')
                    .dxTextBox({
                        value: data?.name,
                        readOnly: true,
                    });

                container.append(result);
            },
            onValueChanged: function (e) {
                resetStatusOptionsVars()
                optionsByTypeAndStatusStore.clearRawDataCache();
                editingRowNewStatusId = e.value;

                renderStatusOptions();

                let allStatuses = documentStatusesStore.__rawData;
                let currentStatus = allStatuses.filter(el => el.id === editingRowNewStatusId)[0];
                handleNewCommentAdded('Новый статус: ' + currentStatus.name.toLowerCase())
            }
        }).dxValidator({
            validationGroup: "documentValidationGroup",
            validationRules: [{
                type: "required",
                message: 'Укажите значение',
            }]
        }).appendTo(popupContentWrapper)

        const statusAndOptionsWrapper = $('<div>')
            .attr('id', 'statusAndOptionsWrapper')
            .appendTo(popupContentWrapper)

        $('<div id="addNewCommentsNewDocumentWrapper">').appendTo(popupContentWrapper)

        $('<div id="popupNewDocumentFilesContainer" style="margin-top:30px">').appendTo(popupContentWrapper)

        return popupContentWrapper;
    }
</script>
