# TinyMCE FileManager Plugin with Silex backend

based on (https://github.com/trippo/ResponsiveFilemanager)

## Installation

Copy config file to config dir and add to project in app.php

``` php
$app['FileManager'] = function() {
        return require (__DIR__.'/../config/'."tinymce_filemanager.php");
    };
```

Add ControllerProvider

``` php
$app->mount('/filemanager', new \Rabies\FileManager\FileManagerControllerProvider());
```

Copy Templates folder to Your twig templates dir

----------
