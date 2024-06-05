export default class GroupItems {

  constructor(items) {
    this.items = items;
    this.title = 'undefined';
    this.colCount = 1;
  }

  setTitle(title) {
    this.title = title;

    return this;
  }

  setColCount(colCount) {
    this.colCount = colCount;
    return this;
  }

  build() {
    return {
      itemType: 'group',
      caption: this.title,
      colCount: this.colCount,
      items: this.items,
    };
  }
}