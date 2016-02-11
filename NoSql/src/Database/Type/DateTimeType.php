<?php
/**
 * Created by PhpStorm.
 * User: dormlock
 * Date: 1/10/15
 * Time: 10:57 PM
 */

namespace NoSql\Database\Type;

use NoSql\Database\NoSqlDriver;

class DateTimeType extends \NoSql\Database\Type
{

    public static $dateTimeClass = 'Cake\I18n\Time';

    protected $_format = 'Y-m-d H:i:s';

    public function __construct($name = null)
    {
        parent::__construct($name);
        if(!class_exists(static::$dateTimeClass)) {
            static::$dateTimeClass = 'DateTime';
        }
    }

    public function toDatabase($value, NoSqlDriver $driver)
    {
        if($value === null || is_string($value)) {
            return  $value;
        }
        if(is_int($value)) {
            $value = new static::$dateTimeClass('@'.$value);
        }

        return $value->format($this->_format);
    }

    public function toPHP($value, NoSqlDriver $driver)
    {
        if($value === null) {
            return null;
        }
        list($value) = explode('.', $value);
        $class = static::$dateTimeClass;
        return $class::createFromFormat($this->_format, $value);
    }

    public function marshal($value)
    {
        if($value instanceof \DateTime) {
            return value;
        }
        $class = static::$dateTimeClass;
        try {
            $compare = $date = false;
            if($value === '' || $value === null || $valaue === false || $value === true) {
                return null;
            } elseif (is_numeric($value)) {
                $date = new $class('@'.$value);
            } elseif (is_string($value)) {
                $date = new $class($value);
                $compare = true;
            }
            if($compare && $date && $date->format($this->_format) !== $value) {
                return $value;
            }
            if($date) {
                return $date;
            }
        } catch(\Exception $e) {
            return $value;
        }

        $value += ['hour'=> 0, 'minute'=>0, 'second'=>0];

        $format = '';
        if(
            isset($value['year'], $value['month'], $value['day']) &&
            (is_numeric($value['year']) && is_numeric($value['month']) && is_numeric($value['day']))
        ) {
            $format .= sprintf('%d-%02d-%02d', $value['year'], $value['month'], $value['day']);
        }
        if(isset($value['meridian'])) {
            $value['hour'] = strtolower($value['meridian']) === 'am' ? $value['hour'] : $value['hour']+12;
        }
        $format .= sprintf('%s%02d:%02d:%02d', (empty($format)  ? '' : ' '), $value['hour'], $value['minute'], $value['second']);

        return new $class($format);
    }
}