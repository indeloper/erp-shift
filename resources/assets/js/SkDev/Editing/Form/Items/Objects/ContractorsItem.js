import { InitDataGrid } from '../../../../InitDataGrid';
import { initDxForm } from '../../../../custom';
import { DefaultDataSource } from '../../../../DataSource/DefaultDataSource';
import {
  ContractorsColumns,
} from '../../../../DataSource/Columns/Objects/ContractorsColumns';

export default class ContractorsItem {
  static build(label = undefined) {
    return {
      visible: true,
      template: (container, options) => {
        let dataGridInstance = $('#objectDataGridEditForm').dxForm('instance');

        const objectId = dataGridInstance.option('formData').id;

        const currentEmployeeData = options[0];

        const initDataGrid = new InitDataGrid('<div>')
          .setTitle('Контрагенты');

        initDxForm(
          initDataGrid,
          new DefaultDataSource(
            'http://localhost:81/projects/objects/' + objectId + '/contractos',
          ),
          new ContractorsColumns(),
        ).appendTo(currentEmployeeData);
      },

    };
  }
}