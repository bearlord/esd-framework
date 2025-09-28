<?php
/**
 * ESD framework
 * @author Lu Fei <lufei@simps.io>
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Plugins\MQTT\Config;

abstract class AbstractConfig
{
    public function __construct(array $data = [])
    {
        foreach ($data as $k => $v) {
            $methodName = 'set' . ucfirst($k);
            if (method_exists($this, $methodName)) {
                $this->{$methodName}($v);
            } else {
                $this->{$k} = $v;
            }
        }
    }
}
