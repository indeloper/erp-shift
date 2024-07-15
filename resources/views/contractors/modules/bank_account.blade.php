<div class="card">
    <div class="card-header">
        <h4 class="card-title">
            <a data-target="#collapseTwo" href="#" data-toggle="collapse">
                Реквизиты
                <b class="caret"></b>
            </a>
        </h4>
    </div>
    <div id="collapseTwo" class="card-collapse collapse">
        <div class="card-body">
            <div class="row info-string">
                <label class="col-sm-3 col-form-label label-info-card">Банк</label>
                <div class="col-sm-9">
                    <p class="form-control-static p-info-card">@if(isset($bank->bank_name)) {{ $bank->bank_name }} @else Информация отсутствует @endif</p>
                </div>
            </div>
            <div class="row info-string">
                <label class="col-sm-3 col-form-label label-info-card">Расчетный счет</label>
                <div class="col-sm-9">
                    <p class="form-control-static p-info-card">@if(isset($bank->check_account)) {{ $bank->check_account }} @else Информация отсутствует @endif</p>
                </div>
            </div>
            <div class="row info-string">
                <label class="col-sm-3 col-form-label label-info-card">Корреспондентский счет</label>
                <div class="col-sm-9">
                    <p class="form-control-static p-info-card">@if(isset($bank->cor_account)) {{ $bank->cor_account }} @else Информация отсутствует @endif</p>
                </div>
            </div>
            <div class="row info-string">
                <label class="col-sm-3 col-form-label label-info-card">Бик</label>
                <div class="col-sm-9">
                    <p class="form-control-static p-info-card">@if(isset($bank->bik)) {{ $bank->bik }} @else Информация отсутствует @endif</p>
                </div>
            </div>
        </div>
    </div>
</div>
