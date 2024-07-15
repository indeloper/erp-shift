<div class="card chat-box card-success chat-card" id="mychatbox2" style="border: 0;border-radius: 0; height: 100vh; margin-bottom:0;">
    <div class="card-header chat-header" style="border-bottom: 1px solid #f7f7f7!important;
        position: absolute;
        z-index: 10;
        top: 60px;
        padding-bottom:10px">
        <div class="row">
            <div class="col-9" style="padding-top: 7px;padding-left: 40px;">
                <h4 style="margin:0">{{ $thread->subject ? $thread->subject : ($thread->participantsWithTrashed()->count() >= 3 ? 'Диалог с пользователями' : 'Диалог с пользователем') }}</h4>
            </div>
            <div class="col-3">
                <a class="nav-link dropdown-toggle chat-more pull-right" href="" id="dropdownChatLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" style="padding-top:0;font-size: 18px;">
                    <i class="fa fa-ellipsis-h"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownChatLink">
                    @if($thread->creator()->id == Auth::id())
                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#edit-thread">
                            <i class="fa fa-edit" style="margin-right:7px"></i> Изменить диалог
                        </a>
                    @endif
{{--                    <a class="dropdown-item" href="#">--}}
{{--                        <i class="fa fa-plus" style="margin-right:7px"></i> Добавить собеседника--}}
{{--                    </a>--}}
{{--                    <a class="dropdown-item" href="#">--}}
{{--                        <i class="fa fa-paperclip" style="margin-right:7px"></i> Показать вложения--}}
{{--                    </a>--}}
{{--                    <a class="dropdown-item" href="#">--}}
{{--                        <i class="fa fa-edit" style="margin-right:7px"></i> Изменить название--}}
{{--                    </a>--}}
                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#thread-participants">
                        <i class="fa fa-group" style="margin-right:7px"></i> Список участников
                    </a>
                    <div class="divider"></div>
                    @if($thread->creator()->id == Auth::id() and $thread->participantsWithTrashed()->count() >= 3)
                        <a href="#" class="dropdown-item text-danger" data-toggle="modal" data-target="#creator-leave">
                            <i class="fa fa-times" style="margin-right:7px"></i> Выйти из чата
                        </a>
                    @else
                        <a href="#" class="dropdown-item text-danger" onclick="askToLeave({{ $thread->id }})">
                            <i class="fa fa-times" style="margin-right:7px"></i> Выйти из чата
                        </a>
                    @endif
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-7">
{{--                <form id="">--}}
{{--                    <input type="text" class="form-control chat-input" placeholder="Поиск сообщений">--}}
{{--                </form>--}}
            </div>
            <div class="col-md-12 text-right action-chat-btns">
                <button id="delete_btn" class="btn btn-sm btn-danger btn-outline d-none" onclick="askToDelete()">
                    <i class="glyphicon fa fa-trash-o"></i>
                </button>
                <button id="edit_btn" class="btn btn-sm btn-outline btn-success d-none" onclick="askToEdit()">
                    <i class="glyphicon fa fa-edit"></i>
                </button>
                <button type="button" id="send" class="btn btn-info btn-outline d-none btn-sm" data-toggle="modal" data-target="#send-in-other-thread">
                    <i class=" fa fa-paper-plane-o "></i>
                </button>
                <button type="button" id="cancel" class="btn d-none btn-outline btn-sm" onclick="endActions()">
                    <i class="fa fa-ban"></i>
                    Отменить
                </button>
            </div>
        </div>

    </div>

    <div id="thread_messages" class="card-body chat-content thread_{{ $thread->id }}" tabindex="3" style="padding-top:50px!important; padding-left:20px;">
        @each('messages.partials.message', $thread->messages, 'message')
    </div>
    <div class="card-footer chat-form">
        <form id="send_message_form" class="form-horizontal" action="{{ route('messages::message_store', $thread->id) }}" method="post" enctype="multipart/form-data">
            @csrf
            <input type="submit" class="d-none" disabled>
            <input id="is_edit" type="hidden" name="edited_message_id" value="0">
{{--            <button type="button" class="btn btn-link btn-paperclip">--}}
{{--                <i class="fa fa-paperclip"></i>--}}
{{--            </button>--}}


            <div class="form-group" style="padding-right: 70px; position:relative; padding-top:7px">
                <textarea id="edited_message" class="form-control" placeholder="Напишите сообщение" name="message" required maxlength="7000" ></textarea>
                <div id="files-input" style="padding: 0 20px;">
                    <div class="file-container" style="border:none; max-width:100%; height:auto">
                        <div class="file-upload" style="width:30px; height:30px; background: transparent;position: absolute;top: 10px;left: 15px;z-index:10">
                            <label>
                                <span><i class="nc-icon nc-attach-87" style="font-size:23px; line-height: 30px; font-weight:bold"></i></span>
                                <input type="file" name="message_files[]" onchange="checkRequired(getFileName(this))" id="uploadedFile" multiple>
                            </label>
                        </div>
                        <div id="fileName" class="file-name" style="padding: 0px"></div>
                    </div>
                </div>
            </div>
            <button type="submit" id="submit" form="send_message_form" class="btn btn-link" style="top: 5px;right: 10px;transform: none;box-shadow: none;">
                <img src="{{ mix('img/send-btn.png') }}" width="22">
            </button>
        </form>
    </div>
    <div class="row">
        <button type="button" id="send" class="btn btn-info d-none" data-toggle="modal" data-target="#send-in-other-thread">Отправить в другой чат</button>
    </div>
