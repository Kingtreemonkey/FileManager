<?php

namespace Rabies\FileManager\Ajax;

use Rabies\FileManager\Utils\SessionHandler;

class ClearClipboard {
    public $r;
    public function action($parent){
        $s = new SessionHandler($parent->app);
        $s->setClipboardPath(null);
        $s->setClipboardPathThumb(null);        
        $this->r = array("",200);
    }   
}
