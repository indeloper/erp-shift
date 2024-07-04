export class WorkVolumesColumns {
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
        caption: 'Версия',
        dataField: 'version',
      },

      {
        caption: 'Наименование',
        dataField: 'option',
      },

      {
        caption: 'Тип',
        dataField: 'type_name',
      },

      {
        caption: 'Статус',
        dataField: 'status_name',
      },

    ];
  }
}