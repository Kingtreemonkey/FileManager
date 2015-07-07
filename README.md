# FileManager
Silex FileManager 

Routing (in silex)
where admin is secured

will need some updates in order to find the twig templates ect.

    $app->get('admin/filemanager', 'Rabies\FileManager\Dialog::Dialog');
    $app->match('admin/filemanager/upload', 'Rabies\FileManager\Upload::upload');
    $app->match('admin/filemanager/download', 'Rabies\FileManager\Download::download');
    $app->match('admin/filemanager/action/{action}', 'Rabies\FileManager\Action\ActionHandler::action');
    $app->match('admin/filemanager/ajax/{action}', 'Rabies\FileManager\Ajax\AjaxHandler::ajax');


----------
