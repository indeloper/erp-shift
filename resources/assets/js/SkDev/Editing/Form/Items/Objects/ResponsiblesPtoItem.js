export default class ResponsiblesPtoItem {
  static build(label = undefined) {
    return {
      label: {
        text: label,
      },
      dataField: 'responsibles_pto',
      colSpan: 4,
    };
  }
}