</div>

<!-- Edit thread modal -->
<div class="modal fade bd-example-modal-lg show" id="edit-thread" role="dialog" aria-labelledby="modal-search" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Изменить диалог</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card border-0">
                    <div class="card-body">
                        <form id="update_thread_form" class="form-horizontal" action="{{ route('messages::thread_update', $thread->id) }}" method="post">
                            @csrf
                            <input type="submit" class="d-none" disabled>
                            <div class="row">
                                <label class="col-sm-3 col-form-label">Название диалога</label>
                                <div class="col-sm-9">
                                    <div class="form-group">
                                        <input class="form-control" type="text" name="name" maxlength="200" value="{{ $thread->subject }}">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-sm-3 col-form-label">Участники<star class="star">*</star></label>
                                <div class="col-sm-9">
                                    <div class="form-group select-accessible-140">
                                        <select id="js-select-user-edit" name="users_id[]" style="width:100%;" required multiple>
                                            @foreach($thread->users->where('id', '!=', $thread->creator()->id) as $user)
                                                <option selected value="{{ $user->id }}">{{ $user->long_full_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                <button type="submit" id="submit_form_edit" form="update_thread_form" class="btn btn-info">Создать</button>
            </div>
        </div>
    </div>
</div>

<!-- Thread participants modal -->
<div class="modal fade bd-example-modal-lg show" id="thread-participants" role="dialog" aria-labelledby="modal-search" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Участники диалога</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card border-0">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover mobile-table">
                                <thead>
                                <tr>
                                    <th>Участник</th>
                                    <th>Статус</th>
                                    <th class="td-0"></th>
                                </tr>
                                </thead>
                                <tbody class="related-messages-body">
                                @foreach($thread->participantsWithTrashed as $participant)
                                    <tr>
                                        <td>{{ $participant->user->long_full_name }}</td>
                                        <td>@if($participant->deleted_at != null) Неактивен @else Активен @endif</td>
                                        <td class="td-0"></td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
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

@if($thread->creator()->id == Auth::id() and $thread->participantsWithTrashed()->count() >= 3)
<!-- Modal for chat creator leaving -->
<div class="modal fade bd-example-modal-lg show" id="creator-leave" role="dialog" aria-labelledby="modal-search" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Выход из диалога</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card border-0">
                    <div class="card-body">
                        <form id="creator_leave_form" class="form-horizontal" action="{{ route('messages::creator_leave', $thread->id) }}" method="post">
                            @csrf
                            <input type="submit" class="d-none" disabled>
                            <div class="row">
                                <p class="col-sm-12" style="font-weight: 300">
                                    Вы уверены? <br>Вам будут недоступны сообщения из диалога, пока вы в него не вернётесь.
                                    Также вам необходимо выбрать своего заместителя для управления диалогом из числа участников
                                </p>
                            </div>
                            <div class="row">
                                <label class="col-sm-3 col-form-label">Заместитель<star class="star">*</star></label>
                                <div class="col-sm-9">
                                    <div class="form-group">
                                        <select id="substitute" name="substitute_user_id" style="width: 100%" required>
                                            <option value="">Выберите заместителя</option>
                                            @foreach($thread->users->where('id', '!=', $thread->creator()->id) as $user)
                                                <option value="{{ $user->id }}">{{ $user->long_full_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                <button type="submit" form="creator_leave_form" class="btn btn-info">Выйти</button>
            </div>
        </div>
    </div>
</div>
@endif

<script>
    var edit = 0;
    var selected_msgs = [];

    $(document).ready(function() {
        $('.main-panel').mouseenter(function() {
            $('.mess-thread_' + '{!! $thread->id !!}').addClass('d-none');
            readThread();
        });

        $('#send_message_form').submit(function(e) {
            e.preventDefault();

            var formData = new FormData(this);
            str = $('#edited_message').data("emojioneArea").getText();
            if ((! (str === null || str.match(/^ *$/) !== null || str.trim() === '')) || formData.has('message_files[]')) {
                var url = $(this).attr('action');
                var method = $(this).attr('method');
                formData.set('message', str);
                var is_edit = $('#is_edit').val();

                // if we forward some messages
                // we need to add them ids in form
                if (selected_msgs.length != 0) {
                    $.each(selected_msgs, function (key, message) {
                        formData.append('forward_messages_id[]', message.id)
                    });
                }

                // clear textarea/reset form
                $(this).trigger('reset');
                endActions();

                if (is_edit != 0) {
                    $.ajax({
                        method: method,
                        data: formData,
                        url: '{{ route('messages::update_message') }}',
                        processData: false,
                        contentType: false,
                        success: function (response) {
                            // update message in thread
                            $('.msg_id_' + response.message).replaceWith(response.html);
                        }
                    });
                } else {
                    $.ajax({
                        method: method,
                        data: formData,
                        url: url,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            var thread = $('#thread_messages');
                            var list = $('.list-thread-' + response.thread);

                            thread.append(response.html);
                            list.empty().append(response.list_item);
                            scrollToLatestMessage();
                        }
                    });
                }

                readThread();
            } else {
                $('#edited_message').data("emojioneArea").setText('');
            }
        });
    });

    function readThread() {
        $.ajax({
            url:'{{ route('messages::read_thread', $thread->id) }}',
            type: 'POST',
            data: {
                _token: CSRF_TOKEN,
            },
            dataType: 'JSON',
            success: function () {}
        });
    }

    $('#js-select-user-edit').select2({
        language: "ru",
        closeOnSelect: false,
        ajax: {
            url: '{{ route('messages::get_users') }}',
            dataType: 'json',
            delay: 250
        }
    })/*.on('select2:select', function (e) {
            checkValuesLengthEdit();
        }).on('select2:unselect', function (e) {
            checkValuesLengthEdit();
        })*/;

    /*function checkValuesLengthEdit()
    {
        values_length = $('#js-select-user').val().length;

        if (values_length >= 1) {
            $('#submit_form_edit').removeClass('d-none');
        } else {
            $('#submit_form').addClass('d-none');
        }
    }*/

    @if($thread->creator()->id == Auth::id() and $thread->participantsWithTrashed()->count() >= 3)
        $('#substitute').select2({
            language: "ru",
        });
    @else
        function askToLeave(thread_id) {
            swal({
                title: 'Вы уверены?',
                text: 'Вам будут недоступны сообщения из диалога, пока вы в него не вернётесь',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                cancelButtonText: 'Назад',
                confirmButtonText: 'Покинуть'
            }).then((result) => {
                if(result.value) {
                    $.ajax({
                        url:'{{ route('messages::leave_thread') }}',
                        type: 'POST',
                        data: {
                            _token: CSRF_TOKEN,
                            thread_id: thread_id,
                        },
                        dataType: 'JSON',
                        success: function () {
                            window.location.replace('{{ route('messages::index') }}');
                        }
                    });
                }
            });
        }
    @endif

    function checkFreshness(msg) {
        now = moment(new Date());
        message_created_at = moment(msg.created_at);
        duration = moment.duration(now.diff(message_created_at));
        minutes = duration.asMinutes().toFixed(0);

        return minutes;
    }

    function askToDelete() {
        // brutforce check
        if (selected_msgs.length == 0) return;
        // check each message freshness (younger than hour)
        formData = new FormData;
        formData.append('_token', CSRF_TOKEN);
        var minutes = [];
        $.each(selected_msgs, function (index, msg) {
            freshness = checkFreshness(msg);
            minutes.push(freshness);
            formData.append('messages[]', msg.id)
        });

        max = Math.max(...minutes);
        if (max <= 60) {
            swal({
                title: 'Вы уверены?',
                text: 'Сообщения будут удалены!',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                cancelButtonText: 'Назад',
                confirmButtonText: 'Удалить'
            }).then((result) => {
                if(result.value) {
                    $.ajax({
                        url:'{{ route('messages::delete_message') }}',
                        type: 'POST',
                        data: formData,
                        dataType: 'JSON',
                        processData: false,
                        contentType: false,
                        success: function () {
                            // remove each message from the page
                            $.each(selected_msgs, function (index, msg) {
                                $('.msg_id_' + msg.id).remove();
                            });

                            endActions();
                        }
                    });
                }
            });
        } else {
            swal({
                title: 'Сообщение нельзя удалить',
                text: 'Прошло больше часа с момента отправки',
                type: 'warning',
            });

            endActions();
        }
    }

    function askToEdit() {
        // brutforce check
        if (selected_msgs.length != 1) return;
        msg = selected_msgs[0];
        // check message freshness (younger than half hour)
        var minutes = checkFreshness(msg);

        if (minutes <= 30) {
            $('html, body').animate({
                scrollTop: $('#send_message_form').offset().top
            }, 'fast');

            message_text = msg.body.trim();
            $("#edited_message")[0].emojioneArea.setText(message_text);
            $('#is_edit').val(msg.id);
            $('#cancel').removeClass('d-none');

            $.ajax({
                url:'{{ route('messages::message_files') }}',
                type: 'POST',
                data: {
                    _token: CSRF_TOKEN,
                    message_id: msg.id
                },
                dataType: 'JSON',
                success: function(response) {
                    $('#files-input').append(response.html);
                }
            });
        } else {
            swal({
                title: 'Сообщение нельзя изменить',
                text: 'Прошло больше 30 минут с момента отправки',
                type: 'warning',
            });

            endActions();
        }
    }

    function removeFile(message_file_id, element) {
        $.ajax({
            url:'{{ route('messages::message_files_delete') }}',
            type: 'POST',
            data: {
                _token: CSRF_TOKEN,
                message_file_id: message_file_id
            },
            dataType: 'JSON',
            success: function (response) {
                // remove element from the page
                $(element).parent().remove();
                // update message
                $('.msg_id_' + response.message).replaceWith(response.html);
            }
        });
    }

    function selectMsg(message, div) {
        // check/uncheck div
        if ($(div).hasClass('chat-details-checked')) {
            $(div).removeClass('chat-details-checked');
            selected_msgs.splice($.inArray(message, selected_msgs),1);
        } else {
            $(div).addClass('chat-details-checked');
            selected_msgs.push(message);
        }

        user_ids = _.union(_.map(selected_msgs, 'user_id'));
        other_users = _.remove(user_ids, function (user_id) {
            return user_id != {!! Auth::id() !!};
        });

        // check checked tr count
        if ($('.chat-details-checked').length == 0) {
            endActions();
        } else {
            $('#cancel').removeClass('d-none');
            $('#send').removeClass('d-none');

            if (other_users.length == 0) {
                $('#edit_btn').removeClass('d-none');
                $('#delete_btn').removeClass('d-none');
            } else {
                $('#edit_btn').addClass('d-none');
                $('#delete_btn').addClass('d-none');
            }
        }

        if ($('.chat-details-checked').length > 1) {
            $('#edit_btn').addClass('d-none');
        }
        // console.log(selected_msgs)
    }

    function showAllRelatedMessages(message) {
        $.ajax({
            url:'{{ route('messages::show_related_messages') }}',
            type: 'POST',
            data: {
                _token: CSRF_TOKEN,
                message_id: message.id
            },
            dataType: 'JSON',
            success: function (response) {
                workArea = $('.related-messages-body');
                $(workArea).empty();
                // put html inside modal
                $(workArea).append(response.html);
                // open modal
                $('#related-messages').modal();
            }
        })
    }

    $("#edited_message").emojioneArea({
        search: false,
        events: {
            keyup: function(editor, event) {
                var text = this.getText();
                if(text.length > 7000){
                    this.setText(text.substring(0, 7000));
                }
                if (event.which == 13) {
                    event.preventDefault();
                    $("#send_message_form").submit();
                }
            }
        }
    });

    function checkRequired(getFileNameOutput) {
        // getFileNameOutput - boolean, output of getFileName function
        var elem = $('#edited_message');
        if (getFileNameOutput) return elem.removeAttr('required');
        else return elem.attr('required', 'required');
    }
</script>
<!-- General JS Scripts -->
<script src="{{ mix('js/plugins/jquery-nicescroll.min.js') }}"></script>
