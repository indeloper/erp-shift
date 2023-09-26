<?php 
    $componentsPath = resource_path().'/views/project_object_documents/mobile/components';
 
    $componentFiles = [];
 
    function scandirFunc($path) 
    {
        $dirElems = scanDir($path);

        foreach($dirElems as $dirElem) {
            if($dirElem === '.' || $dirElem === '..')
            continue;

            if(is_dir($path.'/'.$dirElem)) {
                scandirFunc($path.'/'.$dirElem);
            } else {
                $includeElem = str_replace(resource_path().'/views/', '',  $path.'/'.$dirElem );
                $includeElem = str_replace('.blade.php', '', $includeElem );
                $componentFiles[] = $includeElem;
            }
        }

        foreach($componentFiles as $componentFile)
        echo '<br>'.$componentFile;

        // var_dump( $componentFiles);
    }

    scandirFunc($componentsPath);

    
    echo '<br><br><br><br>';

?>

@include('project_object_documents.dataSource')
@include('project_object_documents.variables')
@include('project_object_documents.methods')

@include('project_object_documents.mobile.components.popupNewDocumentFormContentTemplate')
@include('project_object_documents.mobile.components.popupContentTemplate')
@include('project_object_documents.mobile.components.reusableElements.popupContentTemplateInfoModuleStatusOptions')
@include('project_object_documents.mobile.components.popupContentTemplateInfoModuleStatusSelectBox')
@include('project_object_documents.mobile.components.popupContentTemplateInfoModule')
@include('project_object_documents.mobile.components.popupContentTemplateHistoryModule')
@include('project_object_documents.mobile.components.reusableElements.popupContentTemplateFilesModuleUploader')
@include('project_object_documents.mobile.components.popupContentTemplateFilesModuleList')
@include('project_object_documents.mobile.components.popupContentTemplateFilesModule')
@include('project_object_documents.mobile.components.coreComponent')




