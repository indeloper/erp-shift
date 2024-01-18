DevExpress.ui.dataGrid.defaultOptions({
    device: [
        {deviceType: 'desktop'},
        {deviceType: 'tablet'},
        {deviceType: 'phone'},
    ],
    options: {
        focusedRowEnabled: true,
        hoverStateEnabled: true,
        columnAutoWidth: false,
        showBorders: true,
        showColumnLines: true,
        columnMinWidth: 50,
        columnResizingMode: 'nextColumn',
        syncLookupFilterValues: false,
        columnHidingEnabled: false,
        showRowLines: true,
        remoteOperations: true,
        scrolling: {
            mode: 'infinite',
            rowRenderingMode: 'virtual',
        },
        filterRow: {
            visible: true,
            applyFilter: "auto"
        },
        headerFilter: {
            visible: false,
        },
        filterPanel: {
            visible: false,
            customizeText: (e) => {
                filterText = e.text;
            }
        },
        grouping: {
            autoExpandAll: true,
            allowCollapsing: true,
            expandMode: 'rowClick',
        },
        paging: {
            enabled: true,
            pageSize: 100,
        },
        editing: {
            allowUpdating: true,
            allowAdding: true,
            allowDeleting: true,
            selectTextOnEditStart: true,
            useIcons: true,
        },

        onEditorPreparing: (e) => {
            if (typeof createFilterRowTagBoxFilterControlForLookupColumns === 'function') {
                if (e.parentType === `filterRow` && e.lookup)
                    createFilterRowTagBoxFilterControlForLookupColumns(e)
            }
        },

        toolbar: {
            visible: true,
            items: [{}]
        },
    }
});
