import { BaseEditForm } from '../BaseEditForm';
import NameItem from '../Items/Objects/NameItem';
import GroupItems from '../Items/GroupItems';
import ShortNameItem from '../Items/Objects/ShortNameItem';
import AddressItem from '../Items/Objects/AddressItem';
import CadastralItem from '../Items/Objects/CadastralItem';
import MaterialAccountingTypeItem
  from '../Items/Objects/MaterialAccountingTypeItem';
import IsParticipatesInMaterialAccountingItem
  from '../Items/Objects/IsParticipatesInMaterialAccountingItem';
import IsParticipatesInDocumentsFlowItem
  from '../Items/Objects/IsParticipatesInDocumentsFlowItem';
import TabItems from '../Items/TabItems';
import DirectionItem from '../Items/Objects/DirectionItem';
import HistoryChangesItem from '../Items/Objects/HistoryChangesItem';
import ContractorsItem from '../Items/Objects/ContractorsItem';
import ContactsItem from '../Items/Objects/ContactsItem';
import ResponsiblesItem from '../Items/Objects/ResponsiblesItem';
import EventsItem from '../Items/Objects/EventsItem';
import WorkVolumesItem from '../Items/Objects/WorkVolumesItem';
import CommercialItem from '../Items/Objects/CommercialItem';

export class DataGridEditForm extends BaseEditForm {
  build() {
    return {
      elementAttr: {
        id: 'objectDataGridEditForm',
      },
      colCount: 1,
      items: [

        new TabItems()
          .setPosition('right')
          .setTab('Общее', [
            new GroupItems([
              NameItem.build('Наименование'),
              ShortNameItem.build('Сокращенное наименование'),
              {
                dataField: 'short_name_detail',
                visible: false,
              },
              AddressItem.build('Адрес'),
              CadastralItem.build('Кадастровый номер'),
              DirectionItem.build('Направление'),
            ])
              .setTitle('Объект')
              .setColCount(2)
              .build(),

          ], 'ordersbox')
          .setTab('Производство', [
            new GroupItems([
              MaterialAccountingTypeItem.build('Тип материального учета'),
              IsParticipatesInMaterialAccountingItem.build('ПР.Р'),
              IsParticipatesInDocumentsFlowItem.build('ДО'),
            ])
              .setTitle('Производство работ и документооборот')
              .setColCount(4)
              .build(),

          ], 'cellproperties')
          .setTab('Ответственные', [
            ResponsiblesItem.build(),
          ], 'link')
          .setTab('Контрагенты', [
            ContractorsItem.build(),
          ], 'user')

          .setTab('Контакты', [
            ContactsItem.build(),
          ], 'group')
          .setTab('Документация', [], 'textdocument')
          .setTab('Объемы', [
            WorkVolumesItem.build(),
          ], 'share')
          .setTab('Предложения', [
            CommercialItem.build(),
          ], 'sun')
          .setTab('События', [
            EventsItem.build(),
          ], 'return')
          .setTab('История', [
            HistoryChangesItem.build(),
          ], 'eyeopen')
          .build(),

      ],
    };
  }
}