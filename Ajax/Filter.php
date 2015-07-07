<?php

namespace Rabies\FileManager\Ajax;

use Rabies\FileManager\Utils\SessionHandler;
/**
 * Description of View
 *
 * @author monkey
 */
class Filter {
    public $r;
    
    public function action($parent){
        if(isset($_GET['type'])){
            $s = new SessionHandler($parent->app);
            $s->setFilter($_GET['type']);
            $this->r = array("",200);
            return;
        } else {
            $this->r = array('filter type number missing', 400);
            return;            
        }            
    }
}
