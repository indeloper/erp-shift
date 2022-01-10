@extends('layouts.app')

@section('title', 'Неисправности')

@section('url', route('building::tech_acc::defects.index'))

@section('css_top')
<style media="screen">
    .el-upload-list.el-upload-list--text {
        padding: 0 10px
    }

    .el-upload.el-upload--text {
        margin-left: 13px;
        margin-top: 5px;
    }
</style>
@endsection

@section('content')
<div class="request-container">
    <div class="row">
        <div class="col-md-8 col-xl-9 request-body col-sm-7" style="padding-bottom:10px; display: flex; flex-direction: column">
            <div class="card mb-10" id="mainInfo" v-cloak>
                <div class="card-body card-body-tech request-card">
                    <div class="row">
                        <div class="col-md-12">
                            <h1 class="request-title mb-30 mt-0"><span class="fw-500">#@{{ defect.id }}</span> Неисправность <span class="fw-500">@{{ defect.defectable.name }}</span></h1>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <h6 class="decor-h6-modal mt-10">Описание</h6>
                            <div class="dec-section">
                                <p class="p-light">
                                    @{{ defect.description }}
                                </p>
                            </div>
                            <h6 class="decor-h6-modal mt-40">Видеозаписи</h6>
                            <div class="dec-section">
                                <template v-if="defect.videos.length">
                                    <a v-for="video in defect.videos" href="#" class="btn btn-link btn-primary pd-0 mb-10">@{{ video.original_filename }}</a><br>
                                    {{-- I think we might have some method for video modals --}}
                                </template>
                                <template v-else>
                                    <p class="p-light">Прикреплённые видео отсутствуют</p>
                                </template>
                            </div>
                            <h6 class="decor-h6-modal mt-40">Фото</h6>
                            <div class="dec-section">
                                <template v-if="defect.photos.length">
                                    <div class="demo-image__preview">
                                        <el-image
                                            v-for="photo in defect.photos"
                                            class="small-img"
                                            :src="photo.source_link"
                                            :preview-src-list="srcList"
                                            :fit="fit"
                                            style="margin: 3px">
                                        </el-image>
                                    </div>
                                </template>
                                <template v-else>
                                    <p class="p-light">Прикреплённые фото отсутствуют</p>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-60">
                        <div class="col-md-12 text-right">
                            <template v-if="defect.responsible_user_id == {{ auth()->id() }} || {{ json_encode(auth()->user()->isProjectManager()) }}">
                                <el-button v-if="defect.status == 2" type="danger" size="small" plain @click.stop="decline_modal_show">Отклонить заявку</el-button>
                                <el-button v-if="defect.status == 3" type="primary" size="small" plain @click.stop="close_modal_show">Завершить ремонт</el-button>
                            </template>
                            <el-button v-if="canDestroyDefect(defect)" type="danger" size="small" plain @click.stop="removeDefect(defect.id)">Удалить заявку</el-button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mb-0 request-task-table" style="flex:1 0 auto;" id="tasks" v-if="tasks.length" v-cloak>
                <div class="card-body-tech">
                    <h6 class="req-h6 mb-20">Активные задачи</h6>
                    <div class="table-responsive">
                        <table class="table table-hover mobile-table">
                            <thead>
                                <tr>
                                    <th>Время создания</th>
                                    <th>Название</th>
                                    <th>Исполнитель</th>
                                    <th class="text-right"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="task in tasks">
                                    <td data-label="Время создания">
                                        <span :class="isWeekendDay(task.created_at_formatted, 'DD.MM.YYYY HH:mm') ? 'weekend-day' : ''">
                                            @{{ isValidDate(task.created_at_formatted, 'DD.MM.YYYY HH:mm') ? weekdayDate(task.created_at_formatted, 'DD.MM.YYYY HH:mm', 'DD.MM.YYYY dd HH:mm') : '-' }}
                                        </span>
                                    </td>
                                    <td data-label="Название">@{{ task.name }}</td>
                                    <td data-label="Исполнитель"><a class="table-link" :href="task.responsible_user.card_route" target="_blank">@{{ task.responsible_user.long_full_name }}</a></td>
                                    <td class="actions text-right">
                                        <template v-if="task.status == 26">
                                            <button type="button" name="button" class="btn btn-sx btn-link btn-primary mn-0 btn-space" data-toggle="modal" data-target="#executor">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                        </template>
                                        <template v-else-if="task.status == 33">
                                            <button type="button" name="button" class="btn btn-sx btn-link btn-primary mn-0 btn-space" data-toggle="modal" data-target="#control_defect">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                        </template>
                                        <template v-else-if="task.status == 35">
                                            <button type="button" name="button" class="btn btn-sx btn-link btn-primary mn-0 btn-space" data-toggle="modal" data-target="#control_repair">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                        </template>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xl-3 col-sm-5 col-side request-info" style="padding-bottom:10px" id="additionalInfo" v-cloak>
            <div class="card" style="height:100%">
                <div class="card-body">
                    <div class="sidebar-info-header text-right">
                        <div class="sidebar-info__text-unit">
                            <span class="sidebar-info__head-title">
                                Время создания
                            </span>
                            <span class="sidebar-info__body-title">
                                <span :class="isWeekendDay(defect.created_at_formatted, 'DD.MM.YYYY HH:mm') ? 'weekend-day' : ''">
                                    @{{ isValidDate(defect.created_at_formatted, 'DD.MM.YYYY HH:mm') ? weekdayDate(defect.created_at_formatted, 'DD.MM.YYYY HH:mm', 'DD.MM.YYYY dd HH:mm') : '-' }}
                                </span>
                            </span>
                        </div>
                        <div class="sidebar-info__text-unit">
                            <span class="sidebar-info__head-title">
                                Автор заявки
                            </span>
                            <span class="sidebar-info__body-title">
                                <a :href="defect.author.card_route" target="_blank">@{{ defect.author.long_full_name }}</a>
                            </span>
                        </div>
                        <hr>
                    </div>
                    <div class="sidebar-info-body">
                        <h6 class="decor-h6-modal mt-20 mb-20">Информация о заявке</h6>
                        <div class="dec-section">
                            <div class="sidebar-info__text-unit">
                                <span class="sidebar-info__head-title">
                                    Статус
                                </span>
                                <span class="sidebar-info__body-title">
                                    <span :class="`${getStatusClass(defect.status)}`">@{{ defect.status_name }}</span>
                                </span>
                            </div>
                            <div class="sidebar-info__text-unit">
                                <span class="sidebar-info__head-title">
                                    Исполнитель
                                </span>
                                <span class="sidebar-info__body-title">
                                    <template v-if="defect.responsible_user">
                                        <a :href="defect.responsible_user.card_route" target="_blank">@{{ defect.responsible_user.long_full_name }}</a>
                                    </template>
                                    <template v-else>
                                        <a class="text-danger" data-toggle="modal" data-target="#executor" href="">Ожидает назначения</a>
                                    </template>
                                </span>
                            </div>
                            <div class="sidebar-info__text-unit">
                                <span class="sidebar-info__head-title">
                                    Дата начала ремонта
                                </span>
                                <span class="sidebar-info__body-title">
                                    <template v-if="defect.repair_start_date">
                                        <span :class="isWeekendDay(defect.repair_start, 'DD.MM.YYYY') ? 'weekend-day' : ''">
                                            @{{ isValidDate(defect.repair_start, 'DD.MM.YYYY') ? weekdayDate(defect.repair_start, 'DD.MM.YYYY') : '-' }}
                                        </span>
                                    </template>
                                    <template v-else>
                                        <span class="text-danger">Не определена</span>
                                    </template>
                                </span>
                            </div>
                            <div class="sidebar-info__text-unit">
                                <span class="sidebar-info__head-title">
                                    Дата окончания ремонта
                                </span>
                                <span class="sidebar-info__body-title">
                                    <template v-if="defect.repair_end_date">
                                        <a @if($data['defect']->status == 3 and ($data['defect']->responsible_user_id == auth()->id() or auth()->user()->isProjectManager() )) data-toggle="modal" data-target="#repair_period_update" href=""@endif>
                                            <span :class="isWeekendDay(defect.repair_end, 'DD.MM.YYYY') ? 'weekend-day' : ''">
                                                @{{ isValidDate(defect.repair_end, 'DD.MM.YYYY') ? weekdayDate(defect.repair_end, 'DD.MM.YYYY') : '-' }}
                                            </span>
                                        </a>
                                    </template>
                                    <template v-else>
                                        <span class="text-danger">Не определена</span>
                                    </template>
                                </span>
                            </div>
                        </div>
                        <template v-if="defect.defectable.tank_number">
                            <h6 class="decor-h6-modal mt-30 mb-20">Информация о топливной ёмкости</h6>
                            <div class="dec-section">
                                <div class="sidebar-info__text-unit">
                                    <span class="sidebar-info__head-title">
                                        Номер топливной ёмкости
                                    </span>
                                    <span class="sidebar-info__body-title">
                                        @{{ defect.defectable.tank_number }}
                                    </span>
                                </div>
                                <div class="sidebar-info__text-unit">
                                    <span class="sidebar-info__head-title">
                                        Адрес объекта
                                    </span>
                                    <span class="sidebar-info__body-title">
                                        @{{ defect.defectable.object.short_name ? defect.defectable.object.short_name : defect.defectable.object.location }}
                                    </span>
                                </div>
                            </div>
                        </template>
                        <template v-else>
                            <h6 class="decor-h6-modal mt-30 mb-20">Информация о технике</h6>
                            <div class="dec-section">
                                <div class="sidebar-info__text-unit">
                                    <span class="sidebar-info__head-title">
                                        Инвентарный номер
                                    </span>
                                    <span class="sidebar-info__body-title">
                                        @{{ defect.defectable.inventory_number }}
                                    </span>
                                </div>
                                <div class="sidebar-info__text-unit">
                                    <span class="sidebar-info__head-title">
                                        Категория техники
                                    </span>
                                    <span class="sidebar-info__body-title">
                                        @{{ defect.defectable.category_name }}
                                    </span>
                                </div>
                                <div class="sidebar-info__text-unit">
                                    <span class="sidebar-info__head-title">
                                        Юридическое лицо
                                    </span>
                                    <span class="sidebar-info__body-title">
                                        @{{ defect.defectable.owner }}
                                    </span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row request-task-table_mobile" id="mobile_tasks" v-if="tasks.length" v-cloak>
        <div class="col-md-12" v-if="tasks.length > 0">
            <div class="card mb-10" style="flex:1 0 auto;">
                <div class="card-body-tech">
                    <h6 class="req-h6 mb-20">Активные задачи</h6>
                    <div class="table-responsive">
                        <table class="table table-hover mobile-table">
                            <thead>
                            <tr>
                                <th>Время создания</th>
                                <th>Название</th>
                                <th>Исполнитель</th>
                                <th class="text-right"></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="task in tasks">
                                <td data-label="Время создания">
                                    <span :class="isWeekendDay(task.created_at_formatted, 'DD.MM.YYYY HH:mm') ? 'weekend-day' : ''">
                                        @{{ isValidDate(task.created_at_formatted, 'DD.MM.YYYY HH:mm') ? weekdayDate(task.created_at_formatted, 'DD.MM.YYYY HH:mm', 'DD.MM.YYYY dd HH:mm') : '-' }}
                                    </span>
                                </td>
                                <td data-label="Название">@{{ task.name }}</td>
                                <td data-label="Исполнитель"><a class="table-link" :href="task.responsible_user.card_route" target="_blank">@{{ task.responsible_user.long_full_name }}</a></td>
                                <td class="actions text-right">
                                    <template v-if="task.status == 26">
                                        <button type="button" name="button" class="btn btn-sx btn-link btn-primary mn-0 btn-space" data-toggle="modal" data-target="#executor">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    </template>
                                    <template v-else-if="task.status == 33">
                                        <button type="button" name="button" class="btn btn-sx btn-link btn-primary mn-0 btn-space" data-toggle="modal" data-target="#control_defect">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    </template>
                                    <template v-else-if="task.status == 35">
                                        <button type="button" name="button" class="btn btn-sx btn-link btn-primary mn-0 btn-space" data-toggle="modal" data-target="#control_repair">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    </template>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card" id="activity" v-cloak>
                <div class="card-body card-body-tech request-card">
                    <h6 class="req-h6 mt-0 mb-30">Активности по заявке</h6>
                    <div class="row mt-20">
                        <div class="col-md-12">
                            <div v-if="defect.comments && defect.comments.length > 0">
                                <el-timeline class="activity-timeline">
                                    <el-timeline-item
                                        v-for="(comment, index) in comments"
                                        :key="index"
                                        :icon="`el-icon-check`"
                                        {{--:type="comment.type"
                                        :color="comment.color" --}}
                                        :size="`large`"
                                        :timestamp="isValidDate(comment.created_at_formatted, 'DD.MM.YYYY HH:mm') ? weekdayDate(comment.created_at_formatted, 'DD.MM.YYYY HH:mm', 'DD.MM.YYYY dd HH:mm') : '-'"
                                        placement="top"
                                    >
                                        <span class="activity-author"><a :href="comment.author.card_route">@{{ comment.author.long_full_name }}</a></span>
                                        <p class="activity-content" v-html="comment.pretty_comment"></p>
                                        <template v-if="comment.files.length > 0">
                                            <a href="#" class="activity-content_link mb-0" :data-target="'#comment-' + comment.id + '-files'" data-toggle="collapse">
                                                Приложенных файлов: @{{ comment.files.length }}
                                            </a>
                                            <div :id="'comment-' + comment.id + '-files'" class="card-collapse collapse mb-1">
                                                <template v-for="file in comment.files">
                                                    <a :href="'{{ asset('storage/docs/tech_accounting/') }}' + '/' + file.filename"
                                                       target="_blank" class="tech-link modal-link">
                                                        @{{ file.original_filename }}
                                                    </a>
                                                    <br/>
                                                </template>
                                            </div>
                                        </template>
                                        <span class="comment-actions" v-if="comment.author_id == {{ auth()->id() }} && comment.system == 0">
                                            <button type="button" name="button" class="btn btn-link" @click="editComment(comment.id)">
                                                <i class="fa fa-edit"></i>
                                                Редактировать
                                            </button>
                                            <button type="button" name="button" class="btn btn-link" @click="removeComment(comment.id)">
                                                <i class="fa fa-trash"></i>
                                                Удалить
                                            </button>
                                        </span>
                                    </el-timeline-item>
                                </el-timeline>
                            </div>
                            <div v-else class="task-info__text-unit">
                                Нет данных
                            </div>
                        </div>
                    </div>
                    @if(auth()->user()->hasPermission('tech_acc_defect_comments_create') or in_array(auth()->id(), [$data['defect']->responsible_user_id, $data['defect']->user_id]))
                        <div class="row">
                            <div class="col-md-12 mt-40">
                                <div class="comment-block">
                                    <template v-if="[1,2,3].includes(defect.status)">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6 class="decor-h6-modal border-0">@{{ edit_comment ? 'Редактирование комментария' : 'Комментарий' }}</h6>
                                            </div>
                                            <div v-if="edit_comment" class="col-md-6 text-right">
                                                <h6 class="decor-h6-modal border-0" style="text-transform: none">
                                                    <a href="#" class="tech-link modal-link" @click="resetCommentEdit">
                                                        Отмена
                                                    </a>
                                                </h6>
                                            </div>
                                        </div>

                                        <div class="row task-info__text-unit">
                                            <div class="col-md-12">
                                                <validation-provider rules="max:300" vid="defect-comment-input"
                                                             ref="defect-comment-input" v-slot="v">
                                                    <el-input
                                                        :class="v.classes"
                                                        type="textarea"
                                                        :rows="4"
                                                        maxlength="300"
                                                        id="defect-comment-input"
                                                        clearable
                                                        placeholder="Оставьте комментарий"
                                                        v-model="comment.text"
                                                    ></el-input>
                                                    <div class="error-message" style="padding-top: 0; margin-top: -2px;">@{{ v.errors[0] }}</div>
                                                </validation-provider>
                                                <div class="comment-files modal-section" :style="comment.files.length > 0 ? 'padding-bottom: 20px;' : ''">
                                                    <div class="row">
                                                        <div class="files col-12" id="comment-files">
                                                            <el-upload
                                                                action="{{ route('file_entry.store') }}"
                                                                :headers="{ 'X-CSRF-TOKEN': '{{ csrf_token() }}' }"
                                                                ref="comment_files_upload"
                                                                :limit="10"
                                                                :before-upload="beforeUpload"
                                                                :on-preview="handlePreview"
                                                                :on-remove="handleRemove"
                                                                :on-exceed="handleExceed"
                                                                :on-success="handleSuccess"
                                                                :on-error="handleError"
                                                                :file-list="file_list"
                                                                multiple
                                                            >
                                                                <el-button class="pull-right" icon="el-icon-upload" type="text" style="margin-bottom:0!important; padding: 8px 0!important;">Приложить файлы</el-button>
                                                            </el-upload>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row text-right">
                                            <div class="col-md-12">
                                                <el-button type="primary" size="small" @click.stop="addComment" :loading="comment_submit">@{{ edit_comment ? 'Сохранить' : 'Оставить комментарий' }}</el-button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if($data['tasks']->count())
