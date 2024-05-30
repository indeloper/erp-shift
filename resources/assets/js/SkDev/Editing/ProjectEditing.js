export class ProjectEditing {
  constructor() {
    this.form = null;
    this.popup = null;
  }

  build() {
    return {
      height: 'calc(100vh - 200px)',
      focusedRowEnabled: true,
      editing: {
        mode: 'skPopup',
        popup: this.popup.build(),
        form: this.form.build(),
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