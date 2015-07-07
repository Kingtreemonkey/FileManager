<?php

namespace Rabies\FileManager\Action;

use Rabies\FileManager\Utils\Utility;

class DuplicateFile {
    
    private $config;
    public $r;
    
    public function action($parent){
        $this->config = $parent->config;
        $util = new Utility();
        if ($this->config['duplicate_files']){
            $name=$util->fix_filename($name,$transliteration,$convert_spaces, $replace_with);
            if (!empty($name)){
                if (!$util->duplicate_file($path,$name)){
                    $this->r = array('The file is already exists' , 403);
                    return;
                }

                $util->duplicate_file($path_thumb,$name);

                if ($fixed_image_creation)
                {
                    $info=pathinfo($path);
                    foreach($fixed_path_from_filemanager as $k=>$paths)
                    {
                        if ($paths!="" && $paths[strlen($paths)-1] != "/") $paths.= "/";

                        $base_dir=$paths.substr_replace($info['dirname']."/", '', 0, strlen($current_path));

                        if (file_exists($base_dir.$fixed_image_creation_name_to_prepend[$k].$info['filename'].$fixed_image_creation_to_append[$k].".".$info['extension']))
                        {
                            $util->duplicate_file($base_dir.$fixed_image_creation_name_to_prepend[$k].$info['filename'].$fixed_image_creation_to_append[$k].".".$info['extension'],$fixed_image_creation_name_to_prepend[$k].$name.$fixed_image_creation_to_append[$k]);
                        }
                    }
                }
            }else{
                $this->r = array('The name is empty', 400);
                return ;
            }
        }
    }
}
