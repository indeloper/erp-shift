<script>
    const dataGridEditForm = {
        onInitialized() {
            setLoadedObjectInfo();
        },

        colCount: 1,
        items: [
            {
                itemType: 'group',
                caption: 'Объект',
                colCount: 2,
                items: dataGridEditForm01Group1Elems
            },
            {
                itemType: 'group',
                colCount: 4,
                caption: 'Производство работ и документооборот',
                items: dataGridEditForm01Group2Elems
            },
            {
                itemType: 'group',
                caption: 'Контрагенты',
                items: dataGridEditForm01Group3Elems
            },

        ],
    }

</script>
