<?php

namespace Rabies\FileManager\Action;
use Rabies\FileManager\Utils\Utility;

class RenameFile {
    
    public $r;
    
    public function action($parent){
        $c = $parent->config;
        $name = $parent->name;
        $path = $parent->path;
        $path_thumb = $parent->path_thumb;
        
        $util = new Utility();
        
        if ($c['rename_files']){
            $name=$util->fix_filename($name,$c['transliteration'],$c['convert_spaces'], $c['replace_with']);
            if (!empty($name)){
                if (!$util->rename_file($path,$name,$c['transliteration'])){
                    $this->r = array('The file is already exists', 403);
                    return;
                }
                $util->rename_file($path_thumb,$name,$c['transliteration']);

                if ($fixed_image_creation)
                {
                    $info=pathinfo($path);

                    foreach($c['fixed_path_from_filemanager'] as $k=>$paths)
                    {
                        if ($paths!="" && $paths[strlen($paths)-1] != "/") $paths.="/";

                        $base_dir = $paths.substr_replace($info['dirname']."/", '', 0, strlen($current_path));
                        if (file_exists($c['base_dir'].$c['fixed_image_creation_name_to_prepend'][$k].$info['filename'].$c['fixed_image_creation_to_append'][$k].".".$info['extension']))
                        {
                            $util->rename_file($c['base_dir'].$c['fixed_image_creation_name_to_prepend'][$k].$info['filename'].$c['fixed_image_creation_to_append'][$k].".".$info['extension'],$c['fixed_image_creation_name_to_prepend'][$k].$name.$c['fixed_image_creation_to_append'][$k],$c['transliteration']);
                            
                        }
                    }
                }
                $this->r = array('success', 200);
                return;
            }
            else {
                $this->r = array('The name is empty', 400);
                return;
            }
        }
    }
}