@can('tech_acc_defects_responsible_user_assignment')
<!-- executor -->
<div class="modal fade bd-example-modal-wd show" id="executor" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-wd" role="document">
        <validation-observer ref="observer" :key="observer_key">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Назначение исполнителя на ремонт</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-20 mt-20">
                            <template>
                                <validation-provider rules="required" vid="executor-select"
                                         ref="executor-select" name="исполнитель" v-slot="v">
                                    <el-select v-model="executor"
                                       id="executor-select"
                                       :class="v.classes"
                                       :remote-method="search_responsible_users"
                                       @clear="search_responsible_users('')"
                                       remote
                                       clearable filterable
                                       id="responsible-user-select"
                                       placeholder="Поиск исполнителя"
                                    >
                                        <el-option
                                            v-for="item in executors"
                                            :key="item.id"
                                            :label="item.name"
                                            :value="item.id">
                                        </el-option>
                                    </el-select>
                                    <div class="error-message">@{{ v.errors[0] }}</div>
                                </validation-provider>
                            </template>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 text-center mt-30">
                            <div class="row justify-content-center mb-2">
                                <el-button @click.stop="submit" :loading="loading_submit" type="primary" class="btn btn-info">Сохранить</el-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </validation-observer>
    </div>
</div>
@endcan
    @if($data['defect']->status == 2 && ($data['defect']->responsible_user_id == auth()->id() or auth()->user()->isProjectManager() ))
        <!-- defect control -->
        <div class="modal fade bd-example-modal-lg show" id="control_defect" role="dialog" aria-labelledby="modal-search" style="display: none;">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Контроль неисправности</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body" id="form" v-cloak>
                        <div class="text-center">
                            <validation-observer ref="observer" :key="observer_key">
                                <div class="row">
                                    <div class="col-md-12 text-left">
                                        <label>Результат<span class="star">*</span></label>
                                        <div class="form-group">
                                            <validation-provider rules="required" vid="result-select"
                                                                 ref="result-select" name="результат" v-slot="v">
                                                <el-select :class="v.classes"
                                                           id="result-select"
                                                           required
                                                           v-model="type"
                                                           clearable filterable
                                                           placeholder="Результат"
                                                           @change="refresh()"
                                                >
                                                    <el-option
                                                        v-for="item in types"
                                                        :key="item.id"
                                                        :label="item.name"
                                                        :value="item.id">
                                                    </el-option>
                                                </el-select>
                                                <div class="error-message">@{{ v.errors[0] }}</div>
                                            </validation-provider>
                                        </div>
                                    </div>
                                </div>
                                <template v-if="type == 1">
                                    <div class="row">
                                        <div class="col-md-6 text-left">
                                            <label for="">Время начала ремонта<span class="star">*</span></label>
                                            <validation-provider rules="required" vid="start_date"
                                                                 ref="start_date" name="дата начала" v-slot="v">
                                                <el-date-picker
                                                    style="cursor:pointer"
                                                    :class="v.classes"
                                                    id="start_date"
                                                    v-model="start_date"
                                                    format="dd.MM.yyyy"
                                                    value-format="dd.MM.yyyy"
                                                    placeholder="Укажите время начала ремонта"
                                                    name="start_of_exploitation"
                                                    :picker-options="startDatePickerOptions"
                                                    @focus = "onFocus">
                                                </el-date-picker>
                                                <div class="error-message">@{{ v.errors[0] }}</div>
                                            </validation-provider>
                                        </div>
                                        <div class="col-md-6 text-left">
                                            <label for="">Время окончания ремонта<span class="star">*</span></label>
                                            <validation-provider rules="required" vid="end_date"
                                                                 ref="end_date" name="дата начала" v-slot="v">
                                                <el-date-picker
                                                    style="cursor:pointer"
                                                    :class="v.classes"
                                                    id="end_date"
                                                    v-model="end_date"
                                                    format="dd.MM.yyyy"
                                                    value-format="dd.MM.yyyy"
                                                    placeholder="Укажите время окончания ремонта"
                                                    name="start_of_exploitation"
                                                    :picker-options="endDatePickerOptions"
                                                    @focus = "onFocus">
                                                </el-date-picker>
                                                <div class="error-message">@{{ v.errors[0] }}</div>
                                            </validation-provider>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 mb-20 mt-20 text-left">
                                            <label for="">Комментарий<span class="star">*</span></label>
                                            <validation-provider rules="required|max:300" vid="comment-input"
                                                                 ref="comment-input" name="комментарий" v-slot="v">
                                                <el-input type="textarea"
                                                          :class="v.classes"
                                                          id="comment-input"
                                                          placeholder="Укажите обоснование даты ремонта"
                                                          maxlength="300"
                                                          v-model="comment"
                                                          :autosize="{ minRows:4, maxRows:6 }"
                                                          clearable
                                                ></el-input>
                                                <div class="error-message">@{{ v.errors[0] }}</div>
                                            </validation-provider>
                                        </div>
                                    </div>
                                </template>
                                <template v-else-if="type == 2">
                                    <div class="row">
                                        <div class="col-md-12 mb-20 mt-20 text-left">
                                            <label for="">Комментарий<span class="star">*</span></label>
                                            <validation-provider rules="required|max:300" vid="comment-input"
                                                                 ref="comment-input" name="комментарий" v-slot="v">
                                                <el-input type="textarea"
                                                          :class="v.classes"
                                                          id="comment-input"
                                                          placeholder="Укажите причину отклонения заявки"
                                                          maxlength="300"
                                                          v-model="comment"
                                                          :autosize="{ minRows:2, maxRows:5 }"
                                                          clearable
                                                ></el-input>
                                                <div class="error-message">@{{ v.errors[0] }}</div>
                                            </validation-provider>
                                        </div>
                                    </div>
                                </template>
                            </validation-observer>
                        </div>
                        <div class="row mt-30">
                            <div class="col-md-12 text-center">
                                <el-button type="primary" @click.stop="submit" :loading="submit_loading">Отправить</el-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($data['defect']->status == 3 && ($data['defect']->responsible_user_id == auth()->id() or auth()->user()->isProjectManager() ))
        <!-- repair control -->
        <div class="modal fade bd-example-modal-lg show" id="control_repair" role="dialog" aria-labelledby="modal-search" style="display: none;">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Контроль ремонта</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body" id="form" v-cloak>
                        <validation-observer ref="observer" :key="observer_key">
                            <div class="row">
                                <div class="col-md-12 text-left">
                                    <label>Результат<span class="star">*</span></label>
                                    <div class="form-group">
                                        <validation-provider rules="required" vid="result-select"
                                                             ref="result-select" name="результат" v-slot="v">
                                            <el-select :class="v.classes"
                                                       id="result-select"
                                                       required
                                                       v-model="type"
                                                       clearable filterable
                                                       placeholder="Результат"
                                                       @change="refresh()"
                                            >
                                                <el-option
                                                    v-for="item in types"
                                                    :key="item.id"
                                                    :label="item.name"
                                                    :value="item.id">
                                                </el-option>
                                            </el-select>
                                            <div class="error-message">@{{ v.errors[0] }}</div>
                                        </validation-provider>
                                    </div>
                                </div>
                            </div>
                            <template v-if="type == 1">
                                <div class="row">
                                    <div class="col-md-12 mb-20 mt-20">
                                        <label for="">Местоположение<span class="star">*</span></label>
                                        <validation-provider rules="required" v-slot="v" vid="location-select"
                                                             ref="location-select">
                                            <el-select v-model="tech_location_id"
                                                       :class="v.classes"
                                                       clearable filterable
                                                       id="location-select"
                                                       :remote-method="searchLocations"
                                                       @clear="searchLocations('')"
                                                       remote
                                                       name="object_id"
                                                       placeholder="Поиск объекта"
                                            >
                                                <el-option
                                                    v-for="item in tech_locations"
                                                    :key="item.id"
                                                    :label="item.name"
                                                    :value="item.id">
                                                </el-option>
                                            </el-select>
                                            <div class="error-message">@{{ v.errors[0] }}</div>
                                        </validation-provider>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 mb-20 mt-20">
                                        <label for="">Комментарий<span class="star">*</span></label>
                                        <validation-provider rules="required|max:300" vid="comment-input"
                                                             ref="comment-input" name="комментарий" v-slot="v">
                                            <el-input type="textarea"
                                                      :class="v.classes"
                                                      id="comment-input"
                                                      placeholder="Укажите комментарий"
                                                      maxlength="300"
                                                      v-model="comment"
                                                      :autosize="{ minRows:4, maxRows:6 }"
                                                      clearable
                                            ></el-input>
                                            <div class="error-message">@{{ v.errors[0] }}</div>
                                        </validation-provider>
                                    </div>
                                </div>
                            </template>
                            <template v-else-if="type == 2">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label for="">Время окончания ремонта<span class="star">*</span></label>
                                        <validation-provider rules="required" vid="end_date"
                                                             ref="end_date" name="дата окончания" v-slot="v">
                                            <el-date-picker
                                                style="cursor:pointer"
                                                :class="v.classes"
                                                id="end_date"
                                                v-model="end_date"
                                                format="dd.MM.yyyy"
                                                value-format="dd.MM.yyyy"
                                                placeholder="Укажите время окончания ремонта"
                                                name="start_of_exploitation"
                                                :picker-options="endDatePickerOptions"
                                                @focus = "onFocus">
                                            </el-date-picker>
                                            <div class="error-message">@{{ v.errors[0] }}</div>
                                        </validation-provider>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 mb-20 mt-20">
                                        <label for="">Комментарий<span class="star">*</span></label>
                                        <validation-provider rules="required|max:300" vid="comment-input"
                                                             ref="comment-input" name="комментарий" v-slot="v">
                                            <el-input type="textarea"
                                                      :class="v.classes"
                                                      id="comment-input"
                                                      placeholder="Укажите причину изменения периода ремонта"
                                                      maxlength="300"
                                                      v-model="comment"
                                                      :autosize="{ minRows:4, maxRows:6 }"
                                                      clearable
                                            ></el-input>
                                            <div class="error-message">@{{ v.errors[0] }}</div>
                                        </validation-provider>
                                    </div>
                                </div>
                            </template>
                        </validation-observer>
                        <div class="row mt-30">
                            <div class="col-md-12 text-center">
                                <el-button type="primary" @click.stop="submit" :loading="submit_loading">Отправить</el-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endif
