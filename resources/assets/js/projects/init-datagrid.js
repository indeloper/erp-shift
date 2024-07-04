import { initDxForm } from '../SkDev/custom';
import { DefaultDataSource } from '../SkDev/DataSource/DefaultDataSource';
import { InitDataGrid } from '../SkDev/InitDataGrid';
import { ProjectEditing } from '../SkDev/Editing/ProjectEditing';
import { DataGridPopup } from '../SkDev/Editing/Popup/Projects/DataGridPopup';
import {
  DataGridEditForm,
} from '../SkDev/Editing/Form/Projects/DataGridEditForm';
import { MasterDetail } from '../SkDev/MasterDetail/Projects/MasterDetail';
import {
  ProjectColumns,
} from '../SkDev/DataSource/Columns/Projects/ProjectColumns';

const initDataGrid = new InitDataGrid('#dataGridAnchor')
  .setTitle('Проект')
  .setMasterDetail(
    new MasterDetail(),
  );

initDataGrid
  .setEditing(
    new ProjectEditing(),
  )
  .getEditing()
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
  new DefaultDataSource(
    route('projects::load'),
    route('projects::index'),
    route('projects::store'),
  ),

  new ProjectColumns(),
);