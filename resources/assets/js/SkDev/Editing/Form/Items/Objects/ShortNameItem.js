export default class ShortNameItem {
  static build() {
    return {
      dataField: 'short_name',
      colSpan: 2,
      editorType: 'dxTextBox',
      validationRules: [
        {
          type: 'required',
          message: 'Укажите значение',
        },
      ],
    };
  }
}