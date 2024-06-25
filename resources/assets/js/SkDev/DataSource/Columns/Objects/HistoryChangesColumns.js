export class HistoryChangesColumns {
  build() {

    return [
      {
        caption: 'Кто редактировал',
        dataField: 'editor_FIO',
      },
      {
        caption: 'Наименование объекта',
        dataField: 'objectName',
      },
      {
        caption: 'Название объекта',
        dataField: 'objectCaption',
      },

      {
        caption: 'Индекс',
        dataField: 'postalCode',
      },
      {
        caption: 'Город',
        dataField: 'city',
      },
      {
        caption: 'Кадастровый номер',
        dataField: 'cadastralNumber',
      },

      {
        caption: 'Улица',
        dataField: 'street',
      },
      {
        caption: 'Участок',
        dataField: 'section',
      },
      {
        caption: 'Дом',
        dataField: 'building',
      },
      {
        caption: 'Корпус',
        dataField: 'housing',
      },
      {
        caption: 'Литера',
        dataField: 'letter',
      },
      {
        caption: 'Строение',
        dataField: 'construction',
      },
      {
        caption: 'Земельный участок',
        dataField: 'stead',
      },

      {
        caption: 'Очередь',
        dataField: 'queue',
      },
      {
        caption: 'Лот',
        dataField: 'lot',
      },
      {
        caption: 'Этап',
        dataField: 'stage',
      },
      {
        caption: 'Массив',
        dataField: 'housingArea',
      },
    ];
  }
}