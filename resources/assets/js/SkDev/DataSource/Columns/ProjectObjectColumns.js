export class ProjectObjectColumns {
  build() {
    return [
      {
        caption: 'ID',
        dataField: 'id',
      },
      {
        caption: 'ДО',
        dataField: 'is_participates_in_documents_flow',
        width: 75,
        dataType: 'boolean',
        allowFiltering: false,
        editorOptions: {
          enableThreeStateBehavior: false,
        },
      },
      {
        caption: 'ПР.Р',
        dataField: 'is_participates_in_material_accounting',
        width: 75,
        dataType: 'boolean',
        allowFiltering: false,
        editorOptions: {
          enableThreeStateBehavior: false,
        },
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
        caption: 'Сокращенное наименование',
        dataField: 'short_name',
      },
    ];
    
  }
}