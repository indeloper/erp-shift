<!-- Modal for ttn -->
<div class="modal fade bd-example-modal-lg" id="mass_add_materials_modal" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" id="mass_add_materials">
            <div class="modal-header">
                <h5 class="modal-title">Массовое добавление материала</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <hr style="margin-top:0">
                    <div class="card border-0">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                    <tr>
                                        <th>Материал</th>
                                        <th class="text-right">Количество</th>
                                        <th class="text-right">
                                            <div style="display:inline-block;margin-bottom:0">
                                                <el-checkbox v-model="globalCheck"></el-checkbox>
                                            </div>
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, index) in materials">
                                            <td>@{{ item.material.name + ' ' + (item.used ? 'Б/У' : '') }}</td>
                                            <td class="text-right">
                                                <b>@{{ Number(item.count).toFixed(3) }}</b> @{{ item.unit }} <br>
                                                <span v-for="(base) in item.convert_params" class="amount-materials">
                                                    <b>@{{ calculateConvertedAmount(item.round_count, base.value) }}</b> @{{ base.unit }}<br>
                                                </span>
                                            </td>
                                            <td class="text-right">
                                                <div>
                                                    <el-checkbox v-model="item.checked" :disabled="isPresent(item)"></el-checkbox>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                <button type="button" id="mass_add_materials_button" @click="addMaterialsToInputs()"  class="btn btn-info">Добавить</button>
            </div>
        </div>
    </div>
</div>


@push('js_footer')
    <script>
        var mass_add_materials = new Vue({
            el: '#mass_add_materials',
            data: {
                materials: [],
                globalCheck: false,
            },
            watch: {
                globalCheck(val) {
                    this.materials.map(el => {
                        if (!this.isPresent(el)) {
                            el.checked = val;
                        }
                    });
                }
            },
            mounted() {
              setInterval(() => {
                  this.$forceUpdate();
              }, 200);
            },
            methods: {
                addMaterialsToInputs()
                {
                    let that = this.vueMaterialsInstance();

                    $('#mass_add_materials_modal').modal('hide');
                    axios.post('{{ route('building::mat_acc::report_card::get_materials') }}', {base_id: vm.selected ? vm.selected : vm.object_id_from,}).then(function (response) {
                        that.new_materials = response.data;

                        Object.keys(mass_add_materials.materials).map(function(key) {
                            if (mass_add_materials.materials[key].checked === true) {

                                if (!that.inArray(that.new_materials, {id: mass_add_materials.materials[key].manual_material_id, used: (mass_add_materials.materials[key].used ? 1 : 0)})) {
                                    that.new_materials.push({
                                        id: mass_add_materials.materials[key].manual_material_id,
                                        used: (mass_add_materials.materials[key].used ? 1 : 0),
                                        label: mass_add_materials.materials[key].material.name
                                    })
                                }

                                setTimeout(() => {
                                    if (that.material_inputs.length === 1
                                        && !that.material_inputs[0].material_id
                                        && !that.material_inputs[0].material_unit
                                        && !that.material_inputs[0].material_count) {
                                        that.material_inputs = [];
                                    }
                                    that.material_inputs.push({
                                        id: that.next_mat_id++,
                                        material_id: `${mass_add_materials.materials[key].manual_material_id}_${mass_add_materials.materials[key].used ? 1 : 0}`,
                                        material_unit: that.units.find(function (item) {
                                            if (item.text === mass_add_materials.materials[key].unit) {
                                                return true;
                                            }
                                        }).id,
                                        material_label: '',
                                        used: mass_add_materials.materials[key].used,
                                        material_count: Number(mass_add_materials.materials[key].count),
                                        units: that.units,
                                        materials: response.data
                                    });
                                }, 200);
                                mass_add_materials.materials[key].checked = false;
                            }
                        });
                    });
                },
                vueMaterialsInstance() {
                    if (document.getElementById('materials')) {
                        return materials;
                    } else {
                        return materials_from;
                    }
                },
                isPresent(item) {
                    let that = this.vueMaterialsInstance();
                    const filteredInputs = that.material_inputs.filter(el => el.used === item.used);
                    return filteredInputs.map(el => el.material_id).indexOf(item.material.id) !== -1;
                },
                findAllMaterials(base_id) {
                    let that = this;

                    axios.post('{{ route('building::mat_acc::get_materials_from_base') }}', {base_id: base_id, date: vm.operation_date_from ? vm.operation_date_from : vm.operation_date}).then(function (response) {
                        that.materials = response.data;
                    });
                },
                calculateConvertedAmount: function(base, coef) {
                    return this.prettifyNumber(this.parseCustomFloat(base) * this.parseCustomFloat(coef));
                },
                parseCustomFloat: function(str) {
                    str = String(str);
                    return +str.split(',').join('');
                },
                prettifyNumber: function(num) {
                    const [wholePart, decimalPart] = num.toFixed(3).split('.');
                    const wholePartDestructed = wholePart.split('');
                    const prettyWholePartDestructed = [];

                    for (let i = 0; i < wholePartDestructed.length; i++) {
                        prettyWholePartDestructed.unshift(wholePartDestructed[wholePartDestructed.length - 1 - i]);
                        if (i !== 0 && i % 3 === 2 && i !== wholePartDestructed.length - 1) {
                            prettyWholePartDestructed.unshift(',');
                        }
                    }

                    return `${prettyWholePartDestructed.join('')}.${decimalPart}`;
                },
            }
        })


    </script>
@endpush
