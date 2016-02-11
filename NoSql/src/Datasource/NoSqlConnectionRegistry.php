<?php
/**
 * Created by PhpStorm.
 * User: dormlock
 * Date: 1/14/15
 * Time: 11:29 PM
 */

namespace NoSql\Datasource;

use Cake\Core\App;
use Cake\Core\ObjectRegistry;
use Cake\Datasource\Exception\MissingDatasourceException;

class NoSqlConnectionRegistry extends ObjectRegistry
{

    protected function _resolveClassName($class)
    {
        if(is_object($class)) {
            return $class;
        }
        return App::className($class, 'NoSqlDatasource');
    }

    protected function _throwMissingClassError($class, $plugin)
    {
        throw new MissingDatasourceException([
            'class'=>$class,
            'plugin'=>$plugin
        ]);
    }

    protected function _create($class, $alias, $settings)
    {
        unset($settings['className']);
        return new $class($settings);
    }

    public function unload($name)
    {
        unset($this->_loaded[$name]);
    }
}