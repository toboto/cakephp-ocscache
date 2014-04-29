<?php
/**
 * The OCS Cache Engin test case.
 * 
 * Configure your a cache config named 'ocstest' in your_app/Config/bootstrap.php.
 *
 * Require: PHPUnit 3.7
 *
 * PHP Version 5
 *
 * @copyright Copyright 2014, Murray Wang
 * @package ocs
 * @subpackage ocs.tests.cases.libs
 * @since v 1.0 (29-Apr-2014)
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */

class OcsTestData {
  var $param1 = null;
  var $param2 = null;

  function __construct() {
    $param1 = date('Y-m-d');
    $param2 = date('H:i:s');
  }
}

class OcsEngineTest extends CakeTestCase {

  function testOcsCacheConfig() {
    $config = Cache::config('ocstest');
    $this->assertTrue(!empty($config));
  }

  /**
   * @depends testOcsCacheConfig
   */
  function testOcsCacheString() {
    $var = 'toboto/cakephp-ocscache';
    Cache::write('ocstestdata', $var, 'ocstest');
    $ocsData = Cache::read('ocstestdata', 'ocstest');
    $this->assertEqual($ocsData, $var);
  }

  /**
   * @depends testOcsCacheConfig
   */
  function testOcsCacheArray() {
    $var = array('a' => 'first', 'b' => 'second');
    Cache::write('ocstestdata', $var, 'ocstest');
    $ocsData = Cache::read('ocstestdata', 'ocstest');
    $this->assertEqual($ocsData, $var);
  }

  /**
   * @depends testOcsCacheConfig
   */
  function testOcsCacheObject() {
    $var = new OcsTestData();
    Cache::write('ocstestdata', $var, 'ocstest');
    $ocsData = Cache::read('ocstestdata', 'ocstest');
    $this->assertEqual($ocsData, $var);
  }
}
