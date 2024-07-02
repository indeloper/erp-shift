export default class NoteItem {
  static build(label = undefined) {
    return {
      label: {
        text: label,
      },
      dataField: 'note',
      editorType: 'dxTextArea',
    };
  }
}