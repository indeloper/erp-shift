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
              NameItem.build(),
              ShortNameItem.build(),
              AddressItem.build(),
              CadastralItem.build(),
            ])
              .setTitle('Объект')
              .setColCount(2)
              .build(),

            new GroupItems([
              MaterialAccountingTypeItem.build(),
              IsParticipatesInMaterialAccountingItem.build(),
              IsParticipatesInDocumentsFlowItem.build(),
              ResponsiblesPtoItem.build(),
              ResponsiblesManagersItem.build(),
              ResponsiblesForemen.build(),
            ])
              .setTitle('Производство работ и документооборот')
              .setColCount(4)
              .build(),
          ], 'ordersbox')
          .setTab('Другое', [
            AddressItem.build(),
          ], 'chart')
          .build(),

      ],
    };
  }
}