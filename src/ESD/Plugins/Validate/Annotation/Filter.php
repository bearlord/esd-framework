<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Validate\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\CachedReader;
use Inhere\Validate\Filter\Filtration;
use ReflectionClass;

/**
 * @Annotation
 * @Target("PROPERTY")
 * Class Filter
 * @package ESD\Plugins\Validate\Annotation
 */
class Filter extends Annotation
{
    protected static $cache = [];

    /**
     * Absolute value
     * @var bool
     */
    public $abs = false;

    /**
     * Filter illegal characters and convert to int
     * @var bool
     */
    public $integer = false;

    /**
     * Convert to bool
     * @var bool
     */
    public $boolean = false;

    /**
     * Filter illegal characters and retain data in float format
     * @var bool
     */
    public $float = false;

    /**
     * Filter illegal characters and convert to string
     * @var bool
     */
    public $string = false;

    /**
     * Remove leading and trailing whitespace characters, support for arrays.
     * @var bool
     */
    public $trim = false;

    /**
     * Convert \n \r \n \r to <br/>
     * @var bool
     */
    public $nl2br = false;

    /**
     * Convert string to lower case
     * @var bool
     */
    public $lowercase = false;

    /**
     * String to uppercase
     * @var bool
     */
    public $uppercase = false;

    /**
     * Convert string to snake style
     * @var bool
     */
    public $snakeCase = false;

    /**
     * Convert string to camel style
     * @var bool
     */
    public $camelCase = false;

    /**
     * Convert string to time
     * @var bool
     */
    public $strToTime = false;

    /**
     * URL filtering, removing all characters that do not match the URL
     * @var bool
     */
    public $url = false;

    /**
     * String to array 'tag0, tag1'-> ['tag0', 'tag1']
     * @var bool
     */
    public $str2array = false;

    /**
     * Remove duplicate values from an array (by array_unique ())
     * @var bool
     */
    public $unique = false;

    /**
     * email filtering, remove all characters that do not match email
     * @var bool
     */
    public $email = false;

    /**
     * Removes characters not needed for URL encoding, similar to urlencode () function
     * @var bool
     */
    public $encoded = false;

    /**
     * Clear spaces
     * @var bool
     */
    public $clearSpace = false;

    /**
     * Clean up newlines
     * @var bool
     */
    public $clearNewline = false;

    /**
     * Equivalent to using strip_tags()
     * @var bool
     */
    public $stripTags = false;

    /**
     * Equivalent to escaping data using htmlspecialchars()
     * @var bool
     */
    public $escape = false;

    /**
     * Apply addslashes() to escape data
     * @var bool
     */
    public $quotes = false;

    public function build($name)
    {
        $result = [$name];
        $filter = [];
        foreach ($this as $key => $value) {
            if ($value === true) {
                $filter[] = $key;
            }
        }
        if (!empty($filter)) {
            $result[] = implode("|", $filter);
            return $result;
        } else {
            return null;
        }
    }


    /**
     * @param ReflectionClass|string $reflectionClass
     * @param $values
     * @return array
     * @throws \ReflectionException
     */
    public static function filter($reflectionClass, $values)
    {
        $filterRole = self::buildRole($reflectionClass);
        if (!empty($filterRole)) {
            $result = Filtration::make($values, $filterRole)->filtering();
            foreach ($filterRole as $role) {
                $values[$role[0]] = $result[$role[0]];
            }
        }
        return $values;
    }

    /**
     * @param ReflectionClass|string $reflectionClass
     * @return array
     * @throws \ReflectionException
     */
    public static function buildRole($reflectionClass)
    {
        if (is_string($reflectionClass)) {
            if (array_key_exists($reflectionClass, self::$cache)) {
                return self::$cache[$reflectionClass];
            }
            $reflectionClass = new ReflectionClass($reflectionClass);
        }
        if (array_key_exists($reflectionClass->name, self::$cache)) {
            return self::$cache[$reflectionClass->name];
        }
        $filterRole = [];
        foreach ($reflectionClass->getProperties() as $property) {
            $filters = DIget(CachedReader::class)->getPropertyAnnotations($property);
            foreach ($filters as $filter) {
                if ($filter instanceof Filter) {
                    $one = $filter->build($property->name);
                    if (!empty($one)) {
                        $filterRole[] = $one;
                    }
                }
            }
        }
        self::$cache[$reflectionClass->name] = $filterRole;
        return $filterRole;
    }
}