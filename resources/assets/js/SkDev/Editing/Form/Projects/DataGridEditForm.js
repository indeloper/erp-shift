import { BaseEditForm } from '../BaseEditForm';
import NameItem from '../Items/Objects/NameItem';
import AddressItem from '../Items/Objects/AddressItem';

export class DataGridEditForm extends BaseEditForm {
  build() {
    return {
      onInitialized(e) {

      },
      onContentReady() {

      },
      colCount: 1,
      items: [
        {
          itemType: 'group',
          caption: this.getTitle(),
          colCount: 2,
          items: [
            NameItem.build('Название проекта'),
            AddressItem.build('Адрес проекта'),
          ],
        },
      ],
    };
  }
}