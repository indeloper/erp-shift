<script>
    // Форма. Элементы группы Объект
    const dataGridEditForm01Group1Elems = [
        {
            dataField: "name",
            colSpan: 2,
            validationRules: [{
                type: 'required',
                message: 'Укажите значение',
            }],
        },
        
        {
            dataField: "technic_category_id",
            colSpan: 1,
            validationRules: [{
                type: 'required',
                message: 'Укажите значение',
            }],
        },
        {
            dataField: "responsible_id",
            colSpan: 1,
            validationRules: [{
                type: 'required',
                message: 'Укажите значение',
            }],
        },

        {
            dataField: "technic_brand_id",
            colSpan: 1,
            editorOptions: {
                onSelectionChanged(e){
                    if(e.selectedItem.id) {
                        let interval = setInterval(() => {
                            if($('#technic_brand_model_id').dxSelectBox('instance')) {
                                clearInterval(interval)
                                $('#technic_brand_model_id').dxSelectBox('instance')?.option('dataSource', $('#technic_brand_model_id').dxSelectBox('instance')?.option('dataSource').store.__rawData.filter(el=>el.technic_brand_id ===e.selectedItem.id));
                            }
                        }, 100)
                    }
                }
            },
            validationRules: [{
                type: 'required',
                message: 'Укажите значение',
            }],
        },

        {
            dataField: "technic_brand_model_id",
            colSpan: 1,
            editorType: 'dxSelectBox',
            editorOptions: {
              elementAttr: {
                id: 'technic_brand_model_id'
              }  
            },
            validationRules: [{
                type: 'required',
                message: 'Укажите значение',
            }],
        },


        
        {
            dataField: "brand",
            colSpan: 1,
            // validationRules: [{
            //     type: 'required',
            //     message: 'Укажите значение',
            // }],
        },
        {
            dataField: "model",
            colSpan: 1,
            // validationRules: [{
            //     type: 'required',
            //     message: 'Укажите значение',
            // }],
        },
        {
            dataField: "inventory_number",
            caption: "Инвентарный номер",
            colSpan: 1,
        },
        {
            dataField: "exploitation_start",
            caption: "Начало эксплуатации",
            dataType: "date",
            colSpan: 1,
        },
    ];
</script>
