@extends('layouts.app')

@section('title', 'Группы прав')

@section('url', route('users::index'))

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
@section('content')
<div class="row">
    <div class="col-md-12">
        <div aria-label="breadcrumb" role="navigation">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('users::department_permissions') }}" class="table-link">Группы прав</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('users::group_permissions', $group->department_id) }}" class="table-link">{{ $department->name }}</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">{{ $group->name }}</li>
            </ol>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title" style="margin-bottom:10px">
                            Список прав доступа
                            <!-- <button type="button" name="button" class="btn btn-link mn-0 pd-0"  data-toggle="modal" data-target="#permissions">
                                <i class="fa fa-cog"></i>
                            </button> -->
                        </h4>
                        <div id="access">
                            <el-tree :data="data" :props="defaultProps" @node-click="handleNodeClick" :expand-on-click-node="false"></el-tree>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card strpied-tabled-with-hover">
            <div class="fixed-table-toolbar toolbar-for-btn">
                <div class="fixed-search">
                    <form action="">
                        <input class="form-control" type="text" value="{{ Request::get('search') }}" name="search" placeholder="Поиск">
                    </form>
                </div>
            </div>
            <div class="table-responsive" id="users">
                <table class="table table-hover mobile-table">
                    <thead>
                        <tr>
                            <th style="width: 50px">#</th>
                            <th>Сотрудник</th>
                            <th class="text-right">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($group->users as $key => $user)
                        <tr>
                            <td data-label="Номер">{{ $key + 1 }}</td>
                            <td data-label="Сотрудник"><a href="{{ route('users::card', $user->id) }}" target="_blank" class="table-link">{{ $user->full_name }}</a></td>
                            <td data-label="Действия" class="text-right">
                                <button type="button" name="button" class="btn btn-link mn-0 pd-0" @click="setPermissions({{ $user->user_permissions()->pluck('permission_id') }}, {{ $user->id }})"  data-toggle="modal" data-target="#permissions">
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
                <h5 class="modal-title">Настройка прав пользователя</h5>
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
<script type="text/javascript">
    var access = new Vue ({
        el:'#access',
        data() {
          return {
            data: [
                {
              label: 'Права отдела',
              children: {!! json_encode($department_perms) !!}
          },
          {
            label: 'Права должности',
            children: {!! json_encode($group_permissions) !!}
          }],
            defaultProps: {
              children: 'children',
              label: 'label'
            }
          };
        },
        methods: {
          handleNodeClick(data) {

          }
        }
    });

</script>
<script>
var permission = new Vue({
    el: '#permissions',
    data: {
        permissions: {!! json_encode($permissions) !!},
        value: [],
        is_loading: false,
        user_id: 1
    },
    methods: {
        filterMethod(query, item) {
          return item.label.toLowerCase().indexOf(query.toLowerCase()) > -1;
        },
        add_permissions() {
            permission.is_loading = true;

            axios.post("{{ route('users::add_permissions') }}", {type: 'user', permission_ids: permission.value, user_id: permission.user_id }).then(function (response) {
                permission.is_loading = false;
                location.reload();
            })
        }
    }
});

var users = new Vue({
    el: '#users',
    methods: {
        setPermissions(permission_ids, user_id) {
            permission.user_id = user_id;
            permission.value = permission_ids;
        }
    }
});
</script>
@endsection
