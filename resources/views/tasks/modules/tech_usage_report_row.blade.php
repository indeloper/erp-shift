<el-collapse-transition>
    <div {{--v-show="displays[ticket.id]"--}} :key="ticket.id">
        <validation-observer :ref="'observer-' + ticket.id">
            <hr style="border-color:#F6F6F6">
            <h6 class="decor-h6-modal">@{{ ticket.our_technic.name + ' ' + ticket.our_technic.inventory_number }}</h6>
            <div class="row">
                <div class="col-xl-9">
                    <label for="">
                        Комментарий<span class="star" v-if="usage_durations[ticket.id] === 0">*</span>
                    </label>
                    <validation-provider :rules="usage_durations[ticket.id] === 0 ? 'required|max:300' : 'max:300'" v-slot="v"
                                         :vid="'comment-input-' + ticket.id"
                                         :ref="'comment-input-' + ticket.id">
                        <el-input
                            type="textarea"
                            :rows="4"
                            :id="'comment-input-' + i"
                            maxlength="300"
                            :class="v.classes"
                            style="margin-top: 4px;"
                            placeholder="Напишите комментарий"
                            v-model="comments[ticket.id]"
                        ></el-input>
                        <div class="error-message"
                             :style="v.errors[0] ? 'margin-bottom: 15px;' : ''">@{{
                            v.errors[0] }}
                        </div>
                    </validation-provider>
                </div>
                <div class="col-xl-3">
                    <label for="">Количество&nbspчасов<span class="star">*</span></label>
                    <validation-provider rules="required|max:2" v-slot="v"
                                         :vid="'usage-duration-input-' + i"
                                         :ref="'usage-duration-input-' + i">
                        <el-input-number
                            :min="0"
                            :max="24"
                            :maxlength="2"
                            class="d-block w-100"
                            :class="v.classes"
                            style="margin-top: 4px; margin-bottom: 10px;"
                            v-model="usage_durations[ticket.id]"
                            :id="'usage-duration-input-' + i"
                            :precision="0"
                            :step="1"
                            placeholder="Укажите количество часов"
                        ></el-input-number>
                        <div class="error-message"
                             :style="v.errors[0] ? 'margin-bottom: 15px;' : ''">@{{
                            v.errors[0] }}
                        </div>
                    </validation-provider>
                    <el-button type="{{ $btn_type }}" @click.stop="submit(ticket.id, '{{ $btn_action }}')"
                               :loading="loadings[ticket.id]" style="width: 100%">
                        {{ $btn_text }}
                    </el-button>
                </div>
            </div>
        </validation-observer>
        {{--<div class="col-md-3"><input type="date" name="usage_date"></div>
        <div class="col-md-3"><input type="number" name="usage_duration"></div>
        <div class="col-md-3"><input type="text" name="comment"></div>--}}
    </div>
</el-collapse-transition>
