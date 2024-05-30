import { BaseEditForm } from './BaseEditForm';

export class DataGridEditForm extends BaseEditForm {
  build() {
    return {
      elementAttr: {
        id: 'objectDataGridEditForm',
      },
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
            {
              label: {
                text: 'Название проекта',
                
              },
              dataField: 'name',
              colSpan: 2,
              validationRules: [
                {
                  type: 'required',
                  message: 'Укажите значение',
                },
              ],
            },
            {
              label: {
                text: 'Адрес проекта',
              },
              dataField: 'address',
              colSpan: 2,
              validationRules: [
                {
                  type: 'required',
                  message: 'Укажите значение',
                },
              ],
            },
          ],
        },
      ],
    };
  }
}