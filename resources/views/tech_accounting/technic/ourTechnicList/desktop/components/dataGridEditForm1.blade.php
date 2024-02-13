<script>

    const dataGridEditForm = {
        elementAttr: {
            id: "mainForm"
        },
        colCount: 1,
        items: [
            {
                itemType: 'group',
                // caption: '',
                colCount: 2,
                items: dataGridEditForm01Group1Elems
            },
        ],
        onContentReady(e) {
            buildTechnicFormGUI(e.component)
        }
    }

    function buildTechnicFormGUI(formInstance) {
        const formData = formInstance.option('formData')
        const thirdPartyMarkEditor = formInstance.getEditor('third_party_mark_2')

        if (formData.third_party_mark) {
            thirdPartyMarkEditor.option('value','third_party_technik_radio_elem');
            switchTechnicAffiliation('third_party_technik_radio_elem', formInstance);
        }
        else {
            thirdPartyMarkEditor.option('value','our_technik_radio_elem');
            switchTechnicAffiliation('our_technik_radio_elem', formInstance);
        }
    }
</script>
