export const initDxForm = (InitDataGrid, dataSource, columns) => {

  return $(InitDataGrid.selector).dxForm({
    items: [
      {
        itemType: 'group',
        caption: InitDataGrid.title,
        cssClass: 'datagrid-container',
        items: [
          {
            name: 'mainDataGrid',
            editorType: 'skDataGrid',

            editorOptions: {
              dataSource: dataSource.build(),
              ...InitDataGrid.getEditing().build(),
              ...InitDataGrid.getMasterDetail().build(),
              ...InitDataGrid.getOptions(),
              keyExpr: 'id',
              columns: columns.build(),
              showBorders: true,
            },
          },
        ],
      },
    ],
  });
};

export const initDxDataGrid = (InitDataGrid, dataSource, columns) => {

  return $(InitDataGrid.selector).dxDataGrid({
    items: [
      {
        dataSource: dataSource.build(),
        ...InitDataGrid.getEditing().build(),
        ...InitDataGrid.getMasterDetail().build(),
        ...InitDataGrid.getOptions(),
        keyExpr: 'id',
        remoteOperations: true,
        columns: columns.build(),
        showBorders: true,
      },
    ],
  });
};