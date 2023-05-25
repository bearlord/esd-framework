<?php declare(strict_types=1);

namespace ESD\Nikic\PhpParser\ErrorHandler;

use ESD\Nikic\PhpParser\Error;
use ESD\Nikic\PhpParser\ErrorHandler;

/**
 * Error handler that handles all errors by throwing them.
 *
 * This is the default strategy used by all components.
 */
class Throwing implements ErrorHandler
{
    public function handleError(Error $error) {
        throw $error;
    }
}
