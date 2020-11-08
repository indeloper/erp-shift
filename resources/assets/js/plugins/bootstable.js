/*
Bootstable
 @description  Javascript library to make HMTL tables editable, using Bootstrap
 @version 1.1
 @autor Tito Hinostroza
*/
"use strict";

//Global variables
var params = null;
var colsEdi = null;
var newColHtml =
    '<button id="bEdit" type="button" class="btn btn-xs btn-link mn-0 btn-space btn-info"  onclick="rowEdit(this);">' +
    '<i class="fas fa-pencil-alt"></i>'+
    '</button>'+
    '<button id="bElim" type="button" class="btn btn-xs btn-link mn-0 btn-space  btn-danger"  onclick="rowElim(this);">' +
    '<i class="fas fa-trash" aria-hidden="true"></i>'+
    '</button>'+
    '<button id="bAcep" type="button" class="btn btn-xs btn-link mn-0 btn-space btn-success"  style="display:none;" onclick="rowAcep(this);">' +
    '<i class="fas fa-check"></i>'+
    '</button>'+
    '<button id="bCanc" type="button" class="btn btn-xs btn-link mn-0 btn-space" style="display:none;"  onclick="rowCancel(this);">' +
    '<i class="fas fa-times" aria-hidden="true"></i>'+
    '</button>'+
    '<button id="bSub" type="button" class="btn btn-xs btn-link mn-0 btn-space" onclick="createSubKey(this);">' +
    '<i class="fas fa-plus" aria-hidden="true"></i>'+
    '</button>';

var newSubColHtml =
    '<button id="bEdit" type="button" class="btn btn-xs btn-link mn-0 btn-space btn-info"  onclick="rowEdit(this);">' +
    '<i class="fas fa-pencil-alt"></i>'+
    '</button>'+
    '<button id="bElim" type="button" class="btn btn-xs btn-link mn-0 btn-space  btn-danger"  onclick="rowElim(this);">' +
    '<i class="fas fa-trash" aria-hidden="true"></i>'+
    '</button>'+
    '<button id="bAcep" type="button" class="btn btn-xs btn-link mn-0 btn-space btn-success"  style="display:none;" onclick="rowAcep(this);">' +
    '<i class="fas fa-check"></i>'+
    '</button>'+
    '<button id="bCanc" type="button" class="btn btn-xs btn-link mn-0 btn-space" style="display:none;"  onclick="rowCancel(this);">' +
    '<i class="fas fa-times" aria-hidden="true"></i>'+
    '</button>';

var saveColHtml =
    '<button id="bEdit" type="button" class="btn btn-xs btn-link mn-0 btn-space btn-info" style="display:none;" onclick="rowEdit(this);">' +
    '<i class="fas fa-pencil-alt"></i>'+
    '</button>'+
    '<button id="bAcep" type="button" class="btn btn-xs btn-link mn-0 btn-space btn-success" style=""  onclick="rowAcep(this);">' +
    '<i class="fas fa-check"></i>'+
    '</button>'+
    '<button id="bCanc" type="button" class="btn btn-xs btn-link mn-0 btn-space " onclick="rowCancel(this);">' +
    '<i class="fas fa-times" aria-hidden="true"></i>'+
    '</button>'+
    '<button id="bElim" type="button" class="btn btn-xs btn-link mn-0 btn-space btn-danger" onclick="rowElim(this);">' +
    '<i class="fas fa-trash" aria-hidden="true"></i>'+
    '</button>';

var colEdicHtml = '<td name="buttons" class="text-right d-cell actions">'+newColHtml+'</td>';
var subColEdicHtml = '<td name="buttons" class="text-right d-cell actions">'+newSubColHtml+'</td>';
var colSaveHtml = '<td name="buttons" class="text-right d-cell actions">'+saveColHtml+'</td>';

