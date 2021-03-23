# Phapi

Headless CMS engine, written in php, written for you ❤.

## Startup configuration

In order to handle incoming request with Phapi, you have to redirect the request to a php file (for example: index.php).
If you are using apache server, write a .htaccess file an enable url rewriteing module.

Example content of .htaccess:

```
RewriteEngine On
RewriteRule ^([^?]*) index.php?url=$1 [L,QSA]

<Limit GET POST PUT OPTIONS DELETE>
    Require all granted
</Limit>
<LimitExcept GET POST PUT OPTIONS DELETE>
    Require all denied
</LimitExcept>

```

In php file, you must provide the ROOT constant as the root of phapi server, to handle dynamic imports correctly.
Make an instance of Phapi, register the handlers, and call run method.

Example of index.php:

```php
<?php

define('ROOT', dirname(__FILE__));
define('DS', DIRECTORY_SEPARATOR);

require_once("phapi/Phapi.php");

Phapi::setMysqli(new mysqli(
    "localhost",
    "user",
    "password",
    "database"
));

Phapi::serveApi("api/");
Phapi::serveFolder(ROOT . DS . "public");
Phapi::serveFallbackFile(ROOT . DS . "public" . DS . "index.html");

Phapi::run();

```
