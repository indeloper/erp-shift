$("body").on("submit", "form", function() {
    if ($(this).first().attr('multisumit') != 'true') {
        $(this).submit(function() {
            return false;
        });
        return true;
    }
});

$('[rel="tooltip"]').tooltip({
    trigger : 'hover'
});

$.fn.hasAttr = function(name) {
    return this.attr(name) !== undefined;
};

function modalWork() {
    if ($('html').hasClass('nav-open')) {
        $('html').removeClass('nav-open');
        $('.close-layer').remove();
    }
    $('#modal_open').click();
}

function getFileName(input) {
    var fileExtension = ['jpeg', 'jpg', 'png', 'gif', 'bmp', 'txt', 'doc', 'docx', 'xls', 'xlsx', 'pdf', 'rtf', 'dwg', 'dwl', 'dwl2', 'dxf', 'mpp', 'pptx'];
    // get the files from input
    var files = $(input)[0].files;

    // check input multiple attr
    if ($(input).hasAttr('multiple')) {
        // check count of uploaded files
        if(files.length > 20){
            swal({
                title: "Внимание",
                text: "Можно прикрепить не более двадцати файлов",
                type: 'warning',
            });
            $(input).val('');
            $(input).parent().parent().siblings('#fileName')[0].innerHTML = '';

            return false;
        } else {
            // check each uploaded file extension
            for (var i = 0; i < files.length; i++) {
                if ($.inArray(files[i].name.split('.').pop().toLowerCase(), fileExtension) == -1) {
                    swal({
                        title: "Внимание",
                        text: "Поддерживаемые форматы: "+fileExtension.join(', '),
                        type: 'warning',
                    });
                    $(input).val('');
                    $(input).parent().parent().siblings('#fileName')[0].innerHTML = '';

                    return false;
                } else {
                    if(files.length === 1) {
                        $(input).parent().parent().siblings('#fileName')[0].innerHTML = 'Имя файла: ' + $(input).val().split('\\').pop();
                    } else {
                        $(input).parent().parent().siblings('#fileName')[0].innerHTML = 'Количество файлов: ' + files.length;
                    }
                }
            }

            return true;
        }
    } else {
        if ($.inArray($(input).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
            swal({
                title: "Внимание",
                text: "Поддерживаемые форматы: "+fileExtension.join(', '),
                type: 'warning',
            });
            $(input).val('');
            $(input).parent().parent().siblings('#fileName')[0].innerHTML = '';

            return false;
        } else {
            $(input).parent().parent().siblings('#fileName')[0].innerHTML = 'Имя файла: ' + $(input).val().split('\\').pop();

            return true;
        }
    }
}
function select_all() {
    $('input').on('click', function() {
        if( $(this).attr('pass') || $(this).attr('no-select')) {

        } else {
            $(this).select();
        }
    });
}
select_all();
