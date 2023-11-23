<script>
    function getGridHeight() {
        let content = document.getElementsByClassName('content')[0]
        return 0.82 * content.clientHeight;
    }

    function getUrlWithId(url, id) {
        return url.replace("/setId", "/" + id)
    }

    function resetVars() {
        editingRowId = 0;
        editingRowNewStatusId = 0;
        newAttachments = [];
        deletedAttachments = [];
        editingRowChanges = [];
        newCommentsArr = [];
    }

    function resetStores() {
        responsibles_pto.clearRawDataCache()
        responsibles_foreman.clearRawDataCache()
        responsibles_manager.clearRawDataCache()
        projectObjectDocumentInfoByID.store().clearRawDataCache()
    }

    function addLightGallery(id) {
        lightGallery(document.getElementById(id), {
            plugins: [lgZoom, lgThumbnail, lgRotate],
            licenseKey: "0000-0000-000-0000",
            speed: 500,
        });
    }

    function createDynamicLightGalleryData(e) {

        let clickedElemSrc = e.target.attributes.src.value
        let clickedElemIndex = 0
        let lightGalleryElemsArr = []

        let galleryElemsWrapper = e.target.closest('div.filesGroupWrapperClass')
        let galleryElems = galleryElemsWrapper.querySelectorAll('img')

        for (let index = 0; index < galleryElems.length; index++) {
            const element = galleryElems[index];

            if (element.src.includes(e.target.attributes.src.value))
                clickedElemIndex = index

            lightGalleryElemsArr.push({
                src: element.src,
                thumb: element.src,
            })

            if (index === galleryElems.length - 1)
                openDynamicLightGallery(galleryElemsWrapper, lightGalleryElemsArr, clickedElemIndex)
        }
    }

    function openDynamicLightGallery(rootElem, elemsArr, elemIndex) {
        const dynamicGallery = window.lightGallery(rootElem, {
            dynamic: true,
            dynamicEl: elemsArr,
            plugins: [lgZoom, lgThumbnail, lgRotate],
            licenseKey: "0000-0000-000-0000",
            speed: 500,
        });
        dynamicGallery.openGallery(elemIndex)
    }

    function createCustomToolbarItems() {
        let closeBtn = document.querySelector('.dx-icon-close').closest('.dx-toolbar-item')
        let toolbarRightTop = closeBtn.closest('.dx-toolbar-after')

        let deleteButton = document.createElement('div');
        deleteButton.classList.add('dx-item', 'dx-toolbar-item', 'dx-toolbar-button')

        let copyButton = document.createElement('div');
        copyButton.classList.add('dx-item', 'dx-toolbar-item', 'dx-toolbar-button')

        deleteButton.innerHTML =
            '<div class="dx-item-content dx-toolbar-item-content">' +
            '<div id="deleteDocumentButton" hidden></div>' +
            '</div>'

        copyButton.innerHTML =
            '<div class="dx-item-content dx-toolbar-item-content leftBorderCloseButton">' +
            '<div id="copyDocumentButton" hidden></div>' +
            '</div>'

        toolbarRightTop.insertBefore(deleteButton, closeBtn)
        toolbarRightTop.insertBefore(copyButton, closeBtn)
    }

    function customConfirmDialog(message) {
        return DevExpress.ui.dialog.custom({
            showTitle: false,
            messageHtml: message,
            buttons: [{
                text: "Да",
                onClick: () => true
            }, {
                text: "Нет",
                onClick: () => false
            }]
        })
    }

    function deleteDocument(deletingRowId) {
        customConfirmDialog("Вы уверены, что хотите удалить документ?")
            .show().then((dialogResult) => {
            if (dialogResult) {
                return $.ajax({
                    url: getUrlWithId("{{route('project-object-document.destroy', ['id'=>'setId'])}}", deletingRowId),
                    method: "DELETE",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (data, textStatus, jqXHR) {
                        document.querySelector('.dx-icon-close')?.click()
                        dataSourceList.reload();
                        DevExpress.ui.notify("Данные успешно удалены", "success", 1000)
                    },
                })
            }
        })
    }

    function restoreDocument(undeletingRowId) {
        customConfirmDialog("Вы уверены, что хотите восстановить документ?")
            .show().then((dialogResult) => {
            if (dialogResult) {
                return $.ajax({
                    url: getUrlWithId("{{route('project-object-document.restoreDocument', ['id'=>'setId'])}}", undeletingRowId),
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (data, textStatus, jqXHR) {
                        dataSourceList.reload();
                        DevExpress.ui.notify("Документ успешно восстановлен", "success", 1000)
                    },
                })
            }
        })
    }

    function copyDocument(copyRowId) {
        customConfirmDialog("Создать копию документа?")
            .show().then((dialogResult) => {
            if (dialogResult) {
                return $.ajax({
                    url: getUrlWithId("{{route('projectObjectDocument.clone', ['id'=>'setId'])}}", copyRowId),
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (data, textStatus, jqXHR) {
                        document.querySelector('.dx-icon-close')?.click()
                        dataSourceList.reload().done((res) => {
                            let coreDataGridInstance = getCoreDataGridInstance();
                            let newRowIndex = coreDataGridInstance.getRowIndexByKey((data.newDocument.id))
                            coreDataGridInstance.editRow(newRowIndex)
                        });
                        DevExpress.ui.notify("Создана копия документа", "success", 1000)
                    },
                })
            }
        })
    }

    function downloadXls(reportType) {
        delete filterOptions.skip;
        delete filterOptions.take;

        $('#filterOptions').val(JSON.stringify(filterOptions))
        $('#projectObjectsFilter').val(JSON.stringify(customFilter['projectObjectsFilter']))
        $('#projectResponsiblesFilter').val(JSON.stringify(customFilter['projectResponsiblesFilter']))

        const downloadXlsForm = document.getElementById('downloadXlsForm')
        const connector = downloadXlsForm.action.includes('?') ? '&' : '?';
        downloadXlsForm.action = downloadXlsForm.action + connector + 'reportType=' + reportType

        downloadXlsForm.submit()
    }

    function deleteAttachment(e) {
        let elemParent = e.element.closest('.fileOnServerDivWrapper');
        let fileId = e.element.closest('.fileOnServerDivWrapper')[0]?.getAttribute("id")?.split('-')[1];

        customConfirmDialog("Вы уверены, что хотите удалить файл?")
            .show().then((dialogResult) => {
            if (dialogResult)
                deleteFile();
        })

        function deleteFile() {
            deletedAttachments.push(fileId)
            elemParent.remove()
        }

    }

    function getCoreDataGridInstance() {
        return $('#dataGridContainer').dxDataGrid("instance");
    }

    function getFormInstance() {
        return $('#documentEditingForm').dxForm("instance");
    }

    async function downloadAttachments(filesIdsToDownload) {
        let fliesIds = {
            fliesIds: filesIdsToDownload
        }
        let response = await fetch("{{route('projectObjectDocument.downloadAttachments')}}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json;charset=utf-8',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            body: JSON.stringify(fliesIds)
        });

        let result = await response.json();
        let a = document.createElement("a");
        a.href = result.zipFileLink
        a.download = 'documentFilesArchive';
        a.click();
    }

    // TODO: Вынести функцию в общий модуль
    function createFilterRowTagBoxFilterControlForLookupColumns(e) {
        e.editorName = `dxTagBox`;
        e.editorOptions.showSelectionControls = true;
        e.editorOptions.dataSource = e.lookup.dataSource;
        e.editorOptions.displayExpr = e.lookup.displayExpr;
        e.editorOptions.valueExpr = e.lookup.valueExpr;
        e.editorOptions.applyValueMode = `useButtons`;
        e.editorOptions.value = e.value || [];
        e.editorOptions.dataFieldName = e.dataField;
        e.editorOptions.onValueChanged = () => {
            function calculateFilterExpression() {
                let filterExpression = [];
                e.element.find(`.dx-datagrid-filter-row`).find(`.dx-tagbox`).each((index, item) => {
                    let tagBoxFilterExpression = [];
                    let tagBox = $(item).dxTagBox(`instance`);
                    tagBox.option(`value`).forEach(function (value) {
                        tagBoxFilterExpression.push([tagBox.option().dataFieldName, `=`, Number(value)]);
                        tagBoxFilterExpression.push(`or`);
                    });
                    tagBoxFilterExpression.pop();
                    if (tagBoxFilterExpression.length) {
                        filterExpression.push(tagBoxFilterExpression);
                        filterExpression.push(`and`);
                    }
                })
                filterExpression.pop();
                return filterExpression;
            }

            let calculatedFilterExpression = calculateFilterExpression();


            if (calculatedFilterExpression.length) {
                if (calculatedFilterExpression.length === 1)
                    e.component.filter(calculatedFilterExpression[0]);

                if (calculatedFilterExpression.length > 1)
                    e.component.filter(calculatedFilterExpression);
            } else {
                e.component.clearFilter(`dataSource`)
            }
        }
    }

    async function getPermissions() {
        let response = await fetch("{{route('projectObjectDocument.getPermissions')}}");
        permissions = await response.json();
        return await permissions;
    }

    getPermissions();

    function checkDocumentStatusIsGreen() {
        let coreDataGridInstance = getCoreDataGridInstance();
        let statusId = coreDataGridInstance.cellValue(coreDataGridInstance.getRowIndexByKey(editingRowId), "document_status_id")
        let allStatuses = documentStatusesStore.__rawData;
        let currentStatus = allStatuses.filter(el => el.id === statusId)[0];
        return currentStatus?.project_object_documents_status_type.slug === 'work_with_document_is_finished'
    }

    function checkDocumentStatusButtonIsDisabled() {
        if (!editingRowId)
            return true;

        if (checkDocumentStatusIsGreen() && !permissions.can_setup_final_project_object_document_status)
            return true;

        return false;
    }

    function setDocumentStatusesByTypeStoreDataSourceFilter() {
        if (!permissions.can_setup_final_project_object_document_status)
            documentStatusesByTypeStoreDataSource.filter(['project_object_documents_status_type.slug', '<>', 'work_with_document_is_finished']);
    }

    function handleNewCommentAdded(comment, textBoxInstance = null) {
        if (!comment)
            return;

        const newCommentObj = {
            author: {
                full_name: '{{Auth::user()->user_full_name}}',
                image: '{{Auth::user()->image}}'
            },
            created_at: new Date(),
            comment: comment
        }

        newCommentsArr.unshift(newCommentObj)

        if (document.querySelector('#newAddedComments'))
            document.querySelector('#newAddedComments').innerHTML = ''
        if (document.querySelector('#newAddedCommentsInfoTab'))
            document.querySelector('#newAddedCommentsInfoTab').innerHTML = ''
        if ($('#newAddedComments').length)
            handleCommentsDataArr(newCommentsArr, $('#newAddedComments'))
        if ($('#newAddedCommentsInfoTab').length)
            handleCommentsDataArr(newCommentsArr, $('#newAddedCommentsInfoTab'))
        textBoxInstance?.option('value', '')

    }

    async function submitMobileDocumentForm() {
        const formDataObj = getMobileFormData();
        var body = new FormData();
        body.set('data', JSON.stringify(formDataObj));

        if (editingRowId) {
            var url = getUrlWithId("{{route('project-object-document.update', ['id'=>'setId'])}}", editingRowId);
            body.append('_method', 'put');
        } else {
            var url = "{{route('project-object-document.store')}}";
        }

        let response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            body: body
        });

        let result = await response.json()
            .then(() => {
                hideMobilePopupAndReloadDocsList()
            })
    }

    function getMobileFormData() {
        formData = {}

        if ($('#documentMobileObjectId').dxSelectBox('instance')?.option('value'))
            formData.project_object_id = $('#documentMobileObjectId').dxSelectBox('instance').option('value');

        if ($('#documentMobileName').dxTextBox('instance')?.option('value'))
            formData.document_name = $('#documentMobileName').dxTextBox('instance').option('value');

        if ($('#documentMobileDate').dxDateBox('instance')?.option('value'))
            formData.document_date = $('#documentMobileDate').dxDateBox('instance').option('value');

        if ($('#documentMobileTypeId').dxSelectBox('instance')?.option('value'))
            formData.document_type_id = $('#documentMobileTypeId').dxSelectBox('instance').option('value');

        if ($('#documentMobileStatusId').dxSelectBox('instance')?.option('value'))
            formData.document_status_id = $('#documentMobileStatusId').dxSelectBox('instance').option('value');

        formData.newAttachments = newAttachments;
        formData.deletedAttachments = deletedAttachments;
        formData.typeStatusOptions = editingRowTypeStatusOptions;
        formData.newCommentsArr = newCommentsArr;

        return formData;
    }

    function hideMobilePopupAndReloadDocsList() {
        $('#popupFormMobile').dxPopup('instance').hide()
        $('#documentsListMobile').dxList('instance').reload()
    }

    function setPopupItemVariablesMobile(itemData) {
        editingRowId = itemData.id
        editingRowTypeId = itemData.document_type_id
        editingRowStatusId = itemData.document_status_id
        choosedDocumentItemData = itemData
        editingRowStartOptions = JSON.parse(itemData.options)
    }

    function isArchivedOrDeletedDocuments() {
        return window.location.search.includes('showArchive=1');
    }

    function addToolbarDropDownButton(isDownloadXlsDisabled = true) {
        $('#toolbarDropDownButton').dxDropDownButton({
            icon: 'overflow',
            dropDownOptions: {
                width: 200
            },
            displayExpr: 'text',

            items: [
                {
                    icon: 'fa fa-file-excel-o',
                    text: 'Скачать отчет для РП',
                    disabled: isDownloadXlsDisabled
                },
                {
                    icon: 'fa fa-file-excel-o',
                    text: 'Скачать отчет для ПТО',
                    disabled: isDownloadXlsDisabled
                },
                {
                    icon: 'fa fa-file-excel-o',
                    text: 'Скачать',
                    disabled: isDownloadXlsDisabled
                },
                {
                    icon: 'fas fa-archive',
                    text: 'Открыть архив',
                    visible: !isArchivedOrDeletedDocuments(),
                },
                {
                    icon: 'fas fa-long-arrow-alt-left',
                    text: 'Документы в работе',
                    visible: isArchivedOrDeletedDocuments(),
                },

            ],
            onItemClick(e) {
                if (e.itemData.text === 'Скачать отчет для РП')
                    downloadXls('groupedByPM');

                if (e.itemData.text === 'Скачать отчет для ПТО')
                    downloadXls('groupedByPTO');

                if (e.itemData.text === 'Скачать')
                    downloadXls('ungrouped');

                if (e.itemData.text === 'Открыть архив')
                    window.location.href = "{{route('project-object-documents', ['showArchive'=>'1'])}}"

                if (e.itemData.text === 'Документы в работе')
                    window.location.href = "{{route('project-object-documents')}}"
            }
        })
    }

    function getCheckedCheckboxesFilesToDownload() {
        const attachmentsWrapper = document.getElementById('filesOnServerListWrapper')
        const checkboxes = attachmentsWrapper.querySelectorAll('input');

        if (!checkboxes.length)
            return [];

        const checkedCheckboxes = [];
        checkboxes.forEach(el => {
            if (el.value) {
                checkedCheckboxes.push(el)
            }
        })

        return checkedCheckboxes;
    }

    const setNewCommentElementMobile = (container) => {
        const newCommentArea = $('<div>')
            .css({
                marginBottom: '10px',
            })
            .appendTo(container);

        const newCommentTextArea = $('<div id="newCommentTextArea">').appendTo(newCommentArea)
        const newCommentButton = $('<div id="newCommentButton" style="margin-top:10px">').appendTo(newCommentArea)
        const newCommentsWrapper = $('<div id="newCommentsWrapper">').appendTo(newCommentArea)

        $('#newCommentButton').dxButton({
            text: "Добавить комментарий",
            // icon: 'upload',
            elementAttr: {
                width: '100%',
            },
            onClick() {
                const textAreaInstance = $('#newCommentTextArea').dxTextArea('instance')
                renderNewCommentMobile(textAreaInstance.option('value'), newCommentsWrapper)
                textAreaInstance.option('value', '')
            }
        })

        $('#newCommentTextArea').dxTextArea({
            placeholder: 'Новый комментарий',
            height: '10vh',
            onEnterKey: function (e) {
                renderNewCommentMobile(e.component.option('text'), newCommentsWrapper)
                e.component.reset()
            }
        })
    }

    const renderNewCommentMobile = (value, container) => {
        if (!value)
            return;

        const newCommentObj = {
            author: {
                full_name: '{{Auth::user()->user_full_name}}',
                image: '{{Auth::user()->image}}'
            },
            created_at: new Date(),
            comment: value
        }

        newCommentsArr.unshift(newCommentObj)

        const newCommentWrapper = $('<div>')
            .addClass('documentElemMobile')
            .css({color: '#829be3'})
            .appendTo(container)

        $('<div>').text(value).appendTo(newCommentWrapper)

        container.css({
            marginTop: '10px',
            borderTop: '1px solid #e1e1e1'
        })
    }
</script>
