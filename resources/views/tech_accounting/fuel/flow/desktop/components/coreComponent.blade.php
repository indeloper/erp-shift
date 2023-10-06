<script>
    $(()=>{
        const mainPopup = $('#mainPopup').dxPopup({
            visible: false,
            // contentTemplate: () => {
            //     const content = $('<div id="popupContentWrapper">123</div>');
                
            //     return content;
            // },
            // onInitialized(e){
            //     console.log(e);
            // },
            showTitle: true,
            // title: "Движение топлива",
            hideOnOutsideClick: true,
            showCloseButton: true,
            maxWidth: '60vw',
            height: 'auto',
            onHiding() {
                resetVars();
                resetStores();
            },
            toolbarItems: [
                {
                    widget: 'dxButton',
                    toolbar: 'bottom',
                    location: 'after',
                    options: {
                        text: 'Сохранить',
                    },
                    onClick(e) {
                        
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
                        mainPopup.hide()
                    }
                }
            ]
        }).dxPopup('instance')

        $("#dataGridAncor").dxForm({
            items: [
                {
                    itemType: "group",
                    caption: "Движение топлива по ёмкостям",
                    cssClass: "datagrid-container",
                    items: [{
                        name: "mainDataGrid",
                        editorType: "dxDataGrid",
                        editorOptions: {
                            dataSource: entitiesDataSource,
                            ...dataGridSettings,
                            columns: dataGridColumns,
                            elementAttr: {
                                id: "mainDataGrid"
                            }
                        }
                    }]
                }
            ]
        })
    })
</script>