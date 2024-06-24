export default class CadastralItem {
  static build(label = undefined) {
    return {
      label: {
        text: label,
      },
      dataField: 'cadastral_number',
      colSpan: 2,

    };
  }
}