<!-- video TODO check this too -->
{{--<div class="modal fade bd-example-modal-lg show" id="video" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Название видео</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <video width="100%" height="auto" controls="controls" style="border-radius:5px">
                   <source src="{{ asset('storage/videoplayback.mp4') }}" type='video/mp4; codecs="avc1.42E01E, mp4a.40.2"'>
                   <source src="{{ asset('storage/videoplayback.mp4') }}" type='video/webm; codecs="vp8, vorbis"'>
                   <source src="{{ asset('storage/videoplayback.mp4') }}" type='video/ogg; codecs="theora, vorbis"'>
                </video>
            </div>
        </div>
    </div>
</div>--}}
@if($data['defect']->status == 2 && ($data['defect']->responsible_user_id == auth()->id() or auth()->user()->isProjectManager()))
<!-- reject -->
<div class="modal fade bd-example-modal-lg show" id="decline" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
        <validation-observer ref="observer" :key="observer_key">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Отклонение заявки</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <label for="">Комментарий<span class="star">*</span></label>
                    <validation-provider rules="required|max:300" vid="comment-input"
                         ref="comment-input" name="комментарий" v-slot="v">
                        <el-input type="textarea"
                            :class="v.classes"
                            id="comment-input"
                            placeholder="Укажите причину отклонения заявки"
                            maxlength="300"
                            v-model="comment"
                            :autosize="{ minRows:2, maxRows:5 }"
                            clearable
                        ></el-input>
                        <div class="error-message">@{{ v.errors[0] }}</div>
                    </validation-provider>
                    <div class="row">
                        <div class="col-md-12 text-center mt-30">
                            <el-button type="danger" size="small" plain :loading="decline_loading" @click.stop="submit">Отклонить заявку</el-button>
                        </div>
                    </div>
                </div>
            </div>
        </validation-observer>
    </div>
