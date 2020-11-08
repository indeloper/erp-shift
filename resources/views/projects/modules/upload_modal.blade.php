<div class="modal fade bd-example-modal-lg show" id="save-offer" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Коммерческое предложение</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <hr style="margin-top:0">
                <div class="card border-0" >
                    <form id="attach_document" class="axios" @submit.prevent="preSubmitCheck" method="post" action="{{ route('projects::commercial_offer::upload', [isset($project) ? $project->id : ! isset($commercial_offer) ?: $commercial_offer->project_id ]) }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" id="uploaded_CO_type" name="is_tongue" value="">
                        <div class="row" style="margin-top:20px">
                            <label class="col-sm-5 col-form-label">Объем работ<span class="star">*</span></label>
                            <div class="col-sm-7" id="select_upload_tongue_block" style="display:none">
                                <select name="com_offer_id_tongue" id="select_com_offer_upload_tongue" style="width:100%" v-model="com_offer_id_tongue">
                                    @if(isset($com_offers_options))
                                        <option value="new">Новое коммерческое предложение</option>

                                        @foreach($com_offers_options->where('is_tongue', 1) as $offer)
                                        <option value="{{ $offer->id }}">{{ $offer->option ? $offer->option: ' id: ' . $offer->id  }}</option>
                                        @endforeach
                                    @else
                                    <option value="{{ $commercial_offer->id }}">{{ $commercial_offer->option ? $commercial_offer->option: ' id: ' . $commercial_offer->id  }}</option>
                                    @endif
                                </select>
                            </div>
                            <div class="col-sm-7" id="select_upload_pile_block"  style="display:none">
                                <select name="com_offer_id_pile" id="select_com_offer_upload_pile" style="width:100%" v-model="com_offer_id_pile">
                                    @if(isset($com_offers_options))
                                        <option value="new">Новое коммерческое предложение</option>

                                        @foreach($com_offers_options->where('is_tongue', 0) as $offer)
                                        <option value="{{ $offer->id }}">{{ $offer->option ? $offer->option: ' id: ' . $offer->id  }}</option>
                                        @endforeach
                                    @else
                                    <option value="{{ $commercial_offer->id }}">{{ $commercial_offer->option ? $commercial_offer->option: ' id: ' . $commercial_offer->id  }}</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                         <div class="row" id="com_offer_option" @if(!isset($com_offers_options)) style="display: none;" @endif>
                             <label class="col-sm-5 col-form-label">Наименование<span class="star">*</span></label>
                             <div class="col-sm-7">
                                 <input placeholder="Наименование" id="com_offer_option_input" @if(isset($com_offers_options)) required @endif name="option" class="form-control" max="50" v-model="option">
                             </div>
                         </div>
                        <div class="row" id="file" style="margin-top:10px">
                            <label class="col-sm-5 col-form-label" for="" style="font-size:0.80">
                                Коммерческое предложение
                            </label>
                            <div class="col-sm-7">
                                <div class="file-container">
                                    <div id="fileName5" class="file-name"></div>
                                    <div class="file-upload ">
                                        <label class="pull-right">
                                            <span><i class="nc-icon nc-attach-87" style="font-size:25px; line-height: 40px"></i></span>
                                            <input type="file" name="commercial_offer" accept="*" id="uploadedFile5" class="form-control-file file">
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row" id="file_budget" style="margin-top:10px">
                            <label class="col-sm-5 col-form-label" for="" style="font-size:0.80">Бюджет</label>
                            <div class="col-sm-7">
                                <div class="file-container">
                                    <div id="fileName" class="file-name"></div>
                                    <div class="file-upload ">
                                        <label class="pull-right">
                                            <span><i class="nc-icon nc-attach-87" style="font-size:25px; line-height: 40px"></i></span>
                                            <input type="file" name="budget" accept="*" id="uploadedFile" onchange="getFileName(this)" class="form-control-file file">
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check form-check-radio">
                                    <label class="form-check-label">
                                        <input class="form-check-input" type="radio" value="1" name="negotiation_type" required v-model="negotiation_type">
                                        <span class="form-check-sign"></span>
                                        <span class="label-check" style="text-transform:none; font-size:14px">
                                            Добавить как актуальное
                                            <button type="button" name="button" class="btn btn-link btn-primary btn-xs mn-0 pd-0" data-container="body"
                                                    data-toggle="popover" data-placement="top" data-content="Стандартный порядок согласования КП">
                                                <i class="fa fa-info-circle"></i>
                                            </button>
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-check-radio">
                                    <label class="form-check-label">
                                        <input class="form-check-input" type="radio" value="2" name="negotiation_type" v-model="negotiation_type">
                                        <span class="form-check-sign"></span>
                                        <span class="label-check" style="text-transform:none; font-size:14px">
                                            Добавить как архивное
                                            <button type="button" name="button" class="btn btn-link btn-primary btn-xs mn-0 pd-0" data-container="body"
                                                    data-toggle="popover" data-placement="top" data-content="КП перейдёт в статус Согласовано с заказчиком, процесс составления договора не начнётся">
                                                <i class="fa fa-info-circle"></i>
                                            </button>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="row" style="margin-top: 50px">
                    <div class="col-md-12 text-center">
                        <button type="button" id="submit_form_attach_document" @click="preSubmitCheck" form="attach_document" class="btn btn-info btn-outline" disabled>Сохранить</button> <!-- Кнопка появляется после загрузки или формирования документа -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js_footer')
