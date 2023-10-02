<script>
    // Форма. Элементы группы Объект
    const dataGridEditForm01Group1Elems = [
        {
            dataField: "tank_number",
            colSpan: 1,
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
        
    ];
</script>
