<?php

namespace Rabies\FileManager\Ajax;

use Rabies\FileManager\Utils\Utility;

class Extract {
    public $r;
            
    public function action($parent){
        $c = $parent->config;
        $util = new Utility();
        if (strpos($_POST['path'], '/') === 0 || strpos($_POST['path'], '../') !== false || strpos($_POST['path'], './') === 0){
            $this->r = array('wrong path', 400);
            return;            
        }
        $path = $c['current_path'] . $_POST['path'];
        $info = pathinfo($path);
        $base_folder = $c['current_path'] . $util->fix_dirname($_POST['path']) . "/";
        switch ($info['extension']){
            case "zip":
                $zip = new \ZipArchive;
                if ($zip->open($path) === true)
                {
                    //make all the folders
                    for ($i = 0; $i < $zip->numFiles; $i++)
                    {
                        $OnlyFileName = $zip->getNameIndex($i);
                        $FullFileName = $zip->statIndex($i);
                        if (substr($FullFileName['name'], -1, 1) == "/")
                        {
                            $util->create_folder($base_folder . $FullFileName['name']);
                        }
                    }
                    //unzip into the folders
                    for ($i = 0; $i < $zip->numFiles; $i++)
                    {
                        $OnlyFileName = $zip->getNameIndex($i);
                        $FullFileName = $zip->statIndex($i);

                        if ( ! (substr($FullFileName['name'], -1, 1) == "/"))
                        {
                            $fileinfo = pathinfo($OnlyFileName);
                            if (in_array(strtolower($fileinfo['extension']), $ext))
                            {
                                copy('zip://' . $path . '#' . $OnlyFileName, $base_folder . $FullFileName['name']);
                            }
                        }
                    }
                    $zip->close();
                    
                }else{
                    $this->r =array('Could not extract. File might be corrupt.', 500);
                    return;
                }
            break;
            case "gz":
                $p = new \PharData($path);
                $p->decompress(); // creates files.tar
            break;
            case "tar":
                // unarchive from the tar
                $phar = new \PharData($path);
                $phar->decompressFiles();
                $files = array();
                $util->check_files_extensions_on_phar($phar, $files, '', $ext);
                $phar->extractTo($current_path . fix_dirname($_POST['path']) . "/", $files, true);
            break;

            default:
                $this->r = array('This extension is not supported. Valid: zip, gz, tar.', 400);
                return;
            break;
            }
                                                
    }
}
