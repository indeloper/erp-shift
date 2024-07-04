export class BaseEditing {
  constructor() {
    this.form = null;
    this.popup = null;
    this.allowUpdating = true;
    this.allowAdding = true;
    this.allowDeleting = false;
    this.selectTextOnEditStart = false;
    this.useIcons = true;
  }

  build() {
    return {
      editing: {
        mode: 'skPopup',
        popup: this.popup.build(),
        form: this.form.build(),

        allowUpdating: this.allowUpdating,
        allowAdding: this.allowAdding,
        allowDeleting: this.allowDeleting,
        selectTextOnEditStart: this.selectTextOnEditStart,

        useIcons: this.useIcons,
      },
    };
  }

  setPopup(popup) {
    this.popup = popup;

    return this;
  }

  setAllowAdding(allowAdding) {
    this.allowAdding = allowAdding;

    return this;
  }

  setAllowUpdating(allowUpdating) {
    this.allowUpdating = allowUpdating;
    return this;
  }

  setForm(form) {
    this.form = form;

    return this;
  }

}