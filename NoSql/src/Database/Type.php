<?php
/**
 * Created by PhpStorm.
 * User: dormlock
 * Date: 1/10/15
 * Time: 7:02 PM
 */

namespace NoSql\Database;

use NoSql\Database\NoSqlDriver;

class Type {
    const PARAM_BOOL = 5;

    const PARAM_NULL = 0;

    const PARAM_INT = 1;

    const PARAM_STR = 2;

    const PARAM_LOB = 3;

    protected static $_types = [
        'integer' => 'NoSql\DatabaseType\IntegerType',
        'float' => 'NoSql\DatabaseType\FloatType',
        'datetime' => 'NoSql\DatabaseType\DateTimeType',
        'timestamp' => 'NoSql\DatabaseType\DateTimeType',
        'time' => 'NoSql\DatabaseType\TimeType',
        'date' => 'NoSql\DatabaseType\DateType',
    ];

    protected static $_basicTypes = [
        'string' => ['callback' => 'strval'],
        'text' => ['callback' => 'strval'],
        'boolean' => [
            ['callback' => 'boolval'],
            'type' => Type::PARAM_BOOL
        ],
    ];

    protected static $_builtTypes = [];

    protected $_name = null;

    public function __construct($name = null)
    {
        $this->_name = $name;
    }

    public static function build($name)
    {
        if(isset(static::$_builtTypes[$name])) {
            return static::$_builtTypes[$name];
        }
        if(isset(static::$_basicTypes[$name])) {
            return static::$_basicTypes[$name];
        }
        if(!isset(static::$_types[$name])) {
            throw new \InvalidArgumentException(sprintf('Unknown type "%s"', $name));
        }
        return static::$_builtTypes[$name] = new static::$_types[$name]($name);
    }

    public static function map($type = null, $className = null)
    {
        if($type === null) {
            return self::$_types;
        }
        if(!is_string($type)) {
            self::$_types = $type;
            return;
        }
        if($className === null) {
            return isset(self::$_types[$type]) ? self::$_types[$type] : null;
        }
        self::$_types[$type] = $className;
    }

    public static function clear()
    {
        self::$_types = [];
        self::$_builtTypes = [];
    }

    public function getName()
    {
        return $this->_name;
    }

    public function toDatabase($value, NoSqlDriver $driver)
    {
        return $this->_basicTypeCast($value, $driver);
    }

    public function toPHP($value, NoSqlDriver $driver)
    {
        return $this->_basicTypeCast($value, $driver);
    }

    protected function _basicTypeCast($value, NoSqlDriver $driver)
    {
        if($value === null) {
            return null;
        }

        if(!empty(self::$_basicTypes[$this->_name])) {
            $typeInfo = self::$_basicTypes[$this->_name];
            if(isset($typeInfo['callback'])) {}
            return $typeInfo['callback']($value);
        }
        return $value;
    }

    public function toStatement($value, NoSqlDriver $driver)
    {
        if($value === null) {
            return Type::PARAM_NULL;
        }

        if(!empty(self::$_basicTypes[$this->_name])) {
            $typeInfo = self::$_basicTypes[$this->_name];
            return isset($typeInfo['type']) ? $typeInfo['type'] : Type::PARAM_STR;
        }

        return Type::PARAM_STR;
    }

    public static function boolval($value)
    {
        if(is_string($value) && !is_numeric($value)) {
            return strtolower($value) === 'true' ? true : false;
        }
        return !empty($value);
    }

    public function newId()
    {
        return null;
    }

    public function marshal($value)
    {
        return $value;
    }
}