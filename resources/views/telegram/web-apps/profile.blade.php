@extends('layouts.telegram')

@section('content')
    <h1>Профиль сотрудника</h1>

    @include('telegram.web-apps.shared.alert.block')

    <div class="card card-body">

        <form
                action="{{ route('web-apps::update', [
                 ...TGUserWebApp::getQueryData(),
                ]) }}"
                method="post"
        >
            @csrf
            <div class="mb-3">
                <label
                        for="email"
                        class="form-label"
                >
                    E-Mail
                    @include('telegram.web-apps.shared.required')

                </label>
                <input
                        type="email"
                        name="email"
                        class="form-control"
                        value="{{ $user->email }}"
                        id="email"
                        placeholder="Укажите email..."
                        required
                >
            </div>

            <div class="mb-3">
                <label
                        for="INN"
                        class="form-label"
                >
                    ИНН
                    @include('telegram.web-apps.shared.required')
                </label>
                <input
                        type="text"
                        value="{{ $user->INN }}"
                        maxlength="12"
                        minlength="12"
                        step="1"
                        name="INN"
                        class="form-control"
                        id="INN"
                        placeholder="Введите ИНН..."
                >
            </div>

            <div class="mb-3">
                <label
                        for="first_name"
                        class="form-label"
                >Имя
                    @include('telegram.web-apps.shared.required')
                </label>
                <input
                        type="text"
                        name="first_name"
                        value="{{ $user->first_name }}"
                        class="form-control"
                        id="first_name"
                        placeholder="Введите имя..."
                        required
                >
            </div>

            <div class="mb-3">
                <label
                        for="last_name"
                        class="form-label"
                >Фамилия
                    @include('telegram.web-apps.shared.required')
                </label>
                <input
                        type="text"
                        value="{{ $user->last_name }}"
                        class="form-control"
                        id="last_name"
                        name="last_name"
                        placeholder="Введите фамилию..."
                        required
                >
            </div>

            <div class="mb-3">
                <label
                        for="patronymic"
                        class="form-label"
                >Отчетсво</label>
                <input
                        type="text"
                        value="{{ $user->patronymic }}"
                        name="patronymic"
                        class="form-control"
                        id="patronymic"
                        placeholder="Введите отчество..."
                >
            </div>

            <div class="mb-3">
                <label
                        for="birthday"
                        class="form-label"
                >Дата рождения</label>
                <input
                        type="date"
                        value="{{ $user->birthday?->toDateString() }}"
                        name="birthday"
                        class="form-control"
                        id="birthday"
                        placeholder="Укажите дату рождения..."
                >
            </div>

            <div class="mb-3">
                <label
                        for="person_phone"
                        class="form-label"
                >Телефон</label>
                <input
                        type="text"
                        value="{{ $user->person_phone }}"
                        name="person_phone"
                        class="form-control"
                        id="person_phone"
                        placeholder="Укажите телефон..."
                >
            </div>

            <div class="mb-3">
                <label
                        for="work_phone"
                        class="form-label"
                >Рабочий телефон</label>
                <input
                        type="text"
                        value="{{ $user->work_phone }}"
                        name="work_phone"
                        class="form-control"
                        id="person_phone"
                        placeholder="Укажите рабочий телефон..."
                >
            </div>

            <div class="form-check mb-3">
                <input
                        class="form-check-input"
                        name="accept_policy"
                        type="checkbox"
                        id="accept_policy"
                        required
                        checked
                >
                <label
                        class="form-check-label"
                        for="accept_policy"
                >
                    {{ __('Соглашаюсь с политикой обработки персональных данных и с пользовательским соглашением') }}
                </label>
            </div>

            <input
                    class="btn btn-primary"
                    type="submit"
                    value="Сохранить"
            >
        </form>
    </div>
@endsection