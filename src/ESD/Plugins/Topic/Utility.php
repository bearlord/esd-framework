<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/22
 * Time: 10:17
 */

namespace ESD\Plugins\Topic;

use ESD\Core\Exception;
use Psr\Log\LoggerInterface;

class Utility
{
    /**
     * Check Topic Filter
     *
     * Based on 4.7 Topic Names and Topic Filters
     *
     * @param string $topic_filter
     * @throws BadUTF8
     * @throws Exception
     */
    static public function CheckTopicFilter($topic_filter)
    {
        $max = DIGet(TopicConfig::class)->getTopicMaxLength();
        $length = strlen($topic_filter);
        if ($length == 0 || $length >= $max) {
            throw new Exception("Topic filter must be at 1~$max long");
        }
        self::ValidateUTF8($topic_filter);

        if (false !== strpos($topic_filter, chr(0))) {
            throw new Exception('Null character is not allowed in topic');
        }

        $length = strlen($topic_filter);

        /*
         The multi-level wildcard character MUST be specified either on its own or following a topic level separator.
         In either case it MUST be the last character specified in the Topic Filter [MQTT-4.7.1-2].
         */
        if (($p = strpos($topic_filter, '#')) !== false) {
            if ($p != $length - 1) {
                throw new Exception('"#" MUST be the last char in topic filter');
            } else if ($length > 1 && $topic_filter[$length - 2] != '/') {
                throw new Exception('"#" MUST occupy an entire level of the filter');
            }
        }

        $levels = explode('/', $topic_filter);
        foreach ($levels as $l) {
            if ($l == '') {
                continue;
            } else if (strpos($l, '+') !== false && isset($l[1])) {
                /*
                 The single-level wildcard can be used at any level in the Topic Filter, including first and last levels.
                 Where it is used it MUST occupy an entire level of the filter [MQTT-4.7.1-3].
                 */
                throw new Exception('"+" MUST occupy an entire level of the filter');
            }
        }

        if ($topic_filter[0] == '#') {
            DIGet(LoggerInterface::class)->debug('If you want to subscribe topic begin with $, please subscribe both "#" and "$SOMETOPIC/#"');
        }
    }

    /**
     * Check if string is UTF-8
     *
     * @param string $string
     * @return bool
     * @throws BadUTF8
     */
    static public function ValidateUTF8($string)
    {
        $pop_10s = 0;

        $unicode_char = 0;

        for ($i = 0; isset($string[$i]); $i++) {
            $c = ord($string[$i]);
            if ($pop_10s) {
                # Check if following chars in multibytes are not 10xxxxxx
                if (($c & 0xC0) != 0x80) {
                    throw new BadUTF8('Following characters must be 10xxxxxx');
                } else {
                    $unicode_char <<= 6;
                    $unicode_char |= $c & 0x3F;
                    --$pop_10s;
                }
            } else if (($c & 0x7F) == $c) {
                # single ASCII char
                $unicode_char = 0;

                /*
                 I tried mosquitto, it accepts \0 when publishing Message, no connection is closed.
                 No exception will be thrown here.

                 MQTT-1.5.3-2
                 A UTF-8 encoded string MUST NOT include an encoding of the null character U+0000.
                 If a receiver (Server or Client) receives a Control Packet containing U+0000 it MUST
                 close the Network Connection.

                 */
                continue;
            } else if (($c & 0xFE) == 0xFC) {
                # leading 1111110x
                $pop_10s = 5;

                $unicode_char = 0;
                $unicode_char |= $c & 0x01;
            } else if (($c & 0xFC) == 0xF8) {
                # leading 111110xx
                $pop_10s = 4;

                $unicode_char = 0;
                $unicode_char |= $c & 0x03;
            } else if (($c & 0xF8) == 0xF0) {
                # leading 11110xxx
                $pop_10s = 3;

                $unicode_char = 0;
                $unicode_char |= $c & 0x07;
            } else if (($c & 0xF0) == 0xE0) {
                # leading 1110xxxx
                $pop_10s = 2;

                $unicode_char = 0;
                $unicode_char |= $c & 0x0F;
            } else if (($c & 0xE0) == 0xC0) {
                # leading 110xxxxx
                $pop_10s = 1;

                $unicode_char = 0;
                $unicode_char |= $c & 0x1F;
            } else {
                throw new BadUTF8('Bad leading characters');
            }

            if ($unicode_char >= 0xD800 && $unicode_char <= 0xDFFF) {
                /*
                MQTT-1.5.3.1
                The character data in a UTF-8 encoded string MUST be well-formed UTF-8 as defined
                by the Unicode specification [Unicode] and restated in RFC 3629 [RFC3629]. In
                particular this data MUST NOT include encodings of code points between U+D800 and
                U+DFFF. If a Server or Client receives a Control Packet containing ill-formed UTF-8
                it MUST close the Network Connection [MQTT-1.5.3-1].

                 */
                throw new BadUTF8('U+D800 ~ U+DFFF CAN NOT be used in UTF-8');
            }
        }

        if ($pop_10s) {
            throw new BadUTF8('Missing UTF-8 following characters');
        }

        return true;
    }
}