<?php
/**
 * Created by PhpStorm.
 * User: dormlock
 * Date: 1/14/15
 * Time: 11:17 PM
 */

namespace NoSql\Datasource;


use Cake\Core\StaticConfigTrait;
use Cake\Datasource\Exception\MissingDatasourceConfigException;

class NoSqlConnnectionManager {
    use StaticConfigTrait {
        config as protected _config;
    }

    protected static $_aliasMap = [];

    /**
     * @var $_registery NoSqlConnectionRegistry
     */
    protected static $_registry = null;

    public static function config($key, $config = null)
    {
        if(is_array($config)) {
            $config['name'] = $key;
        }
        return static::_config($key, $config);
    }

    public static function alias($from, $to)
    {
        if(empty(static::$_config[$to]) && empty(static::$_config[$from])) {
            throw new MissingDatasourceConfigException(
                sprintf('Cannot create alias of "%s" as it does not exist.', $from)
            );
        }
        static::$_aliasMap[$to] = $from;
    }

    public static function dropAlias($name)
    {
        unset(static::$_aliasMap[$name]);
    }

    public static function get($name, $useAlias = true)
    {
        if($useAlias && isset(static::$_aliasMap[$name])) {
            $name = static::$_aliasMap[$name];
        }
        if(empty(static::$_config[$name])) {
            throw new MissingDatasourceConfigException(['name'=>$name]);
        }
        if(empty(static::$_registry)) {
            static::$_registry = new NoSqlConnectionRegistry();
        }
        if(isset(static::$_registry->{$name})) {
            return static::$_registry->{$name};
        }
        return static::$_registry->load($name, static::$_config[$name]);
    }
}