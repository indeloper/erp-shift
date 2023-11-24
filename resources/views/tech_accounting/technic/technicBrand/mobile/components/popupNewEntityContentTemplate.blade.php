<script>

    const popupNewEntityContentTemplate = () => {
        const popupContentWrapper = $('<div>')
            .css({
                'width': '100%',
                'height': '100%',
                'positon': 'relative'
            })

        $('<div id="entityName" class="mobile-new-entity-form-element">').dxSelectBox({
            label: 'Наименование',
            labelMode: 'floating',
            dataSource: projectObjectsStore,
            valueExpr: 'id',
            displayExpr: 'name',
        }).dxValidator({
            validationGroup: "entityValidationGroup",
            validationRules: [{
                type: "required",
                message: 'Укажите значение',
            }]
        }).appendTo(popupContentWrapper)

        return popupContentWrapper;
    }
</script>