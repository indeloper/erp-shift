import { InitDataGrid } from '../../../../InitDataGrid';
import { initDxForm } from '../../../../custom';
import { DefaultDataSource } from '../../../../DataSource/DefaultDataSource';
import { BaseEditing } from '../../../BaseEditing';
import { DataGridPopup } from '../../../Popup/Objects/DataGridPopup';
import { DataGridEditForm } from '../../Contacts/DataGridEditForm';
import {
  CommercialColumns,
} from '../../../../DataSource/Columns/Objects/CommercialColumns';

export default class CommercialItem {
  static build(label = undefined) {
    return {
      visible: true,
      template: (container, options) => {
        let dataGridInstance = $('#objectDataGridEditForm').dxForm('instance');

        const objectId = dataGridInstance.option('formData').id;

        const currentEmployeeData = options[0];

        const initDataGrid = new InitDataGrid('<div>')
          .setTitle('Коммерция');

        initDataGrid
          .setEditing(
            new BaseEditing(),
          )
          .getEditing()
          .setAllowAdding(false)
          .setAllowUpdating(false)
          .setPopup(
            new DataGridPopup()
              .setTitle('Коммерция'),
          )
          .setForm(
            new DataGridEditForm()
              .setTitle('Коммерция'),
          );

        initDxForm(
          initDataGrid,
          new DefaultDataSource(
            route('projects::object::commercial::index', { projectObject: objectId }),
            route('projects::object::commercial::index', { projectObject: objectId }),
            route('projects::object::commercial::store', { projectObject: objectId }),
          ),
          new CommercialColumns(),
        ).appendTo(currentEmployeeData);
      },

    };
  }
}