<?php

namespace Rabies\FileManager\Action;
use Rabies\FileManager\Utils\Utility;
class CreateFile {
    public $r;
    
    public function action($parent){
        $config = $parent->config;
        $path = $parent->path;
        $path_thumb = $parent->path_thumb;
        $name = $parent->name;
        $util = new Utility();
        if ($create_text_files === FALSE) {
                $this->r = array('You are not allowed to edit this file.', 403);
                return;
            }
            
            if (!isset($config['editable_text_file_exts']) || !is_array($config['editable_text_file_exts'])){
                $config['editable_text_file_exts'] = array();
            }

            // check if user supplied extension            
            if (strpos($name, '.') === FALSE){
                $this->r = array('You have to add a file extension. '.sprintf('Valid extensions: %s', implode(', ', $config['editable_text_file_exts'])), 400);
                return;
            }

            // correct name
            $old_name = $name;
            $name=$util->fix_filename($name,$config['transliteration'],$config['convert_spaces'], $config['replace_with']);
            if (empty($name))
            {
                $this->r = array('The name is empty', 400);
                return;
            }

            // check extension
            $parts = explode('.', $name);
            if (!in_array(end($parts), $config['editable_text_file_exts'])) {
                $this->r = array('File extension is not allowed. '.sprintf('Valid extensions: %s', implode(', ', $config['editable_text_file_exts'])), 400);
                return;
            }

            // correct paths
            $path = str_replace($old_name, $name, $path);
            $path_thumb = str_replace($old_name, $name, $path_thumb);

            // file already exists
            if (file_exists($path)) {
                $this->r = array('The file is already exists', 403);
		return;
            }

            $content = $_POST['new_content'];
            
            if (@file_put_contents($path, $content) === FALSE) {
                $this->r = array('There was an error while saving the file.', 500);
		return;
            }
            else {
                if ($util->is_function_callable('chmod') !== FALSE){
                    chmod($path, 0644);
                }
                $this->r = array('File successfully saved.', 200);
                return;
            }
    }
}
