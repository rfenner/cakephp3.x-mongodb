<?php
/**
 * Created by PhpStorm.
 * User: dormlock
 * Date: 1/10/15
 * Time: 10:58 PM
 */

namespace NoSql\Database\Type;

class TimeType  extends \NoSql\Database\Type\DateTimeType
{
    protected $_format = "H:i:s";
}