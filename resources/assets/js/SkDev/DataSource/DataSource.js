export class DataSource {
  constructor(loadUrl) {
    this.loadUrl = loadUrl;
  }

  build() {
    return new DevExpress.data.DataSource({
      store: new DevExpress.data.CustomStore({
        key: 'id',
        loadMode: 'processed',
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

        insert: function (values) {

        },

        update: function (key, values, isMobile = false) {

        },
        remove: function (key) {

        },
        byKey: function (key) {

        },
      }),
    });
  }
}