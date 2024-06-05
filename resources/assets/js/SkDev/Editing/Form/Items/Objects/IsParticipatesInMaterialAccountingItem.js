export default class IsParticipatesInMaterialAccountingItem {
  static build() {
    return {
      colSpan: 4,
      editorType: 'dxCheckBox',
      editorOptions: {
        text: 'Участвует в производстве работ',
      },
      label: {
        visible: false,
      },
      dataField: 'is_participates_in_material_accounting',
    };
  }
}