<script>
  Vue.component('sum-cell', {
      template: `
          <div v-if="!isEditing"
               @click="startEdit"
               style="min-height: 23px"
          >
              @{{ value }}
          </div>
          <div v-else>
              <el-select v-model="innerValue"
                          clearable
                         ref="abbreviation-select"
                         @blur="save"
                         @change="save"
              >
                  <el-option
                      v-for="item in abbreviations"
                      :label="item"
                      :value="item"
                  ></el-option>
              </el-select>
          </div>
      `,
      props: ['abbreviations', 'row', 'dayIndex', 'timecardId', 'dayTariffs', 'timecardDayId', 'handleError'],
      data: () => ({
          BLUR_TIMEOUT: 200,
          isEditing: false,
          innerValue: '',
          backdoor: 0,
      }),
      created() {
        eventHub.$on('recalculate', (e) => {
            this.backdoor += 1;
        });
      },
      computed: {
        day() {
            return this.dayIndex ? this.row.days[this.dayIndex] : this.row;
        },
        value() {
          this.backdoor;
          return this.day.commentary ? this.day.commentary :
            (this.dayTariffs.map(el => el.amount).filter(el => el).length > 0 ?
              this.dayTariffs.map(el => el.amount).filter(el => el).reduce((el, acc) => acc += el) : '');
        }
      },
      methods: {
          startEdit() {
            if (!/^[0-9]+$/i.test(this.value)) {
              this.innerValue = this.value;
              this.isEditing = true;
              this.$nextTick(() => this.$refs['abbreviation-select'].$el.click());
            }
          },
          stopEdit() {
              this.backdoor += 1;
              setTimeout(() => {
                  this.isEditing = false;
              }, this.BLUR_TIMEOUT);
          },
          save() {
              this.stopEdit();
              if (this.innerValue !== this.value) {
                const payload = {
                    timecard_day_id: this.timecardDayId,
                    periods: [],
                };
                if (this.innerValue) {
                  payload.periods.push({
                    id: this.day.id,
                    project_id: this.day.project_id ? this.day.project_id : undefined,
                    commentary: this.innerValue,
                  });
                } else {
                  payload.deleted_addition_ids = [this.day.id];
                }
                axios.put('{{ route('human_resources.timecard_day.update_time_periods', 'TIMECARD_DAY_ID') }}'.split('TIMECARD_DAY_ID').join(this.timecardDayId), payload)
                  .then(response => {
                      this.row.days[this.dayIndex].commentary = this.innerValue;
                      this.row.days[this.dayIndex].id = this.innerValue ? response.data.data[0].id : undefined;
                      this.$message.success('Изменения успешно сохранены.');
                      this.stopEdit();
                  })
                  .catch(error => {
                      this.handleError(error);
                      this.stopEdit();
                  });
              } else {
                this.stopEdit();
              }
          },
      },
  });
</script>
