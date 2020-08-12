<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Plugins\Config;

use ReflectionClass;

/**
 * Trait ToConfigArray
 * @package ESD\Core\Plugins\Config
 */
trait ToConfigArray
{
    protected $reflectionClass;

    /**
     * To config array
     *
     * @throws \ReflectionException
     */
    public function toConfigArray()
    {
        $config = [];
        if ($this->reflectionClass == null) {
            $this->reflectionClass = new ReflectionClass(static::class);
        }
        foreach ($this->reflectionClass->getProperties() as $property) {
            if ($property->getDeclaringClass()->getName() != BaseConfig::class) {
                $varName = $property->getName();
                if ($property->isPrivate()) continue;
                if ($this->$varName !== null) {
                    if (is_array($this->$varName)) {
                        foreach ($this->$varName as $key => $value) {
                            if ($value instanceof BaseConfig) {
                                $config[$this->toConnectCase($varName)][$this->toConnectCase($key)] = $value->toConfigArray();
                            } else {
                                $config[$this->toConnectCase($varName)][$this->toConnectCase($key)] = $value;
                            }
                        }
                    } elseif ($this->$varName instanceof BaseConfig) {
                        $config[$this->toConnectCase($varName)] = $this->$varName->toConfigArray();
                    } else {
                        $config[$this->toConnectCase($varName)] = $this->$varName;
                    }
                }
            }
        }
        return $config;
    }

    /**
     * Camel case to underline case
     *
     * @param $var
     * @return mixed
     */
    protected function toConnectCase($var)
    {
        if (is_numeric($var)) {
            return $var;
        }
        $result = "";
        for ($i = 0; $i < strlen($var); $i++) {
            $str = ord($var[$i]);
            if ($str > 64 && $str < 91) {
                $result .= "_" . strtolower($var[$i]);
            } else {
                $result .= $var[$i];
            }
        }
        return $result;
    }

    /**
     * Build from config
     *
     * @param $config
     * @return static
     */
    public function buildFromConfig($config)
    {
        if ($config == null) {
            return $this;
        }
        foreach ($config as $key => $value) {
            $varName = $this->toCamelCase($key);
            $func = "set" . ucfirst($varName);
            if (is_callable([$this, $func])) {
                call_user_func([$this, $func], $value);
            }
        }
        return $this;
    }

    /**
     * Underline to Camel Case
     *
     * @param $var
     * @return mixed
     */
    protected function toCamelCase($var)
    {
        if (is_numeric($var)) {
            return $var;
        }
        $result = "";
        for ($i = 0; $i < strlen($var); $i++) {
            if ($var[$i] == "_") {
                $i = $i + 1;
                $result .= strtoupper($var[$i]);
            } else {
                $result .= $var[$i];
            }
        }
        return $result;
    }
}