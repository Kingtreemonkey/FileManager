<?php
namespace Rabies\FileManager;

use Silex\Application;
use Silex\ControllerProviderInterface;

class FileManagerControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app) {

        $indexController = $app['controllers_factory'];
        $indexController->match('/', 'Rabies\FileManager\Dialog::Dialog');
        $indexController->match('upload', 'Rabies\FileManager\Upload::upload');
        $indexController->match('download', 'Rabies\FileManager\Download::download');
        $indexController->match('action/{action}', 'Rabies\FileManager\Action\ActionHandler::action');
        $indexController->match('ajax/{action}', 'Rabies\FileManager\Ajax\AjaxHandler::ajax');
        return $indexController;
    }



}