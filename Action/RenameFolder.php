<?php

namespace Rabies\FileManager\Action;
use Rabies\FileManager\Utils\Utility;

class RenameFolder {
    
    public $r;
    
    public function action($parent){
        $c = $parent->config;
        $name = $parent->name;
        $path = $parent->path;
        $path_thumb = $parent->path_thumb;
        $util = new Utility();
        if ($c['rename_folders']){
            $name=$util->fix_filename($name,$c['transliteration'],$c['convert_spaces'], $c['replace_with']);
            $name=str_replace('.','',$name);
            var_dump($name);
            if (!empty($name)){
                if (!$util->rename_folder($path,$name,$c['transliteration'],$c['convert_spaces'])){
                    $this->r = array('The folder already exists', 403);
                    return;
                }

                $util->rename_folder($path_thumb,$name,$c['transliteration'],$c['convert_spaces']);
                if ($c['fixed_image_creation']){
                    foreach($fixed_path_from_filemanager as $k=>$paths){
                        if ($paths!="" && $paths[strlen($paths)-1] != "/") $paths.="/";

                        $base_dir=$paths.substr_replace($path, '', 0, strlen($current_path));
                        $util->rename_folder($c['base_dir'],$name,$c['transliteration'],$c['convert_spaces']);
                    }
                }
                $this->r = array('success', 200);
                return;
                
            }
            else {
                $this->r = array('The name is empty', 400);
                return;
            }            
        } else {
                $this->r = array('errror: not allowed to rename folders', 400);
                return;
        }
    }
}
