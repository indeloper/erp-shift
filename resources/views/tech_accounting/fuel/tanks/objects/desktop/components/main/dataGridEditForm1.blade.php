
<script>
    const dataGridEditForm = {
        onInitialized() {
            // setLoadedObjectInfo();
        },

        colCount: 1,
        items: [
            // {
            //     itemType: 'group',
            //     // caption: '',
            //     colCount: 2,
            //     items: dataGridEditForm01Group1Elems
            // },
            {
                itemType: 'tabbed',
                tabs: [
                    dataGridEditForm01Group1Elems_info,
                    dataGridEditForm01Group2Elems_incomes,
                    dataGridEditForm01Group3Elems_outcomes,
                    dataGridEditForm01Group4Elems_adjustments
                ],
            }
        ],
    }

</script>
