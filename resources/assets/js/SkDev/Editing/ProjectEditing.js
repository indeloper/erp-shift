import { BaseEditing } from './BaseEditing';

export class ProjectEditing extends BaseEditing {
  constructor() {
    super();
  }

  build() {
    return {
      editing: {
        mode: 'skPopup',
        popup: this.popup.build(),
        form: this.form.build(),
      },
    };
  }

}