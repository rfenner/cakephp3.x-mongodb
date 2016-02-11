<?php
/**
 * Created by PhpStorm.
 * User: dormlock
 * Date: 1/11/15
 * Time: 12:23 AM
 */

namespace NoSql\Database\Type;

use NoSql\Database\NoSqlDriver;

class BinaryType {
    public function toDatabase($value, NoSqlDriver $driver)
    {
        return $value;
    }

    public function toPHP($value, NoSqlDriver $driver)
    {
        if($value === null) {
            return null;
        }

        if(is_string($value)) {
            return fopen('data:text/plain;base64,'.base64_encode($value), 'rb');
        }
        if(is_readable($value)) {
            return $value;
        }
        throw new Exception(sprintf('Unable to convert %s into binary.', gettype($value)));
    }

    public function toStatement($value, NoSqlDriver $driver) {
        return Type::PARAM_LOB;
    }

}