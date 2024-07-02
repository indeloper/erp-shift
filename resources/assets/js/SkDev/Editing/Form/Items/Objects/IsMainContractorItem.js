export default class IsMainContractorItem {
  static build(label = undefined) {
    return {
      editorType: 'dxCheckBox',
      editorOptions: {
        text: 'Основной',
      },
      caption: '',
      label: {
        visible: false,
      },
      dataField: 'is_main',
    };
  }
}