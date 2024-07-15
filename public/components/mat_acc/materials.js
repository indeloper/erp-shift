Vue.component('material-item', {
  template: '
      <div class="row">\
          <div class="col-md-6">\
              <label style="margin-bottom:7px">\
                  Материал <span class="span">*</span>\
              </label>\
              <template>\
                <el-select @change="changeMaterialId" v-model="default_material_id" clearable filterable :remote-method="search" remote size="large" placeholder="Выберите материал">\
                  <el-option\
                    v-for="item in material_ids"\
                    :key="item.code"\
                    :value="item.code"\
                    :label="item.label">\
                  </el-option>\
                </el-select>\
              </template>\
          </div>\
          <div class="col-md-3">\
              <label for="" style="margin-bottom:7">\
                  Ед. измерения <span class="span">*</span>\
              </label>\
              <template>\
                <el-select @change="changeMaterialUnit" v-model="default_material_unit" placeholder="Ед. измерения">\
                  <el-option\
                    v-for="item in units"\
                    :key="item.id"\
                    :label="item.text"\
                    :value="item.id">\
                  </el-option>\
                </el-select>\
              </template>\
          </div>\
          <div :class="[inputs_length === 1 ? \'col-md-3\' : \'col-md-2\']">\
              <label for="">\
                  Количество <span class="span">*</span>\
              </label>\
              <template>\
                  <el-input-number @change="changeMaterialCount" v-model="default_material_count" :precision="3" :step="0.001" :max="10000000" required></el-input-number>\
              </template>\
          </div>\
          <div class="col-md-1 text-center" v-if="inputs_length > 1">\
            <button rel="tooltip" type="button" v-on:click="$emit(\'remove\')" class="btn-danger btn-link btn pd-0 mn-0" data-original-title="Удалить">\
                <i class="fa fa-times remove-stroke"></i>\
            </button>\
        </div>\
      </div>\
    ',
  props: ['material_id', 'material_unit', 'material_count', 'inputs_length', 'material_index'],
  mounted: function () {
      const that = this;

      axios.post('{{ route('building::mat_acc::report_card::get_materials') }}').then(function (response) {
           that.material_ids = response.data
      })
  },
  methods: {
      changeMaterialId(value) {
          this.$emit('update:material_id', value)
      },
      changeMaterialUnit(value) {
          this.$emit('update:material_unit', value)
      },
      changeMaterialCount(value) {
          this.$emit('update:material_count', value)
      },
      search(query) {
          const that = this;

          if (query !== '') {
            setTimeout(() => {
              axios.post('{{ route('building::mat_acc::report_card::get_materials') }}', {q: query}).then(function (response) {
                  that.material_ids = response.data;
              })
            }, 200);
          } else {
            that.material_ids = [];
          }
      }
  },
  data: function () {
      return {
          default_material_id: '',
          default_material_unit: '',
          default_material_count: '',
          material_ids: [],
          units: {!! json_encode($operation->main_units) !!}
      }
  }
})
