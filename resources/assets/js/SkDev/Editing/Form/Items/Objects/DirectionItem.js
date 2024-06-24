export default class DirectionItem {
  static build(label = undefined) {
    return {
      label: {
        text: label,
      },
      dataField: 'direction',
      editorType: 'dxSelectBox',
      editorOptions: {
        dataSource: [
          {
            label: 'Сваи',
            value: 'piles',
          },
          {
            label: 'Шпунт',
            value: 'sheet_pile',
          },
        ],
        searchEnabled: true,
        // value: 'piles',
        displayExpr: 'label',
        valueExpr: 'value',
      },
      colSpan: 2,

    };
  }
}