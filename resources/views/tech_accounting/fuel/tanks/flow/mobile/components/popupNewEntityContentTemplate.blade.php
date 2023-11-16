<script>

    const popupNewEntityContentTemplate = () => {
        const popupContentWrapper = $('<div>')
            .addClass('popup-content-wrapper-mobile')

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