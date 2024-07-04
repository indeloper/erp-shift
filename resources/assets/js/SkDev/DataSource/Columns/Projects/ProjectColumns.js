export class ProjectColumns {
  build() {
    return [
      {
        caption: 'ID',
        dataField: 'id',
      },
      {
        caption: 'Наименование',
        dataField: 'name',
      },
      {
        caption: 'Адрес',
        dataField: 'address',
      },

      {
        caption: 'Контрагент',
        dataField: 'contractor_name',
      },

      {
        caption: 'Шпунт',
        dataField: 'tongue_statuses',
        cellTemplate: (container, options) => {
          $(`<div>`)
            .html(options.data.tongue_statuses)
            .appendTo(container);
        },
      },

      {
        caption: 'Сваи',
        dataField: 'pile_statuses',
        cellTemplate: (container, options) => {
          $(`<div>`)
            .html(options.data.pile_statuses)
            .appendTo(container);
        },
      },

      {
        caption: 'Юр. Лицо',
        dataField: 'entity',
      },
    ];
  }
}