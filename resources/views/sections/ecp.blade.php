<div class="modal fade bd-example-modal-lg" id="sign_com_offer" role="dialog" aria-labelledby="modal-search" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Заявка на КП</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <hr style="margin-top:0">
                <div class="card border-0" >
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <h6 style="margin:10px 0">Выберите сертификат</h6>
                                    <button type="button" onclick="refreshSelect()" class="btn btn-sm btn-success btn-outline">Обновить список сертификатов</button>
                                <select class="selectpicker" id="cert-selector" title="Сертификат не выбран">
                                    <option value="" hidden selected disabled>Обновите список сертификатов</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="getPdf()" class="btn btn-primary">Подписать и скачать</button>
            </div>
        </div>
    </div>
</div>

@push('js_footer')
<script src="{{ asset('js/plugins/cadesplugin_api.js') }}"></script>
<script language="javascript">
    var CADESCOM_CADES_BES = 1;
    var CAPICOM_CURRENT_USER_STORE = 2;
    var CAPICOM_MY_STORE = "My";
    var CAPICOM_STORE_OPEN_MAXIMUM_ALLOWED = 2;
    var CAPICOM_CERTIFICATE_FIND_SUBJECT_NAME = 1;
    var CADESCOM_BASE64_TO_BINARY = 1;

    CryptoPro = function(options) {

        this.window = window;

        // вы можете использовать любой другой plugin для реализации Promise
        this.q = Promise;

        // плагин движка подписи
        this.cadesplugin = this.window.cadesplugin;

        /**
         * Подписываем файл выбранным сертификатом
         * @param cert
         * @param data
         * @returns {*}
         */
        this.signCreate = function(cert, data) {

            var self = this;

            return self.cadesplugin.CreateObjectAsync("CAdESCOM.Store")
                .then(function(oStore){
                    self.oStore = oStore;
                    self.oStore.Open(self.cadesplugin.CAPICOM_CURRENT_USER_STORE, self.cadesplugin.CAPICOM_MY_STORE, self.cadesplugin.CAPICOM_STORE_OPEN_MAXIMUM_ALLOWED);
                    return self.oStore.Certificates;
                })
                .then(function(oCerts){
                    self.oCerts = oCerts;
                    return self.oCerts.Find(self.cadesplugin.CAPICOM_CERTIFICATE_FIND_SHA1_HASH, cert)
                        .then(function(certs){
                            self.oCerts = certs;
                            return certs.Count;
                        })
                        .then(function(count){
                            if (count < 1) {
                                return self.q.reject('Сертификат не найден');
                            }
                            return self.oCerts.Item(1);
                        });
                })
                .then(function(cert){

                    return self.cadesplugin.CreateObjectAsync('CAdESCOM.CPSigner')
                        .then(function(oSigner){
                            self.oSigner = oSigner;
                            return self.oSigner.propset_Certificate(cert);
                        })
                        .then(function(){
                            return self.cadesplugin.CreateObjectAsync('CAdESCOM.CadesSignedData');
                        })
                        .then(function(oSignedData){
                            self.oSignedData = oSignedData;
                            return self.oSignedData.propset_ContentEncoding(self.cadesplugin.CADESCOM_BASE64_TO_BINARY);
                        })
                        .then(function(){
                            return self.oSignedData.propset_Content(data);
                        })
                        .then(function(){
                            return self.oSignedData.SignCades(self.oSigner, self.cadesplugin.CADESCOM_CADES_BES)
                        })
                        .then(function(signature){
                            self.oStore.Close();
                            return signature;
                        })
                });
        };

        /**
         * Инициализация плагина
         */
        this.load = function(){
            this.oCerts = null;
            this.oStore = null;
            this.oSigner = null;
            this.oSignedData = null;
        };

        /**
         * типа конструктор
         */        this.load();
    };


    var ecp = '';
    function init_ecp() {
        //to store certs you need #cert-selector select
        //to set file you need #uploadedFile input

        window["cadesplugin"].then(() => {
                ecp = new CryptoPro();

                //retrieving certifitactes
                cadesplugin.async_spawn(function*(arg) {
                    var oStore = yield cadesplugin.CreateObjectAsync("CAdESCOM.Store");
                    yield oStore.Open(); //open store from flashdrive, it may take time

                    certs = yield oStore.Certificates; //get certs
                    certCnt = yield certs.Count; //strange thing


                    for (var i = 1; i <= certCnt; i++) { //iterate through them
                        var cert;
                        cert = yield certs.Item(i);

                        var name = yield cert.SubjectName;
                        var date = yield cert.ValidToDate;
                        var CN = yield name.substr(name.indexOf('CN'));

                        if (CN.indexOf('=', 4) != -1) {
                            CN = yield CN.substr(0, CN.indexOf('=', 4) - 4);
                        }

                        var value = yield cert.Thumbprint;

                        $('#cert-selector').append($('<option value="' + value + '">' + CN + ' До ' + date.substr(0, date.indexOf('T')) + '</option>'));
                        $('#cert-selector option:disabled').remove();
                        $('#cert-selector').selectpicker('refresh');

                    }

                    yield oStore.Close();
                });

            }
        ).catch(function (e) {
            swal({
                title: "Внимание",
                text: "Плагин КриптоПро ЭЦП web browser plug-in не обнаружен или настроен не правильно",
                type: 'warning',
            });
            console.log('err');
        });
    }

    //end sketches
