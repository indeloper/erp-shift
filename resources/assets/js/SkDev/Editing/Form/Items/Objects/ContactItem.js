import { DefaultDataSource } from '../../../../DataSource/DefaultDataSource';

export default class ContactItem {
  static build(label = undefined) {
    return {
      label: {
        text: label,
      },

      dataField: 'contact_id',
      editorType: 'dxLookup',
      colSpan: 2,
      editorOptions: {
        dataSource: (new DefaultDataSource(
          route('contacts'),
          route('contacts'),
        ))
          .setLoadMode('raw')
          .buildStore(),
        valueExpr: 'id',
        displayExpr: 'full_name',
      },

    };
  }
}