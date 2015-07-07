<?php

namespace Rabies\FileManager\Action;

class DeleteFile{
    public $r;
    
    public function action($parent){ 
        
        $c = $parent->config;
        $path = $parent->path;
        $path_thumb = $parent->path_thumb;
        
         if ($c['delete_files']){
                unlink($path);
                if (file_exists($path_thumb)) unlink($path_thumb);

                $info=pathinfo($path);
                if ($c['relative_image_creation']){
                    foreach($c['relative_path_from_current_pos'] as $k=>$path)
                    {
                        if ($path!="" && $path[strlen($path)-1]!="/") $path.="/";

                        if (file_exists($info['dirname']."/".$path.$c['relative_image_creation_name_to_prepend'][$k].$info['filename'].$c['relative_image_creation_name_to_append'][$k].".".$info['extension']))
                        {
                            unlink($info['dirname']."/".$path.$c['relative_image_creation_name_to_prepend'][$k].$info['filename'].$c['relative_image_creation_name_to_append'][$k].".".$info['extension']);
                        }
                    }
                }

                if ($c['fixed_image_creation'])
                {
                    foreach($c['fixed_path_from_filemanager'] as $k=>$path)
                    {
                        if ($path!="" && $path[strlen($path)-1] != "/") $path.="/";

                        $base_dir=$path.substr_replace($info['dirname']."/", '', 0, strlen($c['current_path']));
                        if (file_exists($base_dir.$c['fixed_image_creation_name_to_prepend'][$k].$info['filename'].$c['fixed_image_creation_to_append'][$k].".".$info['extension']))
                        {
                            unlink($base_dir.$c['fixed_image_creation_name_to_prepend'][$k].$info['filename'].$c['fixed_image_creation_to_append'][$k].".".$info['extension']);
                        }
                    }
                }
                $this->r = array('File deleted.',200);
                return;
            } else {
                $this->r = array('You are not permitted to delete files.',400);
            }           
    }
}
