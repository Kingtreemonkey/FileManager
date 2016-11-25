<?php

namespace Rabies\FileManager;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Rabies\FileManager\Utils\Utility;
use Rabies\FileManager\Utils\SessionHandler;

class Dialog { 
  
    private $two;
    //
    //mutable vars
    //
    private $cur_dir;
    private $base_url;
    private $current_path;
    private $files_prevent_duplicate;

// // get config from somewhere 'config/config.php';
//include 'include/utils.php';

//?type=1&descending=false&lang=undefined&akey=key"

    public function Dialog(Application $app, Request $request){
        $config = $app['FileManager'];
        $config['ext'] =  array_merge(
            $config['ext_img'],
            $config['ext_file'],
            $config['ext_misc'],
            $config['ext_video'],
            $config['ext_music']
        );
        //handle sessions
        $session = new SessionHandler($app);
        $util = new Utility();
        //handle additional parameters to set views ect
        if (isset($_GET['view'])){            
            $session->setViewType($util->fix_get_params($_GET['view']));
        }
        if(isset($_GET["filter"])){
            $session->setFilter($util->fix_get_params($_GET['filter']));            
        }        
        if (isset($_GET["sort_by"])){
                $session->setSortBy($util->fix_get_params($_GET["sort_by"]));                        
        }
        if (isset($_GET["descending"]))
        {
               $session->setDescending($util->fix_get_params($_GET["descending"]));
        }
        
        
        
        
        
        $subdir = '';
        if (isset($_GET['fldr']) && !empty($_GET['fldr']) && strpos($_GET['fldr'],'../') === FALSE && strpos($_GET['fldr'],'./') === FALSE){
            $subdir = urldecode(trim(strip_tags($_GET['fldr']),"/") ."/");
            $session->setFilter('');
        }


        // If hidden folders are specified
        if(count($config['hidden_folders'])){
            // If hidden folder appears in the path specified in URL parameter "fldr"
            $dirs = explode('/', $subdir);
            foreach($dirs as $dir){
                if($dir !== '' && in_array($dir, $hidden_folders)){
                    // Ignore the path
                    $subdir = "";
                    break;
                }
            }
        }
        
        /***
         *SUB-DIR CODE
         ***/

        if (!isset($_SESSION['RF']["subfolder"])){
            $_SESSION['RF']["subfolder"] = '';
        }
        $rfm_subfolder = '';

        if (!empty($_SESSION['RF']["subfolder"]) && strpos($_SESSION['RF']["subfolder"],'../') === FALSE && strpos($_SESSION['RF']["subfolder"],'./') === FALSE && strpos($_SESSION['RF']["subfolder"],"/") !== 0 && strpos($_SESSION['RF']["subfolder"],'.') === FALSE)        {
            $rfm_subfolder = $_SESSION['RF']['subfolder'];
        }

        if ($rfm_subfolder != "" && $rfm_subfolder[strlen($rfm_subfolder)-1] != "/") { $rfm_subfolder .= "/"; }

        if (!file_exists($config['current_path'].$rfm_subfolder.$subdir))
        {
            $subdir = '';
            if (!file_exists($config['current_path'].$rfm_subfolder.$subdir))
            {
                $rfm_subfolder = "";
            }
        }

        if (trim($rfm_subfolder) == ""){
            $this->cur_dir 	= $config['upload_dir'] . $subdir;
            $cur_path       = $config['current_path'] . $subdir;
            $thumbs_path    = $config['thumbs_base_path'];
            $parent 	= $subdir;
        } else {
            $this->cur_dir 	 = $config['upload_dir'] . $rfm_subfolder.$subdir;
            $cur_path 	 = $config['current_path'] . $rfm_subfolder.$subdir;
            $thumbs_path = $config['thumbs_base_path']. $rfm_subfolder;
            $parent 	 = $rfm_subfolder.$subdir;
        }        
        
        $cycle = TRUE;
        $max_cycles = 50;
        $i = 0;
        while($cycle && $i < $max_cycles){
            $i++;
            if ($parent=="./") $parent="";

            if (file_exists($config['current_path'].$parent."config.php"))
            {
                require_once $config['current_path'].$parent."config.php";
                $cycle = FALSE;
            }

            if ($parent == "") $cycle = FALSE;
            else $parent = $util->fix_dirname($parent)."/";
        }

        if (!is_dir($thumbs_path.$subdir))
        {               
            $util->create_folder(FALSE, $thumbs_path.$subdir);
        }

        if (isset($_GET['popup'])){
            $popup = strip_tags($_GET['popup']);
        }
        else $popup=0;
        //Sanitize popup
        $popup=!!$popup;

        if (isset($_GET['crossdomain'])){
           $crossdomain = strip_tags($_GET['crossdomain']);
        } else {
            $crossdomain=0;
        }

        //Sanitize crossdomain
        $crossdomain=!!$crossdomain;

        //view type
        $view = $session->getViewType();
        //filter
        $filter = $session->getFilter();
        //sorting method
        $sort_by = $session->getSortBy();
        //sorting order
        $descending = $session->getDescending();        

        $boolarray = Array(false => 'false', true => 'true');

        $return_relative_url = isset($_GET['relative_url']) && $_GET['relative_url'] == "1" ? true : false;

        if (!isset($_GET['type'])) $_GET['type'] = 0;

        if (isset($_GET['editor'])){
                $editor = strip_tags($_GET['editor']);
        } else{
            if($_GET['type']==0){
                $editor=false;
            }else{
                $editor='tinymce';
            }
        }

        if (!isset($_GET['field_id'])) $_GET['field_id'] = '';

        $field_id = isset($_GET['field_id']) ? $util->fix_get_params($_GET['field_id']) : '';
        $type_param = $util->fix_get_params($_GET['type']); 

        if ($type_param==1) 	 $apply = 'apply_img';
        elseif($type_param==2) $apply = 'apply_link';
        elseif($type_param==0 && $_GET['field_id']=='') $apply = 'apply_none';
        elseif($type_param==3) $apply = 'apply_video';
        else $apply = 'apply';

        $get_params = http_build_query(array(
            'editor'    => $editor,
            'type'      => $type_param,
            'lang'      => $config['default_language'],
            'popup'     => $popup,
            'crossdomain' => $crossdomain,
            'field_id'  => $field_id,
            'relative_url' => $return_relative_url,
            'akey' 		=> (isset($_GET['akey']) && $_GET['akey'] != '' ? $_GET['akey'] : 'key'),
            'fldr'      => ''
        ));
        //get base config options
        $twigArr = $app['FileManager'];
        
        //overwrite specific's
        $twigArr['lang'] = $app['FileManager']['default_language'];
        $twigArr['ext'] = array_merge($config['ext_img'], $config['ext_file'], $config['ext_misc'], $config['ext_video'], $config['ext_music']);
        $twigArr['apply'] = $apply;
        $twigArr['field_id'] = $field_id;
        $twigArr['popup'] = $popup;
        $twigArr['crossdomain'] = $crossdomain;
        $twigArr['editor'] = $editor;
        $twigArr['view'] = $view;
        $twigArr['filter'] = $filter;        
        $twigArr['sort_by'] = $sort_by;         
        $twigArr['descending'] = $descending;        
        $twigArr['subdir'] = $subdir;
        $twigArr['field_id']  = $field_id;
        $twigArr['type_param'] = $type_param;
        $twigArr['cur_dir'] = $config['upload_dir'] . $subdir;
        $twigArr['cur_path'] = $config['current_path'] . $subdir;
        $twigArr['thumbs_path'] = $config['thumbs_base_path'];
        $twigArr['cur_dir_thumb'] = $twigArr['thumbs_path'].$twigArr['subdir'];
        $twigArr['parent'] = $subdir;
        $twigArr['duplicate_files'] = 0;
        $twigArr['rfm_subfolder'] = "";
        $twigArr['base_url_func'] = $util->base_url();
        $twigArr['current_url'] = str_replace(array('&filter='.$filter,'&sort_by='.$sort_by,'&descending='.intval($descending)),array(''),$twigArr['base_url'].$_SERVER['REQUEST_URI']); 
        $twigArr['get_type'] = $_GET['type'];  
        $twigArr['home_link'] = $_GET['type'];  
        $twigArr['get_params'] = $get_params;
        
        $twigArr['return_relative_url'] = 0;
        if($return_relative_url == true){
            $twigArr['return_relative_url'] = 1;
        }        
        
        if($twigArr['duplicate_files'] === true){
        $twigArr['duplicate_files'] = 1;
        }
        
//        array(
//            'ext_img' => $config['ext_img'],
//            'ext' => array_merge($config['ext_img'], $config['ext_file'], $config['ext_misc'], $config['ext_video'], $config['ext_music']),
//            'aviary_active' => $config['aviary_active'],
//            'Error_extension'
//            'MaxSizeUpload' => (int)$config['MaxSizeUpload'],
//        );
        $template = 'FileManager/view.html.twig';

        $class_ext = '';
        $src = '';

        $files = scandir($config['current_path'].$rfm_subfolder.$subdir);
        
        $n_files=count($files);

        //php sorting
        $sorted=array();
        $current_folder=array();
        $prev_folder=array();
        foreach($files as $k=>$file){
            if($file==".") $current_folder=array('file'=>$file);
            elseif($file=="..") $prev_folder=array('file'=>$file);
            elseif(is_dir($config['current_path'].$rfm_subfolder.$subdir.$file)){
                $date=filemtime($config['current_path'].$rfm_subfolder.$subdir. $file);
                if($config['show_folder_size']){
                    $size=$util->foldersize($config['current_path'].$rfm_subfolder.$subdir. $file);
                } else {
                    $size=0;
                }
                $file_ext='dir';
                $sorted[$k]=array('file'=>$file,'file_lcase'=>strtolower($file),'date'=>$date,'size'=>$size,'extension'=>$file_ext,'extension_lcase'=>strtolower($file_ext));
            }else{
                $file_path=$config['current_path'].$rfm_subfolder.$subdir.$file;
                $date=filemtime($file_path);
                $size=filesize($file_path);
                $file_ext = substr(strrchr($file,'.'),1);
                $sorted[$k]=array('file'=>$file,'file_lcase'=>strtolower($file),'date'=>$date,'size'=>$size,'extension'=>$file_ext,'extension_lcase'=>strtolower($file_ext));
            }
        }

        // Should lazy loading be enabled
        $lazy_loading_enabled = ($config['lazy_loading_file_number_threshold'] == 0 || $config['lazy_loading_file_number_threshold'] != -1 && $n_files > $config['lazy_loading_file_number_threshold']) ? true : false;
        $twigArr['lazy_loading_enabled'] = $lazy_loading_enabled;
                
        switch($sort_by){
                case 'date':
                        usort($sorted, array($this ,'dateSort'));
                        break;
                case 'size':
                        usort($sorted, array($this ,'sizeSort'));
                        break;
                case 'extension':
                        usort($sorted, array($this ,'extensionSort'));
                        break;
                default:
                        usort($sorted, array($this ,'filenameSort'));
                        break;
        }

        if(!$descending){
            $sorted=array_reverse($sorted);
        }

        $files=array_merge(array($prev_folder),array($current_folder),$sorted);

        //Add file / folder stuff to array
        
        
        $twigArr['n_files'] = $n_files;
        $twigArr['uniqid'] = uniqid();
        
        //can open current dir?
        $open_dir = false;
        if(@opendir($config['current_path'].$rfm_subfolder.$subdir)){
            $open_dir = true;
        }
        $twigArr['clipboard'] = 0;
        $clipboard_path = $session->getClipboardPath();
        //var_dump($clipboard_path);
        if(isset($clipboard_path) && trim($clipboard_path) != null){
            $twigArr['clipboard'] = 1;
        }
         
        //$twigArr['open_dir'] = uniqid();
        $twigArr['open_dir'] = $open_dir;
        $twigArr['render_need_name'] = $this->render_need_name($app,$files, $twigArr, $config, $subdir, $filter, $config['transliteration'] ,$thumbs_path,$get_params ,$rfm_subfolder);
        $twigArr['render_need_name_2'] = $this->two;
        $twigArr['files_prevent_duplicate'] = $this->files_prevent_duplicate;
        return $app['twig']->render($template, $twigArr);     
    }   
    


public function render_need_name($app,$files,$twigArr,$config,$subdir, $filter, $transliteration ,$thumbs_path,$get_params, $rfm_subfolder){
    
    //need to pass in rest of required variables.
    
    $jplayer_ext=array("mp4","flv","webmv","webma","webm","m4a","m4v","ogv","oga","mp3","midi","mid","ogg","wav");
    $html = "";
    
    $util = new Utility();
    
    foreach ($files as $file_array){        
        $file=$file_array['file'];        
        if($file == '.' || (isset($file_array['extension']) && $file_array['extension']!='dir') || ($file == '..' && $subdir == '') || in_array($file, $config['hidden_folders']) || ($filter!='' && $n_files>$file_number_limit_js && $file!=".." && stripos($file,$filter)===false)){
            continue; 
        }
        $new_name=$util->fix_filename($file,$transliteration);
        if($file!='..' && $file!=$new_name){
            //rename
            $util->rename_folder($config['current_path'].$subdir.$file,$new_name,$transliteration);
            $file=$new_name;
        }
        //add in thumbs folder if not exist
        if (!file_exists($thumbs_path.$subdir.$file)){                        
            $util->create_folder(false,$thumbs_path.$subdir.$file);
        }
        $class_ext = 3;
        if($file=='..' && trim($subdir) != '' ){
            $src = explode("/",$subdir);
            unset($src[count($src)-2]);
            $src=implode("/",$src);
            //if($src=='') $src="/";
            if($src=='') $src="";
        } elseif ($file!='..') {
            $src = $subdir . $file."/";
        }
        
        $twigArr['file'] = $file;
        $template = null;


        //template specifics
        
        $attr = array(
            'folder_link' => "filemanager?" . $get_params.rawurlencode($src)."&".uniqid(),                    
        );        
        if ($file == '..'){
            $attr['path'] = str_replace('.','',dirname($rfm_subfolder.$subdir));
            $attr['path_thumb'] = dirname($thumbs_path.$subdir)."/";
            $template = 'FileManager/need_name/back.html.twig';
        } else {
            $template = 'FileManager/need_name/folder.html.twig';
        }                
        $twigArr['file_prevent_rename'] = false;
        $twigArr['file_prevent_delete'] = false;
        if (isset($filePermissions[$file])) {
            $twigArr['file_prevent_rename'] = isset($filePermissions[$file]['prevent_rename']) && $filePermissions[$file]['prevent_rename'];
            $twigArr['file_prevent_delete'] = isset($filePermissions[$file]['prevent_delete']) && $filePermissions[$file]['prevent_delete'];
        }
        $twigArr['file_array'] = $file_array;
        $twigArr['temp_attr'] = $attr;

        $html = $html . $app['twig']->render($template, $twigArr);                
    }
    $this->two = $this->two($app, $files, $twigArr, $config, $subdir, $filter, $transliteration ,$thumbs_path,$get_params,$util,$rfm_subfolder);
    return $html;
}

public function two($app, $files, $twigArr, $config, $subdir, $filter, $transliteration ,$thumbs_path,$get_params, Utility $util,$rfm_subfolder){
    $files_prevent_duplicate = array();
    $html = "";
    foreach ($files as $nu=>$file_array) {
        $file=$file_array['file'];
        if($file == '.' || $file == '..' || is_dir($config['current_path'].$rfm_subfolder.$subdir.$file) || in_array($file, $config['hidden_files']) || !in_array($util->fix_strtolower($file_array['extension']), $config['ext']) || ($filter!='' && $n_files>$file_number_limit_js && stripos($file,$filter)===false))
	continue;    
        $file_path=$config['current_path'].$rfm_subfolder.$subdir.$file;
	//check if file have illegal caracter
        $filename=substr($file, 0, '-' . (strlen($file_array['extension']) + 1));
        if($file!=$util->fix_filename($file,$transliteration)){
            $file1=$util->fix_filename($file,$transliteration);
            $file_path1=($this->current_path.$rfm_subfolder.$subdir.$file1);
            if(file_exists($file_path1)){
                $i = 1;
                $info=pathinfo($file1);
                while(file_exists($this->current_path.$rfm_subfolder.$subdir.$info['filename'].".[".$i."].".$info['extension'])) {
                    $i++;
                }
                $file1=$info['filename'].".[".$i."].".$info['extension'];
                $file_path1=($this->current_path.$rfm_subfolder.$subdir.$file1);
            }
            $filename=substr($file1, 0, '-' . (strlen($file_array['extension']) + 1));
            rename_file($file_path,$util->fix_filename($filename,$transliteration),$transliteration);
            $file=$file1;
            $file_array['extension']=$util->fix_filename($file_array['extension'],$transliteration);
            $file_path=$file_path1;
        }

        $is_img=false;
        $is_video=false;
        $is_audio=false;
        $show_original=false;
        $show_original_mini=false;
        $mini_src="";
        $src_thumb="";
        $extension_lower=$util->fix_strtolower($file_array['extension']);
        if($extension_lower === 'svg'){
          //dont try mking thumb for svg file!
        }else{
          if(in_array($extension_lower, $config['ext_img'])){
              $src = $this->base_url . $this->cur_dir . rawurlencode($file);
              $mini_src = $src_thumb = $thumbs_path.$subdir. $file;
              //add in thumbs folder if not exist
              if(!file_exists($src_thumb)){
                  try {
                      if(!$util->create_img($file_path, $src_thumb, 122, 91)){
                          $src_thumb=$mini_src="";
                      }else{
//                          $util->new_thumbnails_creation($this->current_path.$rfm_subfolder.$subdir,$file_path,$file,$this->current_path,'','','','','','','',$confixed_image_creation,$fixed_path_from_filemanager,$fixed_image_creation_name_to_prepend,$fixed_image_creation_to_append,$fixed_image_creation_width,$fixed_image_creation_height,$fixed_image_creation_option);
                      }
                  } catch (Exception $e) {
                      $src_thumb=$mini_src="";
                  }
              }
            }
            $is_img=true;
            //check if is smaller than thumb
            list($img_width, $img_height, $img_type, $attr)=@getimagesize($file_path);
            if($img_width<122 && $img_height<91){
                    $src_thumb=$this->cur_dir.$file;
                    //var_dump($src_thumb);
                    
                    $show_original=true;
            }

            if($img_width<45 && $img_height<38){
                
                $mini_src=$this->cur_dir.$rfm_subfolder.$subdir.$file;
                //var_dump($mini_src);
                //$mini_src=$this->current_path.$rfm_subfolder.$subdir.$file."sr";
                $show_original_mini=true;
            }
            $twigArr['img_width']=$img_width;
            $twigArr['img_height']=$img_height;
            $twigArr['src']=$src;
        }
        
        $is_icon_thumb=false;
        $is_icon_thumb_mini=false;
        $no_thumb=false;
        if($src_thumb==""){
            $no_thumb=true;
            if(file_exists('img/'.$config['icon_theme'].'/'.$extension_lower.".jpg")){
                $src_thumb ='img/'.$config['icon_theme'].'/'.$extension_lower.".jpg";
            }else{
                $src_thumb = "img/".$config['icon_theme']."/default.jpg";
            }
            $is_icon_thumb=true;
        }
        if($mini_src==""){
            $is_icon_thumb_mini=false;
        }

        $class_ext=0;
        if (in_array($extension_lower, $config['ext_video'])) {
            $class_ext = 4;
            $is_video=true;
        }elseif (in_array($extension_lower, $config['ext_img'])) {
            $class_ext = 2;
        }elseif (in_array($extension_lower, $config['ext_music'])) {
            $class_ext = 5;
            $is_audio=true;
        }elseif (in_array($extension_lower, $config['ext_misc'])) {
            $class_ext = 3;
        }else{
            $class_ext = 1;
        }
        $twigArr['class_ext']=$class_ext;
        $twigArr['is_img']=$is_img;
        $twigArr['is_audio']=$is_audio;
        $twigArr['is_video']=$is_video;
        $twigArr['is_icon_thumb']=$is_icon_thumb;
        $twigArr['show_original']=$show_original;
        $twigArr['src_thumb']=$src_thumb;
        $twigArr['extension_lower']=$extension_lower;
        $twigArr['mini_src']=$mini_src;
        $twigArr['show_original_mini']=$show_original_mini;        
        $twigArr['is_icon_thumb_mini'] = $is_icon_thumb_mini;
        $twigArr['filename']=$filename;        
        $twigArr['nu']=$nu;
        
        
        

        $file_prevent_rename = false;
        $file_prevent_delete = false;
        if (isset($filePermissions[$file])) {
            if (isset($filePermissions[$file]['prevent_duplicate']) && $filePermissions[$file]['prevent_duplicate']) {
                $files_prevent_duplicate[] = $file;
            }
            $file_prevent_rename = isset($filePermissions[$file]['prevent_rename']) && $filePermissions[$file]['prevent_rename'];
            $file_prevent_delete = isset($filePermissions[$file]['prevent_delete']) && $filePermissions[$file]['prevent_delete'];
        }
        $twigArr['files_prevent_duplicate'][] = $file;
        $this->files_prevent_duplicate = $twigArr['files_prevent_duplicate'];
        $twigArr['file_prevent_delete'] = $file_prevent_delete;
        $twigArr['file_prevent_rename'] = $file_prevent_rename;
        $twigArr['file_array'] = $file_array;
        
        $twigArr['file'] = $file;
        
        //var_dump($twigArr['subdir']);
        $twigArr['file_array']['makeSize'] = $util->makeSize($file_array['size']);
        if((!($_GET['type']==1 && !$is_img) && !(($_GET['type']==3 && !$is_video) && ($_GET['type']==3 && !$is_audio))) && $class_ext > 0){            
            $template = 'FileManager/two.html.twig';
            $html = $html . $app['twig']->render($template, $twigArr); //template!
        }
    }    
    return $html;
}
    public function filenameSort($x, $y) {
        return $x['file_lcase'] <  $y['file_lcase'];
    }
    public function dateSort($x, $y) {
        return $x['date'] <  $y['date'];
    }
    public function sizeSort($x, $y) {
        return $x['size'] <  $y['size'];
    }
    public function extensionSort($x, $y) {
        return $x['extension_lcase'] <  $y['extension_lcase'];
    }
   
}