export default class AddressItem {
  static build(label = undefined) {
    return {
      label: {
        text: label,
      },
      dataField: 'address',
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