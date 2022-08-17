<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Validate\Annotation;

use DI\DependencyException;
use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\CachedReader;
use ESD\Server\Coroutine\Server;
use ESD\Plugins\Validate\ValidationException;
use Inhere\Validate\Validation;
use ReflectionClass;

/**
 * 验证类
 * @Annotation
 * @Target("PROPERTY")
 */
class Validated extends Annotation
{
    protected static $cache = [];
    /**
     * 要求此字段/属性是必须的(不为空的)。
     * @var bool
     */
    public $required = false;
    /**
     * 验证是否是 int 支持范围检查
     * @var bool
     */
    public $integer = false;
    /**
     * 验证是否是 number(大于0的整数) 支持范围检查
     * @var bool
     */
    public $number = false;
    /**
     * 验证是否是 bool. 关于bool值
     * @var bool
     */
    public $boolean = false;
    /**
     * 验证是否是 float
     * @var bool
     */
    public $float = false;
    /**
     * 验证是否是 string. 支持长度检查
     * @var bool
     */
    public $string = false;
    /**
     * 验证的字段必须为 yes/on/1/true 这在确认「服务条款」是否同意时有用(ref laravel)
     * @var bool
     */
    public $accepted = false;
    /**
     * 验证是否是 url
     * @var bool
     */
    public $url = false;
    /**
     * 验证是否是 email
     * @var bool
     */
    public $email = false;
    /**
     * 验证值是否仅包含字母字符
     * @var bool
     */
    public $alpha = false;
    /**
     * 验证是否仅包含字母、数字
     * @var bool
     */
    public $alphaNum = false;
    /**
     * 验证是否仅包含字母、数字、破折号（ - ）以及下划线（ _ ）
     * @var bool
     */
    public $alphaDash = false;
    /**
     * 验证值是否是一个非自然数组 map (key - value 形式的)
     * @var bool
     */
    public $isMap = false;
    /**
     * 验证值是否是一个自然数组 list (key是从0自然增长的)
     * @var bool
     */
    public $isList = false;
    /**
     * 验证是否是数组
     * @var bool
     */
    public $isArray = false;
    /**
     * 验证字段值是否是一个 int list
     * @var bool
     */
    public $intList = false;
    /**
     *    验证字段值是否是一个 number list
     * @var bool
     */
    public $numList = false;
    /**
     * 验证字段值是否是一个 string list
     * @var bool
     */
    public $strList = false;
    /**
     * 验证字段值是否是一个 array list(多维数组)
     * @var bool
     */
    public $arrList = false;
    /**
     * 数组中的值必须是唯一的
     * @var bool
     */
    public $distinct = false;
    /**
     * 验证是否是 date
     * @var bool
     */
    public $date = false;
    /**
     * 验证是否是json字符串(默认严格验证，必须以{ [ 开始)
     * @var bool
     */
    public $json = false;
    /**
     * 验证是否是上传的文件
     * @var bool
     */
    public $file = false;
    /**
     * 验证是否是上传的图片文件
     * @var bool
     */
    public $image = false;
    /**
     * 验证是否是 IP
     * @var bool
     */
    public $ip = false;
    /**
     * 验证是否是 IPv4
     * @var bool
     */
    public $ipv4 = false;
    /**
     * 验证是否是 IPv6
     * @var bool
     */
    public $ipv6 = false;
    /**
     * 验证是否是 mac Address
     * @var bool
     */
    public $macAddress = false;
    /**
     *    验证是否是 md5 格式的字符串
     * @var bool
     */
    public $md5 = false;
    /**
     * 验证大小范围, 可以支持验证 int, string, array 数据类型,需要设置min，max
     * @var bool
     */
    public $between = false;
    /**
     * 长度验证（ 跟 size差不多, 但只能验证 string, array 的长度,需要设置min，max
     * @var bool
     */
    public $length = false;
    /**
     * 验证是否是 sha1 格式的字符串
     * @var bool
     */
    public $sha1 = false;
    /**
     * 验证是否是html color
     * @var bool
     */
    public $color = false;
    /**
     * 使用正则进行验证
     * @var string
     */
    public $regexp;
    /**
     * 验证是否是 date, 并且是指定的格式
     * @var string
     */
    public $dateFormat;
    /**
     * 验证是否是 date, 并且是否是等于给定日期
     * @var string
     */
    public $dateEquals;
    /**
     * 验证字段值必须是给定日期之前的值(ref laravel)
     * @var string
     */
    public $beforeDate;
    /**
     * 字段值必须是小于或等于给定日期的值(ref laravel)
     * @var string
     */
    public $beforeOrEqualDate;
    /**
     * 字段值必须是大于或等于给定日期的值(ref laravel)
     * @var string
     */
    public $afterOrEqualDate;
    /**
     * 验证字段值必须是给定日期之前的值
     * @var string
     */
    public $afterDate;
    /**
     * 固定的长度/大小(验证 string, array 长度, int 大小)
     * @var int
     */
    public $fixedSize;
    /**
     * 值(string/array)是以给定的字符串开始
     * @var string
     */
    public $startWith;
    /**
     * 值(string/array)是以给定的字符串结尾
     * @var string
     */
    public $endWith;
    /**
     * 枚举验证: 不包含
     * @var array
     */
    public $notIn;
    /**
     * 枚举验证: 包含
     * @var array
     */
    public $in;
    /**
     * 枚举验证: 字段值 存在于 另一个字段（anotherField）的值中
     * @var string
     */
    public $inField;
    /**
     * 必须是等于给定值
     * @var string
     */
    public $mustBe;
    /**
     * 不能等于给定值
     * @var string
     */
    public $notBe;
    /**
     * 字段值比较: 相同
     * @var string
     */
    public $eqField;
    /**
     * 字段值比较: 不能相同
     * @var string
     */
    public $neqField;
    /**
     * 字段值比较: 小于
     * @var string
     */
    public $ltField;
    /**
     * 字段值比较: 小于等于
     * @var string
     */
    public $lteField;
    /**
     * 字段值比较: 大于
     * @var string
     */
    public $gtField;
    /**
     * 字段值比较: 大于等于
     * @var string
     */
    public $gteField;
    /**
     * 最小边界值验证
     * @var int
     */
    public $min;
    /**
     * 最大边界值验证
     * @var int
     */
    public $max;

