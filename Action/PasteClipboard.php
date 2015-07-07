<?php

namespace Rabies\FileManager\Action;

use Rabies\FileManager\Utils\SessionHandler;
use Rabies\FileManager\Utils\Utility;
class PasteClipboard {
    
    public $r;
    
    public function action($parent){ 
        $util = new Utility();
        $app = $parent->app;
        $session = new SessionHandler($app);
        $path = $parent->path;
        $path_thumb = $parent->path_thumb;
        $c = $parent->config;
        $action = $session->getClipboardAction();
        $data = array(
            //$_SESSION['RF']['clipboard'];
            "path" => $session->getClipboardPath(),
            "path_thumb" => $session->getClipboardPathThumb(),

        );
        
        if ( ! isset($action, $data['path'], $data['path_thumb']) || $action == '' || $data['path'] == '' || $data['path_thumb'] == ''){
            $this->r = array('no clipboard data found.', 200);      
            return;		
        }
            
        $data['path'] = $c['current_path'].$data['path'];
        $pinfo = pathinfo($data['path']);

        // user wants to paste to the same dir. nothing to do here...
        if ($pinfo['dirname'] == rtrim($path, '/')) {
            $this->r = array('', 200);      
            return;
        }

        // user wants to paste folder to it's own sub folder.. baaaah.
        if (is_dir($data['path']) && strpos($path, $data['path']) !== FALSE){
            $this->r = array('', 200);      
            return;
        }

        // something terribly gone wrong
        if ($action != 'copy' && $action != 'cut'){
            $this->r = array('no action', 400);      
            return;            
        }

        // check for writability
        if ($util->is_really_writable($path) === FALSE || $util->is_really_writable($path_thumb) === FALSE){
            $this->r = array('The directory you selected is not writable <br/>'.str_replace('../','',$path).'<br/>'.str_replace('../','',$path_thumb), 403);
            return;
        }

        // check if server disables copy or rename        
        if ($util->is_function_callable(($action == 'copy' ? 'copy' : 'rename')) === FALSE){
            $response = sprintf('The %s function has been disabled by the server.',($action == 'copy' ? 'copy' : 'cut'));
            $this->r = array($response, 403);
            return;
        }

        if ($action == 'copy')
        {
            
            $util->rcopy($data['path'], $path);
            $util->rcopy($data['path_thumb'], $path_thumb);
        }
        elseif ($action == 'cut')
        {
            
            $util->rrename($data['path'], $path);
            $util->rrename($data['path_thumb'], $path_thumb);

            // cleanup
            if (is_dir($data['path']) === TRUE){
                $util->rrename_after_cleaner($data['path']);
                $util->rrename_after_cleaner($data['path_thumb']);
            }
        }

        // cleanup
        $session->setClipboardAction(NULL);
        $session->setClipboardPath(NULL);
        $session->setClipboardPathThumb(NULL);  
        $response = $action . ' successful';
        $this->r = array($response, 200);
    }
}
