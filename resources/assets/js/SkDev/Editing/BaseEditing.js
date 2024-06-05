export class BaseEditing {
  constructor() {
    this.form = null;
    this.popup = null;
  }

  build() {
    return {
      editing: {
        mode: 'popup',
        popup: this.popup.build(),
        form: this.form.build(),

        allowUpdating: true,
        allowAdding: true,
        allowDeleting: false,
        selectTextOnEditStart: false,

        useIcons: true,
      },
    };
  }

  setPopup(popup) {
    this.popup = popup;

    return this;
  }

  setForm(form) {
    this.form = form;

    return this;
  }

}