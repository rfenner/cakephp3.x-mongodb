<?php
/**
 * Created by PhpStorm.
 * User: dormlock
 * Date: 1/10/15
 * Time: 10:52 PM
 */

namespace NoSql\Database\Type;

use NoSql\Database\NoSqlDriver;

class IntegerType extends \NoSql\Database\Type
{
    public function toDataBase($value, NoSqlDriver $driver)
    {
        if($value === null || $value === '') {
            return null;
        }
        return (int)$value;
    }

    public function toPHP($value, NoSqlDriver $driver)
    {
        if($value === null) {
            return null;
        }
        return (int)$value;
    }

    public function toStatement($value, NoSqlDriver $driver)
    {
        return Type::PARAM_INT;
    }

    public function marshal($value)
    {
        if($value === null || $value === '') {
            return null;
        }
        if(is_int($value)) {
            return $value;
        }

        if(ctype_digit($value)) {
            return (int)$value;
        }
        return $value;
    }
}