<validation-observer ref="report-sidebar-observer-fines" :key="fine_observer_key">
    <div class="row mt-10 flex-md-nowrap align-content-end"
         v-for="(fine, i) in fines" :key="'fine-' + i">
        <div class="col-xs-12 col-md">
            <label class="mt-10__mobile">
                Штраф&nbsp;@{{i+1}}<span class="star">*</span>
            </label>
            <validation-provider rules="required|max:50|min:1" :vid="`name-input-${i+1}`"
                                 :ref="`name-input-${i+1}`" v-slot="v">
                <el-select
                    :class="v.classes"
                    maxlength="50"
                    :id="`name-input-${i+1}`"
                    clearable filterable
                    :remote-method="searchFines"
                    @clear="searchFines('')"
                    remote
                    placeholder="Введите название штрафа"
                    allow-create
                    v-model="fine.name"
                >
                    <el-option
                        v-for="(item, index) in fine_names"
                        :key="index"
                        :label="item.label"
                        :value="item.label">
                    </el-option>
                </el-select>
                <div class="error-message">@{{ v.errors[0] }}</div>
            </validation-provider>
        </div>
        <div class="col-xs-12 col-md px-md-0">
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
                    placeholder="Введите размер штрафа"
                    v-model="fine.amount"
                ></el-input-number>
                <div class="error-message">@{{ v.errors[0] }}</div>
            </validation-provider>
        </div>
        <div class="col-xs-12 mt-10 col-md text-right align-self-end" style="-ms-flex-positive: 0; flex-grow: 0;">
            <el-tooltip effect="dark" content="Удалить" placement="left">
                <el-button type="danger"
                       @click="removeFine(i)"
                       size="small"
                       style="margin-bottom: 8px !important;"
                       icon="el-icon-delete"
                       circle
                ></el-button>
            </el-tooltip>
        </div>
    </div>
    <div class="row" v-if="fines.length < 20">
        <div class="col-md-12 text-right">
            <button type="button" @click="addFine"
                    class="btn btn-round btn-sm btn-success mt-10 w-100 btn-outline add-material">
                <i class="fa fa-plus"></i>
                Добавить штраф
            </button>
        </div>
    </div>
</validation-observer>
