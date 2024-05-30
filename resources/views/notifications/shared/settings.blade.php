<button class="btn btn-round btn-outline btn-sm add-btn pull-right" style="margin-right: 10px;" data-toggle="modal" data-target="#notif_settings">
    <i class="fa fa-cog"></i>
    Настройка уведомлений
</button>

<!-- Notification settings modal -->
<div class="modal fade bd-example-modal-lg show" id="notif_settings" tabindex="-1" role="dialog" aria-labelledby="modal-search" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Настройка уведомлений</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 text-right mb-20">
                        <button type="button" name="button" class="btn btn-sm btn-round btn-outline" onclick="ban_notification()">
                            <i class="fa fa-ban"></i>
                            Отключить все уведомления
                        </button>
                    </div>
                </div>
                <form id="update_notifications" class="form-horizontal" action="{{ route('users::update_notifications') }}" method="post">
                    @csrf
                    <input type="hidden" name="disableAll" id="disableAll">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>Уведомления</th>
                                <th class="text-right">
                                    Телеграм
                                    <div class="form-check" style="display:inline-block;margin-bottom:0;">
                                        <label class="form-check-label">
                                            <input class="form-check-input" type="checkbox" id="check_all_telegram">
                                            <span class="form-check-sign th-check"></span>
                                        </label>
                                    </div>
                                </th>
                                <th class="text-right">
                                    Система
                                    <div class="form-check" style="display:inline-block;margin-bottom:0">
                                        <label class="form-check-label">
                                            <input class="form-check-input" type="checkbox" id="check_all_system">
                                            <span class="form-check-sign th-check"></span>
                                        </label>
                                    </div>
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($notification_types as $type)
                                <tr>
                                    <td>{{ $type->name }}</td>
                                    <td class="text-right">
                                        <div class="form-check">
                                            <label class="form-check-label">
                                                <input class="form-check-input telegram_check" type="checkbox" name="in_telegram[]" value="{{ $type->id }}" @if(! in_array($type->id, $disabled_in_telegram)) checked @endif>
                                                <span class="form-check-sign"></span>
                                            </label>
                                        </div>
                                    </td>
                                    <td class="text-right">
                                        <div class="form-check">
                                            <label class="form-check-label">
                                                <input class="form-check-input system_check" type="checkbox" name="in_system[]" value="{{ $type->id }}" @if(! in_array($type->id, $disabled_in_system)) checked @endif>
                                                <span class="form-check-sign"></span>
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                <button type="submit" form="update_notifications" class="btn btn-info">Сохранить</button>
            </div>
        </div>
    </div>
</div>