    /**
     * 场景
     * @var string
     */
    public $scene;

    public function build($name)
    {
        $noMinMax = false;
        if ($this->string || $this->integer || $this->number || $this->between || $this->length) {
            $noMinMax = true;
        }
        $result = [];
        foreach ($this as $key => $value) {
            if ($key == "scene") {
                continue;
            }
            if ($noMinMax && ($key == "min" || $key == "max")) {
                continue;
            }
            $one = [$name];
            if ($value === true) {
                $one[] = $key;
            } else if ($value != null) {
                $one[] = $key;
                $one[] = $value;
            }
            if (count($one) > 1) {
                if ($key == "string" || $key == "integer" || $key == "number" || $key == "between" || $key == "length") {
                    if ($this->min != null) {
                        $one["min"] = $this->min;
                    }
                    if ($this->max != null) {
                        $one["max"] = $this->max;
                    }
                }
                if (!empty($this->scene)) {
                    $one["on"] = $this->scene;
                }
                $result[] = $one;
            }
        }
        return $result;
    }

    /**
     * @param ReflectionClass|string $reflectionClass
     * @param $values
     * @param array $roles
     * @param array $messages
     * @param array $translates
     * @param string $scene
     * @return array|\stdClass
     * @throws ValidationException
     */
    public static function valid($reflectionClass, $values, $roles = [], $messages = [], $translates = [], $scene = "")
    {
        $validRole = self::buildRole($reflectionClass, $roles);
        if (!empty($validRole)) {
            $validation = Validation::check($values, $validRole, $translates, $scene);
            $validation->setMessages($messages);
            if ($validation->failed()) {
                throw new ValidationException($validation->firstError());
            }
            return $validation->getSafeData();
        }
        return $values;
    }

    /**
     * @param ReflectionClass|string $reflectionClass
     * @param array $roles
     * @return array
     * @throws DependencyException
     * @throws \DI\NotFoundException
     * @throws \ReflectionException
     */
    public static function buildRole($reflectionClass, $roles = [])
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
        $validRole = [];
        foreach ($reflectionClass->getProperties() as $property) {
            $validateds = DIget(CachedReader::class)->getPropertyAnnotations($property);
            foreach ($validateds as $validated) {
                if ($validated instanceof Validated) {
                    foreach ($validated->build($property->name) as $one) {
                        $validRole[] = $one;
                    }
                }
            }
        }
        self::$cache[$reflectionClass->name] = array_merge($validRole, $roles);
        return self::$cache[$reflectionClass->name];
    }
}
