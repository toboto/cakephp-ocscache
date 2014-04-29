<?php
/**
 * A CakePHP cache engine for Aliyun OCS(Open Cache Service) support.
 *
 * This cache engine is compatible with CakePHP 2.4.
 * The PHP client must be configured with Memcached module with SASL support.
 * 
 * Orignal implementation by toboto(Murray Wang) https://github.com/toboto
 *
 * Reference: https://drupal.org/node/2063811
 *
 * For Chinese developer, the references of Memcached with SASL are:
 *    CentOS: http://help.aliyun.com/view/11108324_13444498.html?spm=5176.7225337.1997284509.3.335dUr
 *    Ubuntu: http://bbs.aliyun.com/read/150127.html?spm=5176.7114037.1996646101.13.hLUJwA
 * 
 * Installation for CakePHP 1.3:
 *   cd your_cake_app/app/Plugin
 *   git clone git://github.com/toboto/cakephp-ocscache.git Ocs 
 *
 * Configuration sample in Config/bootstrap.php:
 *   CakePlugin::load('Ocs');
 *
 *   Cache::config('default', array(
 *     'engine' => 'Ocs.Ocs',
 *     'duration' => 3600,
 *     'probability' => 0, // [deprecated] The cache will not flush in this engine.
 *     'prefix' => 'myapp_ocscache_'
 *     'servers' => array(
 *       array('your_instance.m.cnqdalicm9pub001.ocs.aliyuncs.com', 11211)
 *       // The engine supports only one OCS instance because of the limit in OCS authentication.
 *     ),
 *     'username' => 'your_instance', 
 *     'password' => 'password'
 *   ));
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2014, Murray Wang
 * @package ocs
 * @subpackage ocs.libs.cache
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */
class OcsEngine extends CacheEngine {

  /**
   * Memcached wrapper.
   *
   * @var Memcached
   * @access private
   */
	var $__Memcached = null;

  /**
   * Settings
   *
   *  - servers = string or array of memcache servers, default => 127.0.0.1. If an
   *    array MemcachedEngine will use them as a pool.
   *  - compress = boolean, default => false
   *
   * @var array
   * @access public
   */
	var $settings = array();

  /**
   * Initialize the Cache Engine
   *
   * Called automatically by the cache frontend
   * To reinitialize the settings call Cache::engine('EngineName', [optional] settings = array());
   *
   * @param array $setting array of setting for the engine
   * @return boolean True if the engine has been successfully initialized, false if not
   * @access public
   */
	function init($settings = array()) {
		if (!class_exists('Memcached')) {
			return false;
		}
    else {
      if (!method_exists('Memcached', 'setSaslAuthData')) {
        return false;
      }
    }

    $settings += array(
			'engine'=> 'Memcached', 
			'prefix' => Inflector::slug(APP_DIR) . '_', 
			'servers' => array(array('127.0.0.1', 11211, 100)),
    );
    parent::init($settings);
		if (!isset($this->__Memcached)) {
			$rt = false;
			$this->__Memcached =& new Memcached();
      $this->__Memcached->setOption(Memcached::OPT_COMPRESSION, false);
      $this->__Memcached->setOption(Memcached::OPT_BINARY_PROTOCOL, true);
			foreach ($this->settings['servers'] as $server) {
        list($host, $port, $weight) = $this->_parseServerArray($server);
        if ($this->__Memcached->addServer($host, $port, $weight)) {
          $username = empty($this->settings['username']) ? '': $this->settings['username'];
          $password = empty($this->settings['password']) ? '': $this->settings['password'];
          $this->__Memcached->setSaslAuthData($username, $password);
          $rt = true;
        }
			}
			return $rt;
		}
		return false;
	}

  function _parseServerArray($server) {
    if (!is_array($server))
      $server = array($server);
    $cnt = count($server);

    $host = '127.0.0.1'; $port = 11211; $weight = 100;
    if ($cnt == 1) {
      $host = $server[0];
    }
    elseif ($cnt == 2) {
      $host = $server[0];
      $port = $server[1];
    }
    elseif ($cnt >= 3) {
      $host = $server[0];
      $port = $server[1];
      $weight = $server[2];
    }
    return array($host, $port, $weight);
  }

  /**
   * Write data for key into cache.  When using memcached as your cache engine
   * remember that the Memcache pecl extension does not support cache expiry times greater 
   * than 30 days in the future. Any duration greater than 30 days will be treated as never expiring.
   *
   * @param string $key Identifier for the data
   * @param mixed $value Data to be cached
   * @param integer $duration How long to cache the data, in seconds
   * @return boolean True if the data was succesfully cached, false on failure
   * @see http://php.net/manual/en/memcached.set.php
   * @access public
   */
	function write($key, $value, $duration) {
		if ($duration > 30 * DAY) {
			$duration = 0;
		}
		return $this->__Memcached->set($key, $value, $duration);
	}

  /**
   * Read a key from the cache
   *
   * @param string $key Identifier for the data
   * @return mixed The cached data, or false if the data doesn't exist, has expired, or if there was an error fetching it
   * @access public
   */
	function read($key) {
		return $this->__Memcached->get($key);
	}

  /**
   * Increments the value of an integer cached key
   *
   * @param string $key Identifier for the data
   * @param integer $offset How much to increment
   * @param integer $duration How long to cache the data, in seconds
   * @return New incremented value, false otherwise
   * @access public
   */
	function increment($key, $offset = 1) {
		return $this->__Memcached->increment($key, $offset);
	}

  /**
   * Decrements the value of an integer cached key
   *
   * @param string $key Identifier for the data
   * @param integer $offset How much to substract
   * @param integer $duration How long to cache the data, in seconds
   * @return New decremented value, false otherwise
   * @access public
   */
	function decrement($key, $offset = 1) {
		return $this->__Memcached->decrement($key, $offset);
	}

  /**
   * Delete a key from the cache
   *
   * @param string $key Identifier for the data
   * @return boolean True if the value was succesfully deleted, false if it didn't exist or couldn't be removed
   * @access public
   */
	function delete($key) {
		return $this->__Memcached->delete($key);
	}

  /**
   * Delete all keys from the cache
   *
   * @return boolean True if the cache was succesfully cleared, false otherwise
   * @access public
   */
	function clear($check) {
		return $this->__Memcached->flush();
	}
};
