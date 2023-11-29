<script>
    $(()=>{
        $("#dataGridAncor").dxForm({
            items: [
                {
                    itemType: "group",
                    caption: "{{$sectionTitle}}",
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

        // const groupCaption = $('.datagrid-container').find('.dx-form-group-with-caption');
        //     $('<div>').addClass('dx-form-group-caption-buttons').prependTo(groupCaption);
        //     groupCaption.find('span').addClass('dx-form-group-caption-span-with-buttons');
        // const groupCaptionButtonsDiv = groupCaption.find('.dx-form-group-caption-buttons');
        // groupCaptionButtonsDiv.css('display', 'flex')
        // groupCaptionButtonsDiv
        //     .append('<div id="downloadXlsButton" class="headerToolbarItem">')

        // $('#downloadXlsButton').dxButton({
        //     text: "Скачать",
        //                 icon: "fa fa-download",
        //                 onClick: (e) => {
        //                     delete entitiesDataSource.skip;
        //                     delete entitiesDataSource.take;
                            
        //                     // форма в файле index.blade.php
        //                     // $('#filterList').val(JSON.stringify(''));
        //                     // $('#filterOptions').val(JSON.stringify(''));

        //                     $('#downloadXlsForm').get(0).submit();
        //                 }
                    
        // });
    })
</script>