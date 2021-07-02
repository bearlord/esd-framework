<?php
/**
 * ESD framework
 * @author bearload <565364226@qq.com>
 */

namespace ESD\Plugins\JsonRpc;

/**
 * Class RequestException
 * @package ESD\Plugins\JsonRpc
 */
class RequestException extends \RuntimeException
{
    /**
     * @var array
     */
    protected $throwable;

    /**
     * @param $throwable
     * [
     *     'class' => 'RuntimeException', // The exception class name
     *     'code' => 0, // The exception code
     *     'message' => '', // The exception message
     *     'attributes' => [
     *         'message' => '', // The exception message
     *         'code' => 0, // The exception code
     *         'file' => '/opt/www/hyperf/app/JsonRpc/CalculatorService.php', // The file path which the exception occurred
     *         'line' => 99, // The line of file which the exception occurred
     *     ],
     * ]
     * @param string $message
     * @param int $code
     */
    public function __construct($message = '', $code = 0, array $throwable = [])
    {
        parent::__construct($message, $code);

        $this->throwable = $throwable;
    }

    public function getThrowable(): array
    {
        return $this->throwable;
    }

    public function getThrowableCode(): int
    {
        return intval($this->throwable['code'] ?? $this->throwable['attributes']['code'] ?? 0);
    }

    public function getThrowableMessage(): string
    {
        return strval($this->throwable['message'] ?? $this->throwable['attributes']['message'] ?? '');
    }

    public function getThrowableClassName(): string
    {
        return strval($this->throwable['class']);
    }
}