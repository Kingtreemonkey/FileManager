<?php

namespace Rabies\FileManager\Ajax;

use Rabies\FileManager\Utils\Utility;

class SaveImage {
    
    public $r;
    
    public function action($parent){
        $c = $parent->config;        
        $util = new Utility();
        $info = pathinfo($_POST['name']);
        if ( strpos($_POST['path'], '/') === 0
            || strpos($_POST['path'], '../') !== false
            || strpos($_POST['path'], './') === 0
            || strpos($_POST['url'], 'http://s3.amazonaws.com/feather') !== 0
            || $_POST['name'] != $util->fix_filename($_POST['name'], $c['transliteration'], $c['convert_spaces'], $c['replace_with'])
            || ! in_array(strtolower($info['extension']), array( 'jpg', 'jpeg', 'png' ))
        ){
            $this->r = array('wrong data', 400);
            return;
        }        
        $image_data = file_get_contents($_POST['url']);
        if ($image_data === false)
        {
            $this->r = array('Could not save image', 400);
            return;
        }
        //18/04/2015 add versioning for edits
        $version = $this->versioning($_POST['name'], $c['current_path'], $_POST['path']);
                
        $fp = fopen($c['current_path'] . $_POST['path'] . $version, "w");
        fwrite($fp, $image_data);
        fclose($fp);

        $util->create_img($c['current_path'].$_POST['path'].$version, $c['thumbs_base_path'].$_POST['path'].$version, 122, 91);
        // TODO something with this function cause its blowing my mind
        $util->new_thumbnails_creation(
            $c['current_path'].$_POST['path'],
            $c['current_path'].$_POST['path'].$version,
            $version, 
            $c['current_path'],
            $relative_image_creation,
            $relative_path_from_current_pos,
            $relative_image_creation_name_to_prepend,
            $relative_image_creation_name_to_append,
            $relative_image_creation_width,
            $relative_image_creation_height,
            $relative_image_creation_option,
            $fixed_image_creation,
            $fixed_path_from_filemanager,
            $fixed_image_creation_name_to_prepend,
            $fixed_image_creation_to_append,
            $fixed_image_creation_width,
            $fixed_image_creation_height,
            $fixed_image_creation_option
        );
       
    }
    private function versioning($name, $cpath, $path){
        $strrpos = strrpos($name, "_v");
        var_dump($strrpos);
        if($strrpos === false){ //first (no previous version)
            var_dump('add version');
            $name_ext = explode('.', $name); 
            $extension = $name_ext[1];
            $version = 1;
            $version_str = "_v1";      
            $version_ext = $version_str . "." . $extension; //for use later in loop if required
            $base_name = $name_ext[0];
            $versioned_name = $base_name . $version_str . '.' . $name_ext[1]; //name version extension
            
        } else { //existing file versioning            
            $version_ext =  substr($name, $strrpos);            
            $version_ext_arr = explode('.', $version_ext);            
            $extension  = $version_ext_arr[1];
            $version = (int)substr($version_ext_arr[0],2);
            $version_str = "_v" . ((int)$version + 1);            
            $new_version_extension = $version_str . '.' . $extension;            
            $versioned_name = str_replace($version_ext, $new_version_extension, $name); //replace old version extension with new
        }
        
        
        
        $full_path = $cpath . $path . $versioned_name;
        $prev_version = "_v".$version. '.' . $extension;
        
        while (file_exists($full_path)){ //while file exists increment version
            $version = (int)$version + 1; 
           // var_dump($version);            
            $new_version = "_v" . $version . '.' . $extension;              
            $versioned_name = str_replace($prev_version, $new_version, $versioned_name);    
            $prev_version = $new_version;
            var_dump($versioned_name);
            $full_path = $cpath . $path . $versioned_name;
           // echo "The file $filename exists"; //we must add a subversion
        } 
        
        return $versioned_name;
        
        
    }
}
