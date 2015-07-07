<?php

namespace Rabies\FileManager\Ajax;

use Rabies\FileManager\Utils\Utility;
use Rabies\FileManager\Utils\SessionHandler;
class CopyCut {
    
    public $r;
    
    public function error($str){
        $this->r = array($str,400);
    }
    
    public function action($parent){
        $s = new SessionHandler($parent->app);
        $util = new Utility();
        $c=$parent->config;
        
        if ($_POST['sub_action'] != 'copy' && $_POST['sub_action'] != 'cut'){
            $this->error('wrong sub-action');
            return;
        }
        if (trim($_POST['path']) == '' || trim($_POST['path_thumb']) == ''){
            $this->error('no path');
            return;
        }
        $path = $c['current_path'] . $_POST['path'];

        if (is_dir($path)){
            // can't copy/cut dirs
            if ($c['copy_cut_dirs'] === false){
                $this->error(sprintf('You are not allowed to %s $s.', ($_POST['sub_action'] == 'copy' ? 'copy' : 'cut'), 'folders'));
                return;
            }

            // size over limit
            if ($c['copy_cut_max_size'] !== false && is_int($c['copy_cut_max_size'])){
                if (($copy_cut_max_size * 1024 * 1024) < $util->foldersize($path)){
                    $this->error(sprintf('The selected files/folders are too big to %s. Limit: %d MB/operation', ($_POST['sub_action'] == 'copy' ? 'copy' : 'cut'), $c['copy_cut_max_size']));
                    return;
                }
            }

            // file count over limit
            if ($copy_cut_max_count !== false && is_int($copy_cut_max_count)){
                if ($copy_cut_max_count < filescount($path)){
                    $this->error(sprintf('You selected too many files/folders to %s. Limit: %d files/operation', ($_POST['sub_action'] == 'copy' ? 'copy' : 'cut'), $c['copy_cut_max_count']));
                    return;
                }
            }
        }else{
            // can't copy/cut files
            if ($c['copy_cut_files'] === false)
            {
                    $this->error(sprintf('You are not allowed to %s files.', ($_POST['sub_action'] == 'copy' ? 'copy' : 'cut'), 'files'));
                    exit;
            }
        }
        $s->setClipboardPath($_POST['path']);
        $s->setClipboardPathThumb($_POST['path_thumb']);
        $s->setClipboardAction($_POST['sub_action']);
    }
}