</script>

<script>
function open_sign_modal() {
    if ({{ isset($task->id) ? 0 : 1 }} || ($('#form_solve_task').valid() && $('#status_result').val() == 'accept')) {
        init_ecp();
        $('#sign_com_offer').modal('show');
    }
}

function getPdf() {
    if ({{ isset($task->id) ? 0 : 1 }}  || ($('#form_solve_task').valid() && $('#status_result').val() == 'accept')) {
        fetch(["{{ asset('storage/docs/commercial_offers/' . ($commercial_offer->file_name ?? $com_offers->where('id', $task->target_id)->first()->file_name)) }}"])
            .then(r => r.blob())
            .then(convertToFile);
    } else {
        swal({
            title: "Неудача",
            text: "Заполните результат задачи. Подписывать можно только согласованные КП.",
            type: 'warning'
        });
    }
}

function refreshSelect() {
    $('#cert-selector').selectpicker('refresh');
}

$('#cert-selector').selectpicker();


function refreshSelect() {
    $('#cert-selector').selectpicker('refresh');
}
var oFile = '';

function run(oFile) {
    //to store certs you need #cert-selector select
    //to set file you need #uploadedFile input

    // var oFile = document.getElementById("uploadedFile").files[0];
    var oFReader = new FileReader();

    if (typeof(oFReader.readAsDataURL)!="function") {
        alert("Method readAsDataURL() is not supported in FileReader.");
        return;
    }

    oFReader.readAsDataURL(oFile);

    var sBase64Data = '';

    oFReader.onload = function(oFREvent) {
        var header = ";base64,";
        var sFileData = oFREvent.target.result;
        sBase64Data = sFileData.substr(sFileData.indexOf(header) + header.length);
        ecp.signCreate($('#cert-selector').val(), sBase64Data).then(function (signHash) {

            $.ajax({
                url:"{{ route('projects::commercial_offer::upload_signed_pdf', $commercial_offer->project_id ?? $task->project_id) }}", //SET URL
                type: 'POST', //CHECK ACTION
                data: {
                    _token: CSRF_TOKEN,
                    hash: signHash, //SET ATTRS
                    com_offer_id: {{ $commercial_offer->id ?? $task->target_id }},
                },
                dataType: 'JSON',
                success: function (data) {
                    $.ajax({
                        url:"{{ route('tasks::solve_task', $task->id ?? 0) }}", //SET URL
                        type: 'POST', //CHECK ACTION
                        data: {
                            _token: CSRF_TOKEN,
                            status_result: 'accept', //SET ATTRS
                            com_offer_id: {{ $commercial_offer->id ?? 0 }},
                            final_note: $('#result_note').val() ? $('#result_note').val() : 'Подписано ЭЦП.'
                        }
                    });
                    swal({
                        title: "Успешно",
                        text: "Документ был успешно подписан и сохранён в системе.",
                        type: 'success',
                    }).then(result => {
                        if (result.value) {
                            swal({
                                title: "Загрузка",
                                text: "Хотите скачать подписанный документ сейчас?",
                                type: 'info',
                                showCancelButton: 'true',
                                cancelButtonText : 'Нет',
                                showConfirmButton: 'true',
                                confirmButtonText: 'Да',
                                reverseButtons: true,
                            }).then(result => {
                                if (result.value) {
                                    download('data:' + oFile.type + ';base64,' + signHash, oFile.name, oFile.type);
                                }
                                // ($('#form_solve_task').length && $('#form_solve_task').valid()) ? $('#form_solve_task').submit() : console.log('form is not valid');
                                // console.log($('#form_solve_task').length);
                                // console.log($('#form_solve_task'));
                            }).then(result => {
                                location.reload();
                                $('#sign_com_offer_btn').hide();
                                $('#sign_com_offer').modal('hide');
                            });
                        }
                    });
                }
            });
        });
    };
}

function convertToFile(blob) {
    // It is necessary to create a new blob object with mime-type explicitly set
    // otherwise only Chrome works like it should
    var newBlob = new Blob([blob], {type: "application/pdf"})

    // IE doesn't allow using a blob object directly as link href
    // instead it is necessary to use msSaveOrOpenBlob
    if (window.navigator && window.navigator.msSaveOrOpenBlob) {
        window.navigator.msSaveOrOpenBlob(newBlob);
        return;
    }

    // For other browsers:
    // Create a link pointing to the ObjectURL containing the blob.
    const data = window.URL.createObjectURL(newBlob);

    run(newBlob);
}
</script>

@endpush
