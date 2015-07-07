<?php

namespace Rabies\FileManager\Utils;

use Silex\Application;

class SessionHandler {
    private $session;
    private $config;
    
    private $verify;
    private $subfolder;
    private $view_type;
    private $filter;
    private $sort_by;
    private $descending;
    
    //clipboard
    private $clipboard_action;
    private $clipboard;


    private $cookie_last_position;


    public function __construct(Application $app) {
        $this->session = $app['session'];
        $this->config = $app['FileManager'];
        $this->initViewType();
        $this->initFilter();
        $this->initSortBy();
        $this->initDescending();
    }
    
    public function getSubfolder(){
        
    }
    
    public function setSubfolder($subfolder){
        
    }
    
    /* VIEW TYPE */
    public function getViewType(){
        return $this->view_type;
    }
    
    public function setViewType($view_type){
        $this->session->set('view_type', $view_type);                
    }
    public function initViewType(){
        if($this->session->get('view_type')===NULL){
            $this->view_type = $this->config["default_view_type"];
        } else {
            $this->view_type = $this->session->get('view_type');
        }
    }
    /* END VIEW TYPE */
    
    /* FILTER */
    
    public function getFilter(){
        return $this->filter;
    }
    public function setFilter($filter){
        $this->session->set('filter', $filter);
    }
    public function initFilter(){
        if($this->session->get('filter')===NULL){
            $this->filter = $this->config["default_filter"]; //could also be ""
        } else {
            $this->filter = $this->session->get('filter');
        }
    }    
    /*END FILTER */
    
    /* SORT BY */
    
    public function getSortBy(){
        return $this->sort_by;
    }
    public function setSortBy($sort_by){
        $this->session->set('sort_by', $sort_by);
    }
    public function initSortBy(){
        if($this->session->get('sort_by')===NULL){
            $this->sort_by = 'name';
        } else {
            $this->sort_by = $this->session->get('sort_by');
        }
    }    
    
    /* END SORT BY */
    
    /* Descending */
    
    public function getDescending(){
        return $this->descending;
    }
    public function setDescending($descending){
        $this->session->set('descending', $descending);
    }
    public function initDescending(){
        if($this->session->get('descending')===NULL){
            $this->descending = 1;
        } else {
            $this->sort_by = $this->session->get('descending');
        }
    }
 
    /* END DESCENDING */
    
    
    private function fix_get_params($str){
	return strip_tags(preg_replace("/[^a-zA-Z0-9\.\[\]_| -]/", '', $str));
    }
    
    /* CLIPBOARD */
        /*clipboard_path*/
    public function setClipboardPath($str){
        $this->session->set('clipboard_path',$str);
    }    
    public function getClipboardPath(){
        return $this->session->get('clipboard_path');
    }
    public function deleteClipboardPath(){
        return $this->session->remove('clipboard_path');
    }
        /*clipboard_path_thumb*/
    public function setClipboardPathThumb($str){
        $this->session->set('clipboard_path_thumb',$str);
    }    
    public function getClipboardPathThumb(){
        return $this->session->get('clipboard_path_thumb');
    }
    public function deleteClipboardPathThumb(){
        return $this->session->remove('clipboard_path_thumb');
    }
        /*clipboard_action*/
    public function setClipboardAction($str){
        $this->session->set('clipboard_action',$str);
    }
    public function getClipboardAction(){
        return $this->session->get('clipboard_action');        
    }
    public function deleteClipboardAction(){
        return $this->session->remove('clipboard_action');        
    }
    
    /* END CLIPBOARD */
}
