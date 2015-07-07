<?php

namespace Rabies\FileManager\Action;
use Rabies\FileManager\Utils\Utility;

class CreateFolder {

    public $r;
    
    public function action($parent){
        $util = new Utility();
        $config = $parent->config;
        $path = $parent->path;        
        if ($config['create_folders']){
            $util->create_folder($util->fix_path($path,$config['transliteration'],$config['convert_spaces'], $config['replace_with']),$util->fix_path($parent->path_thumb,$config['transliteration'],$config['convert_spaces'], $config['replace_with']));
            //check folder created
            $this->r = array('folder created', 200);
            return;
            //if not return error!
        }  else {
            $this->r = array('no permissions to create folder', 400);
            return;
        }         
    }
}
