<?php

namespace Rabies\FileManager\Action;

class Chmod {  
    
    public $r;  
    
    public function action(){
        $mode = $_POST['new_mode'];
        $rec_option = $_POST['is_recursive'];
        $valid_options = array('none', 'files', 'folders', 'both');
        $chmod_perm = (is_dir($path) ? $chmod_dirs : $chmod_files);

        // check perm
        if ($chmod_perm === FALSE) {
            $fileORfolder = is_dir($path) ? 'folders' : 'files';
            $response = "Changing" . $fileORfolder . "permissions are not allowed.";
            $this->r = array($response, 403);
            return;
        }

        // check mode
        if (!preg_match("/^[0-7]{3}$/", $mode)){
            $this->r = array('The supplied permission mode is incorrect.', 400);
            return;
        }

        // check recursive option
        if (!in_array($rec_option, $valid_options)){
            $this->r = array("wrong option", 400);
            return;
        }

        // check if server disabled chmod
        if (is_function_callable('chmod') === FALSE){
            $this->r = array('The chmod function has been disabled by the server.', 'chmod', 403);
            return;
        }

        $mode = "0".$mode;
        $mode = octdec($mode);

        rchmod($path, $mode, $rec_option);
    }    
}
