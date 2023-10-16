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
        // projectObjectDocumentInfoByID.items().splice(0, 1)
    }

    function addLightGallery(id) {
        lightGallery(document.getElementById(id), {
            plugins: [lgZoom, lgThumbnail, lgRotate],
            licenseKey: "0000-0000-000-0000",
            speed: 500,
        });
    }

    function createDynamycLightGalleryData(e) {

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
                openDynamycLightGallery(galleryElemsWrapper, lightGalleryElemsArr, clickedElemIndex)
        }

    }

    function openDynamycLightGallery(rootElem, elemsArr, elemIndex) {
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
                        success: function(data, textStatus, jqXHR) {
                            document.querySelector('.dx-icon-close')?.click()
                            dataSourceList.reload();
                            DevExpress.ui.notify("Данные успешно удалены", "success", 1000)
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
                        success: function(data, textStatus, jqXHR) {
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

    function downloadXls() {
        delete filterOptions.skip;
        delete filterOptions.take;

        $('#filterOptions').val(JSON.stringify(filterOptions))
        $('#projectObjectsFilter').val(JSON.stringify(customFilter['projectObjectsFilter']))
        $('#projectResponsiblesFilter').val(JSON.stringify(customFilter['projectResponsiblesFilter']))
        dowloadXls.submit()
    }

    function deleteAttachment(e) {
        let elemParent = e.element.closest('.fileOnServerDivWrapper');
        let fileId = e.element.closest('.fileOnServerDivWrapper')[0]?.getAttribute("id")?.split('-')[1];
                
        customConfirmDialog("Вы уверены, что хотите удалить файл?")
            .show().then((dialogResult) => {
                if(dialogResult)
                deleteFile();
            })

        function deleteFile() {
            deletedAttachments.push(fileId)
            elemParent.remove()
        }
        
    }



    function showArchive() {
        console.log('showArchive()');
    }

    // *** НАЧАЛО *** Подготовка loadOptions в связи с отказом от lookup на верхнем уровне

    function getFormatedLodOptions(loadOptions) {
        loadOptions.filter = updateLoadOptionsKeys(loadOptions.filter)
        return loadOptions
    }

    function updateLoadOptionsKeys(loadOption) {
        if (!loadOption)
            return

        if (typeof loadOption[0] != 'object')
            return getUpdatedLoadOption(loadOption)
        else
            return getUpdatedLoadOptionsArr(loadOption)
    }

    function getUpdatedLoadOption(loadOption) {
        let formatedLoadOption = []
        loadOption.forEach(elem => {
            if (typeof elem == 'number')
                formatedLoadOption.push(elem)

            if (typeof elem == 'string')
                formatedLoadOption.push(getFormatedElem(elem))
        })

        return formatedLoadOption
    }

    function getUpdatedLoadOptionsArr(loadOption) {
        let loadOptionsArr = []
        loadOption.forEach(elem => {
            if (typeof elem == 'object')
                loadOptionsArr.push(getUpdatedLoadOption(elem))
            else
                loadOptionsArr.push(elem)
        })

        return loadOptionsArr;
    }

    function getFormatedElem(elem) {
        formatedElem = elem
        formatedElem = formatedElem.replace('status.name', 'document_status_id')
        formatedElem = formatedElem.replace('type.name', 'document_type_id')
        return formatedElem
    }

    // *** КОНЕЦ *** Подготовка loadOptions в связи с отказом от lookup на верхнем уровне

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
        // let response = await fetch("{{route('projectObjectDocument.downloadAttachments')}}", {
        let response = await fetch("{{route('fileEntry.downloadAttachments')}}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json;charset=utf-8',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            body: JSON.stringify(fliesIds)
        });

        let result = await response.json();
        console.log('result', result);
        let a = document.createElement("a");
        a.href = result.zipFileLink
        a.download = 'documentFilesArchive';
        console.log('a', a);
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
                    tagBox.option(`value`).forEach(function(value) {
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
        return currentStatus?.style === '#1f931f'
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
            documentStatusesByTypeStoreDataSource.filter(['style', '<>', '#1f931f']);
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
        document.querySelector('#newAddedComments').innerHTML = ''
        document.querySelector('#newAddedCommentsInfoTab').innerHTML = ''
        handleCommentsDataArr(newCommentsArr, $('#newAddedComments'))
        handleCommentsDataArr(newCommentsArr, $('#newAddedCommentsInfoTab'))
        textBoxInstance?.option('value', '')
    }

    function touchFakeSubmitDatafield() {
        let coreDataGridInstance = getCoreDataGridInstance();
        coreDataGridInstance.cellValue(coreDataGridInstance.getRowIndexByKey(editingRowId), "fake_submit_dataField", ' ')
    }

</script>