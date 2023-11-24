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
        let response = await fetch("{{route('objects::getPermissions')}}");
        permissions = await response.json();
        return await permissions;
    }

    getPermissions();

    function checkSaveButtonAvailable() {
        if ((!permissions.objects_create && !editingRowId) || (!permissions.objects_edit && editingRowId)) {
            const saveButton = $('[aria-label="Сохранить"]')
            saveButton.remove()
        }
    }

    function setReadonlyFormElemsProperties(isReadonly) {
        $('#clearBitrixProjectsOpenPopupButton').dxButton('instance').option('disabled', isReadonly)
        $('#bitrixProjectsOpenPopupButton').dxButton('instance').option('disabled', isReadonly)

        let dataGrid = $("#dataGridContainer").dxDataGrid("instance")
        dataGrid.option("columns").forEach((columnItem) => {
            if (![
                'responsibles_pto',
                'responsibles_managers',
                'responsibles_foremen'
            ]
                .includes(columnItem.dataField)
            ) {
                dataGrid.columnOption(columnItem.dataField, "allowEditing", !isReadonly)
            }
        });
    }

    // Конец Общие

    function setLoadedObjectInfo() {
        objectInfoByID.reload().done((data) => {
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
                disabled: !permissions.can_assign_responsible_pto_user
            })

        $('#responsiblesManagersfield')
            .dxTagBox('instance')
            .option({
                dataSource: allAvailableResponsibles.managers,
                value: objectResponsibles.managers,
                disabled: !permissions.can_assign_responsible_projectManager_user
            })

        $('#responsiblesForemenfield')
            .dxTagBox('instance')
            .option({
                dataSource: allAvailableResponsibles.foremen,
                value: objectResponsibles.foremen,
                disabled: !permissions.can_assign_responsible_foreman_user
            })
    }

    function setContractorsObjectInfo(contractors) {
        const objectContractorsWrapper = $('#objectContractorsWrapper');

        if (contractors.length > 3)
            objectContractorsWrapper.css({columnCount: 2})

        if (contractors.length)
            objectContractorsWrapper.html('');
        else
            objectContractorsWrapper.html('<span class="popup-field-nodata">Нет данных</span>');
        let i = 0;
        contractors.forEach(el => {
            if (i > 0) {
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

        const objectDataGridInstance = $('#dataGridContainer').dxDataGrid('instance');
        const bitrixProjectId = $('#bitrixProjectsDataGrid').dxDataGrid('instance').option('focusedRowKey');
        const isNewRow = objectDataGridInstance.option('focusedRowIndex') === undefined;
        const objectDataGridRowIndex = isNewRow ? 0 : objectDataGridInstance.option('focusedRowIndex');
        const currentObjectShortName = objectDataGridInstance.cellValue(objectDataGridRowIndex, 'short_name')

        if (bitrixProjectId) {
            const bitrixProjectName = getBitrixProjectFormDisplayValue(bitrixProjectId)
            const showShortNameChangingConfirmationDialog = currentObjectShortName && currentObjectShortName !== bitrixProjectName;

            objectDataGridInstance.cellValue(objectDataGridRowIndex, 'bitrix_id', bitrixProjectId);
            if (showShortNameChangingConfirmationDialog) {
                const confirmDialogText = `Изменить сокращенное наименование на ${bitrixProjectName}?`
                customConfirmDialog(confirmDialogText).show().then((dialogResult) => {
                    if (dialogResult) {
                        objectDataGridInstance.cellValue(objectDataGridRowIndex, 'short_name', bitrixProjectName);
                    } else {
                        let shortName = objectDataGridInstance.cellValue(objectDataGridRowIndex, 'short_name');
                        shortName = shortName.replace(/\[ID(\d+)]\s*-\s*/, `[ID${bitrixProjectId}] - `);
                        objectDataGridInstance.cellValue(objectDataGridRowIndex, 'short_name', shortName);
                    }

                    operationsWithFormDataAfterFormRepaint(objectDataGridRowIndex, bitrixProjectId)
                })
            } else {
                objectDataGridInstance.cellValue(objectDataGridRowIndex, 'short_name', bitrixProjectName);
            }
        }

        $('#bitrixProjectsPopup').dxPopup('instance').hide()
    }

    function operationsWithFormDataAfterFormRepaint(choosedDataGridRowIndex, choosedBitrixProjectId) {

        const bitrixProjectFormElement = getBitrixProjectFormDisplayValue(choosedBitrixProjectId)
        const objectInfo = objectInfoByID.store().__rawData;

        $('#dataGridContainer').dxDataGrid('instance').cellValue(choosedDataGridRowIndex, 'bitrix_id', choosedBitrixProjectId)
        $('#bitrix-project-name').dxTextBox({value: bitrixProjectFormElement})
        setResponsiblesObjectInfo(objectInfo.allAvailableResponsibles, objectInfo.objectResponsibles)
        // setContractorsObjectInfo(objectInfo.contractors);
    }

    function getBitrixProjectFormDisplayValue(bitrix_id) {
        if (!bitrix_id)
            return 'Выбрать...';

        const bitrixProject = bitrixProjectsArray.find(el => +el.ID === +bitrix_id)
        return `[ID${bitrix_id}] - ${bitrixProject.NAME}`
    }

    function saveResponsiblesEditorsValues() {
        if (!$('#dataGridContainer').dxDataGrid('instance').option("focusedRowKey"))
            return;

        objectInfoByID.store().__rawData.objectResponsibles.foremen = $('#responsiblesForemenfield').dxTagBox('instance').option('value')
        objectInfoByID.store().__rawData.objectResponsibles.managers = $('#responsiblesManagersfield').dxTagBox('instance').option('value')
        objectInfoByID.store().__rawData.objectResponsibles.pto = $('#responsiblesPTOfield').dxTagBox('instance').option('value')
    }

</script>
