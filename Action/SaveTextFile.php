<?php

namespace Rabies\FileManager\Action;

class SaveTextFile {
    
    public $r;   
    
    public function action(){
        $content = $_POST['new_content'];
        // $content = htmlspecialchars($content); not needed
        // $content = stripslashes($content);

        // no file
        if (!file_exists($path)) {
            $this->r = array('File_Not_Found', 404);            
            return;
        }

        // not writable or edit not allowed
        if (!is_writable($path) || $edit_text_files === FALSE) {
            $this->r = array('You are not allowed to edit this file.', 403);
            return;
        }

        if (@file_put_contents($path, $content) === FALSE) {
            $this->r = array('There was an error while saving the file.', 500);
            return;
        }
        else {
            response('File successfully saved.',200);
            return;
        }
    }
}
