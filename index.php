<?php
spl_autoload_register(function ($name) {
    $name = 'src/' . str_replace('Bittr\\', DIRECTORY_SEPARATOR, $name) . '.php';
    include $name;
});

Session::id('00ea34c53f4c67e1159334d1a605bb2a');
$session = Session::start();

$session->registerErrorHandler(function($error, $error_code)
{
    var_dump($error, $error_code);
});





