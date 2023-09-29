<script>
    // Форма. Элементы группы Объект
    const dataGridEditForm01Group1Elems = [
        {
            dataField: "tank_number",
            colSpan: 2,
            validationRules: [{
                type: 'required',
                message: 'Укажите значение',
            }],
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
            dataField: "explotation_start",
            colSpan: 2,
        }
    ];
</script>
