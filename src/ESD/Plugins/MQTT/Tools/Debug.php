<?php
/**
 * ESD framework
 * @author Lu Fei <lufei@simps.io>
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Plugins\MQTT\Tools;

class Debug
{
    private $encode;

    /**
     * @param string $encode
     */
    public function __construct(string $encode = '')
    {
        $this->encode = $encode;
    }

    /**
     * @param string $encode
     * @return $this
     */
    public function setEncode(string $encode): self
    {
        $this->encode = $encode;

        return $this;
    }

    /**
     * @return string
     */
    public function getEncode(): string
    {
        return $this->encode;
    }

    /**
     * @return string
     */
    public function hexDump(): string
    {
        return $this->toHexDump($this->getEncode());
    }

    /**
     * @return string
     */
    public function hexDumpAscii(): string
    {
        return $this->toHexDump($this->getEncode(), true);
    }

    /**
     * @return string
     */
    public function ascii(): string
    {
        return $this->toAscii($this->getEncode());
    }

    /**
     * @return string
     */
    public function printableText(): string
    {
        return $this->getEncode();
    }

    /**
     * @return string
     */
    public function hexStream(): string
    {
        return bin2hex($this->getEncode());
    }

    /**
     * @param string $contents
     * @param bool $hasAscii
     * @return string
     */
    private function toHexDump(string $contents, bool $hasAscii = false): string
    {
        $address = $column = 0;
        $result = $hexDump = $asciiDump = '';

        $sprintf = '%08x    %-48s';

        if ($hasAscii) {
            $sprintf = '%08x    %-48s   %s';
        }

        foreach (str_split($contents) as $c) {
            $hexDump = $hexDump . sprintf('%02x ', ord($c));
            if ($hasAscii) {
                if (ord($c) > 31 && ord($c) < 128) {
                    $asciiDump .= $c;
                } else {
                    $asciiDump .= '.';
                }
            }
            $column++;
            if (($column % 16) == 0) {
                $line = sprintf($sprintf, $address, $hexDump, $asciiDump);
                $result .= $line . PHP_EOL;

                $asciiDump = '';
                $hexDump = '';
                $column = 0;
                $address += 16;
            }
        }

        if ($column > 0) {
            $line = sprintf($sprintf, $address, $hexDump, $asciiDump);
            $result .= $line;
        }

        return $result;
    }

    /**
     * @param string $contents
     * @return string
     */
    private function toAscii(string $contents): string
    {
        $address = $column = 0;
        $result = $asciiDump = '';

        $sprintf = '%08x    %s';

        foreach (str_split($contents) as $c) {
            if (ord($c) > 31 && ord($c) < 128) {
                $asciiDump .= $c;
            } else {
                $asciiDump .= '.';
            }

            $column++;
            if (($column % 16) == 0) {
                $line = sprintf($sprintf, $address, $asciiDump);
                $result .= $line . PHP_EOL;

                $asciiDump = '';
                $column = 0;
                $address += 16;
            }
        }

        if ($column > 0) {
            $line = sprintf($sprintf, $address, $asciiDump);
            $result .= $line;
        }

        return $result;
    }
}
