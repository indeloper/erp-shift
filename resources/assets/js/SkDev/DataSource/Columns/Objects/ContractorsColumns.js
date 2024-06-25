export class ContractorsColumns {
  build() {

    return [
      {
        caption: 'ID',
        dataField: 'id',
      },

      {
        caption: 'Наименование',
        dataField: 'full_name',
      },

      {
        caption: 'Кто добавил',
        dataField: 'full_name_user',
      },
    ];
  }
}