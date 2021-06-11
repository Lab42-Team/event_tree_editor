<p align="center">
    <h1 align="center">Extended Event Tree Editor</h1>
    <br />
</p>

Extended Event Tree Editor (EETE) is a web-based tool for building Extended Event Tree Diagrams (EETD).

EETE is based on PHP 7 and [Yii 2 Framework](http://www.yiiframework.com/).

Editor uses [jsPlumb Toolkit](https://jsplumbtoolkit.com/), version 2.12.9 for EETD visualization.


[![Latest Stable Version](https://img.shields.io/packagist/v/yiisoft/yii2-app-basic.svg)](https://packagist.org/packages/yiisoft/yii2-app-basic)
[![Total Downloads](https://img.shields.io/packagist/dt/yiisoft/yii2-app-basic.svg)](https://packagist.org/packages/yiisoft/yii2-app-basic)
[![Build Status](https://travis-ci.org/yiisoft/yii2-app-basic.svg?branch=master)](https://travis-ci.org/yiisoft/yii2-app-basic)


DIRECTORY STRUCTURE
-------------------

      assets/             contains assets definition
      commands/           contains console commands (controllers) for creation lang, user and EETDs by default
      config/             contains application configurations
      modules/            contains two modules:
          editor/         contains main controllers, models and views for EETE
          main/           contains views for representation main index, contact and error views:
      web/                contains the entry script and Web resources


REQUIREMENTS
------------

The minimum requirement by this project template that your Web server supports PHP 7.0, jsPlumb 2.12, PostgreSQL 9.0.


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
    'password' => 'admin',
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