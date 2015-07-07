<?php

namespace Rabies\FileManager;
use Rabies\FileManager\Utils\Utility;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class Upload{    
    public function upload(Application $app, Request $req){
        $config = $app['FileManager'];
        $util = new Utility();
         
        $current_path = $config['current_path'];  
        $thumbs_base_path = $config['thumbs_base_path'];
         $config['ext'] =  array_merge(
            $config['ext_img'],
            $config['ext_file'],
            $config['ext_misc'],
            $config['ext_video'],
            $config['ext_music']
        );
        $ext = $config['ext'];  
        $transliteration = $config['transliteration'];
        $convert_spaces = $config['convert_spaces'];
        $replace_with = $config['replace_with'];
        $ext_img = $config['ext_img'];
                
        if (isset($_POST['path'])){            
           $storeFolder = $_POST['path'];
           $storeFolderThumb = $_POST['path_thumb'];
        } else {           
           $storeFolder = $current_path.$_POST["fldr"]; // correct for when IE is in Compatibility mode
           $storeFolderThumb = $thumbs_base_path.$_POST["fldr"];
        }
        $path_pos  = strpos($storeFolder, $current_path);
        $thumb_pos = strpos($storeFolderThumb,$thumbs_base_path);
        if ( $path_pos!==0 || $thumb_pos !==0 || strpos($storeFolderThumb,'../',strlen($thumbs_base_path)) !== FALSE || strpos($storeFolderThumb,'./',strlen($thumbs_base_path)) !== FALSE || strpos($storeFolder,'../',strlen($current_path)) !== FALSE || strpos($storeFolder,'./',strlen($current_path)) !== FALSE ){
            die('wrong path');            
        }
        $path = $storeFolder;
        $cycle = TRUE;
        $max_cycles = 50;
        $i = 0;
        while ($cycle && $i < $max_cycles)
        {
                $i++;
                if ($path == $current_path){ $cycle = FALSE;}
                if (file_exists($path."config.php"))
                {
                        require_once $path."config.php";
                        $cycle = FALSE;
                }
                $path = $util->fix_dirname($path).'/';
        }
        if ( ! empty($_FILES))
        {
                $info = pathinfo($_FILES['file']['name']);
                if (in_array($util->fix_strtolower($info['extension']), $ext))
                {
                        $tempFile = $_FILES['file']['tmp_name'];
                        $targetPath = $storeFolder;
                        $targetPathThumb = $storeFolderThumb;
                        $_FILES['file']['name'] = $util->fix_filename($_FILES['file']['name'],$transliteration,$convert_spaces, $replace_with);
                        // Gen. new file name if exists
                        if (file_exists($targetPath.$_FILES['file']['name']))
                        {
                                $i = 1;
                                $info = pathinfo($_FILES['file']['name']);
                                // append number
                                while(file_exists($targetPath.$info['filename']."_".$i.".".$info['extension'])) {
                                        $i++;
                                }
                                $_FILES['file']['name'] = $info['filename']."_".$i.".".$info['extension'];
                        }
                        $targetFile =  $targetPath. $_FILES['file']['name'];
                        $targetFileThumb =  $targetPathThumb. $_FILES['file']['name'];
                        // check if image (and supported)
                        if (in_array($util->fix_strtolower($info['extension']),$ext_img)){
                            $is_img=TRUE;
                        }
                        else{
                            $is_img=FALSE;
                        }
                        // upload
                        move_uploaded_file($tempFile,$targetFile);
                        chmod($targetFile, 0755);
                        if ($is_img){
                            $memory_error = FALSE;
                            if ( ! $util->create_img($targetFile, $targetFileThumb, 122, 91)){
                                        $memory_error = FALSE;
                                }else{
                                        // TODO something with this long function baaaah...
                                        if( ! $util->new_thumbnails_creation($targetPath,$targetFile,$_FILES['file']['name'],$current_path,$relative_image_creation,$relative_path_from_current_pos,$relative_image_creation_name_to_prepend,$relative_image_creation_name_to_append,$relative_image_creation_width,$relative_image_creation_height,$relative_image_creation_option,$fixed_image_creation,$fixed_path_from_filemanager,$fixed_image_creation_name_to_prepend,$fixed_image_creation_to_append,$fixed_image_creation_width,$fixed_image_creation_height,$fixed_image_creation_option))
                                        {
                                                $memory_error = FALSE;
                                        }
                                        else
                                        {
                                                $imginfo = getimagesize($targetFile);
                                                $srcWidth = $imginfo[0];
                                                $srcHeight = $imginfo[1];
                                                // resize images if set
                                                if ($image_resizing)
                                                {
                                                        if ($image_resizing_width == 0) // if width not set
                                                        {
                                                                if ($image_resizing_height == 0)
                                                                {
                                                                        $image_resizing_width = $srcWidth;
                                                                        $image_resizing_height = $srcHeight;
                                                                }
                                                                else
                                                                {
                                                                        $image_resizing_width = $image_resizing_height*$srcWidth/$srcHeight;
                                                                }
                                                        }
                                                        elseif ($image_resizing_height == 0) // if height not set
                                                        {
                                                                $image_resizing_height = $image_resizing_width*$srcHeight/$srcWidth;
                                                        }
                                                        // new dims and create
                                                        $srcWidth = $image_resizing_width;
                                                        $srcHeight = $image_resizing_height;
                                                        $util->create_img($targetFile, $targetFile, $image_resizing_width, $image_resizing_height, $image_resizing_mode);
                                                }
                                                //max resizing limit control
                                                $resize = FALSE;
                                                if ($image_max_width != 0 && $srcWidth > $image_max_width && $image_resizing_override === FALSE)
                                                {
                                                        $resize = TRUE;
                                                        $srcWidth = $image_max_width;
                                                        if ($image_max_height == 0){ 
                                                            $srcHeight = $image_max_width*$srcHeight/$srcWidth;
                                                        }
                                                }
                                                if ($image_max_height != 0 && $srcHeight > $image_max_height && $image_resizing_override === FALSE){
                                                        $resize = TRUE;
                                                        $srcHeight = $image_max_height;
                                                        if ($image_max_width == 0){
                                                            $srcWidth = $image_max_height*$srcWidth/$srcHeight;
                                                        }
                                                }
                                                if ($resize) $util->create_img($targetFile, $targetFile, $srcWidth, $srcHeight, $image_max_mode);
                                        }
                                }
                                // not enough memory
                                if ($memory_error){
                                        unlink($targetFile);
                                        header('HTTP/1.1 406 Not enought Memory',TRUE,406);
                                        exit();
                                }
                        }
                        return $app->json($_FILES['file']['name'],200);
                        
                }
                else // file ext. is not in the allowed list
                {
                        header('HTTP/1.1 406 file not permitted',TRUE,406);
                        exit();
                }
        }
        else // no files to upload
        {
                header('HTTP/1.1 405 Bad Request', TRUE, 405);
                exit();
        }
        // redirect
        if (isset($_POST['submit']))
        {
                $query = http_build_query(array(
                        'type'	  	=> $_POST['type'],
                        'lang'	  	=> $_POST['lang'],
                        'popup'	 	=> $_POST['popup'],
                        'field_id'  => $_POST['field_id'],
                        'fldr'	  	=> $_POST['fldr'],
                ));
                header("location: dialog.php?" . $query);
        }
    }    
    
}
