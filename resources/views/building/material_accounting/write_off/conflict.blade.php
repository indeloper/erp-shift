@extends('layouts.app')

@section('title', 'Материальный учет')

@section('url', '')

@section('css_top')
<style>
    .el-select {width: 100%}
    .el-date-editor.el-input {width: inherit;}
    .margin-top-15 {
        margin-top: 15px;
    }
    .el-input-number {
        width: inherit;
    }
</style>
@endsection

@section('content')

@include('building.material_accounting.modules.breadcrump')

@include('building.material_accounting.modules.operation_title')

<div class="row">
    <div class="col-md-12 col-xl-10 ml-auto mr-auto pd-0-min">
        @include('building.material_accounting.modules.info_about_materials')

        <div class="card strpied-tabled-with-hover" id="materials">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <h5 class="materials-info-title">Сведения о проблемах</h5>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <span style="margin-top:30px; padding:0 10px;font-size:14px;">
                            <span :class="[problem_description != 'Операция может быть выполнена' ? 'text-danger' : 'text-success']" >
                                <template>
                                    <ul>
                                      <li v-for="item in problem_description">
                                       @{{ item }}
                                      </li>
                                    </ul>
                                </template>
                            </span>
                        </span>
                    </div>
                </div>
                <div class="row" v-if="problem_description != 'Операция может быть выполнена'">
                    <div class="col-md-12" style="margin:10px 0 15px; padding:0 25px;font-size:14px">
                        <span>В данный момент операция не может быть выполнена. Необходимо отредактировать операцию или отменить её.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js_footer')

<script>
var materials = new Vue({
    el: '#materials',
    data: {
        problem_description: '',
    },
    mounted: function () {
        axios.post('{{ route('building::mat_acc::check_problem', $operation->id) }}').then(function (response) {
             materials.problem_description = response.data.result;
        })
    }
})

</script>

@endsection
