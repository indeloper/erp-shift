<!-- Modal for ttn -->
<div class="modal fade bd-example-modal-lg show" id="create-ttn" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" id="ttn-form">
            <div class="modal-header">
                <h5 class="modal-title">Генерация транспортной накладной</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <hr style="margin-top:0">
                <form method="post" target="_blank" id="print_ttn" multisumit="true" action="{{ route('building::mat_acc::moving::make_ttn') }}">
                    @csrf
                    <input name="operation_id" value="{{ $operation->id }}" type="hidden">
                    <input name="materials_from" id="materials_f" type="hidden">

                    <div class="card border-0">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <label>Грузоотправитель</label>
                                    <select id="" class="selectpicker" name="main_entity_from" v-model="main_entity_from" data-style="btn-default btn-outline" data-menu-style="dropdown-blue">
                                        @foreach($entities as $key => $entity)
                                            <option value="{{ $entity }}" {{ $key == 1 ? 'selected' : '' }}>{{ $entity }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <label>Грузополучатель</label>
                                    <select id="" class="selectpicker" name="main_entity_to" v-model="main_entity_to" data-style="btn-default btn-outline" data-menu-style="dropdown-blue">
                                        @foreach($entities as $key => $entity)
                                            <option value="{{ $entity }}" {{ $key == 1 ? 'selected' : '' }}>{{ $entity }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <h6 class="h6-left-border">Прием груза</h6>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <label>Дата и время подачи транспортного средства под погрузку</label>
                                    <template>
                                        <div class="block">
                                            <el-date-picker
                                                style="cursor:pointer"
                                                name="take[time]"
                                                v-model="take.time"
                                                format="dd.MM.yyyy H:mm"
                                                value-format="dd.MM.yyyy H:mm"
                                                type="datetime"
                                                placeholder="Выберите день"
                                                :picker-options="{firstDayOfWeek: 1}"
                                                @focus = "onFocus"
                                            >
                                            </el-date-picker>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label>Фактические дата и время прибытия</label>
                                    <template>
                                        <div class="block">
                                            <el-date-picker
                                                style="cursor:pointer"
                                                name="take[fact_arrival_time]"
                                                v-model="take.fact_arrival_time"
                                                format="dd.MM.yyyy H:mm"
                                                value-format="dd.MM.yyyy H:mm"
                                                type="datetime"
                                                placeholder="Выберите день"
                                                :picker-options="{firstDayOfWeek: 1}"
                                                @focus = "onFocus"
                                            >
                                            </el-date-picker>
                                        </div>
                                    </template>
                                </div>
                                <div class="col-md-6">
                                    <label>Фактические дата и время убытия</label>
                                    <template>
                                        <div class="block">
                                            <el-date-picker
                                                style="cursor:pointer"
                                                name="take[fact_departure_time]"
                                                v-model="take.fact_departure_time"
                                                format="dd.MM.yyyy H:mm"
                                                value-format="dd.MM.yyyy H:mm"
                                                type="datetime"
                                                placeholder="Выберите день"
                                                :picker-options="{firstDayOfWeek: 1}"
                                                @focus = "onFocus"
                                            >
                                            </el-date-picker>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label>Масса груза</label>
                                    <el-input-number placeholder="Укажите массу" :precision="3" :min="0" :step="0.001" :max="100000" name="take[weight]" v-model="take.weight"></el-input-number>
                                </div>
                                <div class="col-md-6">
                                    <label>Количество грузовых мест</label>
                                    <el-input-number placeholder="Укажите грузовые места" :min="0" :step="1" name="take[places_count]" :max="100000" v-model="take.places_count"></el-input-number>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <h6 class="h6-left-border">Сдача груза</h6>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <label>Дата и время подачи транспортного средства под выгрузку</label>
                                    <template>
                                        <div class="block">
                                            <el-date-picker
                                                style="cursor:pointer"
                                                name="give[time]"
                                                v-model="give.time"
                                                type="datetime"
                                                format="dd.MM.yyyy H:mm"
                                                value-format="dd.MM.yyyy H:mm"
                                                placeholder="Выберите день"
                                                :picker-options="{firstDayOfWeek: 1}"
                                                @focus = "onFocus"
                                            >
                                            </el-date-picker>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label>Фактические дата и время прибытия</label>
                                    <template>
                                        <div class="block">
                                            <el-date-picker
                                                style="cursor:pointer"
                                                name="give[fact_arrival_time]"
                                                v-model="give.fact_arrival_time"
                                                format="dd.MM.yyyy H:mm"
                                                value-format="dd.MM.yyyy H:mm"
                                                type="datetime"
                                                placeholder="Выберите день"
                                                :picker-options="{firstDayOfWeek: 1}"
                                                @focus = "onFocus"
                                            >
                                            </el-date-picker>
                                        </div>
                                    </template>
                                </div>
                                <div class="col-md-6">
                                    <label>Фактические дата и время убытия</label>
                                    <template>
                                        <div class="block">
                                            <el-date-picker
                                                style="cursor:pointer"
                                                name="give[fact_departure_time]"
                                                v-model="give.fact_departure_time"
                                                format="dd.MM.yyyy H:mm"
                                                value-format="dd.MM.yyyy H:mm"
                                                type="datetime"
                                                placeholder="Выберите день"
                                                :picker-options="{firstDayOfWeek: 1}"
                                                @focus = "onFocus"
                                            >
                                            </el-date-picker>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label>Масса груза</label>
                                    <el-input-number placeholder="Укажите массу" :min="0" :precision="3" :step="0.001" name="give[weight]" v-model="give.weight"></el-input-number>
                                </div>
                                <div class="col-md-6">
                                    <label>Количество грузовых мест</label>
                                    <el-input-number placeholder="Укажите грузовые места" :min="0" :step="1"  name="give[places_count]" v-model="give.places_count"></el-input-number>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <h6 class="h6-left-border">Перевозчик</h6>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <template>
                                        <el-select v-model="entity" clearable filterable allow-create :remote-method="search_suppliers" remote name="entity" placeholder="Поиск поставщика">
                                            <el-option
                                                v-for="item in suppliers"
                                                :key="item.code"
                                                :label="item.label"
                                                :value="item.code">
                                            </el-option>
                                        </el-select>
                                    </template>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <label>Город</label>
                                    <template>
                                      <el-input placeholder="" name="city" v-model="city"></el-input>
                                    </template>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <label>Адрес</label>
                                    <template>
                                      <el-input placeholder="" name="address" v-model="address"></el-input>
                                    </template>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label>Индекс</label>
                                    <template>
                                      <el-input placeholder="" name="index" v-model="index"></el-input>
                                    </template>
                                </div>
                                <div class="col-md-6">
                                    <label>Телефон</label>
                                    <template>
                                      <el-input placeholder="" name="phone_number" v-model="phone_number"></el-input>
                                    </template>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label>ФИО водителя</label>
                                    <template>
                                      <el-input placeholder="" name="driver_name" v-model="driver_name"></el-input>
                                    </template>
                                </div>
                                <div class="col-md-6">
                                    <label>Телефон водителя</label>
                                    <template>
                                      <el-input placeholder="" name="driver_phone_number" v-model="driver_phone_number"></el-input>
                                    </template>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <h6 class="h6-left-border">Транспортное средство</h6>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-8">
                                    <label>Транспортное средство</label>
                                    <template>
                                      <el-input placeholder="ТС и его параметры" name="vehicle" v-model="vehicle"></el-input>
                                    </template>
                                </div>
                                <div class="col-md-4">
                                    <label>Номер ТС</label>
                                    <template>
                                      <el-input placeholder="Номер ТС" name="vehicle_number" id="vehicle_number" v-model="vehicle_number"></el-input>
                                    </template>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-8">
                                    <label>Прицеп</label>
                                    <template>
                                      <el-input placeholder="Прицеп и его параметры" name="trailer" v-model="trailer"></el-input>
                                    </template>
                                </div>
                                <div class="col-md-4">
                                    <label>Номер прицепа</label>
                                    <template>
                                      <el-input placeholder="Номер прицепа" name="trailer_number" id="trailer_number" v-model="trailer_number"></el-input>
                                    </template>
                                </div>
                            </div>
                            <div class="row" style="margin-top:30px">
                                <div class="col-md-12">
                                    <label>Перевозчик (уполномоченное лицо)</label>
                                    <template>
                                      <el-input placeholder="ФИО" name="carrier" v-model="carrier"></el-input>
                                    </template>
                                </div>
                            </div>
                            <div class="row" style="margin-top:30px">
                                <div class="col-md-12">
                                    <label>Грузоотправитель (уполномоченное лицо)</label>
                                    <template>
                                        <el-select v-model="consignor" clearable filterable :remote-method="search_responsible_users" remote name="consignor" placeholder="Поиск сотрудника">
                                            <el-option
                                                v-for="item in responsible_users"
                                                :key="item.code"
                                                :label="item.label"
                                                :value="item.code">
                                            </el-option>
                                        </el-select>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                <button type="button" id="ttn_form_button" @click="createTtn()" class="btn btn-info">Подтвердить</button>
            </div>
        </div>
    </div>
</div>


@push('js_footer')
<script>

$("#print_ttn").on("submit", function() {
    $(this).submit(function() {
        return false;
    });
    return true;
});
 var ttn = new Vue({
     el: '#ttn-form',
     data: {
         operation_id: {{ $operation->id }},
         main_entity_from: '{!! $entities[1] !!}',
         main_entity_to: '{!! $entities[1] !!}',
         take: {
             time: '',
             fact_arrival_time: '',
             fact_departure_time: '',
             weight: 0,
             places_count: 0
         },
         give: {
             time: '',
             fact_arrival_time: '',
             fact_departure_time: '',
             weight: 0,
             places_count: 0
         },
         entity: '',
         suppliers: [],
         city: '',
         address: '',
         index: '',
         phone_number: '',
         driver_name: '',
         driver_phone_number: '',
         vehicle: '',
         vehicle_number: '',
         trailer: '',
         trailer_number: '',
         carrier: '',
         consignor: '{!! Auth::user()->id !!}',
         responsible_users: []
     },
     mounted: function () {
         axios.post('{{ route('building::mat_acc::get_users') }}', {
             responsible_user_id: '{!! Auth::user()->id !!}'
         }).then(response => ttn.responsible_users = response.data);


         axios.post('{{ route('building::mat_acc::report_card::get_suppliers') }}', {ttn: 'true'}).then(response => ttn.suppliers = response.data);
     },
     methods: {
         onFocus: function() {
             $('.el-input__inner').blur();
         },
         search_suppliers(query) {
             if (query !== '') {
                 setTimeout(() => {
                     axios.post('{{ route('building::mat_acc::report_card::get_suppliers') }}', {q: query, ttn: 'true'}).then(function (response) {
                         ttn.suppliers = response.data;
                     })
                 }, 200);
             } else {
                 axios.post('{{ route('building::mat_acc::report_card::get_suppliers') }}', {ttn: 'true'}).then(response => ttn.suppliers = response.data);
             }
         },
         search_responsible_users(query) {
             if (query !== '') {
                 setTimeout(() => {
                     axios.post('{{ route('building::mat_acc::get_users') }}', {q: query}).then(function (response) {
                         ttn.responsible_users = response.data;
                     })
                 }, 200);
             } else {
                 axios.post('{{ route('building::mat_acc::get_users') }}').then(response => ttn.responsible_users = response.data);
             }
         },
         createTtn() {
             var data = ('{' + '"material_count":[' + materials_from.material_inputs.map(function(item) { return '"' + item.material_count + '"'}) +
                 '],"material_id":[' + materials_from.material_inputs.map(function(item) { return '"' + item.material_id + '"'}) +
                 '],"material_unit":[' + materials_from.material_inputs.map(function(item) { return '"' + item.units[item.material_unit - 1].text + '"' }) + ']' + '}'
             );

             $('#materials_f').val(data);

             answer_from.exist_ttn = true;
             $('create-ttn').modal('hide');

             $('#print_ttn').submit();
         }
     }
 })

 $('#vehicle_number').mask('A 000 AA 00Z', {
    translation: {
        'Z': {
            pattern: /[0-9]/, optional: true
        },
        'A': {
            pattern: /[А-Яа-я]/, optional: false
        }
    }
 });

</script>
@endpush
