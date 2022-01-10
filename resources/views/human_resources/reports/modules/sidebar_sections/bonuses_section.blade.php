<validation-observer ref="report-sidebar-observer-bonuses" :key="bonus_observer_key">
    <div class="row mt-10 align-content-end"
         v-for="(bonus, i) in bonuses" :key="'bonus-' + i">
        <div class="col-12">
            <label class="mt-10__mobile">
                Проект<span class="star">*</span>
            </label>
            <validation-provider rules="required" :vid="`bonus-location-id-select-${i+1}`"
                                 :ref="`bonus-location-id-select-${i+1}`" v-slot="v">
                <el-select v-model="bonus.project_id"
                           :class="v.classes"
                           clearable filterable
                           id="`bonus-location-id-select-${i+1}`"
                           :remote-method="searchLocations"
                           @clear="searchLocations('')"
                           remote
                           placeholder="Поиск проекта"
                >
                    <el-option
                        v-for="item in locations"
                        :key="item.code"
                        :label="item.name"
                        :value="item.code"
                    ></el-option>
                </el-select>
                <div class="error-message">@{{ v.errors[0] }}</div>
            </validation-provider>
        </div>
        <div class="col-12 col-md pr-md-1">
            <label class="mt-10__mobile">
                Премия&nbsp;@{{i+1}}<span class="star">*</span>
            </label>
            <validation-provider rules="required|max:50|min:1" :vid="`bonus-name-input-${i+1}`"
                                 :ref="`bonus-name-input-${i+1}`" v-slot="v">
                <el-select
                    :class="v.classes"
                    maxlength="50"
                    :id="`bonus-name-input-${i+1}`"
                    clearable filterable
                    :remote-method="searchBonuses"
                    @clear="searchBonuses('')"
                    remote
                    placeholder="Введите название премии"
                    allow-create
                    v-model="bonus.name"
                >
                    <el-option
                        v-for="(item, index) in bonuses_names"
                        :key="index"
                        :label="item.label"
                        :value="item.label">
                    </el-option>
                </el-select>
                <div class="error-message">@{{ v.errors[0] }}</div>
            </validation-provider>
        </div>
        <div class="col-12 col-md pl-md-1">
            <label class="mt-10__mobile">
                Размер,&nbsp;руб.<span class="star">*</span>
            </label>
            <validation-provider rules="required|positive" :vid="`bonus-amount-input-${i+1}`"
                                 :ref="`bonus-amount-input-${i+1}`" v-slot="v">
                <el-input-number
                    class="w-100"
                    maxlength="10"
                    :controls="false"
                    :class="v.classes"
                    :id="`bonus-amount-input-${i+1}`"
                    :min="0"
                    :precision="2"
                    :step="1"
                    placeholder="Введите размер премии"
                    v-model="bonus.amount"
                ></el-input-number>
                <div class="error-message">@{{ v.errors[0] }}</div>
            </validation-provider>
        </div>
        <div class="col-xs-12 mt-10 col-md text-right align-self-end" style="-ms-flex-positive: 0; flex-grow: 0;">
            <el-tooltip effect="dark" content="Удалить" placement="left">
                <el-button type="danger"
                       @click="removeBonus(i)"
                       size="small"
                       style="margin-bottom: 8px !important;"
                       icon="el-icon-delete"
                       circle
                ></el-button>
            </el-tooltip>
        </div>
    </div>
    <div class="row" v-if="bonuses.length < 20">
        <div class="col-md-12 text-right">
            <button type="button" @click="addBonus"
                    class="btn btn-round btn-sm btn-success mt-10 w-100 btn-outline add-material">
                <i class="fa fa-plus"></i>
                Добавить премию
            </button>
        </div>
    </div>
</validation-observer>
