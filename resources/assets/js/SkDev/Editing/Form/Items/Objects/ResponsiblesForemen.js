export default class ResponsiblesForemen {
  static build(label = undefined) {
    return {
      label: {
        text: label,
      },
      dataField: 'responsibles_foremen',
      colSpan: 4,
    };
  }
}