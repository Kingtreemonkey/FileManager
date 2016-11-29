# TinyMCE FileManager Plugin with Silex backend

Based on (https://github.com/trippo/ResponsiveFilemanager)

and see author repo (https://github.com/Kingtreemonkey/FileManager)

## Installation

### manipulation on Silex

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

### Add plugin to TinyMCE

copy plugin file to tinymce plugin folder

add responsivefilemanager to plugin list and buttons list in tinyMCE init

add path to file manager and title

```
external_filemanager_path:"/filemanager",
filemanager_title:"Responsive Filemanager"
```

for example
```
tinymce.init({
selector: '.wysiwyg',
language: 'en',
browser_spellcheck: true,
theme: 'modern',
plugins: [
'advlist autolink lists link image charmap print preview hr anchor pagebreak',
'searchreplace wordcount visualblocks visualchars code fullscreen',
'insertdatetime media nonbreaking save table contextmenu directionality',
'emoticons template paste textcolor colorpicker textpattern imagetools localautosave responsivefilemanager'
],
toolbar1: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent ',
toolbar2: 'print preview | fontsizeselect forecolor backcolor emoticons | link media image responsivefilemanager | localautosave',
relative_urls: false,
image_advtab: true,
external_filemanager_path:"/filemanager",
filemanager_title:"Responsive Filemanager"
});

```
