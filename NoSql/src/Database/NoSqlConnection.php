<?php
/**
 * Created by PhpStorm.
 * User: dormlock
 * Date: 1/10/15
 * Time: 4:55 PM
 */

namespace NoSql\Database;

use Cake\Database\Exception\MissingConnectionException;
use Cake\Database\Exception\MissingDriverException;
use Cake\Database\Exception\MissingExtensionException;

class NoSqlConnection
{
    use TypeConverterTrait;

    protected $_config;

    protected $_driver = null;

    protected $_logger = null;

    public function __construct($config)
    {
        $this->_config = $config;

        $driver = '';
        if(!empty($config['driver'])) {
            $driver = $config['driver'];
        }
        $this->driver($driver, $config);
    }

    public function __destruct()
    {
        unset($this->_driver);
    }

    public function config()
    {
        return $this->_config();
    }

    public function configName()
    {
        if(empty($this->_config['name'])) {
            return '';
        }
        return $this->_config['name'];
    }

    public function driver($driver = null,  $config = [])
    {
        if($driver === null) {
            return $this->_driver;
        }
        if(is_string($driver)) {
            if(!class_exists($driver)) {
                throw new MissingDriverException(['driver' => $driver]);
            }
            $driver = new $driver($config);
        }
        if(!$driver->enabled()) {
            throw new MissingExtensionException(['driver' => get_class($driver)]);
        }
        return $this->_driver = $driver;
    }

    public function connect()
    {
        try {
            $this->_driver->connect();
            return true;
        } catch (\Exception $e) {
            throw new MissingConnectionException(['reason' => $e->getMessage()]);
        }
    }

    public function disconnect()
    {
        $this->_driver->disconnect();
    }

    public function isConnected()
    {
        $this->_driver->isConnected();
    }
}