<script>
    const dataGridEditForm01Group1Elems_info = {
        tabTemplate(data, index, element) {
            return '<div class="tab-template-header-wrapper"><div class="fa fa-info-circle info-circle-icon-color tab-template-header-icon-elem"></div><div>Инфо</div></div>'
        },
        colCount: 2,
        onClick() {
            choosedFormTab = 'info'
        },
        items: [

            {
                dataField: "tank_number",
                colSpan: 1,
                editorType: 'dxTextBox',

                validationRules: [
                    {
                        type: 'required',
                        message: 'Укажите значение',
                    },
                    {
                        type: 'async',
                        message: 'Значение должно быть уникальным',
                        validationCallback(e) {
                            return validateTankNumberUnique(e.value);
                        },
                    }
                ],
            },
            {
                dataField: "explotation_start",
                colSpan: 1,
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
                dataField: "object_id",
                colSpan: 2,
                editorType: "dxSelectBox",
                editorOptions: {
                    showClearButton: false,
                },
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

        ]
    }
</script>