</div>
@endif
@if($data['defect']->status == 3 and ($data['defect']->responsible_user_id == auth()->id() or auth()->user()->isProjectManager()))
<!-- close -->
<div class="modal fade bd-example-modal-lg show" id="close" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Завершение ремонта</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <validation-observer ref="observer" :key="observer_key">
                    <div class="row">
                        <div class="col-md-12 mb-20 mt-20">
                            <label for="">Местоположение<span class="star">*</span></label>
                            <validation-provider rules="required" v-slot="v" vid="location-select"
                                                 ref="location-select">
                                <el-select v-model="tech_location_id"
                                           :class="v.classes"
                                           clearable filterable
                                           id="location-select"
                                           :remote-method="searchLocations"
                                           @clear="searchLocations('')"
                                           remote
                                           name="object_id"
                                           placeholder="Поиск объекта"
                                >
                                    <el-option
                                        v-for="item in tech_locations"
                                        :key="item.id"
                                        :label="item.name"
                                        :value="item.id">
                                    </el-option>
                                </el-select>
                                <div class="error-message">@{{ v.errors[0] }}</div>
                            </validation-provider>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-20 mt-20">
                            <label for="">Комментарий<span class="star">*</span></label>
                            <validation-provider rules="required|max:300" vid="comment-input"
                                                 ref="comment-input" name="комментарий" v-slot="v">
                                <el-input type="textarea"
                                          :class="v.classes"
                                          id="comment-input"
                                          placeholder="Укажите комментарий"
                                          maxlength="300"
                                          v-model="comment"
                                          :autosize="{ minRows:4, maxRows:6 }"
                                          clearable
                                ></el-input>
                                <div class="error-message">@{{ v.errors[0] }}</div>
                            </validation-provider>
                        </div>
                    </div>
                </validation-observer>
                <div class="row mt-30">
                    <div class="col-md-12 text-center">
                        <el-button type="primary" @click.stop="submit" :loading="submit_loading">Отправить</el-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- repair period -->