$.fn.SetEditable = function (options) {
    var defaults = {
        $addButton: null, //Jquery object of "Add" button
        onEdit: function() {}, // Called after edition (check-button click)
        onBeforeDelete: function() {}, // Called before deletion
        onBeforeSubDelete: function() {}, // Called before sub_key deletion
        onDelete: function() {}, // Called after deletion
        onSubDelete: function() {}, // Called after sub_key deletion,
        onAdd: function() {}, // Called after new row create
    };

    params = $.extend(defaults, options);
    this.find('thead tr').append('<th name="buttons" class="text-right d-cell actions"></th>');  // add th for buttons
    this.find('tbody tr').each(function (index, tr) {
        if ($(tr).is('[class^=sub_key_]')) {
            $(tr).append(subColEdicHtml);
        } else {
            $(tr).append(colEdicHtml);
        }
    });
    var $tabedi = this;   //Read reference to the current table, to resolve "this" here.
    //Process "addButton" parameter
    if (params.$addButton != null) {
        //Se proporcionó parámetro
        params.$addButton.click(function() {
            rowAddNew($tabedi.attr("id"));
        });
    }
};

function rowAddNew(tabId) {
    var $tab_en_edic = $("#" + tabId);  //Table to edit
    var $ultFila = $tab_en_edic.find('tr:last'); // grab last tr from tbody
    $ultFila.clone().attr('class', '').addClass('real').appendTo($ultFila.parent()); // clone tr
    $tab_en_edic.find('tr:last').attr('id','editing');
    $ultFila = $tab_en_edic.find('tr:last'); // find freshly cloned tr
    var $cols = $ultFila.find('td');  // select td from tr

    makeRowEditable($cols);
    $ultFila.find('td:last').html(saveColHtml);
    params.onAdd();
}

function initializeSelect2() {
    $('.names-select').select2({
        tags: true,
        ajax: {
            url: '/projects/contracts/key_dates_names',
            dataType: 'json',
            delay: 250,
        },
    });
}

function updateDateTimePickers(value = '') {
    $('.datepicker').datetimepicker({
        format: 'DD.MM.YYYY',
        locale: 'ru',
        icons: {
            time: "fa fa-clock-o",
            date: "fa fa-calendar",
            up: "fa fa-chevron-up",
            down: "fa fa-chevron-down",
            previous: 'fa fa-chevron-left',
            next: 'fa fa-chevron-right',
            today: 'fa fa-screenshot',
            clear: 'fa fa-trash',
            close: 'fa fa-remove'
        },
        date: (value != '' ? moment(value, "DD.MM.YYYY") : null)
    });
}

function makeRowEditable($cols) {
    $cols.each(function () {
        if ($(this).attr('type') == 'input') {
            var div = '<div style="display: none;"></div>';
            var input = '<select class="input-editable names-select" style="width: 100%"></select>';
            $(this).html(div + input);

            initializeSelect2();
        } else if ($(this).attr('type') == 'number') {
            var div = '<div style="display: none;"></div>';
            var number = '<input class="input-editable" type="number" step="0.01" min="0" maxlength="10" onblur="validateNumberInputs(this)">';
            $(this).html(div + number);
        } else if ($(this).attr('type') == 'text') {
            var div = '<div style="display: none;"></div>';
            var text = '<textarea class="input-editable"></textarea>';
            $(this).html(div + text);
        } else if ($(this).attr('type') == 'from') {
            var div = '<div style="display: none;"></div>';
            var date = '<input id="dateFrom" name="dateFrom" type="text" class="form-control datepicker" placeholder="Выберите дату" autocomplete="off" onkeydown="return false">';
            $(this).html(div + date);

            updateDateTimePickers();
        } else if ($(this).attr('type') == 'to') {
            var div = '<div style="display: none;"></div>';
            var date = '<input id="dateTo" name="dateTo" type="text" class="form-control datepicker" placeholder="Выберите дату" autocomplete="off" onkeydown="return false">';
            $(this).html(div + date);

            updateDateTimePickers();
        }
    });
}

