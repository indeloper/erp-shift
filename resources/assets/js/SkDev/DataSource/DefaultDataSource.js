export class DefaultDataSource {
  constructor(loadUrl, byKeyUrl, storeUrl) {
    this.loadUrl = loadUrl;
    this.byKeyUrl = byKeyUrl;
    this.storeUrl = storeUrl;

    this.loadMode = 'processed';
    this.key = 'id';
  }

  setLoadMode(value) {
    this.loadMode = value;
    return this;
  }

  setKey(value) {
    this.key = value;
    return this;
  }

  buildStore() {
    return new DevExpress.data.CustomStore({
      key: this.key,
      loadMode: this.loadMode,
      reshapeOnPush: true,
      load: loadOptions => {

        let d = $.Deferred();

        $.getJSON(this.loadUrl, {
          data: JSON.stringify(loadOptions),
        }).done(function (result) {
          // You can process the received data here

          d.resolve(result.data, { summary: result.total });

        });

        return d.promise();
      },

      insert: (values) => {
        return $.ajax({
          url: this.storeUrl,
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
          },
          data: {
            data: values,
            options: null,
          },
          success: function (data, textStatus, jqXHR) {
            DevExpress.ui.notify('Данные успешно добавлены', 'success', 1000);
          },
        });
      },

      update: (key, values, isMobile = false) => {
        return $.ajax({
          url: this.byKeyUrl + '/' + key,
          method: 'PUT',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
          },
          data: {
            data: values,
            options: null,
          },
          success: function (data, textStatus, jqXHR) {
            DevExpress.ui.notify('Данные успешно обновлены', 'success', 1000);
          },
        });
      },

      remove: function (key) {

      },
      byKey: (key) => {
        let d = new $.Deferred();

        $.get(this.byKeyUrl + '/' + key)
          .done(function (dataItem) {
            d.resolve(dataItem.data);
          });
        return d.promise();
      },
    });
  }

  build() {
    return new DevExpress.data.DataSource({
      store: this.buildStore(),

    });
  }
}