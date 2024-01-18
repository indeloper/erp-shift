<?php

namespace App\Services\Common;

use Illuminate\Support\Facades\Route;

class FileSystemService {

    private $fileNames;

    public function __construct ($fileNames = [])
    {
        $this->fileNames = $fileNames;
    }

    public function getBladeTemplateFileNamesInDirectory($componentsPath, $baseBladePath, $needAttachments = false)
    {
        $this->getFixedBladeTemplateFilesNames($baseBladePath);
        // if(is_dir($baseBladePath . '/attachments')) {
        //     $this->getBladeTemplateComponentsFilesNames($baseBladePath . '/attachments');
        // }

        $this->getBladeTemplateComponentsFilesNames(resource_path().'/views/1_base/assets', $needAttachments);
        $this->getBladeTemplateComponentsFilesNames($componentsPath);

        return $this->fileNames;
    }

    public function getFixedBladeTemplateFilesNames($baseBladePath)
    {
        $cleanbaseBladePath = str_replace(resource_path().'/views/', '',  $baseBladePath );
        $cleanbaseBladePath = str_replace('.blade.php', '', $cleanbaseBladePath );
        if(!is_file($baseBladePath.'/dataSource.blade.php')) {
            $this->fileNames[] = '1_base/dataSource';
        }
        else {
            $this->fileNames[] = $cleanbaseBladePath.'/dataSource';
        }

        if(!is_file($baseBladePath.'/additionalResources.blade.php')) {
            $this->fileNames[] = '1_base/additionalResources';
        }
        else {
            $this->fileNames[] = $cleanbaseBladePath.'/additionalResources';
        }

        $this->fileNames[] = '1_base/variables';
        $this->fileNames[] = $cleanbaseBladePath.'/variables';
        $this->fileNames[] = $cleanbaseBladePath.'/methods';
    }

    public function getBladeTemplateComponentsFilesNames($componentsPath, $needAttachments = false)
    {
        $dirElems = scanDir($componentsPath);

        foreach($dirElems as $dirElem) {
            if($dirElem === '.' || $dirElem === '..')
            continue;

            if(!$needAttachments && $dirElem === 'attachments')
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
