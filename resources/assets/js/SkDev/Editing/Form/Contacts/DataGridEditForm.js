import { BaseEditForm } from '../BaseEditForm';
import ContactItem from '../Items/Objects/ContactItem';
import NoteItem from '../Items/Objects/NoteItem';

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
          items: [
            ContactItem.build('Контакт'),
            NoteItem.build('Дополнительно'),
          ],
        },
      ],
    };
  }
}