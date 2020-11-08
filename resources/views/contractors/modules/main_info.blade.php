<div class="card">
    <div class="card-header">
        <h4 class="card-title">
            <a data-target="#collapseOne" href="#" data-toggle="collapse">
                Основные данные
                <b class="caret"></b>
            </a>
        </h4>
    </div>
    <div id="collapseOne" class="card-collapse collapse show">
        <div class="card-body">
            <div class="row info-string">
                <label class="col-sm-3 col-form-label label-info-card">Полное наименование</label>
                <div class="col-sm-9">
                    <p class="p-info-card">{{ $contractor->full_name }}</p>
                </div>
            </div>
            <div class="row info-string">
                <label class="col-sm-3 col-form-label label-info-card">Краткое наименование</label>
                <div class="col-sm-9">
                    <p class="p-info-card">{{ $contractor->short_name }}</p>
                </div>
            </div>
            <div class="row info-string">
                <label class="col-sm-3 col-form-label label-info-card">Тип контрагента</label>
                <div class="col-sm-9">
                    <p class="p-info-card">{{ $contractor->types }}</p>
                </div>
            </div>
            <div class="row info-string">
                <label class="col-sm-3 col-form-label label-info-card">ИНН</label>
                <div class="col-sm-9">
                    <a href="https://sbis.ru/contragents/{{ $contractor->inn . ($contractor->kpp ? '/' . $contractor->kpp: '')}}" target="_blank" class="p-info-card">{{ ($contractor->inn ? $contractor->inn . ' (ссылка на sbis.ru)' : '')}}</a>
                </div>
            </div>
            <div class="row info-string">
                <label class="col-sm-3 col-form-label label-info-card">КПП</label>
                <div class="col-sm-9">
                    <p class="p-info-card">{{ $contractor->kpp }}</p>
                </div>
            </div>
            <div class="row info-string">
                <label class="col-sm-3 col-form-label label-info-card">ОГРН</label>
                <div class="col-sm-9">
                    <p class="p-info-card">{{ $contractor->ogrn }}</p>
                </div>
            </div>
            <div class="row info-string">
                <label class="col-sm-3 col-form-label label-info-card">Юридический адрес</label>
                <div class="col-sm-9">
                    <p class="p-info-card">{{ $contractor->legal_address }}</p>
                </div>
            </div>
            <div class="row info-string">
                <label class="col-sm-3 col-form-label label-info-card">Физический адрес</label>
                <div class="col-sm-9">
                    <p class="p-info-card">{{ $contractor->physical_adress }}</p>
                </div>
            </div>
            <div class="row info-string">
                <label class="col-sm-3 col-form-label label-info-card">Генеральный директор</label>
                <div class="col-sm-9">
                    <p class="p-info-card">{{ $contractor->general_manager }}</p>
                </div>
            </div>
            <div class="row info-string">
                <label class="col-sm-3 col-form-label label-info-card">Телефон</label>
                <div class="col-sm-9">
                    @foreach($contractor->phones as $phone)
                    <p class="p-info-card">
                        {!! $phone->is_main != '0'? '<b>' : '' !!}
                        {{ $phone->name . ': ' . $phone->phone_number . ' ' . $phone->dop_phone }}
                        {!! $phone->is_main != '0'? '</b>' : '' !!}
                    </p>
                    @endforeach
                </div>
            </div>
            <div class="row info-string">
                <label class="col-sm-3 col-form-label label-info-card">email</label>
                <div class="col-sm-9">
                    <p class="p-info-card">{{ $contractor->email }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
