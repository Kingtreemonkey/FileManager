<?php

namespace Rabies\FileManager;

use Rabies\FileManager\Utils\Utility;
use Silex\Application;
use Symfony\Component\HttpFoundation\Response;

class Download {
    public function download(Application $app){
        $r = new Response();
        $util = new Utility();
        $_path = $_POST['path'];
        
        $c = $app['FileManager'];
        $c['ext'] =  array_merge(
            $c['ext_img'],
            $c['ext_file'],
            $c['ext_misc'],
            $c['ext_video'],
            $c['ext_music']
        );
//        include 'include/mime_type_lib.php';

        if ( strpos($_path, '/') === 0 || strpos($_path, '../') !== false || strpos($_path, './') === 0){
                return $r->create('wrong path', 400);                
        }


        if (strpos($_POST['name'], '/') !== false){
                return $r->create('wrong path', 400);                
        }

        $path = $c['current_path'] . $_path;
        $name = $_POST['name'];

        $info = pathinfo($name);

        if ( ! in_array($util->fix_strtolower($info['extension']), $c['ext'])){
                return $r->create('wrong extension', 400);	
        }

        if ( ! file_exists($path . $name)){
            return $r->create('File not found', 404);	
        }
        return $app->sendFile($path . $name)->setContentDisposition(\Symfony\Component\HttpFoundation\ResponseHeaderBag::DISPOSITION_ATTACHMENT,$name);
//$img_size = (string) (filesize($path . $name)); // Get the image size as string
//
//$mime_type = get_file_mime_type($path . $name); // Get the correct MIME type depending on the file.
//
//response(file_get_contents($path . $name), 200, array(
//	'Pragma'              => 'private',
//	'Cache-control'       => 'private, must-revalidate',
//	'Content-Type'        => $mime_type,
//	'Content-Length'      => $img_size,
//	'Content-Disposition' => 'attachment; filename="' . ($name) . '"'
//))->send();
//
//exit;
    }
}
