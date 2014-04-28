<?php
/**
 * The OCS Cache Engin test case.
 * 
 * Configure your a cache config named 'ocstest' in your_app/config/core.php or in the startCase() function before your start this test case.
 *
 * Require: SimpleTest 1.0.1
 *
 * PHP Version 5
 *
 * @copyright Copyright 2014, Murray Wang
 * @package ocs
 * @subpackage ocs.test.cases.libs
 * @since v 1.0 (29-Apr-2014)
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */

class OcsTest extends CakeTestCase {
  function startCase() {
    if (Cache::config('ocstest') == FALSE) {
      Cache::config('ocstest', array(
        'engine' => 'Ocs.Ocs',
        'duration' => 3600,
        'probability' => 0,
        'prefix' => Inflector::slug(APP_DIR) . '_', //[optional]  prefix every cache file with this string
        'servers' => array(
          array('your_instance.m.cnqdalicm9pub001.ocs.aliyuncs.com', 11211)
        ),
        'username' => 'your_instance',
        'password' => 'password'
      ));
    }
  }

  function testOcsCache() {
    $var = 'toboto/cakephp-ocscache';
    Cache::write('ocstestdata', $var, 'ocstest');
    $ocsData = Cache::read('ocstestdata', 'ocstest');
    $this->assertEqual($ocsData, $var);
  }
};
