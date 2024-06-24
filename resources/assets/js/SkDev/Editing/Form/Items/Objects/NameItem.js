export default class NameItem {
  static build(label = undefined) {
    return {
      label: {
        text: label,
      },
      dataField: 'name',

      colSpan: 2,
      validationRules: [
        {
          type: 'required',
          message: 'Укажите значение',
        },
      ],
    };
  }
}