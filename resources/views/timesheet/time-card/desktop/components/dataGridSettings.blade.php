<script>
    const dataGridSettings = {
        height: "calc(100vh - 200px)",
        width: "100%",
        focusedRowEnabled: false,
        hoverStateEnabled: false,
        columnAutoWidth: false,
        showBorders: true,
        showColumnLines: true,
        showColumnHeaders: false,
        columnMinWidth: 20,
        columnResizingMode: 'nextColumn',
        syncLookupFilterValues: false,
        columnHidingEnabled: false,
        showRowLines: true,
        remoteOperations: false,
        scrolling: {
            mode: 'infinite',
            rowRenderingMode: 'virtual',
        },
        headerFilter: {
            visible: false,
        },
        filterPanel: {
            visible: false,
            customizeText: (e) => {
                let filterText = e.text;
            }
        },
        paging: {
            enabled: true,
            pageSize: 100,
        },
        grouping: {
            autoExpandAll: true,
        },
        editing: {
            mode: "batch",
            allowUpdating: true,
            selectTextOnEditStart: true,
            useIcons: true,
        },
        toolbar: {
            visible: true,
            items: [{}]
        },
        onEditingStart: (e) => {
            switch (e.data.rowType) {
                case 'timesheetHeader':
                case 'timesheetDelimiter':
                    e.cancel = true;
                    return;
            }

            if (e.column.dataField === 'tariffName'){
                e.cancel = true;
            }

            if (e.column.dataField === 'dealMultiplier'){
                e.cancel = true;
            }
        },
        onEditorPreparing: (e) => {
            console.log("onEditorPreparing", e);

            if (e.parentType !== "dataRow") {
                return
            }

            switch (e.row.data.rowType) {
                case 'timesheetSummaryHours':
                    e.editorName = "dxTextBox";
                    e.editorOptions = {
                        ...e.editorOptions
                    }
                    break;
            }
        },
        onCellPrepared: (e) => {
            switch (e.row.data.rowType) {
                case 'timesheetHeader':
                    if (e.column.name === "tariffName") {
                        e.cellElement.attr("colspan", 2);
                    } else {
                        if (e.column.name === "dealMultiplier") {
                            e.cellElement.css("display", "none");
                        }
                    }

                    e.cellElement.addClass('timesheet-header-cell');
                    e.cellElement.addClass('dx-cell-focus-disabled');
                    break;
                case 'timesheetDelimiter':
                    if (e.column.name === "tariffName") {
                        e.cellElement.attr("colspan", e.component.getVisibleColumns().length);
                    } else {
                        e.cellElement.css("display", "none");
                    }

                    e.cellElement.addClass('timesheet-delimiter-cell');
                    break;
            }
        },
        onContentReady: (e) => {
            hideServicesColumns(e);
            setColumnContentAlignment(e);
        }
    }

    function hideServicesColumns(contentReadyEvent) {
        const serviceColumnsDataFields = [
            'id',
            'rowType',
            'daysInMonthCount',
            'caption',
            'timeCardId'
        ]

        serviceColumnsDataFields.forEach((item) => {
            contentReadyEvent.component.columnOption(item, 'visible', false)
        });
    }

    function setColumnContentAlignment(contentReadyEvent) {
        let leftAlignmentColumnsNames = ['tariffName'];

        contentReadyEvent.component.getVisibleColumns().forEach((item) => {
            if (leftAlignmentColumnsNames.includes(item.dataField)) {
                contentReadyEvent.component.columnOption(item.dataField, 'alignment', 'left');
            } else {
                contentReadyEvent.component.columnOption(item.dataField, 'alignment', 'center');
            }

            if (item.dataField === 'tariffName') {
                contentReadyEvent.component.columnOption(item.dataField, 'width', '120px');
            }
        })
    }

    function timesheetHeaderTemplate(options) {
        //console.log('e', e);
    }
</script>