<div class="modal fade bd-example-modal-wd show" id="repair_period_update" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-wd" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Новый период ремонтных работ</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <validation-observer ref="observer" :key="observer_key">
                    <div class="row">
                        <div class="col-md-12">
                            <label for="">Время окончания ремонта<span class="star">*</span></label>
                            <validation-provider rules="required" vid="end_date"
                                                 ref="end_date" name="дата окончания" v-slot="v">
                                <el-date-picker
                                    style="cursor:pointer"
                                    :class="v.classes"
                                    id="end_date"
                                    v-model="end_date"
                                    format="dd.MM.yyyy"
                                    value-format="dd.MM.yyyy"
                                    placeholder="Укажите время окончания ремонта"
                                    name="start_of_exploitation"
                                    :picker-options="endDatePickerOptions"
                                    @focus = "onFocus">
                                </el-date-picker>
                                <div class="error-message">@{{ v.errors[0] }}</div>
                            </validation-provider>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-20 mt-20">
                            <label for="">Комментарий<span class="star">*</span></label>
                            <validation-provider rules="required|max:300" vid="comment-input"
                                                 ref="comment-input" name="комментарий" v-slot="v">
                                <el-input type="textarea"
                                          :class="v.classes"
                                          id="comment-input"
                                          placeholder="Укажите причину изменения периода ремонта"
                                          maxlength="300"
                                          v-model="comment"
                                          :autosize="{ minRows:4, maxRows:6 }"
                                          clearable
                                ></el-input>
                                <div class="error-message">@{{ v.errors[0] }}</div>
                            </validation-provider>
                        </div>
                    </div>
                </validation-observer>
                <div class="row mt-30">
                    <div class="col-md-12 text-center">
                        <el-button @click.stop="submit" :loading="submit_loading" type="primary" size="small">Сохранить</el-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
<!-- files -->
{{-- TODO I'm found activity files modal!
<div class="modal fade bd-example-modal-lg show" id="activity-comment-files" role="dialog" aria-labelledby="modal-search" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Приложенные файлы</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body"  id="commentFiles">
                <h6 class="decor-h6-modal">Видеозаписи</h6>
                <div class="dec-section">
                    <video width="100%" height="auto" controls="controls" style="border-radius:5px">
                       <source src="{{ asset('storage/videoplayback.mp4') }}" type='video/mp4; codecs="avc1.42E01E, mp4a.40.2"'>
                       <source src="{{ asset('storage/videoplayback.mp4') }}" type='video/webm; codecs="vp8, vorbis"'>
                       <source src="{{ asset('storage/videoplayback.mp4') }}" type='video/ogg; codecs="theora, vorbis"'>
                    </video>
                </div>
                <h6 class="decor-h6-modal mt-40">Фото</h6>
                <div class="dec-section">
                    <template>
                        <div class="demo-image__preview">
                             <div v-for="fit in fits" :key="fit">
                                  <el-image
                                    class="small-img"
                                    :src="img1"
                                    :preview-src-list="srcList"
                                    :fit="fit">
                                </el-image>
                                <el-image
                                  class="small-img"
                                  :src="img2"
                                  :preview-src-list="srcList"
                                  :fit="fit">
                                </el-image>
                              </div>
                         </div>
                    </template>
                </div>
                <h6 class="decor-h6-modal mt-40">Документы</h6>
                <div class="dec-section">
                    <a href="#" class="btn btn-link btn-primary pd-0 mb-10"> Название документа </a><br>
                </div>
            </div>
        </div>
    </div>
</div>--}}
@endsection

