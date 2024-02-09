<script>
    $(function() {
        class skDataGrid extends DevExpress.ui.dxDataGrid {
            static formEditStates = {
                UNKNOWN: 'unknown',
                INSERT: 'insert',
                UPDATE: 'update',
                DUPLICATE: 'duplicate',
                DELETE: 'delete'
            }

            constructor(element, options) {
                 super(element, options);

                this._replaceClassRecursively(this._$element[0], 'dx-datagrid-container', 'dx-datagrid');

                this._customizeColumns();
            }

            _replaceClassRecursively(element, oldClass, newClass) {
                if (element) {
                    element.classList.replace(oldClass, newClass);

                    let children = element.children;

                    for (let i = 0; i < children.length; i++) {
                        this._replaceClassRecursively(children[i], oldClass, newClass);
                    }
                }
            }

            _getDefaultOptions() {
                const defaultOptions = {

                }

                return $.extend(true, {}, super._getDefaultOptions(), defaultOptions);
            }

            _customizeColumns() {
                // Находим колонку с типом "buttons" и изменяем её шаблон
                console.log("_customizeColumns");
                // const buttonsColumnIndex = this.columnOption("buttonsColumnName"); // Укажите имя колонки с кнопками
                // if (buttonsColumnIndex !== -1) {
                    this.columnOption(2, "headerCellTemplate", this._customHeaderCellTemplate.bind(this));
                //}
            }

            _customHeaderCellTemplate(container, options) {
                // Создаем свой собственный шаблон заголовка ячейки
                const $header = $("<div>")
                    .addClass("custom-header")
                    .text("Custom Header");

                $(container).append($header);
            }
        }

        DevExpress.registerComponent("skDataGrid", skDataGrid);
    })
</script>
