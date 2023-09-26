<script>
    const renderStatusOptions = (statusAndOptionsWrapper) => {

        let optionsLoadIndicator = optionsLoadIndicatorHandler()

        getOptionsDxList([]);

        optionsByTypeAndStatusStore.load().done((options) => {
            
            if(optionsLoadIndicator)
            optionsLoadIndicator.remove()

            options = JSON.parse(options);

            getOptionsDxList(options);
        })
    }

    const optionsLoadIndicatorHandler = () => {

        const optionsListWrapper = $("<div>").attr("id", "optionsList").appendTo(statusAndOptionsWrapper)

        const optionsLoadIndicatorWrapper = $('<div>')
            .attr('id', 'optionsLoadIndicatorWrapper')
            .css({
                'display': 'flex',
                'justify-content': 'center',
                'margin': '10px 0'
            })
            .appendTo(optionsListWrapper)

        $('<div>')
            .dxLoadIndicator({
                height: 30,
                width: 30,
            }).appendTo(optionsLoadIndicatorWrapper)

        return optionsLoadIndicatorWrapper
    }

    const getOptionsDxList = (options) => {
        $('#optionsList').dxList({
            dataSource: options,
            hoverStateEnabled: false,
            itemTemplate(data) {
                const result = $('<div />').addClass("status-option");
                if (data.type === 'checkbox') {
                    $('<div />').dxCheckBox({
                        enableThreeStateBehavior: false,
                        value: getStartOptionValue(data.id),
                        text: data.label,
                        hint: data.label,
                        onValueChanged(e) {
                            editingRowTypeStatusOptions.push({
                                id: data.id,
                                type: data.type,
                                value: e.value,
                                comment: data.label
                            });
                        }
                    }).appendTo(result)
                }

                if (data.type === 'select') {
                    $('<div />').dxSelectBox({
                        dataSource: getOptionsSelectSource(data.source),
                        value: getStartOptionValue(data.id),
                        valueExpr: "id",
                        displayExpr: 'user_full_name',
                        label: data.label,
                        labelMode: "floating",
                        onValueChanged(e) {
                            editingRowTypeStatusOptions.push({
                                id: data.id,
                                type: data.type,
                                value: e.value,
                                comment: data.label,
                                source: data.source
                            });
                        }
                    }).appendTo(result)
                }

                if (data.type === 'text') {
                    $('<div />').dxTextBox({
                        label: data.label,
                        labelMode: "floating",
                        value: getStartOptionValue(data.id),
                        onValueChanged(e) {
                            editingRowTypeStatusOptions.push({
                                id: data.id,
                                type: data.type,
                                value: e.value,
                                comment: data.label,
                            });
                        }
                    }).appendTo(result)
                }

                return result;
            }
        })
    }

    const getOptionsSelectSource = (selectSourceName) => {
        if (selectSourceName === 'responsible_managers_and_pto')
            return responsible_managers_and_pto
        if (selectSourceName === 'responsible_managers_and_foremen')
            return responsible_managers_and_foremen
    }

    const getStartOptionValue = (optionId) => {

        if (typeof(editingRowStartOptions) === null || typeof(editingRowStartOptions) === 'undefined' || editingRowStartOptions === null)
            return false

        if (typeof editingRowStartOptions[optionId] === 'undefined')
            return false

        return editingRowStartOptions[optionId].value;
    }

    const resetStatusOptionsVars = () => {
        editingRowNewStatusId = 0;
        editingRowTypeStatusOptions = [];
        editingRowTypeStatusOptions_tmp = [];
        optionsByTypeAndStatusStore.clearRawDataCache();
    }
</script>