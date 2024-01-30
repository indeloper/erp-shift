<script>
    let externalEditingRowId = 0;
    let externalOperations = [];
    let externalDeletedOperations = [];
    externalPermissions = {can_delete_project_object_document_files: true};

    function resetExternalVars() {
        externalUploadingFiles = [];
        externalNewAttachments = [];
        externalEditingRowId = 0;
        externalDeletedAttachments = [];
    }

    function resetExternalStores() {
        externalEntityInfoByID.store().clearRawDataCache()
        externalEntityInfoByID._isLoaded = false
    }

    function updateFuelFlowDataGrid(formData) {
        let dataGridInstance
        if(formData.fuel_tank_flow_type_id === 1) {
            dataGridInstance = $('#mainDataGrid_fuel_flow_incomes').dxDataGrid('instance')
        }
        if(formData.fuel_tank_flow_type_id === 2) {
            dataGridInstance = $('#mainDataGrid_fuel_flow_outcomes').dxDataGrid('instance')
        }
        if(formData.fuel_tank_flow_type_id === 3) {
            dataGridInstance = $('#mainDataGrid_fuel_flow_adjusments').dxDataGrid('instance')
        }

        let currentDatasource
        if(Array.isArray(dataGridInstance.option('dataSource')))
        {
            currentDatasource = dataGridInstance.option('dataSource')
        } else {
            currentDatasource = dataGridInstance.option('dataSource').items()
        }

        if(formData.id) {
            currentDatasource.filter(el => el.id != formData.id)
            dataGridInstance.option('dataSource', currentDatasource)
            dataGridInstance.option('focusedRowKey', formData.id)
        } else {
            formData.responsible_id = $('#fuel_tank_responsible_id').dxSelectBox('instance').option('value')
            // formData.responsible_id = $('#mainDataGrid').dxDataGrid('instance').option('dataSource').items().find(el=>el.id===editingRowId).responsible_id
            formData.id = new DevExpress.data.Guid();
            formData.guid = true
            formData.author_id = authUserId
            currentDatasource.unshift(formData)
            dataGridInstance.option('dataSource', currentDatasource)
            dataGridInstance.option('focusedRowKey', formData.id)
        }
    }

    const isAddFlowButtonVisible = () => {
        if(!editingRowId && choosedFormTab != 'fuelAdjustments') {
            return false
        }

        if(userPermissions.adjust_fuel_tank_remains && choosedFormTab === 'fuelAdjustments') {
            return true
        }
        if(!additionalResources.fuelTanks.find(el=>el.id === editingRowId)?.awaiting_confirmation) {
            if(userPermissions.create_fuel_tank_flows_for_reportable_tanks || userPermissions.create_fuel_tank_flows_for_any_tank) {
                return true
            }
        }
        return false
    }

    function removeRowFromFuelFlowDataGrid(rowData) {
        let dataGridInstance
        if(rowData.fuel_tank_flow_type_id === 1) {
            dataGridInstance = $('#mainDataGrid_fuel_flow_incomes').dxDataGrid('instance')
        }
        if(rowData.fuel_tank_flow_type_id === 2) {
            dataGridInstance = $('#mainDataGrid_fuel_flow_outcomes').dxDataGrid('instance')
        }
        if(rowData.fuel_tank_flow_type_id === 3) {
            dataGridInstance = $('#mainDataGrid_fuel_flow_adjusments').dxDataGrid('instance')
        }

        let currentDatasource
        if(Array.isArray(dataGridInstance.option('dataSource')))
        {
            currentDatasource = dataGridInstance.option('dataSource').filter(el=>el.id !== rowData.id)
        } else {
            currentDatasource = dataGridInstance.option('dataSource').items().filter(el=>el.id !== rowData.id)
        }

        dataGridInstance.option('dataSource', currentDatasource)

        if(!rowData.guid) {
            externalDeletedOperations.push(rowData.id)
        }
    }


</script>
