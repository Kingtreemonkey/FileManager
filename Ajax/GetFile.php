<?php

namespace Rabies\FileManager\Ajax;

class GetFile {
    public $r;
    public function action($parent){
        $c = $parent->config;
        $sub_action = $_GET['sub_action'];
        $preview_mode = $_GET["preview_mode"];

        if ($sub_action != 'preview' && $sub_action != 'edit'){
                $this->r = array("wrong action",400);
                return;
        }

        $selected_file = ($sub_action == 'preview' ? $_GET['file'] : $c['current_path'] . $_POST['path']);
        $info = pathinfo($selected_file);

        if ( ! file_exists($selected_file)){
            $this->r = array('Could not find the file.',404);
            return;                
        }

        if ($preview_mode == 'text')
        {
                $is_allowed = ($sub_action == 'preview' ? $c['preview_text_files'] : $c['edit_text_files']);
                $allowed_file_exts = ($sub_action == 'preview' ? $c['previewable_text_file_exts'] : $c['editable_text_file_exts']);
        }
        elseif ($preview_mode == 'viewerjs')
        {
                $is_allowed = $c['viewerjs_enabled'];
                $allowed_file_exts = $c['viewerjs_file_exts'];
        }
        elseif ($preview_mode == 'google')
        {
                $is_allowed = $c['googledoc_enabled'];
                $allowed_file_exts = $c['googledoc_file_exts'];
        }
        if ( ! isset($allowed_file_exts) || ! is_array($allowed_file_exts))
        {
                $allowed_file_exts = array();
        }

        if ( ! in_array($info['extension'], $allowed_file_exts)
                || ! isset($is_allowed)
                || $is_allowed === false
                || ! is_readable($selected_file)
        )
        {            
                $this->r = array(sprintf('You are not allowed to %s this file.', ($sub_action == 'preview' ? 'open' : 'Edit')), 403);
                return;
        }

        if ($sub_action == 'preview')
        {
                if ($preview_mode == 'text')
                {
                        // get and sanities
                        $data = stripslashes(htmlspecialchars(file_get_contents($selected_file)));

                        $ret = '';                        
                        if ( ! in_array($info['extension'],$c['previewable_text_file_exts_no_prettify']))
                        {
                            
                                $ret .= '<script src="https://google-code-prettify.googlecode.com/svn/loader/run_prettify.js?lang='.$info['extension'].'&skin=sunburst"></script>';
                                $ret .= '<div class="text-center"><strong>'.$info['basename'].'</strong></div><pre class="prettyprint">'.$data.'</pre>';
                        }
                        else
                        {
                                $ret .= '<div class="text-center"><strong>'.$info['basename'].'</strong></div><pre class="no-prettify">'.$data.'</pre>';
                        }

                }
                elseif ($preview_mode == 'viewerjs')
                {
                        $ret = '<iframe id="viewer" src="js/ViewerJS/#../../'.$_GET["file"].'" allowfullscreen="" webkitallowfullscreen="" class="viewer-iframe"></iframe>';

                }
                elseif ($preview_mode == 'google')
                {
                        $url_file = $c['base_url'] . $c['upload_dir'] . str_replace($c['current_path'], '', $_GET["file"]);
                        $googledoc_url = urlencode($url_file);
                        $googledoc_html = "<iframe src=\"http://docs.google.com/viewer?url=" . $googledoc_url . "&embedded=true\" class=\"google-iframe\"></iframe>";
                        $ret = '<div class="text-center"><strong>' . $info['basename'] . '</strong></div>' . $googledoc_html . '';
                }
        }
        else
        {
                $data = stripslashes(htmlspecialchars(file_get_contents($selected_file)));
                $ret = '<textarea id="textfile_edit_area" style="width:100%;height:300px;">'.$data.'</textarea>';
        }
        
        $this->r = array($ret,200);        
    }
}
