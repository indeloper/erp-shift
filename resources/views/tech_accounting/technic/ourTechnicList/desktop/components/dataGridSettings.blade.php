<script>
    const dataGridSettings = {
        height: "calc(100vh - 200px)",
        popup: dataGridPopup,
        form: {
        onInitialized() {
            const dataGrid = $('#mainDataGrid').dxDataGrid('instance');
            
            setReadonlyFormElemsProperties(!userPermissions.technics_create_update_delete, dataGrid);

            const interval = setInterval( () => {
                if($('#mainForm').dxForm('instance')) {
                    clearInterval(interval)
                    if(!dataGrid.cellValue(dataGrid.getRowIndexByKey(editingRowId), 'third_party_mark')) {
                        switchTechnicAffiliation('our_technik_radio_elem')
                    }
                    else {
                        switchTechnicAffiliation('third_party_technik_radio_elem')
                    }
                }
            }, 10)
            
        },
       
        elementAttr: {
            id: "mainForm"
        },
        colCount: 1,
        items: [
            {
                itemType: 'group',
                // caption: '',
                colCount: 2,
                items: dataGridEditForm01Group1Elems
            },
        ],
    },    
        
    }
</script>
