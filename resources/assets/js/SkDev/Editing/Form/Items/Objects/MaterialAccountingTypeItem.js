import { DefaultDataSource } from '../../../../DataSource/DefaultDataSource';

export default class MaterialAccountingTypeItem {
  static build(label = undefined) {
    return {
      label: {
        text: label,
      },

      dataField: 'material_accounting_type',
      colSpan: 4,

      template: (container, options) => {
        const currentEmployeeData = options[0];

        $(currentEmployeeData).dxLookup({
          dataSource: (new DefaultDataSource(
            route('objects::getMaterialAccountingTypes::index'),
            route('objects::getMaterialAccountingTypes::index'),
          ))
            .setLoadMode('raw')
            .buildStore(),
          valueExpr: 'id',
          displayExpr: 'name',
        });
      },

    };
  }
}