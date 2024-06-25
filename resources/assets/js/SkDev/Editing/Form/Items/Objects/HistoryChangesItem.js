import { InitDataGrid } from '../../../../InitDataGrid';
import { initDxForm } from '../../../../custom';
import { DefaultDataSource } from '../../../../DataSource/DefaultDataSource';
import {
  HistoryChangesColumns,
} from '../../../../DataSource/Columns/Objects/HistoryChangesColumns';

export default class HistoryChangesItem {
  static build(label = undefined) {
    return {
      visible: true,
      template: (container, options) => {
        let dataGridInstance = $('#objectDataGridEditForm').dxForm('instance');

        const objectId = dataGridInstance.option('formData').id;

        const currentEmployeeData = options[0];

        const initDataGrid = new InitDataGrid('<div>')
          .setTitle('История изменений');

        initDxForm(
          initDataGrid,
          new DefaultDataSource(
            route('projects::object::history_changes', { projectObject: objectId }),
          ),
          new HistoryChangesColumns(),
        ).appendTo(currentEmployeeData);
      },

    };
  }
}