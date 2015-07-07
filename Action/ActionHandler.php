<?php

namespace Rabies\FileManager\Action;

use Rabies\FileManager\Utils\Utility;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class ActionHandler {
    
    public $app;
    public $request;
    public $config;


    public $name;

    public function action(Application $app, Request $req, $action){
        $this->app = $app;
        $this->request = $req;
        
        $allowed_action = array(           
            "CreateFolder", //C R
            "RenameFolder", //U
            "DeleteFolder", //D                       
            "CreateFile", //C R
            "RenameFile", //U
            "DeleteFile", //D            
            "DuplicateFile",
            "PasteClipboard",
            "Chmod",
            "SaveTextFile",
        );
        if( ! in_array($action,$allowed_action)){
            //action is not allowed
            return $app->json('Action Denied', 400);
        }
        $config = $app['FileManager'];
        $config['ext'] =  array_merge(
            $config['ext_img'],
            $config['ext_file'],
            $config['ext_misc'],
            $config['ext_video'],
            $config['ext_music']
        );
        
        $util = new Utility();
        
        $thumb_pos  = strpos($_POST['path_thumb'], $config['thumbs_base_path']);
        
        if ($thumb_pos !=0)
        {            
            return $app->json('Wrong path', 400);            
        }
        if( strpos($_POST['path_thumb'],'../',strlen($config['thumbs_base_path'])+$thumb_pos)!==FALSE ){
            return $app->json('Wrong path 1', 400);            
        }
        if( strpos($_POST['path'],'/')===0 ){
            return $app->json('Wrong path 2', 400);            
        }        
        if(strpos($_POST['path'],'../')!==FALSE){
            return $app->json('Wrong path 3', 400);            
        }       
        if(strpos($_POST['path'],'./')===0){
            return $app->json('Wrong path 4', 400);            
        }
//        if (isset($_SESSION['RF']['language_file']) && file_exists($_SESSION['RF']['language_file']))
//        {
//                //TODO Very bad practice
//            require_once $_SESSION['RF']['language_file'];
//        }
//        else
//        {
//            response('Language file is missing!', 500)->send();
//                exit;
//        }

        $base = $config['current_path'];
        $path = $base.$_POST['path'];
        $cycle = TRUE;
        $max_cycles = 50;
        $i = 0;
        while($cycle && $i<$max_cycles)
        {
            $i++;
            if ($path == $base)  $cycle=FALSE;

            if (file_exists($path."config.php"))
            {
                require_once $path."config.php";
                $cycle = FALSE;
            }
            $path = $util->fix_dirname($path)."/";
            $cycle = FALSE;
        }

        $path = $base.$_POST['path'];
        $this->path = $path;
        
        $path_thumb = $_POST['path_thumb'];
        $this->path_thumb = $path_thumb;
        if (isset($_POST['name']))
        {
            $name = $util->fix_filename($_POST['name'],$config['transliteration'],$config['convert_spaces'], $config['replace_with']);
            if (strpos($name,'../') !== FALSE){
                return $app->json('Wrong name', 400);                        
            }
            $this->name = $name;
        }

        $info = pathinfo($path);
        if (isset($info['extension']) && !(isset($action) && $action =='DeleteFolder') && !in_array(strtolower($info['extension']), $config['ext']) && $action != 'CreateFile')
        {
            return $app->json('Wrong extension', 400);            
        }
        
        // Perform Action
        $action = "Rabies\\FileManager\\Action\\" . $action;
        $perform = new $action();
        $this->config = $config;
        $perform->action($this);
        
        return $app->json($perform->r[0], $perform->r[1]);        
    }
}
