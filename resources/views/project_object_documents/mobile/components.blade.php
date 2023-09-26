@include('project_object_documents.dataSource')
@include('project_object_documents.variables')
@include('project_object_documents.methods')

@foreach($components as $component)
    @include($component)
@endforeach

