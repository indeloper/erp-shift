@extends('layouts.app')

@section('title', 'Группы прав')

@section('url', route('users::department_permissions'))

@section('content')
@section('css_top')
<style>
  .custom-tree-node {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 16px;
    padding-right: 8px;
  }

  .el-tree-node__content {
    height: 35px!important;
}

@media (min-width: 320px) and (max-width:768px){

    .el-transfer-panel {
       width: 100%!important;
   }

   .el-transfer__buttons {
       transform:rotate(90deg);
       align-self: center;
   }

   .el-transfer {
       display: flex;
       flex-direction: column;
   }
}

@media (min-width: 767.9px){
    .el-transfer-panel {
       width: 40%!important;
   }

   .el-transfer {
       display: flex;
       justify-content: space-between;
   }
   .el-transfer__buttons {
       align-self: center;
   }
}

.el-dialog {
    max-width: 900px!important;
}
</style>

@endsection
<div class="row">
    <div class="col-md-12">
        <div class="nav-container" style="margin:0 0 10px 15px">
            <ul class="nav nav-icons" role="tablist">
                <li class="nav-item @if(Request::is('users::index')) show active @endif">
                    <a class="nav-link link-line @if (Request::is('users::index')) active-link-line @endif" href="{{ route('users::index') }}">
                        Список сотрудников
                    </a>
                </li>
                <li class="nav-item show active">
                    <a class="nav-link link-line active-link-line" href="{{ route('users::department_permissions') }}">
                        Группы прав
                    </a>
                </li>
            </ul>
        </div>
        <div class="card strpied-tabled-with-hover" >
            <div class="fixed-table-toolbar toolbar-for-btn text-center text-sm-left">
                <div class="fixed-search">
                    <form action="">
                        <input class="form-control" type="text" value="{{ Request::get('search') }}" name="search" placeholder="Поиск">
                    </form>
                </div>
                @if(strlen(Request::get('search')) > 0)
                    <a href="{{ route('users::department_permissions') }}" role="button"
                    class="btn btn-secondary btn-sm btn-outline m-0 d-block d-sm-inline-block">Сброс</a>
                @endif
            </div>
            <div class="table-responsive" id="departments">
                <table class="table table-hover mobile-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Подразделение</th>
                            <!-- <th>Руководитель отдела</th> -->
                            <th class="text-center">Количество сотрудников</th>
                            <th class="text-right">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($departments as $department)
                        <tr>
                            <td data-label="Номер">{{ $department->id }}</td>
                            <td  data-label="Подразделение"><a href="{{ route('users::group_permissions', $department->id) }}" target="_blank" class="table-link">{{ $department->name }}</a></td>
                            <!-- <td>
                                <a href="#" target="_blank" class="table-link" data-toggle="modal" data-target="#edit_access">{{ $department->users()->first()->full_name ?? '-' }}</a>
                            </td> -->
                            <td data-label="Кол-во сторудников" class="text-center">
                                {{ $department->users()->count() }}
                            </td>
                            <td data-label="Действия" class="text-right">
                                <button type="button" name="button" class="btn btn-link mn-0 pd-0" @click="setPermissions({{ $permissions->whereIn('id', $department->permission_ids($department->groups))->values()->pluck('id') }}, {{ $department->id }})"  data-toggle="modal" data-target="#permissions">
                                    <i class="fa fa-cog"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="col-md-12 fix-pagination">
                <div class="right-edge">
                    <div class="page-container">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade bd-example-modal-lg" id="permissions" role="dialog" aria-labelledby="modal-search" aria-hidden="true" style="padding-right:0">
    <div class="modal-dialog modal-lg permission-modal" role="document" style="margin: 0 auto">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Настройка прав подразделения</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card border-0">
                    <div class="card-body">
                        <div>
                            <el-transfer
                              filterable
                              :filter-method="filterMethod"
                              filter-placeholder=""
                              v-model="value"
                              :titles="['Список прав', 'Назначенные права']"
                              :data="permissions">
                            </el-transfer>
                        </div>
                    </div>
                </div>
                <div class="row" style="margin-top:50px">
                    <div class="col-md-12 text-center">
                        <el-button type="primary" @click="add_permissions" :loading="is_loading">Добавить права</el-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
@section('js_footer')
<script>
var permission = new Vue({
    el: '#permissions',
    data: {
        permissions: {!! json_encode($permissions) !!},
        value: [],
        is_loading: false,
        department_id: 1
    },
    methods: {
        filterMethod(query, item) {
          return item.label.toLowerCase().indexOf(query.toLowerCase()) > -1;
        },
        add_permissions() {
            permission.is_loading = true;

            axios.post("{{ route('users::add_permissions') }}", {type: 'department', permission_ids: permission.value, department_id: permission.department_id }).then(function (response) {
                permission.is_loading = false;
                location.reload();
            })
        }
    }
});

var groups = new Vue({
    el: '#departments',
    methods: {
        setPermissions(permission_ids, department_id) {
            permission.department_id = department_id;
            permission.value = permission_ids;
        }
    }
});
</script>
@endsection