@section('js_footer')
<script type="text/javascript">
    Vue.component('validation-provider', VeeValidate.ValidationProvider);
    Vue.component('validation-observer', VeeValidate.ValidationObserver);

    var mainInfo = new Vue({
        el: '#mainInfo',
        data: {
            defect: JSON.parse('{!! addslashes(json_encode($data['defect'])) !!}'),
            fit: 'cover',
            srcList : [],
        },
        mounted() {
            this.srcList = this.defect.photos.map(el => el.source_link);
        },
        methods: {
            decline_modal_show() {
                $('#decline').modal('show');
                $('.modal').css('overflow-y', 'auto');
                $('#decline').focus();
            },
            close_modal_show() {
                $('#close').modal('show');
                $('.modal').css('overflow-y', 'auto');
                $('#close').focus();
            },
            canDestroyDefect(defect) {
                auth_id = {{ auth()->id() }};

                return (defect.user_id === auth_id && defect.status === 1) ;
            },
            isWeekendDay(date, format) {
                return [5, 6].indexOf(moment(date, format).weekday()) !== -1;
            },
            isValidDate(date, format) {
                return moment(date, format).isValid();
            },
            weekdayDate(date, inputFormat, outputFormat) {
                return moment(date, inputFormat).format(outputFormat ? outputFormat : 'DD.MM.YYYY dd');
            },
            removeDefect(id) {
                if (!this.loading) {
                    swal({
                        title: 'Вы уверены?',
                        text: "Заявка на неисправность будет удалена!",
                        type: 'warning',
                        showCancelButton: true,
                        cancelButtonText: 'Назад',
                        confirmButtonText: 'Удалить'
                    }).then((result) => {
                        if (result.value) {
                            axios.delete('{{ route('building::tech_acc::defects.destroy', ['']) }}' + '/' + id)
                                .then(() => {
                                    location.reload();
                                })
                                //TODO add actual error handler
                                .catch(error => console.log(error));
                        }
                    });
                }
            },
        }
    });

    var additionalInfo = new Vue({
        el: '#additionalInfo',
        data: {
            defect: mainInfo.defect,
        },
        methods: {
            getStatusClass(status_id) {
                switch (status_id) {
                    case 1:
                        return 'text-success';
                    case 2:
                        return 'text-warning';
                    case 3:
                        return 'text-primary';
                    case 4:
                        return '';
                    case 5:
                        return 'text-danger';
                }
            },
            isWeekendDay(date, format) {
                return [5, 6].indexOf(moment(date, format).weekday()) !== -1;
            },
            isValidDate(date, format) {
                return moment(date, format).isValid();
            },
            weekdayDate(date, inputFormat, outputFormat) {
                return moment(date, inputFormat).format(outputFormat ? outputFormat : 'DD.MM.YYYY dd');
            },
        }
    });

    var tasks = new Vue({
        el: '#tasks',
        data: {
            tasks: JSON.parse('{!! addslashes(json_encode($data['tasks'])) !!}'),
        }
    });

    var mobileTasks = new Vue({
        el: '#mobile_tasks',
        data: {
            tasks: tasks.tasks,
        }
    });

    @if($data['tasks']->count())
        @can('tech_acc_defects_responsible_user_assignment')
            var executor = new Vue ({
                el: '#executor',
                data: {
                    executor: '',
                    executors: [],
                    loading_submit: false,
                    observer_key: 2
                },
                mounted() {
                    this.search_responsible_users('');
                },
                methods: {
                    search_responsible_users(query) {
                        if (query) {
                            axios.get('{{ route('users::get_users_for_tech_tickets') }}', {params: {
                                    group_ids: [46, 47, 48],
                                    q: query,
                                }})
                                .then(response => this.executors = response.data.map(el => ({
                                    name: el.label,
                                    id: el.code
                                })))
                                .catch(error => console.log(error));
                        } else {
                            axios.get('{{ route('users::get_users_for_tech_tickets') }}', { params: {
                                    group_ids: [46, 47, 48],
                                }})
                                .then(response => this.executors = response.data.map(el => ({
                                    name: el.label,
                                    id: el.code
                                })))
                                .catch(error => console.log(error));
                        }
                    },
                    submit() {
                        this.$refs.observer.validate().then(success => {
                            if (!success) {
                                const error_field_vid = Object.keys(this.$refs.observer.errors).find(el => this.$refs[el].errors.length > 0);
                                $('.modal').animate({
                                    scrollTop: $('#' + error_field_vid).offset().top
                                }, 1200);
                                $('#' + error_field_vid).focus();
                                return;
                            }

                            this.loading_submit = true;
                            axios.put('{{ route('building::tech_acc::defects.select_responsible', $data['defect']->id) }}', {
                                user_id: this.executor,
                            }).then((response) => {
                                location.reload();
                            }).catch(error => this.handleError(error));
                        });
                    }
                }
            });
        @endcan
        @if(($data['defect']->responsible_user_id == auth()->id() or auth()->user()->isProjectManager()) and $data['defect']->status == 2)
            var control = new Vue ({
                el: '#control_defect',
                data: {
                    comment: '',
                    submit_loading: false,
                    observer_key: 1,
                    type: null,
                    types: [
                        {id: 1, name: 'Подтвердить'},
                        {id: 2, name: 'Отклонить'},
                    ],
                    start_date: '',
                    end_date: '',
                    endDatePickerOptions: {
                        firstDayOfWeek: 1,
                        disabledDate: date => date < moment().startOf('date') || (control.start_date ? (date < moment(control.start_date, "DD.MM.YYYY")) : false),
                    },
                    startDatePickerOptions: {
                        firstDayOfWeek: 1,
                        disabledDate: date => date < moment().startOf('date') || (control.end_date ? (date > moment(control.end_date, "DD.MM.YYYY")) : date < moment().subtract(1, 'days')),
                    },
                },
                methods: {
                    submit() {
                        this.$refs.observer.validate().then(success => {
                            if (!success) {
                                const error_field_vid = Object.keys(this.$refs.observer.errors).find(el => this.$refs[el].errors.length > 0);
                                $('.modal').animate({
                                    scrollTop: $('#' + error_field_vid).offset().top
                                }, 1200);
                                $('#' + error_field_vid).focus();
                                return;
                            }

                            if (this.start_date && this.end_date) { return this.accept(); }
                            return this.decline();
                        });
                    },
                    decline() {
                        this.$refs.observer.validate().then(success => {
                            if (!success) {
                                const error_field_vid = Object.keys(this.$refs.observer.errors).find(el => this.$refs[el].errors.length > 0);
                                $('.modal').animate({
                                    scrollTop: $('#' + error_field_vid).offset().top
                                }, 1200);
                                $('#' + error_field_vid).focus();
                                return;
                            }

                            this.submit_loading = true;
                            const payload = {};
                            payload.comment = this.comment;
                            axios.put('{{ route('building::tech_acc::defects.decline', $data['defect']->id) }}', payload)
                                .then((response) => {
                                    location.reload();
                                });
                            // TODO add errors handling
                            /*.catch(error => this.handleError(error))*/
                        });
                    },
                    accept() {
                        this.$refs.observer.validate().then(success => {
                            if (!success) {
                                const error_field_vid = Object.keys(this.$refs.observer.errors).find(el => this.$refs[el].errors.length > 0);
                                $('.modal').animate({
                                    scrollTop: $('#' + error_field_vid).offset().top
                                }, 1200);
                                $('#' + error_field_vid).focus();
                                return;
                            }

                            this.submit_loading = true;
                            const payload = {};
                            payload.comment = this.comment;
                            payload.repair_start_date = this.start_date;
                            payload.repair_end_date = this.end_date;
                            payload.change_end_date = false;
                            axios.put('{{ route('building::tech_acc::defects.accept', $data['defect']->id) }}', payload)
                                .then((response) => {
                                    location.reload();
                                });
                            // TODO add errors handling
                            /*.catch(error => this.handleError(error))*/
                        });
                    },
                    onFocus: function() {
                        $('.el-input__inner').blur();
                    },
                    refresh() {
                        this.$refs.observer.reset();
                        this.comment = '';
                        this.submit_loading = false;
                        this.start_date = '';
                        this.end_date = '';
                    }
                }
            });
        @endif
    @endif

    @if($data['defect']->status == 2 && ($data['defect']->responsible_user_id == auth()->id() or auth()->user()->isProjectManager() ))
        var decline = new Vue({
            el: '#decline',
            data: {
                comment: '',
                decline_loading: false,
                observer_key: 1,
            },
            methods: {
                submit() {
                    this.$refs.observer.validate().then(success => {
                        if (!success) {
                            const error_field_vid = Object.keys(this.$refs.observer.errors).find(el => this.$refs[el].errors.length > 0);
                            $('.modal').animate({
                                scrollTop: $('#' + error_field_vid).offset().top
                            }, 1200);
                            $('#' + error_field_vid).focus();
                            return;
                        }

                        this.decline_loading = true;
                        const payload = {};
                        payload.comment = this.comment;
                        axios.put('{{ route('building::tech_acc::defects.decline', $data['defect']->id) }}', payload)
                        .then((response) => {
                            location.reload();
                        });
                        // TODO add errors handling
                        /*.catch(error => this.handleError(error))*/
                    });
                }
            }
        });
    @endif

    @if($data['defect']->status == 3 and ($data['defect']->responsible_user_id == auth()->id() or auth()->user()->isProjectManager() ))
        var updateRepair = new Vue ({
            el: '#repair_period_update',
            data: {
                comment: '',
                submit_loading: false,
                observer_key: 1,
                old_end_date: mainInfo.defect.repair_end,
                end_date: '',
                endDatePickerOptions: {
                    firstDayOfWeek: 1,
                    disabledDate: date => date < moment().startOf('date') || (updateRepair.old_end_date ? date < moment(updateRepair.old_end_date, "DD.MM.YYYY").add(1, 'days') : false),
                },
            },
            methods: {
                submit() {
                    this.$refs.observer.validate().then(success => {
                        if (!success) {
                            const error_field_vid = Object.keys(this.$refs.observer.errors).find(el => this.$refs[el].errors.length > 0);
                            $('.modal').animate({
                                scrollTop: $('#' + error_field_vid).offset().top
                            }, 1200);
                            $('#' + error_field_vid).focus();
                            return;
                        }

                        this.submit_loading = true;
                        const payload = {};
                        payload.comment = this.comment;
                        payload.repair_start_date = mainInfo.defect.repair_start;
                        payload.repair_end_date = this.end_date;
                        axios.put('{{ route('building::tech_acc::defects.update_repair_dates', $data['defect']->id) }}', payload)
                            .then((response) => {
                                location.reload();
                            });
                        // TODO add errors handling
                        /*.catch(error => this.handleError(error))*/
                    });
                },
                onFocus: function() {
                    $('.el-input__inner').blur();
                }
            }
        });

        var endRepair = new Vue ({
            el: '#close',
            data: {
                comment: '',
                submit_loading: false,
                observer_key: 7,
                tech_location_id: null,
                tech_locations: [],
            },
            created() {
                this.searchLocations('');
            },
            methods: {
                submit() {
                    this.$refs.observer.validate().then(success => {
                        if (!success) {
                            const error_field_vid = Object.keys(this.$refs.observer.errors).find(el => this.$refs[el].errors.length > 0);
                            $('.modal').animate({
                                scrollTop: $('#' + error_field_vid).offset().top
                            }, 1200);
                            $('#' + error_field_vid).focus();
                            return;
                        }

                        this.submit_loading = true;
                        const payload = {};
                        payload.comment = this.comment;
                        payload.start_location_id = this.tech_location_id;
                        axios.put('{{ route('building::tech_acc::defects.end_repair', $data['defect']->id) }}', payload)
                            .then((response) => {
                                location.reload();
                            });
                        // TODO add errors handling
                        //.catch(error => this.handleError(error))
                    });
                },
                onFocus: function() {
                    $('.el-input__inner').blur();
                },
                searchLocations(query) {
                    if (query) {
                        axios.post('{{ route('building::mat_acc::report_card::get_objects') }}', {q: query})
                            //TODO make field names in location objects equal (name, id in tech; label, code in following route)
                            .then(response => this.tech_locations = response.data.map(el => ({ name: el.label, id: el.code })))
                            //TODO add actual error handler
                            .catch(error => console.log(error));
                    } else {
                        axios.post('{{ route('building::mat_acc::report_card::get_objects') }}')
                            //TODO make field names in location objects equal (name, id in tech; label, code in following route)
                            .then(response => this.tech_locations = response.data.map(el => ({ name: el.label, id: el.code })))
                            //TODO add actual error handler
                            .catch(error => console.log(error));
                    }
                },
            }
        });

        var controlRepair = new Vue ({
            el: '#control_repair',
            data: {
                comment: '',
                submit_loading: false,
                observer_key: 6,
                type: null,
                types: [
                    {id: 1, name: 'Завершить ремонт'},
                    {id: 2, name: 'Увеличить срок ремонта'},
                ],
                end_date: '',
                tech_location_id: null,
                tech_locations: [],
                old_end_date: '',
                endDatePickerOptions: ''
            },
            created() {
                this.searchLocations('');
            },
            mounted() {
                this.old_end_date = mainInfo.defect.repair_end;
                this.endDatePickerOptions = {
                    firstDayOfWeek: 1,
                    disabledDate: date => date < moment().startOf('date') || (this.old_end_date ? date < moment(this.old_end_date, "DD.MM.YYYY").add(1, 'days') : false),
                };
            },
            methods: {
                submit() {
                    this.$refs.observer.validate().then(success => {
                        if (!success) {
                            const error_field_vid = Object.keys(this.$refs.observer.errors).find(el => this.$refs[el].errors.length > 0);
                            $('.modal').animate({
                                scrollTop: $('#' + error_field_vid).offset().top
                            }, 1200);
                            $('#' + error_field_vid).focus();
                            return;
                        }

                        if (this.end_date) { return this.update(); }
                        return this.close();
                    });
                },
                close() {
                    this.$refs.observer.validate().then(success => {
                        if (!success) {
                            const error_field_vid = Object.keys(this.$refs.observer.errors).find(el => this.$refs[el].errors.length > 0);
                            $('.modal').animate({
                                scrollTop: $('#' + error_field_vid).offset().top
                            }, 1200);
                            $('#' + error_field_vid).focus();
                            return;
                        }

                        this.submit_loading = true;
                        const payload = {};
                        payload.comment = this.comment;
                        payload.start_location_id = this.tech_location_id;
                        axios.put('{{ route('building::tech_acc::defects.end_repair', $data['defect']->id) }}', payload)
                            .then((response) => {
                                location.reload();
                            });
                        // TODO add errors handling
                        //.catch(error => this.handleError(error))
                    });
                },
                update() {
                    this.$refs.observer.validate().then(success => {
                        if (!success) {
                            const error_field_vid = Object.keys(this.$refs.observer.errors).find(el => this.$refs[el].errors.length > 0);
                            $('.modal').animate({
                                scrollTop: $('#' + error_field_vid).offset().top
                            }, 1200);
                            $('#' + error_field_vid).focus();
                            return;
                        }

                        this.submit_loading = true;
                        const payload = {};
                        payload.comment = this.comment;
                        payload.repair_start_date = mainInfo.defect.repair_start;
                        payload.repair_end_date = this.end_date;
                        payload.change_end_date = true;
                        axios.put('{{ route('building::tech_acc::defects.update_repair_dates', $data['defect']->id) }}', payload)
                            .then((response) => {
                                location.reload();
                            });
                        // TODO add errors handling
                        //.catch(error => this.handleError(error))
                    });
                },
                onFocus: function() {
                    $('.el-input__inner').blur();
                },
                refresh() {
                    this.$refs.observer.reset();
                    this.comment = '';
                    this.submit_loading = false;
                    this.end_date = '';
                    this.tech_location_id = '';
                },
                searchLocations(query) {
                    if (query) {
                        axios.post('{{ route('building::mat_acc::report_card::get_objects') }}', {q: query})
                            //TODO make field names in location objects equal (name, id in tech; label, code in following route)
                            .then(response => this.tech_locations = response.data.map(el => ({ name: el.label, id: el.code })))
                            //TODO add actual error handler
                            .catch(error => console.log(error));
                    } else {
                        axios.post('{{ route('building::mat_acc::report_card::get_objects') }}')
                            //TODO make field names in location objects equal (name, id in tech; label, code in following route)
                            .then(response => this.tech_locations = response.data.map(el => ({ name: el.label, id: el.code })))
                            //TODO add actual error handler
                            .catch(error => console.log(error));
                    }
                },
            }
        });
    @endif

    var activities = new Vue ({
        el: '#activity',
        data: {
            comments: mainInfo.defect.comments,
            defect: mainInfo.defect,
            comment: {
                text: '',
                files: [],
            },
            comment_id: null,
            file_list: [],
            files_to_remove: [],
            edit_comment: false,
            comment_submit: false
        },
        @if(auth()->user()->hasPermission('tech_acc_defect_comments_create') or in_array(auth()->id(), [$data['defect']->responsible_user_id, $data['defect']->user_id]))
            mounted() {
                $('#defect-comment-input').on('keydown', function (e) {
                    if (e.which == 13 && !e.shiftKey)
                    {
                        e.preventDefault();
                        activities.addComment();
                    }
                });
            },
            methods: {
                update(defect) {
                    this.defect = defect;
                    this.$forceUpdate();
                },
                isWeekendDay(date, format) {
                    return [5, 6].indexOf(moment(date, format).weekday()) !== -1;
                },
                isValidDate(date, format) {
                    return moment(date, format).isValid();
                },
                weekdayDate(date, inputFormat, outputFormat) {
                    return moment(date, inputFormat).format(outputFormat ? outputFormat : 'DD.MM.YYYY dd');
                },
                addComment() {
                    if (this.comment.text) {
                        this.$refs['defect-comment-input'].validate().then(result => {
                            if (result.valid) {
                                this.comment_submit = true;
                                if (!this.edit_comment) {
                                    axios.post('{{ route('comments.store') }}', {
                                        comment: this.comment.text,
                                        commentable_id: this.defect.id,
                                        commentable_type: this.defect.class_name,
                                        file_ids: this.comment.files.map(el => el.id),

                                    })
                                        .then((response) => {
                                            if (this.defect.comments) {
                                                this.defect.comments.push(response.data.data.comment);
                                            } else {
                                                this.$set(this.defect, 'comments', [response.data.data.comment]);
                                            }
                                            this.resetCommentEdit();
                                        })

                                        .catch((error) => { console.log(error) });
                                } else {
                                    //TODO change route
                                    axios.put('{{ route('comments.update', '') }}' + '/' + this.comment_id, {
                                        comment: this.comment.text,
                                        deleted_file_ids: this.files_to_remove,
                                        file_ids: this.comment.files.map(el => el.id),
                                    })
                                        .then((response) => {
                                            const comment_index = this.defect.comments.findIndex(el => el.id === response.data.data.comment.id);
                                            this.$set(this.defect.comments, comment_index, response.data.data.comment);
                                            this.resetCommentEdit();
                                        })
                                        .catch((error) => { console.log(error) });
                                }
                            }
                        });
                    }
                },
                editComment(id) {
                    this.edit_comment = true;
                    this.comment_id = id;
                    const comment_to_edit = this.defect.comments.find(el => el.id === id);
                    this.comment = {
                        text: comment_to_edit.comment,
                        files: comment_to_edit.files.slice(),
                    };
                    this.file_list = comment_to_edit.files.map(el => {el.name = el.original_filename; return el;});
                },
                resetCommentEdit() {
                    this.edit_comment = false;
                    this.comment_submit = false;
                    this.$refs.comment_files_upload.clearFiles();
                    this.comment = {
                        text: '',
                        files: [],
                    };
                    this.file_list = [];
                },
                removeComment(id) {
                    this.$confirm('Комментарий будет удален.', 'Вы уверены?', {
                        confirmButtonText: 'Удалить',
                        cancelButtonText: 'Отмена',
                        type: 'warning'
                    }).then(() => {
                        axios.delete('{{ route('comments.destroy', '') }}' + '/' + id)
                            .then((response) => {
                                this.defect.comments.splice(this.defect.comments.findIndex(el => el.id === id), 1);
                                this.resetCommentEdit();
                            })
                            .catch((error) => { console.log(error) });
                    }).catch(() => {});
                },
                handleRemove(file, fileList) {
                    if (file.hasOwnProperty('response')) {
                        this.files_to_remove.push(...file.response.data.map(el => el.id));
                        //this.comment.files.splice(this.comment.files.findIndex(el => el.response.data[0].id === file.response.data[0].id));
                    } else {
                        this.files_to_remove.push(file.id);
                    }
                },
                handleSuccess(response, file, fileList) {
                    file.url = file.response.data[0] ? '{{ asset('storage/docs/tech_accounting/') }}' + '/' + file.response.data[0].filename : '#';
                    this.comment.files.push(...file.response.data.map(el => el));
                },
                handleError(error, file, fileList) {
                    let message = '';
                    let errors = error.response.data.errors;
                    for (let key in errors) {
                        message += errors[key][0] + '<br>';
                    }
                    swal({
                        type: 'error',
                        title: "Ошибка загрузки файла",
                        html: message,
                    });
                },
                handlePreview(file) {
                    window.open(file.url ? file.url : '{{ asset('storage/docs/tech_accounting/') }}' + '/' + file.filename, '_blank');
                    $('#form_tech').focus();
                },
                handleExceed(files, fileList) {
                    this.$message.warning(`Невозможно загрузить ${files.length} файлов. Вы можете загрузить еще ${10 - fileList.length} файлов`);
                },
                beforeUpload(file) {
                    const FORBIDDEN_EXTENSIONS = ['exe'];
                    const FILE_MAX_LENGTH = 50000000;
                    const nameParts = file.name.split('.');
                    if (FORBIDDEN_EXTENSIONS.indexOf(nameParts[nameParts.length - 1]) !== -1) {
                        this.$message.warning(`Ошибка загрузки файла. Файл не должен быть исполняемым`);
                        return false;
                    }
                    if (file.size > FILE_MAX_LENGTH) {
                        this.$message.warning(`Ошибка загрузки файла. Размер файла не должен превышать 50Мб`);
                        return false;
                    }
                    return true;
                },
            }
        @endif
    });
