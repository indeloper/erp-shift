jQuery.extend(jQuery.validator,{
    messages: {
        required: "Это поле является обязательным.",
        remote: "Исправьте это поле.",
        email: "Введите действительный адрес электронной почты.",
        url: "Введите корректный адрес сайта.",
        date: "Введите правильную дату.",
        dateISO: "Введите действительную дату (ISO).",
        number: "Введите корректное число.",
        digits: "Введите только цифры.",
        creditcard: "Введите действительный номер кредитной карты.",
        equalTo: "Поля должны совпадать.",
        accept: "Введите значение с допустимым расширением.",
        maxlength: jQuery.validator.format("Введите не более {0} символов."),
        minlength: jQuery.validator.format("Введите не менее {0} символов."),
        rangelength: jQuery.validator.format("Введите значение от {0} до {1} символов."),
        range: jQuery.validator.format("Введите значение между {0} и {1}."),
        max: jQuery.validator.format("Введите значение, меньшее или равное {0}."),
        min: jQuery.validator.format("Введите значение, большее или равное {0}."),
        step: (jQuery.validator.format("Введите число c шагом {0}"))
    }
});
