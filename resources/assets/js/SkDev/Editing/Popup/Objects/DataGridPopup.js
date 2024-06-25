import { BasePopup } from '../BasePopup';

export class DataGridPopup extends BasePopup {
  constructor() {
    super();

  }

  build() {
    return {
      showTitle: true,
      title: this.getTitle(),
      hideOnOutsideClick: true,
      showCloseButton: true,
      width: '800px',
      height: '800px',
      fullScreen: true,
    };
  }

}


