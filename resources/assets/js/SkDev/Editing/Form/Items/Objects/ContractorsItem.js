import { InitDataGrid } from '../../../../InitDataGrid';
import { initDxForm } from '../../../../custom';
import { DefaultDataSource } from '../../../../DataSource/DefaultDataSource';
import {
  ContractorsColumns,
} from '../../../../DataSource/Columns/Objects/ContractorsColumns';
import { BaseEditing } from '../../../BaseEditing';
import { DataGridPopup } from '../../../Popup/Objects/DataGridPopup';
import { DataGridEditForm } from '../../Contractors/DataGridEditForm';

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

        initDataGrid
          .setEditing(
            new BaseEditing(),
          )
          .getEditing()
          .setPopup(
            new DataGridPopup()
              .setTitle('Контрагенты'),
          )
          .setForm(
            new DataGridEditForm()
              .setTitle('Контрагенты'),
          );

        initDxForm(
          initDataGrid,
          new DefaultDataSource(
            route('projects::object::contractos::index', { projectObject: objectId }),
            route('projects::object::contractos::index', { projectObject: objectId }),
            route('projects::object::contractos::store', { projectObject: objectId }),
          ),
          new ContractorsColumns(),
        ).appendTo(currentEmployeeData);
      },

    };
  }
}