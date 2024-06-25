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
import ResponsiblesPtoItem from '../Items/Objects/ResponsiblesPtoItem';
import ResponsiblesManagersItem
  from '../Items/Objects/ResponsiblesManagersItem';
import ResponsiblesForemen from '../Items/Objects/ResponsiblesForemen';
import TabItems from '../Items/TabItems';
import DirectionItem from '../Items/Objects/DirectionItem';
import HistoryChangesItem from '../Items/Objects/HistoryChangesItem';
import ContractorsItem from '../Items/Objects/ContractorsItem';

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
            new GroupItems([
              ResponsiblesPtoItem.build('Ответственные ПТО'),
              ResponsiblesManagersItem.build('Ответственные РП'),
              ResponsiblesForemen.build('Ответственные прорабы'),
            ])
              .setTitle('Ответственные')
              .setColCount(4)
              .build(),

          ], 'link')
          .setTab('Контрагенты', [
            ContractorsItem.build(),
          ], 'group')
          .setTab('История', [
            HistoryChangesItem.build(),
          ], 'eyeopen')
          .build(),

      ],
    };
  }
}