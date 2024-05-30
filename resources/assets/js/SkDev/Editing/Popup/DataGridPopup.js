import { BasePopup } from './BasePopup';

export class DataGridPopup extends BasePopup {
  constructor(onHiding, onShowing) {
    super();
    this.onHiding = onHiding;
    this.onShowing = onShowing;
  }

  build() {
    return {
      showTitle: true,
      title: this.getTitle(),
      hideOnOutsideClick: true,
      showCloseButton: true,
      maxWidth: '60vw',
      height: 'auto',
      onHiding() {
        // this.onHiding();
      },
      onShowing() {
        // this.onShowing();
      },
    };
  }

}


