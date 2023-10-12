<script>
    const dataGridEditForm01Group1Elems_info = {
        tabTemplate(data, index, element) {
            return '<div style="display: flex; align-items:center"><div class="fa fa-info-circle info-circle-icon-color" style="padding-top: 1px;"></div><div style="margin-left:6px">Инфо</div></div>'
        },
        colCount: 2,
        onClick(){
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
                        type: 'custom',
                        message: 'Значение должно быть уникальным',
                        validationCallback(e) {
                            if(fuelTanksStore.__rawData.find(el=>el.tank_number == e.value))
                                return false
                            return true
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
                }
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