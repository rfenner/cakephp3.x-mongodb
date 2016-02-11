<?php
/**
 * Created by PhpStorm.
 * User: dormlock
 * Date: 1/10/15
 * Time: 10:57 PM
 */

namespace NoSql\Database\Type;

use NoSql\Database\NoSqlDriver;

class DateType  extends \NoSql\Database\Type\DateTimeType
{
    protected $_format = 'Y-m-d';

    public function toPHP($value, NoSqlDriver $driver)
    {
        $date = parent::toPHP($value, $driver);
        if($date instanceof \DateTime) {
            $date->setTime(0,0,0);
        }
        return $date;
    }

    public function marshal($value)
    {
        $date = parent::marshal($value);
        if($date instanceof \DateTime) {
            $date->setTime(0,0,0);
        }
        return $date;
    }
}