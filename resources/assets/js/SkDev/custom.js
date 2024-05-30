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
              keyExpr: 'ID',
              columns: columns.build(),
              showBorders: true,
            },
          },
        ],
      },
    ],
  });
};