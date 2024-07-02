import { DefaultDataSource } from '../../../../DataSource/DefaultDataSource';

export default class ContractorItem {
  static build(label = undefined) {
    return {
      label: {
        text: label,
      },

      dataField: 'contractor_id',
      editorType: 'dxLookup',
      colSpan: 2,
      editorOptions: {
        dataSource: (new DefaultDataSource(
          route('contractors::load'),
          route('contractors::load'),
        ))
          .setLoadMode('raw')
          .buildStore(),
        valueExpr: 'id',
        displayExpr: 'short_name',
      },



    };
  }
}