function IterarCamposEdit($cols, tarea) {
    // !!! tarea = function
    //Itera por los campos editables de una fila
    var n = 0;
    $cols.each(function() {
        n++;
        if ($(this).attr('name')=='buttons') return;  //excluye columna de botones
        if (!EsEditable(n-1)) return;   //noe s campo editable
        tarea($(this));
    });

    function EsEditable(idx) {
        //Indica si la columna pasada está configurada para ser editable
        if (colsEdi == null) {  //no se definió
            return true;  //todas son editable
        } else {  //hay filtro de campos
            for (var i = 0; i < colsEdi.length; i++) {
                if (idx == colsEdi[i]) return true;
            }
            return false;  //no se encontró
        }
    }
}

function FijModoNormal(but) {
    $(but).parent().find('#bAcep').hide();
    $(but).parent().find('#bCanc').hide();
    $(but).parent().find('#bEdit').show();
    $(but).parent().find('#bElim').show();
    $(but).parent().find('#bSub').show();
    var $row = $(but).parents('tr');  //accede a la fila
    $row.attr('id', '');  //quita marca
}

function FijModoEdit(but) {
    $(but).parent().find('#bAcep').show();
    $(but).parent().find('#bCanc').show();
    $(but).parent().find('#bEdit').hide();
    $(but).parent().find('#bElim').hide();
    $(but).parent().find('#bSub').hide();
    var $row = $(but).parents('tr');  //accede a la fila
    $row.attr('id', 'editing');  //indica que está en edición
}

function ModoEdicion($row) {
    if ($row.attr('id')=='editing') {
        return true;
    } else {
        return false;
    }
}

function rowEdit(but) {
    var $td = $(but).parent().parent();
    rowAcep($td);
    var $row = $(but).parents('tr');
    var $input = $row.find('td[type="input"]');
    var $number = $row.find('td[type="number"]');
    var $text = $row.find('td[type="text"]');
    var $from = $row.find('td[type="from"]');
    var $to = $row.find('td[type="to"]');
    var trLenght = $('.editable tr').length;

    if (ModoEdicion($row)) return;  //Ya está en edición

    IterarCamposEdit($input, function($td) {  //itera por la columnas
        var cont = $td.html(); //lee contenido
        var div = '<div style="display: none;">' + cont + '</div>';  //guarda contenido
        var input =
            '<select class="input-editable names-select" style="width: 100%">\n' +
            '<option value="' + $td.attr('class') + '" selected>' + cont + '</option>\n' +
            '</select>';

        $td.html(div + input);  //fija contenido

        initializeSelect2();
    });

    IterarCamposEdit($number, function($td) {  //itera por la columnas
        var cont = $td.html(); //lee contenido
        var div = '<div style="display: none;">' + cont + '</div>';  //guarda contenido
        var number = '<input class="input-editable" type="number" value="' + cont + '" onblur="validateNumberInputs(this)">';
        $td.html(div + number);  //fija contenido
    });

    IterarCamposEdit($text, function($td) {  //itera por la columnas
        var cont = $td.html(); //lee contenido
        var div = '<div style="display: none;">' + cont + '</div>';  //guarda contenido
        var text = '<textarea class="input-editable">' + cont + '</textarea>';
        $td.html(div + text);  //fija contenido
    });

    IterarCamposEdit($from, function($td) {  //itera por la columnas
        var cont = $td.html(); //lee contenido
        var div = '<div style="display: none;">' + cont + '</div>';  //guarda contenido
        var date = '<input type="text" id="dateFrom" class="form-control datepicker input-editable" value="' + cont + '" onkeydown="return false">';

        $td.html(div + date);

        updateDateTimePickers(cont);
    });

    IterarCamposEdit($to, function($td) {  //itera por la columnas
        var cont = $td.html(); //lee contenido
        var div = '<div style="display: none;">' + cont + '</div>';  //guarda contenido
        var to = '<input type="text" id="dateTo" class="form-control datepicker input-editable" value="' + cont + '" onkeydown="return false">';
        $td.html(div + to);  //fija contenido

        updateDateTimePickers(cont);
    });

    FijModoEdit(but);
}

