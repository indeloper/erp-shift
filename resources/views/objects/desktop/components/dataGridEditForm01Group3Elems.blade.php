<script>
    // Форма. Элементы группы Контрагенты
    const dataGridEditForm01Group3Elems = [{
        itemType: 'simple',
        template: (data, itemElement) => {
            const objectContractorsWrapper = $('<div>')
                .attr('id', 'objectContractorsWrapper')
                .appendTo(itemElement)

            $('<div>').dxLoadIndicator({
                height: 50,
                width: 50,
            }).appendTo(objectContractorsWrapper);

            function createTemplateElements() {
                const objectInfoTemplate = objectInfoByID.store().__rawData;
                setContractorsObjectInfo(objectInfoTemplate.contractors);
                data.component.updateData('contractors', objectInfoTemplate.contractors);
            }

            if (objectInfoByID.isLoaded()) {
                createTemplateElements()
            } else {
                let checkDataSourceIsLoaded = setInterval(() => {
                        if (objectInfoByID.isLoaded()) {
                            clearInterval(checkDataSourceIsLoaded);
                            createTemplateElements()
                        }
                    }
                    , 100);
            }
        }
    }

    ];
</script>
