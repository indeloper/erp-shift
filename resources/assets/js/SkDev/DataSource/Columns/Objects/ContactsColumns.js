export class ContactsColumns {
  build() {

    return [
      {
        caption: 'ID',
        dataField: 'id',
      },

      {
        caption: 'ФИО',
        dataField: 'contact_full_name',
      },

      {
        caption: 'Телефон',
        dataField: 'contact_phone_number',
      },
      {
        caption: 'Должность',
        dataField: 'contact_position',
      },

      {
        caption: 'Заметка',
        dataField: 'contact_note',
      },

      {
        caption: 'Дополнительно',
        dataField: 'note',
      },
    ];
  }
}