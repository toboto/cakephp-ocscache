# Aliyun OCS(Open Cache Service) Cache Engine for CakePHP 1.3

## Requirements

- PHP5
- CakePHP = 1.3

## Installation

	cd your_cake_app/app/plugins
	git clone git://github.com/toboto/cakephp-ocscache.git ocs 

## Configuration sample in config/core.php

	<?php
	Cache::config('default', array(
	  'engine' => 'Ocs.Ocs',
	  'duration' => 3600,
	  'probability' => 0, // [deprecated] The cache will not flush in this engine.
	  'prefix' => Inflector::slug(APP_DIR) . '_', //[optional]  prefix every cache file with this string
	  'servers' => array(
	    array('your_instance.m.cnqdalicm9pub001.ocs.aliyuncs.com', 11211)
	    // The engine supports only one OCS instance because of the limit in OCS authentication.
	  ),
	  'username' => 'your_instance', 
	  'password' => 'password'
	));

## Author
Murray Wang (toboto) http://weibo.com/tobotorui

email to wangrui@teeker.com

