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
            dataField: "brand",
            colSpan: 1,
            validationRules: [{
                type: 'required',
                message: 'Укажите значение',
            }],
        },
        {
            dataField: "model",
            colSpan: 1,
            validationRules: [{
                type: 'required',
                message: 'Укажите значение',
            }],
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
