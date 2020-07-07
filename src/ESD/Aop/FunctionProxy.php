<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace ESD\Aop;

/**
 * Class FunctionProxy
 * @package ESD\Aop
 */
class FunctionProxy extends \Go\Proxy\FunctionProxy
{
    public function __toString()
    {
        $functionsCode = (
            $this->namespace->getDocComment() . "\n" . // Doc-comment for file
            'namespace ' . // 'namespace' keyword
            $this->namespace->getName() . // Name
            ";\n" . // End of namespace name
            implode("\n", $this->functionsCode) // Function definitions
        );

        return $functionsCode
            // Inject advices on call
            . PHP_EOL
            . '\\' . __CLASS__ . "::injectJoinPoints('"
            . $this->namespace->getName() . "',"
            . var_export($this->advices, true) . ');';
    }
}