@extends('layouts.app')

@section('title', 'Учет техники')

@section('css_top')
    <style>
        [data-balloon],
        [data-balloon]:before,
        [data-balloon]:after {
            z-index: 9999;
        }
    </style>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12 mobile-card">
        <div class="card col-xl-10 mr-auto ml-auto pd-0-min" id="base" v-cloak>
            <div class="card-body card-body-tech" >
                <h4 class="h4-tech fw-500" style="margin-top:0">Техника</h4>
                <div class="fixed-table-toolbar toolbar-for-btn" style="padding-left:0">
                    <div class="row">
                        <div class="col-sm-6 col-md-4">
                            <input class="form-control" v-model="search_tf" type="text" placeholder="Поиск по наименованию">
                        </div>
                        <div class="col-sm-6 col-md-8 text-right mt-10__mobile">
                            @can('tech_acc_tech_category_create')
                                <a href="{{ route('building::tech_acc::technic_category.create') }}" class="btn btn-sm btn-primary btn-round" style="margin-right: 10px">Создать категорию</a>
                            @endcan
                            @can('tech_acc_tech_categories_trashed')
                                <a href="{{ route('building::tech_acc::technic_category.display_trashed') }}" class="float-right btn btn-outline btn-sm">Просмотр удалённых записей</a>
                            @endcan
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mobile-table">
                        <thead>
                            <tr>
{{--                                <th>#</th>--}}
                                <th>Категория</th>
                                <th class="text-center">Собств. техника ( свободно / занято ), шт</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                                <tr v-for="category in categories_filtered">
{{--                                    <td data-label="#">--}}
{{--                                        @{{ category.id }}--}}
{{--                                    </td>--}}
                                    <td data-label="Категория">
                                        <a :href="'{{ route('building::tech_acc::technic_category.show', '') }}' + '/' + category.id" class="tech-link">@{{ category.name }}</a>
                                    </td>
                                    <td class="text-center" data-label="Собств. техника ( свободно / занято ), шт">
                                        <a :href="'{{ route('building::tech_acc::technic_category.our_technic.index', 'category_id') }}'.split('category_id').join(category.id)" class="tech-link fw-500">@{{ category.technics_count }}</a> (
                                        <a :href="'{{ route('building::tech_acc::technic_category.our_technic.index', ['category_id', 'status' => 'Свободен']) }}'.split('category_id').join(category.id)" class="link-success fw-500"> @{{ category.free_technics_count }}</a> /
                                        <a :href="'{{ route('building::tech_acc::technic_category.our_technic.index', ['category_id', 'status' => ['В работе', 'Ремонт']]) }}'.split('category_id').join(category.id)" class="link-danger fw-500"> @{{ category.technics_count - category.free_technics_count }} </a> )
                                    </td>
                                    <td class="text-right actions">
                                        <a  data-balloon-pos="up"  aria-label="Просмотр категории" :href="'{{ route('building::tech_acc::technic_category.show', '')  }}' + '/' + category.id" class="btn btn-link btn-xs btn-space actions btn-primary mn-0">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        @can('tech_acc_tech_category_update')
                                            <a data-balloon-pos="up"  aria-label="Редактировать" :href="'{{ route('building::tech_acc::technic_category.edit', 'category_id') }}'.split('category_id').join(category.id)" class="btn btn-link btn-xs btn-space actions btn-success mn-0">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                        @endcan
                                        @can('tech_acc_tech_category_delete')
                                            <button data-balloon-pos="up"  aria-label="Удалить" class="btn btn-link btn-xs btn-space btn-danger mn-0" @click="deleteCategory(category.id)">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        @endcan
                                    </td>
                                </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('js_footer')
<script type="text/javascript">
    vm = new Vue({
        el: '#base',
        data: {
            search_tf: '',
            categories: JSON.parse('{!! addslashes(json_encode($technic_categories)) !!}'),
        },
        computed: {
            categories_filtered() {
                return this.categories.filter(category => {
                    //Search textfield filter
                    const search_tf_pattern = new RegExp(_.escapeRegExp(this.search_tf), 'i');
                    const search_tf_filter = search_tf_pattern.test(category.name);
                    return search_tf_filter;
                });
            }
        },
        methods: {
            @can('tech_acc_tech_category_delete')
            deleteCategory(id) {
                swal({
                    title: 'Вы уверены?',
                    text: "Категория будет удалена!",
                    type: 'warning',
                    showCancelButton: true,
                    cancelButtonText: 'Назад',
                    confirmButtonText: 'Удалить'
                }).then((result) => {
                    if (result.value) {
                        axios.delete(window.location + '/' + id).then(function () {
                            location.reload();
                        });
                    }
                });
            }
            @endcan
        }
    });
</script>
@endsection
