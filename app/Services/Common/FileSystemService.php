<?php

namespace App\Services\Common;

class FileSystemService {

    private $fileNames;

    public function __construct ($fileNames = [])
    {
        $this->fileNames = $fileNames;
    }



    public function getBladeTemplateFileNamesInDirectory($componentsPath, $baseBladePath)
    {
        $this->getFixedBladeTemplateFilesNames($baseBladePath);
        $this->getBladeTemplateComponentsFilesNames($componentsPath);
        return $this->fileNames;
    }

    public function getFixedBladeTemplateFilesNames($baseBladePath)
    {
        $cleanbaseBladePath = str_replace(resource_path().'/views/', '',  $baseBladePath );
        $cleanbaseBladePath = str_replace('.blade.php', '', $cleanbaseBladePath );
        $this->fileNames[] = $cleanbaseBladePath.'/dataSource';
        $this->fileNames[] = $cleanbaseBladePath.'/variables';
        $this->fileNames[] = $cleanbaseBladePath.'/methods';
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
