<?php

namespace Rabies\FileManager\Ajax;

use Rabies\FileManager\Utils\Utility;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class AjaxHandler {
    
    public function ajax(Application $app, Request $req, $action){
        $this->app = $app;
        $this->request = $req;
        
        $allowed_action = array(    
            "Chmod",
            "ClearClipboard",
            "CopyCut",
            "Extract",
            "Filter", //U
            "GetFile",
            "ImageSize", //C R
            "MediaPreview",
            "SaveImage", //U
            "Sort", //D     
            "View", //C R
            "DuplicateFile",
            "PasteClipboard",            
            "SaveTextFile",
        );
        if( ! in_array($action,$allowed_action)){
            // ajax action is not allowed
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
        
        // Perform Action
        $ajaxaction = "Rabies\\FileManager\\Ajax\\" . $action;
        $perform = new $ajaxaction();
        $this->config = $config;
        $perform->action($this);
        
        //var_dump($perform->r);
        if($action = 'GetFile'){
            return new \Symfony\Component\HttpFoundation\Response($perform->r[0], 200);
        } else {
            return $app->json($perform->r[0], $perform->r[1]);
        }
        
        
//        if(isset($_GET['action']))
//                {
//                    switch($_GET['action'])
//                    {
//                                //case 'view':
//                                    
//                                //case 'filter':
//                                       
//                                //case 'sort':
//                                 
//                                //case 'image_size': // not used
//                                
//                                //case 'save_img': needs some tlc
//                                  
//                                //case 'extract': mabe couple tweaks
//                                        
//                                //case 'media_preview':tlc
//                                        
//                                //case 'copy_cut':
//                                        
//                                //case 'clear_clipboard':
//                                  
//                                //case 'chmod':
//                                 
//                                case 'get_lang':
//                                        if ( ! file_exists('lang/languages.php'))
//                                        {
//                                                response(trans('Lang_Not_Found'), 404)->send();
//                                                exit;
//                                        }
//
//                                        $languages = include 'lang/languages.php';
//                                        if ( ! isset($languages) || ! is_array($languages))
//                                        {
//                                                response(trans('Lang_Not_Found'), 404)->send();
//                                                exit;
//                                        }
//
//                                        $curr = $_SESSION['RF']['language'];
//
//                                        $ret = '<select id="new_lang_select">';
//                                        foreach ($languages as $code => $name)
//                                        {
//                                                $ret .= '<option value="' . $code . '"' . ($code == $curr ? ' selected' : '') . '>' . $name . '</option>';
//                                        }
//                                        $ret .= '</select>';
//
//                                        response($ret)->send();
//                                        exit;
//
//                                        break;
//                                case 'change_lang':
//                                        $choosen_lang = $_POST['choosen_lang'];
//
//                                        if ( ! file_exists('lang/' . $choosen_lang . '.php'))
//                                        {
//                                                response(trans('Lang_Not_Found'), 404)->send();
//                                                exit;
//                                        }
//
//                                        $_SESSION['RF']['language'] = $choosen_lang;
//                                        $_SESSION['RF']['language_file'] = 'lang/' . $choosen_lang . '.php';
//
//                                        break;
//                                case 'get_file': // preview or edit
//                                        
//
//                                        break;
//                            default: response('no action passed', 400)->send();
//                                        exit;
//                    }
//                }
//                else
//                {
//                        response('no action passed', 400)->send();
//                        exit;
             //   }
    }
}
