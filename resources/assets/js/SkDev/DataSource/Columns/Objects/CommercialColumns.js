export class CommercialColumns {
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
        caption: 'Тип',
        dataField: 'is_tongue',
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
        caption: 'Статус',
        dataField: 'status',
      },

    ];
  }
}