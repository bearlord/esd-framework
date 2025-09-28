<?php
/**
 * ESD framework
 * @author Lu Fei <lufei@simps.io>
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Plugins\MQTT\Message;

use ESD\Plugins\MQTT\Protocol\ProtocolInterface;

abstract class AbstractMessage
{
    /**
     * @var int
     */
    protected $protocolLevel = ProtocolInterface::MQTT_PROTOCOL_LEVEL_3_1_1;

    /**
     * @var array
     */
    protected $properties = [];

    /**
     * @param array $data
     */
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

    /**
     * @return int
     */
    public function getProtocolLevel(): int
    {
        if (!empty($this->getProperties()) && $this->protocolLevel !== ProtocolInterface::MQTT_PROTOCOL_LEVEL_5_0) {
            return ProtocolInterface::MQTT_PROTOCOL_LEVEL_5_0;
        }

        return $this->protocolLevel;
    }

    /**
     * @param int $protocolLevel
     * @return $this
     */
    public function setProtocolLevel(int $protocolLevel): self
    {
        $this->protocolLevel = $protocolLevel;

        return $this;
    }

    /**
     * @return array
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @param array $properties
     * @return $this
     */
    public function setProperties(array $properties): self
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * @return bool
     */
    public function isMQTT5()
    {
        return $this->getProtocolLevel() === ProtocolInterface::MQTT_PROTOCOL_LEVEL_5_0;
    }

    /**
     * @param bool $getArray
     * @return mixed
     */
    abstract public function getContents(bool $getArray = false);

    /**
     * @return mixed
     */
    public function toArray()
    {
        return $this->getContents(true);
    }

    /**
     * @return mixed
     */
    public function __toString()
    {
        return $this->getContents();
    }
}
