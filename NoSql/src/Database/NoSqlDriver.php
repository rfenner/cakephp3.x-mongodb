<?php
/**
 * Created by PhpStorm.
 * User: dormlock
 * Date: 1/10/15
 * Time: 6:06 PM
 */

namespace NoSql\Database;

use Cake\Database\Exception\MissingConnectionException;
use Cake\Database\Exception\MissingDriverException;
use Cake\Database\Exception\MissingExtensionException;

abstract class NoSqlDriver {
    protected $_config;

    protected $_baseConfig = [];

    protected $_connection = null;

    public function __construct($config = [])
    {
        if(empty($config['username']) && !empty($config['login'])) {
            throw new \InvalidArgumentException('Please pass "username" instead of "login" to the database');
        }
        $config += $this->_baseConfig;
        $this->_config = $config;
    }

    abstract public function connect();

    abstract public function disconnect();

    abstract public function connection($connectio = null);

    abstract public function enabled();

    public function isConnnected()
    {
        return $this->_connection !== null;
    }

    public function __destruct()
    {
        $this->_connection = null;
    }

    public function __debugInfo()
    {
        return [
            'connected' => $this->isConnnected()
        ];
    }
}