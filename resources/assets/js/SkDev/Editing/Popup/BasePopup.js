export class BasePopup {
  constructor() {
    this.title = 'DEFAULT TITLE';
  }

  setTitle(title) {
    this.title = title;

    return this;
  }

  getTitle() {
    return this.title;
  }
  
}