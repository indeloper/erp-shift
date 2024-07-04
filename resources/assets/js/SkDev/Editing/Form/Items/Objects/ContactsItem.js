import { InitDataGrid } from '../../../../InitDataGrid';
import { initDxForm } from '../../../../custom';
import { DefaultDataSource } from '../../../../DataSource/DefaultDataSource';
import {
  ContactsColumns,
} from '../../../../DataSource/Columns/Objects/ContactsColumns';
import { BaseEditing } from '../../../BaseEditing';
import { DataGridPopup } from '../../../Popup/Objects/DataGridPopup';
import { DataGridEditForm } from '../../Contacts/DataGridEditForm';

export default class ContactsItem {
  static build(label = undefined) {
    return {
      visible: true,
      template: (container, options) => {
        let dataGridInstance = $('#objectDataGridEditForm').dxForm('instance');

        const objectId = dataGridInstance.option('formData').id;

        const currentEmployeeData = options[0];

        const initDataGrid = new InitDataGrid('<div>')
          .setTitle('Контакты');

        initDataGrid
          .setEditing(
            new BaseEditing(),
          )
          .getEditing()
          .setPopup(
            new DataGridPopup()
              .setTitle('Контакты'),
          )
          .setForm(
            new DataGridEditForm()
              .setTitle('Контакты'),
          );

        initDxForm(
          initDataGrid,
          new DefaultDataSource(
            route('projects::object::contacts::index', { projectObject: objectId }),
            route('projects::object::contacts::index', { projectObject: objectId }),
            route('projects::object::contacts::store', { projectObject: objectId }),
          ),
          new ContactsColumns(),
        ).appendTo(currentEmployeeData);
      },

    };
  }
}