<script>
var commercialOfferExistenceChecker = new Vue({
    el: '#save-offer',
    data: {
        com_offer_id_tongue: 'new',
        com_offer_id_pile: 'new',
        option: '',
        negotiation_type: ''
    },
    mounted() {
        $('#select_com_offer_upload_tongue').select2();
        $('#select_com_offer_upload_pile').select2();

        $('#select_com_offer_upload_tongue, #select_com_offer_upload_pile').on('change', function() {
            if ($(this).val() == 'new') {
                $('#com_offer_option').show();
                $('#com_offer_option_input').attr('required');
            } else {
                $('#com_offer_option').hide();
                $('#com_offer_option_input').removeAttr('required');
            }
        });

        $('#select_com_offer_upload_tongue').on('change', function() {
            commercialOfferExistenceChecker.com_offer_id_tongue = $(this).val();
            commercialOfferExistenceChecker.option = $('#select_com_offer_upload_tongue option:selected').text();
        });

        $('#select_com_offer_upload_pile').on('change', function() {
            commercialOfferExistenceChecker.com_offer_id_pile = $(this).val();
            commercialOfferExistenceChecker.option = $('#select_com_offer_upload_pile option:selected').text();
        });
    },
    methods: {
        preSubmitCheck(submitEvent) {
            payload = {};
            payload.is_tongue = $('#uploaded_CO_type').val();
            payload.com_offer_id_tongue = commercialOfferExistenceChecker.com_offer_id_tongue;
            payload.com_offer_id_pile = commercialOfferExistenceChecker.com_offer_id_pile;
            payload.option = commercialOfferExistenceChecker.option;
            payload.negotiation_type = commercialOfferExistenceChecker.negotiation_type;
            payload.axios = true;

            axios.post('{{ route('projects::commercial_offer::upload', [isset($project) ? $project->id : ! isset($commercial_offer) ?: $commercial_offer->project_id ]) }}', payload)
                .then(function (response) {
                    commercialOfferExistenceChecker.$off('submit');
                    $("#attach_document").removeClass('axios');
                    document.getElementById("attach_document").submit();
                })
                .catch(function (request) {
                    var errors = Object.values(request.response.data.errors);

                    errors.forEach(function (error, key) {
                        setTimeout(function () {
                            commercialOfferExistenceChecker.$message({
                                showClose: true,
                                message: error[0],
                                type: 'error',
                                duration: 5000
                            });
                        }, (key + 1) * 100);
                    });
                });
        },
    }
});

$('input#uploadedFile5').change(function(){
    var files = $(this)[0].files;
    var fileExtension = ['jpeg', 'jpg', 'png', 'gif', 'bmp', 'txt', 'doc', 'docx', 'xls', 'xlsx', 'pdf', 'rtf', 'dwg', 'dwl', 'dwl2', 'dxf', 'mpp', 'pptx'];

    if ($.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
        swal({
            title: "Внимание",
            text: "Поддерживаемые форматы: "+fileExtension.join(', '),
            type: 'warning',
        });
        $(this).val('');
        $(this).parent().parent().siblings('#fileName5')[0].innerHTML = '';

        return false;
    } else {
        document.getElementById('fileName5').innerHTML = 'Количество файлов: ' + files.length;
        if(files.length === 1) {
            document.getElementById('fileName5').innerHTML = 'Имя файла: ' + $('#uploadedFile5').val().split('\\').pop();
            $('#submit_form_attach_document').removeAttr('disabled');
        }
    }
});
</script>
@endpush
