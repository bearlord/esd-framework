<?php

namespace ESD\Nikic\PhpParser;


spl_autoload_register(function ($class) {
    if (strpos($class, 'ESD\\Nikic\\PhpParser\\') === 0) {
        $name = substr($class, strlen('ESD\\Nikic\\PhpParser'));
        require __DIR__ . strtr($name, '\\', DIRECTORY_SEPARATOR) . '.php';
    }
});
