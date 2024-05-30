import { InitDataGrid } from '../InitDataGrid';
import { initDxForm } from '../custom';
import { DataSource } from '../DataSource/DataSource';
import {
  ProjectObjectColumns,
} from '../DataSource/Columns/ProjectObjectColumns';

export class MasterDetail {
  build() {
    return {
      masterDetail: {
        enabled: true,
        template(container, options) {
          const currentEmployeeData = options.data;

          const params = new URLSearchParams({
            project_id: currentEmployeeData.id,
          });

          const initDataGrid = new InitDataGrid('<div>')
            .setTitle('Объекты');

          initDxForm(
            initDataGrid,
            new DataSource(
              'http://localhost:81/projects/objects/load?' + params.toString(),
            ),
            new ProjectObjectColumns(),
          ).appendTo(container);
        },
      },
    };
  }
}