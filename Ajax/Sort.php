<?php

namespace Rabies\FileManager\Ajax;

use Rabies\FileManager\Utils\SessionHandler;
/**
 * Description of View
 *
 * @author monkey
 */
class Sort {
    public $r;    
    
    public function action($parent){
        $s = new SessionHandler($parent->app);
        if (isset($_GET['sort_by']))
        {
            $s->setSortBy($_GET['sort_by']);
            $this->success();
            return;
        } else {
            $this->error("invalid sort_by");
            return;
        }

        if (isset($_GET['descending']))
        {
            $descending = $_GET['descending'] === "TRUE";
            $s->setDescending($descending);
            return;
        }else {
            $this->error("invalid descending");
            return;
        }        
        $this->error("invalid sorting action");
        return;
    }
    
    protected function error($str){
       $this->r = array($str,400);
    }
    
    protected function success(){
       $this->r = array("success",400);
    }
}
