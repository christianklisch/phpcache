<?php

/**
 * PHPCache - a PHP caching class
 *
 * @author      Christian Klisch <info@christian-klisch.de>
 * @copyright   2013 Christian Klisch
 * @link        https://github.com/christianklisch/phpcache
 * @license     https://github.com/christianklisch/phpcache/LICENSE
 * @version     0.1.0
 * @package     PHPCache
 *
 * APACHE LICENSE 
 * 
 * Copyright (c) 2013 Christian Klisch    
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *   
 * http://www.apache.org/licenses/LICENSE-2.0
 *   
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * PHPCache
 * @package PHPCache
 * @author  Christian Klisch
 * @since   0.1.0
 */
class PHPCache {

    /**
     * @var array
     */
    protected $settings;

    /**
     * @var array
     */
    protected $primarykeys;

    /*     * ******************************************************************************
     * Instantiation and Configuration
     * ***************************************************************************** */

    /**
     * Constructor
     * @param  array $settings Associative array of caching settings
     */
    public function __construct($settings = array()) {
	// Setup caching
	$this->settings = array_merge(static::getDefaultSettings(), $settings);
    }

    /**
     * Get default application settings
     * @return array
     */
    public static function getDefaultSettings() {
	return array(
	    // directory
	    'cacheDir' => 'cache',
	    // caching time in seconds
	    'cacheTime' => '60',
	    // Debugging
	    'debug' => true
	);
    }

    /**
     * Read and Write config
     * @return string     
     */
    public function getConfig($name) {
	return isset($this->settings[$name]) ? $this->settings[$name] : null;
    }

    /**
     * Setting get-functions for primary keys of classes
     * @param  array $primkeys Associative array of class primary-key-function
     */
    public function setPrimaryKeys($primkeys = array()) {
	$this->primarykeys = $primkeys;
    }

    /*     * ******************************************************************************
     * Logic functions
     * ***************************************************************************** */

    /**
     * get cached value in time or return value
     * @return object     
     */
    public function cacheVal($value, $id = null) {
	if ($id == null) {
	    $id = $this->getIDfromOjb($value);

	    if ($id == null && $this->getConfig('debug')) {
		echo "no caching id";
		return $value;
	    }
	}

	$cachefile = $this->getConfig('cacheDir') . '/' . $id;
	$cachetime = $this->getConfig('cacheTime');

	if (file_exists($cachefile) && time() - $cachetime < filemtime($cachefile)) {
	    $ser = file_get_contents($cachefile);
	    $val = unserialize($ser);
	    return $val;
	} else {
	    $ser = serialize($value);
	    file_put_contents($cachefile, $ser);
	    return $value;
	}

	if ($this->getConfig('debug'))
	    echo "no caching";
    }

    /**
     * cache function result and return it
     * @return object     
     */
    public function cacheFun($function) {
	if ($this->getConfig('debug') && !is_callable($function))
	    echo "no valid function";

	$r = new ReflectionFunction($function);
	$id = null;

	foreach ($r->getStaticVariables() as $key => $var) {
	    if ($key == 'key')
		$id = $var;
	}

	if (is_object($id))
	    $id = $this->getIDfromOjb($id);

	if ($id == null && $this->getConfig('debug')) {
	    echo "no caching id";
	    return $function();
	}

	$cachefile = $this->getConfig('cacheDir') . '/' . $id;
	$cachetime = $this->getConfig('cacheTime');

	if (file_exists($cachefile) && time() - $cachetime < filemtime($cachefile)) {
	    $ser = file_get_contents($cachefile);
	    $val = unserialize($ser);
	    return $val;
	} else {
	    $value = $function();
	    $ser = serialize($value);
	    file_put_contents($cachefile, $ser);
	    return $value;
	}

	if ($this->getConfig('debug'))
	    echo "no caching";
    }

    /**
     * check, if id or object in first parameter is cached. Returns true, if cached
     * @return bool
     */
    public function isCached($id) {
	if ($id != null) {
	    $id = $this->getIDfromOjb($id);
	}

	if ($id == null)
	    return false;

	$cachefile = $this->getConfig('cacheDir') . '/' . $id;
	$cachetime = $this->getConfig('cacheTime');

	if (file_exists($cachefile) && time() - $cachetime < filemtime($cachefile)) {
	    return true;
	}

	return false;
    }

    /**
     * get id from object $value
     * @return id     
     */
    private function getIDfromOjb($value) {
	$id = null;
	if ($this->primarykeys) {
	    foreach ($this->primarykeys as $key => $function) {
		if (is_subclass_of($value, $key) || is_a($value, $key)) {
		    $id = $value->$function();
		    break;
		}
	    }
	}
	return $id;
    }

    /**
     * delete all cached files 
     */
    public function clearCache() {
	$files = glob($this->getConfig('cacheDir') . '/*', GLOB_MARK);
	foreach ($files as $file) {
	    unlink($file);
	}
    }
}

?>