
@if(!$project_docs->isEmpty())
<div class="card">
   <div class="card-header">
       <h4 class="card-title">
           <a data-target="#collapseFive" href="#" data-toggle="collapse" class="" aria-expanded="true">
               Проектная документация
               <b class="caret"></b>
           </a>
       </h4>
   </div>
   <div id="collapseFive" class="card-collapse collapse" style="">
       <div class="card-body card-body-table">
           <div class="card strpied-tabled-with-hover">
               @if(!$project_docs->isEmpty())
               <div class="table-responsive">
                   <table class="table table-hover">
                       <thead>
                           <tr>
                               <th>Название</th>
                               <th class="text-center">Дата добавления</th>
                               <th>Автор</th>
                               <th class="text-center">
                                   Версия</th>
                           </tr>
                       </thead>
                       <tbody>
                           @foreach($project_docs as $document)
                               <tr class="header">
                                   <td data-target=".doc-collapse{{$document->id}}" data-toggle="collapse" class="collapsed tr-pointer" aria-expanded="false">
                                       {{ $document->name }}
                                   </td>
                                   <td class="text-center">{{ $document->updated_at }}</td>
                                   <td><a @if($document->user_full_name) href="{{ route('users::card', $document->user_id) }}" @endif class="table-link">{{ $document->user_full_name ? $document->user_full_name : 'Опросный лист' }}</a></td>
                                   <td class="text-center">{{ $document->version }}</td>
                                   <td class="td-actions text-right">
                                       <a target="_blank" href="{{ asset('storage/docs/project_documents/' . $document->file_name) }}" rel="tooltip" class="btn-default btn-link btn-xs" data-original-title="Просмотр">
                                           <i class="fa fa-eye"></i>
                                       </a>
                                   </td>
                               </tr>
                               <tr>
                                   @foreach($extra_documents->where('project_document_id', $document->id) as $extra_document)
                                       <tr class="doc-collapse{{$document->id}} contact-note card-collapse collapse">
                                           <td></td>
                                           <td class="text-center">{{ $extra_document->created_at }}</td>
                                           <td>
                                               <a href="{{ route('users::card', $document->user_id) }}" class="table-link">{{ $extra_document->user_full_name }}</a>
                                           </td>
                                           <td class="text-center">{{ $extra_document->version }}</td>
                                           <td class="td-actions text-right">
                                               <a target="_blank" href="{{ asset('storage/docs/project_documents/' . $extra_document->file_name) }}" rel="tooltip" class="btn-default btn-link btn-xs" data-original-title="Просмотр">
                                                   <i class="fa fa-eye"></i>
                                               </a>
                                           </td>
                                       </tr>
                                   @endforeach
                               </tr>
                           @endforeach
                       </tbody>
                   </table>
               </div>
               @else
                   <p class="text-center">Документы не найдены</p>
               @endif
                <!-- <div class="col-md-12">
                   <div class="right-edge">
                       <div class="page-container">
                           <button class="btn btn-sm show-all">
                               Показать все
                           </button>
                       </div>
                   </div>
               </div> -->
           </div>
       </div>
   </div>
</div>
@endif