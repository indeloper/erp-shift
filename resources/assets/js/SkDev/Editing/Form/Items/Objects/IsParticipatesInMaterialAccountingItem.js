export default class IsParticipatesInMaterialAccountingItem {
  static build(label = undefined) {
    return {

      colSpan: 4,
      editorType: 'dxCheckBox',
      editorOptions: {
        text: 'Участвует в производстве работ',
      },
      label: {
        visible: false,
        text: label,
      },
      dataField: 'is_participates_in_material_accounting',
    };
  }
}