<?php

namespace App\Services\Common;

class FileSystemService {

    private $fileNames;

    public function __construct ($fileNames = [])
    {
        $this->fileNames = $fileNames;
    }



    public function getBladeTemplateFileNamesInDirectory($componentsPath, $basePath)
    {
        $this->getFixedBladeTemplateFilesNames($basePath);
        $this->getBladeTemplateComponentsFilesNames($componentsPath);
        return $this->fileNames;
    }

    public function getFixedBladeTemplateFilesNames($basePath)
    {
        $cleanBasePath = str_replace(resource_path().'/views/', '',  $basePath );
        $cleanBasePath = str_replace('.blade.php', '', $cleanBasePath );
        $this->fileNames[] = $cleanBasePath.'/dataSource';
        $this->fileNames[] = $cleanBasePath.'/variables';
        $this->fileNames[] = $cleanBasePath.'/methods';
    }

    public function getBladeTemplateComponentsFilesNames($componentsPath)
    {
        $dirElems = scanDir($componentsPath);

        foreach($dirElems as $dirElem) {
            if($dirElem === '.' || $dirElem === '..')
            continue;

            if(is_dir($componentsPath.'/'.$dirElem)) {
                $this->getBladeTemplateComponentsFilesNames($componentsPath.'/'.$dirElem);
            } else {
                $includeElem = str_replace(resource_path().'/views/', '',  $componentsPath.'/'.$dirElem );
                $includeElem = str_replace('.blade.php', '', $includeElem );
                $this->fileNames[] = $includeElem;
            }
        }


    }

}
