export class EventsColumns {
  build() {

    return [
      {
        caption: 'ID',
        dataField: 'id',
      },
      {
        caption: 'Дата создания',
        dataField: 'created_at',
      },
      {
        caption: 'Дата исполнения',
        dataField: 'updated_at',
      },
      {
        caption: 'Наименование',
        dataField: 'name',
      },
      {
        caption: 'Исполнитель',
        dataField: 'user_full_name',
      },
      {
        caption: 'Автор',
        dataField: 'author_full_name',
      },

    ];
  }
}