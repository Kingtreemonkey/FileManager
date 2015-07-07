<?php

namespace Rabies\FileManager\Action;

use Rabies\FileManager\Utils\Utility;
class DeleteFolder {
    
    public $r;
    
    public function action($parent){
        $path_thumb = $parent->path_thumb;
        $path = $parent->path;
        $c = $parent->config;
        $util = new Utility();
        if ($c['delete_folders']){
                if (is_dir($path_thumb))
                {
                    $util->deleteDir($path_thumb);
                }

                if (is_dir($path))
                {
                    $util->deleteDir($path);
                    if ($c['fixed_image_creation'])
                    {
                        foreach($c['fixed_path_from_filemanager'] as $k=>$paths){
                            if ($paths!="" && $paths[strlen($paths)-1] != "/") $paths.="/";
                            
                            $base_dir=$paths.substr_replace($path, '', 0, strlen($current_path));
                            if (is_dir($base_dir)) $util->deleteDir($base_dir);
                        }
                    }
                }
                $this->r = array('Folder deleted.',200);
                return;
            }
           $this->r = array('You are not permitted to delete folders.',400); 
    }
}
