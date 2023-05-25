<?php

namespace ESD\Nikic\FastRoute;

require __DIR__ . '/functions.php';

spl_autoload_register(function ($class) {
    if (strpos($class, 'ESD\\Nikic\\FastRoute\\') === 0) {
        $name = substr($class, strlen('ESD\Nikic\FastRoute'));
        require __DIR__ . strtr($name, '\\', DIRECTORY_SEPARATOR) . '.php';
    }
});
