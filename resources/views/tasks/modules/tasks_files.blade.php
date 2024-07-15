<div class="card">
   <div class="card-header">
       <h4 class="card-title">
           <a data-target="#collapseNOFiles" href="#" data-toggle="collapse">
               Приложенные файлы
               <b class="caret"></b>
           </a>
       </h4>
   </div>
   <div id="collapseNOFiles" class="card-collapse collapse show">
       <div class="card-body">
           <div class="card strpied-tabled-with-hover">
               @if(!$task_files->isEmpty())
               <div class="table-responsive">
                   <table class="table mini-table">
                       <thead>
                           <tr>
                               <th>Наименование</th>
                               <th>Автор</th>
                               <th>Дата загрузки</th>
                           </tr>
                       </thead>
                       <tbody>
                           @foreach($task_files as $task_file)
                               <tr>
                                   <td>
                                       <a target="_blank" href="{{ asset('storage/docs/task_files/' . $task_file->file_name) }}" class="table-link">
                                           {{ $task_file->original_name }}
                                       </a>
                                   </td>
                                   <td>
                                       <a href="{{ route('users::card', $task_file->user_id) }}" class="table-link">
                                           {{ $task_file->full_name }}
                                       </a>
                                   </td>
                                   <td>{{ $task_file->created_at }}</td>
                               </tr>
                           @endforeach
                       </tbody>
                   </table>
               </div>
               @else
                   <p class="text-center">Приложенные файлы не найдены</p>
               @endif
           </div>
       </div>
   </div>
</div>
