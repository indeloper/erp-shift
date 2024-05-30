export class EmptyEditingDataGrid {
  build() {
    return {
      editing: {
        allowUpdating: false,
        allowAdding: false,
        allowDeleting: false,
      },
    };
  }
}