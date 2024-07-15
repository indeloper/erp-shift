@extends('layouts.app')

@section('title', 'Учет техники')

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
                            <a href="{{ route('building::tech_acc::technic_category.index') }}" class="float-right btn btn-outline btn-sm">Просмотр обычных записей</a>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mobile-table">
                        <thead>
                            <tr>
                                <th>Категория</th>
                                <th class="text-center">Собств. техника ( свободно / занято ), шт</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="category in categories_filtered">
                                <td data-label="Категория">
                                    <a :href="'{{ route('building::tech_acc::technic_category.show_trashed', '') }}' + '/' + category.id" class="tech-link">@{{ category.name }}</a>
                                </td>
                                <td class="text-center" data-label="Собств. техника ( свободно / занято ), шт">
                                    <a :href="'{{ route('building::tech_acc::technic_category.our_technic.trashed_index', 'category_id') }}'.split('category_id').join(category.id)" class="tech-link fw-500">@{{ category.trashed_technics_count }}</a> {{--( <a href="" class="link-success fw-500"> данных </a> / <a href="" class="link-danger fw-500"> совсем </a> )--}}
                                </td>
                                <td class="text-right actions">
                                    <a  rel="tooltip" data-original-title="Просмотр категории" :href="'{{ route('building::tech_acc::technic_category.show_trashed', '')  }}' + '/' + category.id" class="btn btn-link btn-xs btn-space actions btn-primary mn-0">
                                        <i class="fa fa-eye"></i>
                                    </a>
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
    });
</script>
@endsection
