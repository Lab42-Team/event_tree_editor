<p align="center"><h1 align="center">Extended Event Tree Editor</h1><br /></p>

<b>The Extended Event Tree Editor (EETE)</b> is a web-based tool for designing Extended Event Tree Diagrams (EETD).

EETE is based on the [PHP 7](https://www.php.net/releases/7_0_0.php) and the [Yii 2 Framework](http://www.yiiframework.com/).

Editor uses [jsPlumb Toolkit](https://jsplumbtoolkit.com/), version 2.12.9 for EETD visualization.


[![Latest Stable Version](https://img.shields.io/packagist/v/yiisoft/yii2-app-basic.svg)](https://packagist.org/packages/yiisoft/yii2-app-basic)
[![Total Downloads](https://img.shields.io/packagist/dt/yiisoft/yii2-app-basic.svg)](https://packagist.org/packages/yiisoft/yii2-app-basic)
[![Build Status](https://travis-ci.org/yiisoft/yii2-app-basic.svg?branch=master)](https://travis-ci.org/yiisoft/yii2-app-basic)


DIRECTORY STRUCTURE
-------------------

      assets/             contains assets definition
      commands/           contains console commands (controllers) for creation langs, users and diagrams by default
      components/         contains XML/OWL importers and XML generator
      config/             contains application configurations (db, web)
      messages/           contains localization files for Russian and English
      migrations/         contains all migrations for database
      modules/            contains two modules:
          editor/         contains main models, controllers and views for EETE
          main/           contains views for representation of main index, contact and error
      web/                contains css-scripts, js-scripts, images and other web resources


REQUIREMENTS
------------

The minimum requirement by this project template that your Web server supports <b>PHP 7.0</b>, <b>jsPlumb 2.12</b>, <b>PostgreSQL 9.0</b>.


INSTALLATION
------------

### Install via Composer

If you do not have [Composer](http://getcomposer.org/), you may install it by following the instructions
at [getcomposer.org](http://getcomposer.org/doc/00-intro.md#installation-nix).

You can then install this editor using the following command:

~~~
composer create-project nikita-dorodnykh/eeteditor
~~~


CONFIGURATION
-------------

### Database

Edit the file `config/db.php` with real data, for example:

```php
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'pgsql:host=localhost;port=5432;dbname=eeteditor;',
    'username' => 'postgres',
    'password' => 'root',
    'charset' => 'utf8',
    'tablePrefix' => 'eeteditor_',
    'schemaMap' => [
        'pgsql'=> [
            'class'=>'yii\db\pgsql\Schema',
            'defaultSchema' => 'public'
        ]
    ],
];
```

**NOTES:**
- EETE won't create the database for you, this has to be done manually before you can access it.
- Check and edit the other files in the `config/` directory to customize your application as required.