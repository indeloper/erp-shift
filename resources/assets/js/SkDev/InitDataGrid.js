import { EmptyMasterDetail } from './MasterDetail/EmptyMasterDetail';
import { EmptyEditingDataGrid } from './Editing/EmptyEditing';

export class InitDataGrid {

  constructor(selector) {
    this.editing = new EmptyEditingDataGrid();

    this.masterDetail = new EmptyMasterDetail();

    this.options = {};

    this.selector = selector;
    this.title = 'TITLE';
  }

  getOptions() {
    return this.options;
  }

  setOption(key, value) {
    this.options[key] = value;

    return this;
  }

  setOptions(object) {
    this.options = object;

    return this;
  }

  setMasterDetail(masterDetail) {
    this.masterDetail = masterDetail;

    return this;
  }

  getMasterDetail() {
    return this.masterDetail;
  }

  getEditing() {
    return this.editing;
  }

  setEditing(editing) {
    this.editing = editing;

    return this;
  }

  // НАЗВАНИЕ
  setTitle(title) {
    this.title = title;

    return this;
  }
}