@extends('layouts.messages')

@section('title', 'Диалоги')

@section('url', route('messages::index'))

@section('css_top')
    <style>
        .selected_thread {
            background-color: #c4edef
        }

        .chat-content-row {
            height: 100vh;
        }

        /* @media (max-width: 767.9px){
            .chat-content-row {
                height: calc(100vh - 80px);
            }
        } */
    </style>
@endsection

@section('content')
    <div class="row chat-content-row" style="">
        <div class="col-md-12" id="thread_place" style="padding:0; align-self:center">
            <div class=" chat-box card-success chat-card" id="mychatbox2" style="border: 0;border-radius: 0;">
                <div class="row">
                    <div class="col-md-12">
                        <h5 style="font-size:1.3rem; text-align: center; color: #787878;padding: 0 20px;">Чтобы начать переписку, <a id="start-chat-mobile">выберите диалог</a> <br>

                        </h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create New Dialog Modal -->
    <div class="modal fade bd-example-modal-lg show" id="add-new-thread" role="dialog" aria-labelledby="modal-search" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Новый диалог</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="card border-0">
                        <div class="card-body ">
                            <form id="create_thread_form" class="form-horizontal" action="{{ route('messages::thread_store') }}" method="post">
                                @csrf
                                <div id="optional_name" class="row">
                                    <label class="col-sm-3 col-form-label">Название диалога</label>
                                    <div class="col-sm-9">
                                        <div class="form-group">
                                            <input id="name" class="form-control" type="text" name="name" maxlength="200">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label">Участники<star class="star">*</star></label>
                                    <div class="col-sm-9">
                                        <div class="form-group select-accessible-140">
                                            <select id="js-select-user" name="users_id[]" style="width:100%;" required multiple>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label">Сообщение<star class="star">*</star></label>
                                    <div class="col-sm-9">
                                        <div class="form-group">
                                            <textarea id="message_field" class="form-control textarea-rows" name="message" required maxlength="7000"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                    <button type="submit" id="submit" form="create_thread_form" class="btn btn-info btn-start-chat">Создать</button>
                </div>
            </div>
        </div>
    </div>

    <!-- View related message modal -->
    <div class="modal fade bd-example-modal-lg show" id="related-messages" role="dialog" aria-labelledby="modal-search" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Пересланные сообщения</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="card border-0">
                        <div class="card-body">
                            <div class="related-messages-body">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Send in other thread modal -->
    <div class="modal fade bd-example-modal-lg show" id="send-in-other-thread" role="dialog" aria-labelledby="modal-search" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Отправить в другой диалог</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="card border-0">
                        <div class="card-body">
                            <form id="send_in_other_thread_form" class="form-horizontal" action="{{ route('messages::send_messages') }}" method="post">
                                @csrf
                                <input type="submit" class="d-none" disabled>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label">Диалог<star class="star">*</star></label>
                                    <div class="col-sm-9">
                                        <div class="form-group select-accessible-140">
                                            <select name="thread_id" style="width:100%;" class="thread-select" data-title="Выберите диалог" data-style="btn-default btn-outline" data-menu-style="dropdown-blue" required>
                                                <option value="">Выберите диалог</option>
                                                @foreach(Auth::user()->threads as $thread)
                                                    @if($thread->hasParticipant(Auth::id()))
                                                        <option value="{{ $thread->id }}">
                                                            {{ $thread->subject ? $thread->subject : ($thread->participantsWithTrashed()->count() >= 3 ? 'Диалог с пользователями' : 'Диалог с ' . $thread->participantsString(Auth::id())) }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label">Сообщение<star class="star">*</star></label>
                                    <div class="col-sm-9">
                                        <div class="form-group">
                                            <textarea id="message_field" class="form-control textarea-rows" name="message" required maxlength="7000"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3 col-form-label">Файлы</label>
                                    <div class="col-sm-6">
                                        <div class="file-container">
                                            <div id="fileName" class="file-name"></div>
                                            <div class="file-upload">
                                                <label class="pull-right">
                                                    <span><i class="nc-icon nc-attach-87" style="font-size:25px; line-height: 40px"></i></span>
                                                    <input type="file" name="message_files[]" onchange="getFileName(this)" id="uploadedFile" multiple>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                    <button type="submit" form="send_in_other_thread_form" class="btn btn-info">Отправить</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js_footer')
    <script>
        function loadThread(thread_id, element)
        {
            $('.media-active').removeClass('media-active');
            $(element).addClass('media-active');
            $.ajax({
                method: 'POST',
                data: {
                    _token: CSRF_TOKEN,
                    thread_id: thread_id
                },
                url: '{{ route('messages::load_thread') }}',
                success: function (response) {
                    $('#thread_place').empty().append(response.html);
                    $('.mess-thread_' + thread_id).addClass('d-none');

                    scrollToLatestMessage();
                }
            });
        }

        @if (Request::has('thread'))
            $('.list-thread-' + '{{ Request::get('thread') }}').click();
        @endif

        $('#js-select-user').select2({
            language: "ru",
            ajax: {
                url: '/tasks/get-users',
                dataType: 'json',
                delay: 250
            }
        })/*.on('select2:select', function (e) {
            checkValuesLength();
        }).on('select2:unselect', function (e) {
            checkValuesLength();
        })*/;

        /*function checkValuesLength()
        {
            values_length = $('#js-select-user').val().length;
            alert(values_length)

            if (values_length >= 2) {
                $('#optional_name').removeClass('d-none');
                $('#name').attr('required', 'required');
            } else {
                $('#optional_name').addClass('d-none');
                $('#name').removeAttr('required');
            }
        }*/

        function askToJoin(thread_id) {
            swal({
                title: 'Вы уверены?',
                text: 'Вернуться в диалог?',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                cancelButtonText: 'Назад',
                confirmButtonText: 'Вернуться'
            }).then((result) => {
                if(result.value) {
                    $.ajax({
                        url:'{{ route('messages::join_thread') }}',
                        type: 'POST',
                        data: {
                            _token: CSRF_TOKEN,
                            thread_id: thread_id,
                        },
                        dataType: 'JSON',
                        success: function (response) {
                            window.location = response.route;
                        }
                    });
                }
            });
        }

        $("#message_field").emojioneArea({
            search: false,
            events: {
                keyup: function(editor, event) {
                    var text = this.getText();
                    if(text.length > 7000){
                        this.setText(text.substring(0, 7000));
                    }
                }
            }
        });

        $('.thread-select').select2();

        function endActions() {
            // reset form
            $('#send_message_form').trigger('reset');
            // reset emoji-textarea
            $("#edited_message")[0].emojioneArea.setText('');
            // reset filename input
            $('#fileName').empty();
            // no edit now
            $('#is_edit').val(0);
            // hide cancel button
            $('#cancel').addClass('d-none');
            // hide edit and delete button
            $('#edit_btn').addClass('d-none');
            $('#delete_btn').addClass('d-none');
            // remove file-list
            $('.file-list').remove();
            // uncolor selected tr's
            $('.chat-details-checked').removeClass('chat-details-checked');
            // hide send button
            $('#send').addClass('d-none');
            selected_msgs = [];
        }

        $(document).ready(function () {
            $('#send_in_other_thread_form').submit(function(e) {
                e.preventDefault();

                var url = $(this).attr('action');
                var method = $(this).attr('method');
                var formData = new FormData(this);

                // if we forward some messages
                // we need to add them ids in form
                if (selected_msgs.length != 0) {
                    $.each(selected_msgs, function (key, message) {
                        formData.append('forward_messages_id[]', message.id)
                    });
                }

                // clear textarea/reset form
                $(this).trigger('reset');
                $('#send-in-other-thread .close').click();
                endActions();

                $.ajax({
                    method: method,
                    data: formData,
                    url: url,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        window.location = response.route;
                    }
                });
            });
        });
    </script>
@endsection
