<script>
    // Форма. Элементы группы Производство работ и документооборот
    const dataGridEditForm01Group2Elems = [{
            dataField: "material_accounting_type",
            colSpan: 2
            // validationRules: [{
            //     type: 'required',
            //     message: 'Укажите значение',
            // }],
        },
        {
            editorType: 'dxCheckBox',
            editorOptions: {
                text: 'Участвует в производстве работ'
            },
            label: {
                visible: false
            },
            dataField: "is_participates_in_material_accounting",
            // validationRules: [],
        },
        {
            editorType: 'dxCheckBox',
            editorOptions: {
                text: 'Участвует в документообороте'
            },
            caption: '',
            label: {
                visible: false
            },
            dataField: "is_participates_in_documents_flow",
            // validationRules: [],
        },

        {
            dataField: "responsibles_pto",
            colSpan: 4
        },

        {
            dataField: "responsibles_managers",
            colSpan: 4
        },

        {
            dataField: "responsibles_foremen",
            colSpan: 4
        },

        // {
        //     itemType: 'group',
        //     colCount: 2,
        //     items: [

        //     ]
        // },
    ];
</script>
