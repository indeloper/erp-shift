@extends('layouts.app')

@section('title', 'Подрядчики')

@section('url', route('subcontractors::index'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div aria-label="breadcrumb" role="navigation">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('subcontractors::card', $contractor->id) }}" class="table-link">{{ $contractor->short_name }}</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Редактирование данных</li>
            </ol>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-12 col-xl-9 mr-auto ml-auto">
        <div class="card ">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-7">
                        <div class="left-edge">
                            <div class="page-container">
                                <h4 class="card-title" style="margin-top: 5px">Редактирование данных подрядчика</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">

                    </div>
                </div>
                <hr>
            </div>
            <div class="card-body">
                <form id="form_update_contractor" class="form-horizontal" action="{{ route('subcontractors::update', $contractor->id) }}" method="post" novalidate="novalidate" enctype="multipart/form-data">
                    @csrf
                    <h5 style="margin-bottom:20px">Основные данные</h5>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Полное наименование<star class="star">*</star></label>
                        <div class="col-sm-8">
                            <div class="form-group">
                                <input class="form-control" value="{{ $contractor->full_name }}" type="text" name="full_name" maxlength="200" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Краткое наименование<star class="star">*</star></label>
                        <div class="col-sm-7">
                            <div class="form-group">
                                <input class="form-control" type="text" value="{{ $contractor->short_name }}" name="short_name" maxlength="100" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">ИНН</label>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <input maxlength="12" class="form-control raz" type="text" value="{{ $contractor->inn }}" name="inn" id="inn" minlength="10" maxlength="14">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">КПП</label>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <input class="form-control raz" type="text" value="{{ $contractor->kpp }}" name="kpp" id="kpp" minlength="9" maxlength="9">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">ОГРН</label>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <input class="form-control raz" type="text" value="{{ $contractor->ogrn }}" name="ogrn" id="ogrn" minlength="12" maxlength="15">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Юридический адрес</label>
                        <div class="col-sm-8">
                            <div class="form-group">
                                <input class="form-control" type="text" value="{{ $contractor->legal_address }}" name="legal_address" maxlength="200">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Физический адрес</label>
                        <div class="col-sm-8">
                            <div class="form-group">
                                <input class="form-control" type="text" value="{{ $contractor->physical_adress }}" name="physical_adress" maxlength="200">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Генеральный директор</label>
                        <div class="col-sm-8">
                            <div class="form-group">
                                <input class="form-control" type="text" value="{{ $contractor->general_manager }}" name="general_manager" maxlength="100">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Телефон</label>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <input class="form-control" type="tel" value="{{ $contractor->phone_number }}" name="phone_number" id="phone_number"  minlength="17" maxlength="17">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Email</label>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <input class="form-control" type="email" value="{{ $contractor->email }}" name="email">
                            </div>
                        </div>
                    </div>
                    <h5 style="margin-top:30px; margin-bottom:20px">Реквизиты</h5>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Банк</label>
                        <div class="col-sm-7">
                            <div class="form-group">
                                <input class="form-control" type="text" value="@if(isset($bank->bank_name)) {{ $bank->bank_name }} @endif" name="bank_name" maxlength="100">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Расчетный счет</label>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <input class="form-control raz" type="text" value="@if(isset($bank->check_account)) {{ $bank->check_account }} @endif" id="check_account" name="check_account" minlength="20" maxlength="20">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">Корреспондентский счет</label>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <input class="form-control raz" type="text" value="@if(isset($bank->cor_account)) {{ $bank->cor_account }} @endif" id="cor_account" name="cor_account" minlength="20" maxlength="20">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3 col-form-label">БИК</label>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <input class="form-control raz" type="text" value="@if(isset($bank->bik)) {{ $bank->bik }} @endif" name="bik" id="bik" minlength="9" maxlength="9">
                            </div>
                        </div>
                    </div>
                </form>
                <div class="card-footer text-right">
                    <button type="submit" form="form_update_contractor" class="btn btn-info">Сохранить</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js_footer')
<script>

$('#phone_number').mask('7 (000) 000-00-00');

$('#inn').mask('00000000000000');
$('#kpp').mask('000000000');
$('#ogrn').mask('000000000000');
$('#bik').mask('000000000');
$('#check_account').mask('00000000000000000000');
$('#cor_account').mask('00000000000000000000');

$('#form_update_contractor').validate();
</script>
@endsection
