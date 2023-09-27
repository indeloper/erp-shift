<script>
    function showBitrixProjectsPopup() {

        const bitrixProjectsPopup = $('#bitrixProjectsPopup').dxPopup({
            title: 'Проекты Bitrix24',
            width: 'auto',
            height: 'auto',
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
                    onClick() {
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
            width: '800px',
            height: '300px',
            focusedRowEnabled: true,
            hoverStateEnabled: true,
            showBorders: true,
            showColumnLines: true,
            columnResizingMode: 'nextColumn',
            showRowLines: true,
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
            },


            onRowDblClick: function (e) {
                handleChoosingBitrixProject()
            },

            onDisposing(e) {
                e.component.option("focusedRowKey", undefined);
            },

            columns: [
                {
                    caption: 'Bitrix ID',
                    dataField: 'ID',
                    width: 75,
                },
                {
                    caption: 'Наименование проекта',
                    dataField: 'NAME',
                    sortOrder: "asc"
                },
            ],
        })
    }
</script>