function rowAcep(but) {
    //Acepta los cambios de la edición
    var $row = $(but).parents('tr');  //accede a la fila
    var $cols = $row.find('td');  //lee campos
    if (!ModoEdicion($row)) return;  //Ya está en edición
    var $input = $row.find('td[type="input"]');
    var value = $input.first().find('select').find('option:selected').text();

    if (value == '' && $($row).is('[class*=key_date_]')) {
        rowCancel(but);
    } else if (value == '') {
        return $($row).remove();
    }

    //Está en edición. Hay que finalizar la edición
    IterarCamposEdit($cols, function($td) {  //itera por la columnas
        $td.find('select').select2('destroy');
        var cont = $td.find('input').val();
        var select = $td.find('select').find('option:selected').text();
        var text = $td.find('textarea').val(); //lee contenido del input
        $td.html(select);
        $td.html(cont);
        $td.html(text);
    });

    if ($($row).is('[class*=sub_key_]')) {
        $row.find('td:last').html(newSubColHtml);
    } else {
        $row.find('td:last').html(newColHtml);
    }

    FijModoNormal(but);
    params.onEdit($row);
}

function rowCancel(but) {
    //Rechaza los cambios de la edición
    var $row = $(but).parents('tr');  //accede a la fila
    var $cols = $row.find('td');  //lee campos
    if (!ModoEdicion($row)) return;  //Ya está en edición

    var $input = $row.find('td[type="input"]');
    var value = $input.first().find('select').find('option:selected').text();

    if (value == '' || $($row).is('[class="real"]')) {
        $($row).remove();
        if ($($row).is('[class^=sub_key_]')) {
            return params.onSubDelete($row);
        } else {
            return params.onDelete();
        }
    }

    //Está en edición. Hay que finalizar la edición
    IterarCamposEdit($cols, function($td) {  //itera por la columnas
        var cont = $td.find('div').html(); //lee contenido del div
        $td.html(cont);  //fija contenido y elimina controles
    });

    FijModoNormal(but);
}

function rowElim(but) {
    //Elimina la fila actual
    var $row = $(but).parents('tr');  //accede a la fila

    if ($($row).is('[class^=sub_key_]')) {
        params.onBeforeSubDelete($row);
        $row.remove();
        params.onSubDelete($row);
    } else {
        params.onBeforeDelete($row);
        $row.remove();
        params.onDelete();
    }
}

function createSubKey(but) {
    var base_tr = _.first($('.example.d-none'));
    var parent_tr = _.first($(but).parent().parent());
    var parent_id = Number($(parent_tr).attr('class').split("_").pop()) ? $(parent_tr).attr('class').split("_").pop() : null;

    if (parent_id == null) return;

    var subKeyDatesCount = $('[class^=sub_key_' + parent_id + ']').length;

    if (subKeyDatesCount > 0) {
        var added_tr = $(base_tr).clone().attr('class', '').attr('id', 'editing').addClass('sub_key_' + parent_id).insertAfter($('[class^=sub_key_' + parent_id + ']')[subKeyDatesCount - 1]);
    } else {
        var added_tr = $(base_tr).clone().attr('class', '').attr('id', 'editing').addClass('sub_key_' + parent_id).insertAfter(parent_tr);
    }

    updateSubNumbers(parent_id);
    makeRowEditable($(added_tr).find('td'));
    $(added_tr).find('td:last').html(saveColHtml);
}

function updateNumbers() {
    var trSelector = $('tr.real');
    var num = 0;
    trSelector.each(function (index, elem) {
        num = index + 1;
        $(elem).first().find('td:first').text(num);
    });
}

function updateSubNumbers(parent_id) {
    var parent_num = Number($('tr.key_date_' + parent_id).find('td:first').text());
    var trSelector = $('tr.sub_key_' + parent_id);
    var num = 0;

    trSelector.each(function (index, elem) {
        num = num + 1;
        $(elem).first().find('td:first').text(parent_num + '.' + num);
    });
}

function removeSubKeys(parent_id) {
    $('.sub_key_' + parent_id).remove();
}

function validateNumberInputs(input) {
    var num = parseFloat($(input).val());
    var cleanNum = num.toFixed(2);
    $(input).val(cleanNum);
}
