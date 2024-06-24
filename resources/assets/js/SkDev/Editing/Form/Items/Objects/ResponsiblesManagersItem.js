export default class ResponsiblesManagersItem {
  static build(label = undefined) {
    return {
      label: {
        text: label,
      },
      dataField: 'responsibles_managers',
      colSpan: 4,
    };
  }
}