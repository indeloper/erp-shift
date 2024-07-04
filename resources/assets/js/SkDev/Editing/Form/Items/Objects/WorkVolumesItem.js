import { InitDataGrid } from '../../../../InitDataGrid';
import { initDxForm } from '../../../../custom';
import { DefaultDataSource } from '../../../../DataSource/DefaultDataSource';
import { BaseEditing } from '../../../BaseEditing';
import { DataGridPopup } from '../../../Popup/Objects/DataGridPopup';
import { DataGridEditForm } from '../../Contacts/DataGridEditForm';
import {
  WorkVolumesColumns,
} from '../../../../DataSource/Columns/Objects/WorkVolumesColumns';

export default class WorkVolumesItem {
  static build(label = undefined) {
    return {
      visible: true,
      template: (container, options) => {
        let dataGridInstance = $('#objectDataGridEditForm').dxForm('instance');

        const objectId = dataGridInstance.option('formData').id;

        const currentEmployeeData = options[0];

        const initDataGrid = new InitDataGrid('<div>')
          .setTitle('Объемы работ');

        initDataGrid
          .setEditing(
            new BaseEditing(),
          )
          .getEditing()
          .setAllowAdding(false)
          .setAllowUpdating(false)
          .setPopup(
            new DataGridPopup()
              .setTitle('Объемы работ'),
          )
          .setForm(
            new DataGridEditForm()
              .setTitle('Объемы работ'),
          );

        initDxForm(
          initDataGrid,
          new DefaultDataSource(
            route('projects::object::work_volumes::index', { projectObject: objectId }),
            route('projects::object::work_volumes::index', { projectObject: objectId }),
            route('projects::object::work_volumes::store', { projectObject: objectId }),
          ),
          new WorkVolumesColumns(),
        ).appendTo(currentEmployeeData);
      },

    };
  }
}