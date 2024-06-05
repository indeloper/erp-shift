export default class TabItems {

  constructor() {

    this.position = 'top';

    this.stylingMode = 'secondary';

    this.iconPosition = 'top';

    this.tabs = [];
  }

  setIconPosition(position) {
    this.iconPosition = position;

    return this;
  }

  setStylingMode(mode) {
    this.stylingMode = mode;

    return this;
  }

  setTab(name, items = [], icon = null) {
    this.tabs.push({
      title: name,
      icon: icon,
      items: items,
    });

    return this;
  }

  setPosition(position) {
    this.position = position;

    return this;
  }

  build() {

    return {
      itemType: 'tabbed',
      tabPanelOptions: {
        width: 'auto',
        // height: 600,
        rtlEnabled: false,
        selectedIndex: 0,
        showNavButtons: false,
        tabsPosition: this.position,
        stylingMode: this.stylingMode,
        iconPosition: this.iconPosition,
      },
      tabs: this.tabs,
    };
  }
}