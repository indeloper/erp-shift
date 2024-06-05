export default class IsParticipatesInDocumentsFlowItem {
  static build() {
    return {
      colSpan: 4,
      editorType: 'dxCheckBox',
      editorOptions: {
        text: 'Участвует в документообороте',
      },
      caption: '',
      label: {
        visible: false,
      },
      dataField: 'is_participates_in_documents_flow',
    };
  }
}