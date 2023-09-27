<script>

// Общие
    function getUrlWithId(url, id) {
        return url.replace("/setId", "/" + id)
    }

    function getGridHeight() {
        let content = document.getElementsByClassName('content')[0]
        return 0.82 * content.clientHeight;
    }

    function resetVars() {
        editingRowId = 0;
    }

    function resetStores() {
        objectInfoByID.store().clearRawDataCache()
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
        let response = await fetch("{{route('objects::getPermissions')}}");
        permissions = await response.json();
        return await permissions;
    }
    getPermissions();


// Конец Общие

    function setLoadedObjectInfo() {
        objectInfoByID.reload().done((data)=>{
            objectInfo = objectInfoByID.store().__rawData;
            setResponsiblesObjectInfo(objectInfo.allAvailableResponsibles, objectInfo.objectResponsibles);
            // setContractorsObjectInfo(objectInfo.contractors);
        })
    }

    function setResponsiblesObjectInfo(allAvailableResponsibles, objectResponsibles) {


        $('#responsiblesPTOfield')
            .dxTagBox('instance')
            .option({
                dataSource: allAvailableResponsibles.pto,
                value: objectResponsibles.pto,
                disabled: permissions.can_assign_responsible_pto_user
            })

        $('#responsiblesManagersfield')
            .dxTagBox('instance')
            .option({
                dataSource: allAvailableResponsibles.managers,
                value: objectResponsibles.managers,
                disabled: permissions.can_assign_responsible_projectManager_user
            })

        $('#responsiblesForemenfield')
            .dxTagBox('instance')
            .option({
                dataSource: allAvailableResponsibles.foremen,
                value: objectResponsibles.foremen,
                disabled: permissions.can_assign_responsible_foreman_user
            })
    }

    function setContractorsObjectInfo(contractors) {
        const objectContractorsWrapper = $('#objectContractorsWrapper');

        if(contractors.length>3)
        objectContractorsWrapper.css({columnCount: 2})

        if(contractors.length)
            objectContractorsWrapper.html('');
        else
            objectContractorsWrapper.html('<span class="popup-field-nodata">Нет данных</span>');
        let i=0;
        contractors.forEach(el=>{
            if(i>0) {
                objectContractorsWrapper.append(',<br>')
            }
            objectContractorsWrapper.append(el.short_name)
            i++;
        })
    }

    function resetChoosedBitrixProject() {
        const choosedDataGridRowIndex = $('#dataGridContainer').dxDataGrid('instance').option('focusedRowIndex')
        operationsWithFormDataAfterFormRepaint(choosedDataGridRowIndex, '')
    }

    function handleChoosingBitrixProject() {

        saveResponsiblesEditorsValues()

        const choosedBitrixProjectId = $('#bitrixProjectsDataGrid').dxDataGrid('instance').option('focusedRowKey')
        const choosedDataGridRowIndex = $('#dataGridContainer').dxDataGrid('instance').option('focusedRowIndex')

        if(choosedBitrixProjectId) {
            const choosedBitrixProject = bitrixProjectsArray.find(el=>el.ID === choosedBitrixProjectId)
            const currentObjectShortName = $('#dataGridContainer').dxDataGrid('instance').cellValue(choosedDataGridRowIndex, 'short_name')

            // if(choosedBitrixProject.NAME === currentObjectShortName){
            //     $('#bitrixProjectsPopup').dxPopup('instance').hide()
            //     return
            // }

            if(choosedDataGridRowIndex >= 0){
                if(!currentObjectShortName) {
                    $('#dataGridContainer').dxDataGrid('instance').cellValue(choosedDataGridRowIndex, 'short_name', choosedBitrixProject.NAME);
                    operationsWithFormDataAfterFormRepaint(choosedDataGridRowIndex, choosedBitrixProjectId)

                } else {
                    customConfirmDialog(`Изменить сокращенное наименование на ${choosedBitrixProject.NAME}?`)
                        .show().then((dialogResult) => {
                            if (dialogResult) {
                                $('#dataGridContainer').dxDataGrid('instance').cellValue(choosedDataGridRowIndex, 'short_name', choosedBitrixProject.NAME);
                            }

                            operationsWithFormDataAfterFormRepaint(choosedDataGridRowIndex, choosedBitrixProjectId)

                            // $('#dataGridContainer').dxDataGrid('instance').cellValue(choosedDataGridRowIndex, 'bitrixId', choosedBitrixProjectId)
                            // $('#bitrix-project-name').dxTextBox({value: bitrixProjectFormElement})

                            // const objectInfo = objectInfoByID.store().__rawData;
                            // setResponsiblesObjectInfo(objectInfo.allAvailableResponsibles, objectInfo.objectResponsibles)
                            // setContractorsObjectInfo(objectInfo.contractors);
                        })
                }


            } else {
                $('#bitrixIdFormField').dxSelectBox('instance').option('value', choosedBitrixProjectId)
                $('#objectShotrNameFormField').dxTextBox('instance').option('value', choosedBitrixProject.NAME)
                $('#bitrix-project-name').dxTextBox({value: getBitrixProjectFormDisplayValue(choosedBitrixProjectId)})
            }
        }

        $('#bitrixProjectsPopup').dxPopup('instance').hide()
    }

    function operationsWithFormDataAfterFormRepaint(choosedDataGridRowIndex, choosedBitrixProjectId) {

        const bitrixProjectFormElement = getBitrixProjectFormDisplayValue(choosedBitrixProjectId)
        const objectInfo = objectInfoByID.store().__rawData;

        $('#dataGridContainer').dxDataGrid('instance').cellValue(choosedDataGridRowIndex, 'bitrixId', choosedBitrixProjectId)
        $('#bitrix-project-name').dxTextBox({value: bitrixProjectFormElement})
        setResponsiblesObjectInfo(objectInfo.allAvailableResponsibles, objectInfo.objectResponsibles)
        // setContractorsObjectInfo(objectInfo.contractors);
    }

    function getBitrixProjectFormDisplayValue(bitrixId) {
        if(!bitrixId)
        return 'Выбрать...';

        const bitrixProject = bitrixProjectsArray.find(el=>+el.ID === +bitrixId)
        return '[ID' + bitrixId + ']' + ' - ' + bitrixProject.NAME
    }

    function saveResponsiblesEditorsValues()  {
        if(!$('#dataGridContainer').dxDataGrid('instance').option("focusedRowKey"))
            return;

        objectInfoByID.store().__rawData.objectResponsibles.foremen = $('#responsiblesForemenfield').dxTagBox('instance').option('value')
        objectInfoByID.store().__rawData.objectResponsibles.managers = $('#responsiblesManagersfield').dxTagBox('instance').option('value')
        objectInfoByID.store().__rawData.objectResponsibles.pto = $('#responsiblesPTOfield').dxTagBox('instance').option('value')
    }

</script>
