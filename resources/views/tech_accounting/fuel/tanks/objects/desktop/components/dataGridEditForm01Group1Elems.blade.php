<script>
    // Форма. Элементы группы Объект
    const dataGridEditForm01Group1Elems = [
        {
            dataField: "tank_number",
            colSpan: 1,
            editorType: 'dxTextBox',
            validationRules: [{
                type: 'required',
                message: 'Укажите значение',
            }],
        },
        {
            dataField: "explotation_start",
            colSpan: 1,
        },
        {
            dataField: "object_id",
            colSpan: 2,
            editorType: "dxSelectBox",
            editorOptions: {
                showClearButton: true,
            }
        },
        {
            dataField: "company_id",
            colSpan: 2,
            editorType: "dxSelectBox",
            validationRules: [{
                type: 'required',
                message: 'Укажите значение',
            }],
        },
        {
            dataField: "responsible_id",
            colSpan: 2,
            editorType: "dxSelectBox",
            validationRules: [{
                type: 'required',
                message: 'Укажите значение',
            }],
        },
        
    ];
</script>
