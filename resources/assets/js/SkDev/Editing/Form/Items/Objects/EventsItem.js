import { InitDataGrid } from '../../../../InitDataGrid';
import { initDxForm } from '../../../../custom';
import { DefaultDataSource } from '../../../../DataSource/DefaultDataSource';
import { BaseEditing } from '../../../BaseEditing';
import { DataGridPopup } from '../../../Popup/Objects/DataGridPopup';
import { DataGridEditForm } from '../../Contacts/DataGridEditForm';
import {
  EventsColumns,
} from '../../../../DataSource/Columns/Objects/EventsColumns';

export default class EventsItem {
  static build(label = undefined) {
    return {
      visible: true,
      template: (container, options) => {
        let dataGridInstance = $('#objectDataGridEditForm').dxForm('instance');

        const objectId = dataGridInstance.option('formData').id;

        const currentEmployeeData = options[0];

        const initDataGrid = new InitDataGrid('<div>')
          .setTitle('События');

        initDataGrid
          .setEditing(
            new BaseEditing(),
          )
          .getEditing()
          .setAllowAdding(false)
          .setAllowUpdating(false)
          .setPopup(
            new DataGridPopup()
              .setTitle('События'),
          )
          .setForm(
            new DataGridEditForm()
              .setTitle('События'),
          );

        initDxForm(
          initDataGrid,
          new DefaultDataSource(
            route('projects::object::events::index', { projectObject: objectId }),
            route('projects::object::events::index', { projectObject: objectId }),
            route('projects::object::events::store', { projectObject: objectId }),
          ),
          new EventsColumns(),
        ).appendTo(currentEmployeeData);
      },

    };
  }
}