</script>
<script type="text/javascript">
    var vm = new Vue ({
        el: '#base',
        mounted() {
            const that = this;
            $('.prerendered-date-time').each(function() {
                const date = $(this).text();
                const content = that.isValidDate(date, 'DD.MM.YYYY HH:mm:ss') ? that.weekdayDate(date, 'DD.MM.YYYY HH:mm:ss', 'DD.MM.YYYY dd HH:mm:ss') : '-';
                const innerSpan = $('<span/>', {
                    'class': that.isWeekendDay(date, 'DD.MM.YYYY HH:mm:ss') ? 'weekend-day' : ''
                });
                innerSpan.text(content);
                $(this).html(innerSpan);
            });
            $('.prerendered-date').each(function() {
                const date = $(this).text();
                const content = that.isValidDate(date, 'DD.MM.YYYY') ? that.weekdayDate(date, 'DD.MM.YYYY') : '-';
                const innerSpan = $('<span/>', {
                    'class': that.isWeekendDay(date, 'DD.MM.YYYY') ? 'weekend-day' : ''
                });
                innerSpan.text(content);
                $(this).html(innerSpan);
            });
        },
        methods: {
            isWeekendDay(date, format) {
                return [5, 6].indexOf(moment(date, format).weekday()) !== -1;
            },
            isValidDate(date, format) {
                return moment(date, format).isValid();
            },
            weekdayDate(date, inputFormat, outputFormat) {
                return moment(date, inputFormat).format(outputFormat ? outputFormat : 'DD.MM.YYYY dd');
            },
        }
    })
</script>
@endsection
