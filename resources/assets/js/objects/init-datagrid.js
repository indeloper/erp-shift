import { DefaultDataSource } from '../SkDev/DataSource/DefaultDataSource';
import { InitDataGrid } from '../SkDev/InitDataGrid';
import { initDxForm } from '../SkDev/custom';
import {
  ObjectColumns,
} from '../SkDev/DataSource/Columns/Objects/ObjectColumns';
import {
  DataGridEditForm,
} from '../SkDev/Editing/Form/Objects/DataGridEditForm';
import { DataGridPopup } from '../SkDev/Editing/Popup/Objects/DataGridPopup';
import { BaseEditing } from '../SkDev/Editing/BaseEditing';

const initDataGrid = new InitDataGrid('#dataGridAnchor')
  .setTitle('Объекты');

initDataGrid
  .setEditing(
    new BaseEditing(),
  )
  .getEditing()
  .setAllowAdding(false)
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
    route('objects::index'),
    route('objects::base-template'),
    route('objects::store'),
  ),
  new ObjectColumns(),
);