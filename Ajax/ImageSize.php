<?php

namespace Rabies\FileManager\Ajax;

use Rabies\FileManager\Utils\Utility;
class ImageSize {
    
    public $r;
    
    public function action($parent){
        $c = $parent->config;        
                
        $pos = strpos($_POST['path'], $c['upload_dir']);
        if ($pos !== false){
            $info = getimagesize(substr_replace($_POST['path'], $c['current_path'], $pos, strlen($c['upload_dir'])));
            $this->r = array($info,200);
            return;
        }        
        $this->r = array('error finding image size',200);
    }
}
