<?php

namespace Rabies\FileManager\Ajax;

use Rabies\FileManager\Utils\Utility;

class Chmod {
    public $r;
    public function action($parent){
        $util = new Utility();
        $c = $parent->config;
        $path = $c['current_path'] . $_POST['path'];
        if (
                (is_dir($path) && $c['chmod_dirs'] === false)
                || (is_file($path) && $c['chmod_files'] === false)
                || ($util->is_function_callable("chmod") === false) )
        {
                $this->r=array(sprintf('Changing %s permissions are not allowed.', (is_dir($path) ? 'folders' : 'files')),403);
                return;
        }
        else
        {
            
                $perm = decoct(fileperms($path) & 0777);
                $perm_user = substr($perm, 0, 1);
                $perm_group = substr($perm, 1, 1);
                $perm_all = substr($perm, 2, 1);

                $ret = '<div id="files_permission_start">
                <form id="chmod_form">
                        <table class="file-perms-table">
                                <thead>
                                        <tr>
                                                <td></td>
                                                <td>r&nbsp;&nbsp;</td>
                                                <td>w&nbsp;&nbsp;</td>
                                                <td>x&nbsp;&nbsp;</td>
                                        </tr>
                                </thead>
                                <tbody>
                                        <tr>
                                                <td>User</td>
                                                <td><input id="u_4" type="checkbox" data-value="4" data-group="user" onChange="chmod_logic();"'.($util->chmod_logic_helper($perm_user, 4) ? " checked" : "").'></td>
                                                <td><input id="u_2" type="checkbox" data-value="2" data-group="user" onChange="chmod_logic();"'.($util->chmod_logic_helper($perm_user, 2) ? " checked" : "").'></td>
                                                <td><input id="u_1" type="checkbox" data-value="1" data-group="user" onChange="chmod_logic();"'.($util->chmod_logic_helper($perm_user, 1) ? " checked" : "").'></td>
                                        </tr>
                                        <tr>
                                                <td>Group</td>
                                                <td><input id="g_4" type="checkbox" data-value="4" data-group="group" onChange="chmod_logic();"'.($util->chmod_logic_helper($perm_group, 4) ? " checked" : "").'></td>
                                                <td><input id="g_2" type="checkbox" data-value="2" data-group="group" onChange="chmod_logic();"'.($util->chmod_logic_helper($perm_group, 2) ? " checked" : "").'></td>
                                                <td><input id="g_1" type="checkbox" data-value="1" data-group="group" onChange="chmod_logic();"'.($util->chmod_logic_helper($perm_group, 1) ? " checked" : "").'></td>
                                        </tr>
                                        <tr>
                                                <td>All</td>
                                                <td><input id="a_4" type="checkbox" data-value="4" data-group="all" onChange="chmod_logic();"'.($util->chmod_logic_helper($perm_all, 4) ? " checked" : "").'></td>
                                                <td><input id="a_2" type="checkbox" data-value="2" data-group="all" onChange="chmod_logic();"'.($util->chmod_logic_helper($perm_all, 2) ? " checked" : "").'></td>
                                                <td><input id="a_1" type="checkbox" data-value="1" data-group="all" onChange="chmod_logic();"'.($util->chmod_logic_helper($perm_all, 1) ? " checked" : "").'></td>
                                        </tr>
                                        <tr>
                                                <td></td>
                                                <td colspan="3"><input type="text" name="chmod_value" id="chmod_value" value="'.$perm.'" data-def-value="'.$perm.'"></td>
                                        </tr>
                                </tbody>
                        </table>';

                if (is_dir($path))
                {
                        $ret .= '<div>Apply recursively?
                                        <ul>
                                                <li><input value="none" name="apply_recursive" type="radio" checked> No </li>
                                                <li><input value="files" name="apply_recursive" type="radio"> Files</li>
                                                <li><input value="folders" name="apply_recursive" type="radio"> Folders </li>
                                                <li><input value="both" name="apply_recursive" type="radio"> Files & Folders</li>
                                        </ul>
                                    </div>';
                }

                $ret .= '</form></div>';
                $this->r = array($ret,200);                
        }
                                        
    }
}
