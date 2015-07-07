<?php

namespace Rabies\FileManager\Ajax;

use Rabies\FileManager\Utils\SessionHandler;
/**
 * Description of View
 *
 * @author monkey
 */
class View {
    public $r;
    
    public function action($parent){
        if(isset($_GET['type'])){
            $s = new SessionHandler($parent->app);
            $s->setViewType($_GET['type']);
            $this->r = array("",200);
            return;
        } else {
            $this->r = array('view type number missing', 400);
            return;            
        }            
    }
}
