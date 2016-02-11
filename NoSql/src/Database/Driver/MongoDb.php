<?php
/**
 * Created by PhpStorm.
 * User: dormlock
 * Date: 1/10/15
 * Time: 6:04 PM
 */

namespace NoSql\Database\Driver;

use Cake\Log\Log;

class MongoDb extends \NoSql\Database\NoSqlDriver
{
  /**
   * Base Config
   * set_string_id:
   *      true: In read() method, convert MongoId object to string and set it to array 'id'
   *      false: not convert and set
   * persist_id: the id of the persistent connection to use. For driver version < 1.2.0 this should be a
   *      true or false value. For 1.2.0 and greater it's an id to persistent connection to use.
   * host: hte host to connect to. the replica set option overrides this
   * username: username to connect with if auth is enabled. replica set will use this if auth enabled
   * password:  password to connect with if auth is enabled. replica set will use this if auth enabled
   * replicaset:
   *      auth:
   *          true: will build auth string to add to the seed list
   *          false: seeds have no auth
   *      seeds : array of seed hosts
   *      set_name: the name of the replica set
   * require_version: when checking if the driver is enabled will check the driver version is >= to this version
   *      if it doesn't meet the require_version the driver will be considered disabled.
   *
   * @var array
   */
  protected $_baseConfig = [
      'set_string_id'   => true,
      'persist_id'      => true,
      'host'            => 'localhost',
      'database'        => '',
      'port'            => '27017',
      'username'        => '',
      'password'        => '',
      'replicaset'      => [],
      'require_version' => '1.0.2',
      'options'         => ['connect' => true],
      'driver_options'  => []
  ];

  protected $_driverVersion = null;

  protected $_class = 'MongoClient';

  protected $_db = null;

  protected $_mongodb = false;

  public function connect()
  {
    $host    = $this->createConnectionName($this->_config, $this->_driverVersion);
    $options = $this->_config['options'];

    if(!empty($this->_config['replicaset']) && (
            $this->_driverVersion >= '1.0.9' || $this->_mongodb)
    ) {
      //replicaSet added in version 1.0.9 and above
      if(!isset($this->_config['replicaset']['seeds']) || empty($this->_config['replicaset']['seeds'])) {
        throw new \InvalidArgumentException('You  must specify at least one seed server when using a replicaset');
      }
      if(!isset($this->_config['replicaset']['set_name']) || empty($this->_config['replicasaet']['set_name'])) {
        throw new \InvalidArgumentException('You must specify a set name when using a replicaset');
      }

      if($this->_driverVersion < '1.2.0' && !$this->_mongodb) {
        //replicaSet was a boolean before 1.2.0
        $options['replicaSet'] = (is_string($this->_config['replicaset']['set_name']) ? true : $this->_config['replicaset']['set_name']);
      } else {
        $options['replicaSet'] = $this->_config['replicaset']['set_name'];
      }
      $host = $this->createReplicaSeedList($this->_config, $this->_driverVersion);
    }

    if($this->_driverVersion < '1.0.2' && !$this->_mongodb) {
      $persist    = is_string($this->_config['persist_id']) ? true : $this->_config['persist_id'];
      $connection = new $this->_class($host, $options['connect'], $persist);
    } else {
      $connection = new $this->_class($host, $options, $this->_config['driver_options']);
    }
    $this->connection($connection);

    //since we can't just call a method to get the DB we specified when connecting
    if(!empty($this->_config['database'])) {
      if($this->_mongodb) {
        $this->_db = $this->_connection->selectDatabase($this->_config['database']);
      } else {
        $this->_db = $this->_connection->selectDB($this->_config['database']);
      }
    }
  }

  public function connection($connection = null)
  {
    if($connection !== null) {
      $this->_connection = $connection;
    }

    return $this->_connection;
  }

  public function disconnect()
  {
    if($this->_connection != null && !$this->_mongodb) {
      $this->_connection->close();
    }
    $this->_db         = null;
    $this->_connection = null;
  }

  public function enabled()
  {
    //Legacy Extension
    if(extension_loaded('Mongo')) {
      Log::write('debug', 'Using Legacy mongo extension');
      if(class_exists('Mongo')) {
        $this->_driverVersion = \Mongo::VERSION;
        $this->_class         = 'Mongo';
      } else {
        $this->_driverVersion = \MongoClient::VERSION;
      }
      //since config should be set we will check if we have the requested version
      //if there is no config then return true
      if(empty($this->_config) || $this->_driverVersion >= $this->_config['require_version']) {
        return true;
      }
      //New Driver used with the PHP Library mongodb
    } elseif(extension_loaded('MongoDB')) {
      Log::write('debug', 'Using new mongodb extension');
      $this->_mongodb       = true;
      $this->_driverVersion = MONGODB_VERSION;
      $this->_class         = '\MongoDB\Client';
      if(empty($this->_config) || $this->_driverVersion >= $this->_config['require_version']) {
        return true;
      }
    }

    return false;
  }

  protected function createConnectionName($config, $version)
  {
    $host = null;
    if($version >= '1.0.2' || $this->_mongodb) {
      $host = "mongodb://";
    } else {
      $host = '';
    }

    if(!empty($config['username'])) {
      $host .= $config['username'] . ':' . $config['password'] . '@';
    }

    $host .= $config['host'] . ':' . $config['port'];

    if(!empty($config['database'])) {
      $host .= '/' . $config['database'];
    }

    return $host;
  }

  protected function createReplicaSeedList($config, $version)
  {
    $host = null;
    if($version >= '1.0.2' || $this->_mongodb) {
      $host = "mongodb://";
    } else {
      $host = '';
    }
    if($config['replicaset']['auth'] === true && (empty($config['username']) || empty($config['password']))) {
      throw new \InvalidArgumentException('When using auth must provide a username and password to connect with.');
    }

    if($config['replicaset']['auth'] === true && !empty($config['username'])) {
      $host .= $config['username'] . ':' . $config['password'] . '@';
    }
    foreach($config['replicaset']['seeds'] as $seed) {
      $host .= $seed . ',';
    }
    $host = rtrim($host, ',');
    if(!empty($config['database'])) {
      $host .= '/' . $config['database'];
    }

    return $host;
  }

  public function selectDB($db_name = null)
  {
    if($db_name === null) {
      $db_name = $this->_config['database'];
    }
    if(empty($db_name) || !$this->isConnnected()) {
      return false;
    }
    try {
      if($this->_mongodb) {
        $this->_db = $this->_connection->selectDatabase($db_name);
      } else {
        $this->_db = $this->_connection->selectDB($db_name);
      }
      return true;
    } catch(\Exception $e) {
      $this->_db = null;

      return false;
    }
  }

  public function getCollection($coll_name)
  {
    if(empty($coll_name) || !$this->isConnnected() || $this->_db === null) {
      return null;
    }
    try {
      return $this->_db->selectCollection($coll_name);
    } catch(\Exception $e) {
      return null;
    }
  }
}