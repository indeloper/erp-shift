<script>
    // Форма. Элементы группы Объект
    const dataGridEditForm01Group1Elems = [


        {
            dataField: "fuel_tank_id",
            colSpan: 2,
            validationRules: [{
                type: 'required',
                message: 'Укажите значение',
            }],
        },
        {
            dataField: "type",
            colSpan: 1,
            validationRules: [{
                type: 'required',
                message: 'Укажите значение',
            }],
            editorOptions: {
                elementAttr: {
                    id: 'selectBoxFuelOperationType'
                },
                onSelectionChanged(e) {
                    if(e.component.option('value') === 'Поступление') {
                        $('#selectBoxFuelConsumer').dxSelectBox('instance')?.option('disabled', true)
                        $('#selectBoxFuelSupplier').dxSelectBox('instance')?.option('disabled', false)
                    }
                    else {
                        $('#selectBoxFuelConsumer').dxSelectBox('instance')?.option('disabled', false)
                        $('#selectBoxFuelSupplier').dxSelectBox('instance')?.option('disabled', true)
                    }
                },
            }
        },
        {
            dataField: "volume",
            colSpan: 1,
            editorType: 'dxNumberBox',
            editorOptions: {
                min: 0.001
            },
            validationRules: [{
                type: 'required',
                message: 'Укажите значение',
            }],
        },
        {
            dataField: "our_technic_id",
            colSpan: 1,
            editorType: "dxSelectBox",
            validationRules: [{
                type: 'required',
                message: 'Укажите значение',
            }],
            editorOptions: {
                elementAttr: {
                    id: 'selectBoxFuelConsumer'
                },
                onContentReady(e){
                    if($('#selectBoxFuelOperationType').dxSelectBox('instance').option('value') === 'Поступление')
                    e.component.option('disabled', true)
                }
            }
        },
        {
            dataField: "contractor_id",
            colSpan: 1,
            validationRules: [{
                type: 'required',
                message: 'Укажите значение',
            }],
            editorOptions: {
                elementAttr: {
                    id: 'selectBoxFuelSupplier'
                },
                onContentReady(e){
                    if($('#selectBoxFuelOperationType').dxSelectBox('instance').option('value') === 'Расход')
                    e.component.option('disabled', true)
                }
            }
        },
       
    ];
</script>
