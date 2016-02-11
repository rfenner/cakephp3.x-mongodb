<?php
/**
 * Created by PhpStorm.
 * User: dormlock
 * Date: 1/11/15
 * Time: 12:23 AM
 */

namespace NoSql\Database\Type;


use NoSql\Database\NoSqlDriver;
use Symfony\Component\Config\Definition\Exception\Exception;

class FloatType extends \NoSql\Database\Type
{
    public function toDatabase($value, NoSqlDriver $driver)
    {
        if($value === null || $value === '') {
            return null;
        }
        return floatval($value);
    }

    public function toPHP($value, NoSqlDriver $driver)
    {
        if($value === null) {
            return null;
        }
        return floatval($value);
    }

    public function toStatement($value, NoSqlDriver $driver) {
        return Type::PARAM_STR;
    }

    public function marshal($value)
    {
        if($value === null || $value === '') {
            return null;
        }
        if(is_numeric($value)) {
            return (float)$value;
        }
        return $value;
    }
}