<script>
    function showBitrixProjectsPopup() {

        const bitrixProjectsPopup =  $('#bitrixProjectsPopup').dxPopup({
            title: 'Проекты в Битрикс',
            width: 500,
            height: '50vh',
            visible: true,
            hideOnOutsideClick: true,
            showCloseButton: true,
            contentTemplate: bitrixProjectsPopupContentTemplate,

            onShowing() {
                let focusedRowIndexBitrixId = $('#bitrixIdFormField').dxSelectBox('instance').option('value')
                bitrixProjectsDataGridInstance = $('#bitrixProjectsDataGrid').dxDataGrid('instance');
                bitrixProjectsDataGridInstance.option("focusedRowKey", focusedRowIndexBitrixId)
            },

            toolbarItems: [
                {
                    widget: 'dxButton',
                    toolbar: 'bottom',
                    location: 'after',
                    options: {
                        text: 'OK',
                    },
                    onClick(e) {
                        handleChoosingBitrixProject()
                    }
                },
                {
                    widget: 'dxButton',
                    toolbar: 'bottom',
                    location: 'after',
                    options: {
                        text: 'Отмена',
                    },
                    onClick(e) {
                        bitrixProjectsPopup.hide()
                    }
                }
            ]
        }).dxPopup('instance')
    }

    const bitrixProjectsPopupContentTemplate = () => {

            return $('<div id="bitrixProjectsDataGrid">').dxDataGrid({
                dataSource: bitrixProjectsArray,
                keyExpr: 'ID',
                // focusedRowEnabled: true,
                // hoverStateEnabled: true,
                // columnAutoWidth: true,
                // showBorders: true,
                // showColumnLines: true,
                // showRowLines: true,

                focusedRowEnabled: true,
                hoverStateEnabled: true,
                columnAutoWidth: false,
                showBorders: true,
                showColumnLines: true,
                columnMinWidth: 50,
                columnResizingMode: 'nextColumn',
                syncLookupFilterValues: false,
                columnHidingEnabled: false,
                showRowLines: true,
                remoteOperations: true,
                scrolling: {
                    mode: 'standard',
                    rowRenderingMode: 'standard',
                },
                filterRow: {
                    visible: false,
                    applyFilter: "auto"
                },
                headerFilter: {
                    visible: false,
                },


                filterPanel: {
                    visible: false,
                    customizeText: (e) => {
                        filterText = e.text;
                    }
                },

                paging: {
                    enabled: false,
                    // pageSize: 100,
                },



                onRowDblClick: function(e) {
                    handleChoosingBitrixProject()
                },

                onDisposing(e) {
                    e.component.option("focusedRowKey", undefined);
                },

                columns: [
                    {
                        caption: 'BitrixID',
                        dataField: 'ID',
                        width: 75
                    },
                    {
                        caption: 'Наименование проекта',
                        dataField: 'NAME'
                    },
                ],


            })
        }

</script>
