<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace ESD\Yii\Base;

/**
 * ExitException represents a normal termination of an application.
 *
 * Do not catch ExitException. Yii will handle this exception to terminate the application gracefully.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ExitException extends \Exception
{
    /**
     * @var int the exit status code
     */
    public $statusCode;


    /**
     * Constructor.
     * @param int|null $status the exit status code
     * @param string|null $message error message
     * @param int|null $code error code
     * @param \Exception|null $previous The previous exception used for the exception chaining.
     */
    public function __construct(?int $status = 0, ?string $message = null, ?int $code = 0, ?\Exception $previous = null)
    {
        $this->statusCode = $status;
        parent::__construct($message, $code, $previous);
    }
}
