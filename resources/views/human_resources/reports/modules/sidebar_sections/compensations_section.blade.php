<validation-observer ref="report-sidebar-observer-compensations" :key="compensation_observer_key">
    <template v-for="(compensation, i) in compensations">
        <div class="row mt-10 flex-md align-content-end" :key="'compensation-1-' + i">
            <div class="col-12">
                <label class="mt-10__mobile">
                    Компенсация&nbsp;@{{i+1}}<span class="star">*</span>
                </label>
                <validation-provider rules="required|max:50|min:1" :vid="`name-input-${i+1}`"
                                        :ref="`name-input-${i+1}`" v-slot="v">
                    <el-select
                        :class="v.classes"
                        maxlength="50"
                        :id="`name-input-${i+1}`"
                        clearable filterable
                        :remote-method="searchCompensations"
                        @clear="searchCompensations('')"
                        remote
                        placeholder="Введите название компенсации"
                        allow-create
                        v-model="compensation.name"
                    >
                        <el-option
                            v-for="(item, index) in compensation_names"
                            :key="index"
                            :label="item.label"
                            :value="item.label"
                        ></el-option>
                    </el-select>
                    <div class="error-message">@{{ v.errors[0] }}</div>
                </validation-provider>
            </div>
            <div class="col-xs-12 col-md">
                <label class="mt-10__mobile">
                    Размер,&nbsp;руб.<span class="star">*</span>
                </label>
                <validation-provider rules="required|positive" :vid="`amount-input-${i+1}`"
                                        :ref="`amount-input-${i+1}`" v-slot="v">
                    <el-input-number
                        class="w-100"
                        maxlength="10"
                        :controls="false"
                        :class="v.classes"
                        :id="`amount-input-${i+1}`"
                        :min="0"
                        :precision="2"
                        :step="1"
                        placeholder="Введите размер компенсации"
                        v-model="compensation.amount"
                    ></el-input-number>
                    <div class="error-message">@{{ v.errors[0] }}</div>
                </validation-provider>
            </div>
        </div>
        <div class="row mt-10 flex-md-nowrap align-content-end" :key="'compensation-2-' + i">
            <div class="col align-self-center" :key="'compensation-' + i">
                <el-tooltip v-if="!isCurrentMonth" effect="dark" content="Пролонгировать компенсацию можно только за текущий месяц" placement="top-start">
                    <el-checkbox style="text-transform: none"
                             :disabled="!isCurrentMonth"
                             v-model="compensation.prolonged"
                    >Пролонгация</el-checkbox>
                </el-tooltip>
                <el-checkbox v-else style="text-transform: none"
                            :disabled="!isCurrentMonth"
                            v-model="compensation.prolonged"
                >Пролонгация</el-checkbox>
            </div>
            <div class="col text-right align-self-start" style="-ms-flex-positive: 0; flex-grow: 0;">
                <el-tooltip effect="dark" content="Удалить" placement="left">
                    <el-button type="danger"
                                @click="removeCompensation(i)"
                                size="small"
                                icon="el-icon-delete"
                                circle
                    ></el-button>
                </el-tooltip>
            </div>
        </div>
    </template>
    <div class="row" v-if="compensations.length < 20">
        <div class="col-md-12 text-right">
            <button type="button" @click="addCompensation"
                    class="btn btn-round btn-sm btn-success mt-10 w-100 btn-outline add-material">
                <i class="fa fa-plus"></i>
                Добавить компенсацию
            </button>
        </div>
    </div>
</validation-observer>
