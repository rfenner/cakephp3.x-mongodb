<?php
/**
 * Created by PhpStorm.
 * User: dormlock
 * Date: 1/10/15
 * Time: 7:03 PM
 */

namespace NoSql\Database;


trait TypeConverterTrait {

    public function cast($value, $type)
    {
        if(is_string($type)) {
            $type = Type::build($type);
        }
        if($type instanceof Type) {
            $value = $type->toDataBase($value, $this->_driver);
            $type = $type->toStatement($value, $this->_driver);
        }
        return [$value, $type];
    }

    public function matchTypes($columns, $types)
    {
        if(!is_int(key($types))) {
            $positions  = array_intersect_key(array_flip($columns), $types);
            $types = array_intersect_key($types, $positions);
            $types = array_combine($positions, $types);
        }
        return $types;
    }
}