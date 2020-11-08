<button class="btn btn-outline btn-sm btn-primary edit-btn" onclick="makeProjectImportant({{ $project->id ?? $project->project_id }})">
    <i class="glyphicon fa fa-star"></i>
    {{ ! $project->is_important ? 'Отметить проект важным' : 'Снять важность проекта' }}
</button>

@push('js_footer')
    <script>
        function makeProjectImportant(project_id) {
            swal({
                title: '{{ ! $project->is_important ? 'Отметить проект важным?' : 'Снять важность проекта?' }}',
                type: 'question',
                showCancelButton: true,
                cancelButtonText: 'Назад',
                confirmButtonText: '{{ ! $project->is_important ? 'Отметить' : 'Снять' }}'
            }).then((result) => {
                if(result.value) {
                    $.ajax({
                        url: '{{ route('projects::importance_toggler') }}',
                        type: 'POST',
                        data: {
                            _token: CSRF_TOKEN,
                            project_id: project_id
                        },
                        dataType: 'JSON',
                        success: function() {
                            location.reload();
                        }
                    });
                }
            })
        }
    </script>
@endpush
