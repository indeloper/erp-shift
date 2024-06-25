import { InitDataGrid } from '../../InitDataGrid';
import { initDxForm } from '../../custom';
import { DefaultDataSource } from '../../DataSource/DefaultDataSource';
import {
  ProjectObjectColumns,
} from '../../DataSource/Columns/Projects/ProjectObjectColumns';

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
            new DefaultDataSource(
              route('projects::object::index') + '?' + params.toString(),
            ),
            new ProjectObjectColumns(),
          ).appendTo(container);
        },
      },
    };
  }
}