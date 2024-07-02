import { BaseEditForm } from '../BaseEditForm';
import IsMainContractorItem from '../Items/Objects/IsMainContractorItem';
import ContractorItem from '../Items/Objects/ContractorItem';

export class DataGridEditForm extends BaseEditForm {
  build() {
    return {
      onInitialized(e) {

      },
      onContentReady() {

      },
      colCount: 1,
      items: [
        {
          itemType: 'group',
          caption: this.getTitle(),
          colCount: 2,
          items: [
            ContractorItem.build('Контрагент'),
            IsMainContractorItem.build()
          ],
        },
      ],
    };
  }
}