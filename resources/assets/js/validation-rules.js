const { extend, setInteractionMode } = VeeValidate;

const isNullOrUndefined = (value) => value === null || value === undefined;
const isEmptyArray = (arr) => Array.isArray(arr) && arr.length === 0;
const numericMask = /^[0-9]+\.?[0-9]*$/;
const naturalMask = /^[0-9]+$/;
const vehRegNumberMask = /^[ABEKMHOPCTYXАВЕКМНОРСТУХabekmhopctyxавекмнорстух][0-9][0-9][0-9][ABEKMHOPCTYXАВЕКМНОРСТУХabekmhopctyxавекмнорстух][ABEKMHOPCTYXАВЕКМНОРСТУХabekmhopctyxавекмнорстух][0-9][0-9][0-9]?$/i;

setInteractionMode('lazy');

extend('required', {
    validate(value) {
        const result = {
            valid: false,
            required: true
        };
        if (isNullOrUndefined(value) || isEmptyArray(value)) {
            return result;
        }
        result.valid = !!String(value).trim().length;
        return result;
    },
    computesRequired: true,
    message: 'Обязательное поле',
});

extend('max', {
    validate(value, args) {
        const testValue = (val) => String(val).length <= args.length;
        if (isNullOrUndefined(value)) {
            return args.length >= 0;
        }
        if (Array.isArray(value)) {
            return value.every(testValue);
        }
        return testValue(value);
    },
    params: ['length'],
    message: 'Длина поля не должна превышать {length} символов',
});

extend('min', {
    validate(value, args) {
        const testValue = (val) => String(val).length >= args.length;
        if (isNullOrUndefined(value)) {
            return args.length === 0;
        }
        if (Array.isArray(value)) {
            return value.every(testValue);
        }
        return testValue(value);
    },
    params: ['length'],
    message: 'Длина поля не должна быть меньше {length} символов',
});

extend('positive', {
    validate(value) {
        const testValue = (val) => value > 0;
        if (Array.isArray(value)) {
            return value.every(testValue);
        }
        return testValue(value);
    },
    message: 'Число должно превосходить ноль',
});

extend('numeric', {
    validate(value) {
        const testValue = (val) => numericMask.test(String(val));
        if (Array.isArray(value)) {
            return value.every(testValue);
        }
        return testValue(value);
    },
    message: 'Значение должно быть числовым',
});

extend('natural', {
    validate(value) {
        const testValue = (val) => naturalMask.test(String(val));
        if (Array.isArray(value)) {
            return value.every(testValue);
        }
        return testValue(value);
    },
    message: 'Значение должно быть натуральным числом',
});

extend('max_value', {
    validate(value, args) {
        if (isNullOrUndefined(value) || value === '') {
            return false;
        }
        const testValue = (val) => Number(value) <= args.max;
        if (Array.isArray(value)) {
            return value.every(testValue);
        }
        return testValue(value);
    },
    params: ['max'],
    message: 'Максимальное значение: {max}',
});

extend('veh_reg_number', {
    validate(value) {
        const testValue = (val) => vehRegNumberMask.test(String(val));
        if (Array.isArray(value)) {
            return value.every(testValue);
        }
        return testValue(value);
    },
    message: 'Регистрационный номер транспорта указан в неверном формате',
});

extend('veh_property_short_name', {
    params: ['target'],
    validate(value, { target }) {
        const result = {
            valid: false,
            required: true
        };

        if (result.valid = !(String(target).trim().length > 30)) {
            return result;
        }

        result.valid = !(isNullOrUndefined(value) || isEmptyArray(value) || !String(value).trim().length);
        return result;
    },
    computesRequired: true,
    message: 'Обязательное поле, если наименование превышает 30 символов',
});
