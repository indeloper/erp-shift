import { initDxForm } from '../SkDev/custom';
import { DataGridPopup } from '../SkDev/Editing/Popup/DataGridPopup';
import { DataGridEditForm } from '../SkDev/Editing/Form/DataGridEditForm';
import { DataSource } from '../SkDev/DataSource/DataSource';
import { InitDataGrid } from '../SkDev/InitDataGrid';
import { ProjectColumns } from '../SkDev/DataSource/Columns/ProjectColumns';
import { MasterDetail } from '../SkDev/MasterDetail/MasterDetail';
import { ProjectEditing } from '../SkDev/Editing/ProjectEditing';

const initDataGrid = new InitDataGrid('#dataGridAnchor')
  .setTitle('Проект')
  .setEditing(
    new ProjectEditing(),
  )
  .setMasterDetail(
    new MasterDetail(),
  );

initDataGrid.editing
  .setPopup(
    new DataGridPopup()
      .setTitle('Информация об проекте'),
  )
  .setForm(
    new DataGridEditForm()
      .setTitle('Проект'),
  );

initDxForm(
  initDataGrid,
  new DataSource(
    'http://localhost:81/projects/load',
  ),
  new ProjectColumns(),
);